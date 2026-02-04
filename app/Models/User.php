<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\HasApiTokens;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, LogsActivity;

    protected $guarded = ['id'];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'app_credit' => 'decimal:2',
        'app_credit_amount_per_order' => 'decimal:2',
        'app_credit_orders_remaining' => 'integer',
        'wallet_amount_per_order' => 'decimal:2',
        'wallet_orders_remaining' => 'integer',
    ];

    // Append the photo_url attribute to JSON responses
    protected $appends = ['photo_url'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*']) // Log all attributes, or specify: ['name', 'price', 'number_of_cards']
            ->logOnlyDirty() // Only log changed attributes
            ->dontSubmitEmptyLogs()
            ->useLogName('user') // Custom log name
            ->setDescriptionForEvent(fn(string $eventName) => "User has been {$eventName}");
    }

    public function walletTransactions()
    {
        return $this->hasMany(WalletTransaction::class);
    }

    // Add a custom accessor for the photo URL
    public function getPhotoUrlAttribute()
    {
        if ($this->photo) {
            // Use the APP_URL from the .env file
            $baseUrl = rtrim(config('app.url'), '/');
            return $baseUrl . '/assets/admin/uploads/' . $this->photo;
        }

        return null;
    }

    public function addBalance($amount, $note = null, $adminId = null, $userId = null)
    {
        $this->increment('balance', $amount);

        return $this->walletTransactions()->create([
            'user_id' => $userId,
            'admin_id' => $adminId,
            'amount' => $amount,
            'type_of_transaction' => WalletTransaction::TYPE_ADD,
            'note' => $note,
        ]);
    }

    public function bans()
    {
        return $this->hasMany(UserBan::class);
    }

    public function activeBan()
    {
        return $this->hasOne(UserBan::class)->where('is_active', true)->latest();
    }

    /**
     * Check if user is currently banned
     */
    public function isBanned()
    {
        $activeBan = $this->activeBan;

        if (!$activeBan) {
            return false;
        }

        // Check if temporary ban has expired
        if (!$activeBan->is_permanent && $activeBan->isExpired()) {
            // Auto-unban if expired
            $this->unbanUser(null, 'Automatic unban - ban period expired');
            return false;
        }

        return true;
    }

    /**
     * Ban the user
     */
    public function banUser($adminId, $reason, $description = null, $banUntil = null, $isPermanent = false)
    {
        // Deactivate any existing active bans
        $this->bans()->where('is_active', true)->update(['is_active' => false]);

        // Create new ban
        $ban = $this->bans()->create([
            'admin_id' => $adminId,
            'ban_reason' => $reason,
            'ban_description' => $description,
            'banned_at' => Carbon::now(),
            'ban_until' => $banUntil,
            'is_permanent' => $isPermanent,
            'is_active' => true,
        ]);

        // Update user status to banned (2)
        $this->update(['activate' => 2]);

        return $ban;
    }

    /**
     * Unban the user
     */
    public function unbanUser($adminId = null, $reason = null)
    {
        $activeBan = $this->activeBan;

        if ($activeBan) {
            $activeBan->update([
                'is_active' => false,
                'unbanned_at' => Carbon::now(),
                'unbanned_by' => $adminId,
                'unban_reason' => $reason,
            ]);
        }

        // Update user status to active (1)
        $this->update(['activate' => 1]);

        return true;
    }

    /**
     * Relationships
     */
    public function challengeProgress()
    {
        return $this->hasMany(UserChallengeProgress::class);
    }

    public function completedChallenges()
    {
        return $this->hasMany(UserChallengeProgress::class)->where('is_completed', true);
    }

    /**
     * Get or create progress for a challenge
     */
    public function getChallengeProgress($challengeId)
    {
        return $this->challengeProgress()
            ->firstOrCreate(
                ['challenge_id' => $challengeId],
                ['current_count' => 0, 'is_completed' => false]
            );
    }

    /**
     * Update challenge progress
     */
    public function updateChallengeProgress($challengeType, $amount = 1)
    {
        $challenges = Challenge::active()->ofType($challengeType)->get();

        foreach ($challenges as $challenge) {
            $progress = $this->getChallengeProgress($challenge->id);

            // Skip if user has completed max times
            if ($progress->times_completed >= $challenge->max_completions_per_user) {
                continue;
            }

            $progress->incrementProgress($amount);
        }
    }

    // ========== رصيد التطبيق (App Credit) Methods ==========
    
    /**
     * الحصول على المبلغ المتاح من رصيد التطبيق للرحلة الحالية
     */
    public function getAvailableAppCreditForOrder()
    {
        // التحقق من تفعيل نظام التوزيع
        $distributionEnabled = DB::table('settings')
            ->where('key', 'enable_app_credit_distribution_system')
            ->value('value') == 1;
        
        // إذا لم يكن النظام مفعل، لا يوجد رصيد متاح
        if (!$distributionEnabled) {
            return 0;
        }
        
        // إذا لم يكن هناك رحلات متبقية، لا يوجد رصيد متاح
        if ($this->app_credit_orders_remaining <= 0) {
            return 0;
        }
        
        // إذا لم يكن هناك مبلغ محدد لكل رحلة، لا يوجد رصيد متاح
        if ($this->app_credit_amount_per_order <= 0) {
            return 0;
        }
        
        // التحقق من أن الرصيد الفعلي كافي
        $requiredAmount = min($this->app_credit_amount_per_order, $this->app_credit);
        
        return $requiredAmount;
    }
    
    /**
     * تقليل عداد الرحلات المتبقية في رصيد التطبيق
     */
    public function decrementAppCreditOrdersRemaining()
    {
        if ($this->app_credit_orders_remaining > 0) {
            $this->decrement('app_credit_orders_remaining');
            \Log::info("User {$this->id}: App credit orders remaining decremented to {$this->app_credit_orders_remaining}");
        }
    }
    
    /**
     * تطبيق توزيع رصيد التطبيق على المستخدم
     */
    public function applyAppCreditDistribution($totalAmount, $numberOfOrders, $adminId = null)
    {
        $amountPerOrder = $numberOfOrders > 0 ? $totalAmount / $numberOfOrders : 0;
        
        $this->app_credit = $totalAmount;
        $this->app_credit_amount_per_order = $amountPerOrder;
        $this->app_credit_orders_remaining = $numberOfOrders;
        $this->save();
        
        // تسجيل الحركة
        DB::table('app_credit_transactions')->insert([
            'user_id' => $this->id,
            'admin_id' => $adminId,
            'amount' => $totalAmount,
            'type_of_transaction' => 1, // إضافة
            'note' => "إضافة رصيد تطبيق: {$totalAmount} JD موزع على {$numberOfOrders} رحلة ({$amountPerOrder} JD لكل رحلة)",
            'orders_remaining_before' => 0,
            'orders_remaining_after' => $numberOfOrders,
            'amount_per_order' => $amountPerOrder,
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        \Log::info("User {$this->id}: App credit distribution applied - {$totalAmount} JD for {$numberOfOrders} orders");
    }
    
    /**
     * إزالة توزيع رصيد التطبيق من المستخدم
     */
    public function removeAppCreditDistribution()
    {
        $oldCredit = $this->app_credit;
        
        $this->app_credit = 0;
        $this->app_credit_amount_per_order = 0;
        $this->app_credit_orders_remaining = 0;
        $this->save();
        
        if ($oldCredit > 0) {
            // تسجيل الحركة
            DB::table('app_credit_transactions')->insert([
                'user_id' => $this->id,
                'amount' => $oldCredit,
                'type_of_transaction' => 2, // سحب
                'note' => "إزالة توزيع رصيد التطبيق",
                'orders_remaining_before' => $this->app_credit_orders_remaining,
                'orders_remaining_after' => 0,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
        
        \Log::info("User {$this->id}: App credit distribution removed");
    }

    // ========== المحفظة الحقيقية (Real Wallet) Methods - بدون تأثر بالتوزيع ==========
    
    /**
     * الحصول على رصيد المحفظة الحقيقية (بدون تأثير التوزيع)
     */
    public function getRealWalletBalance()
    {
        return $this->balance;
    }
    
    // ========== Relations ==========
    
    public function orders()
    {
        return $this->hasMany(Order::class);
    }
    
    public function appCreditTransactions()
    {
        return $this->hasMany(AppCreditTransaction::class);
    }
}
