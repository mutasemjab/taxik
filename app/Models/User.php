<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
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

    public function setWalletDistribution($totalAmount, $numberOfOrders)
    {
        if ($totalAmount <= 0 || $numberOfOrders <= 0) {
            return false;
        }

        // حساب المبلغ لكل رحلة
        $amountPerOrder = $totalAmount / $numberOfOrders;

        // تقريب إلى رقمين عشريين
        $amountPerOrder = round($amountPerOrder, 2);

        $this->wallet_amount_per_order = $amountPerOrder;
        $this->wallet_orders_remaining = $numberOfOrders;
        $this->save();

        \Log::info("Wallet distribution set for user {$this->id}: {$amountPerOrder} JD per order for {$numberOfOrders} orders");

        return true;
    }
    /**
     * الحصول على المبلغ المتاح من المحفظة للرحلة الحالية
     */
    public function getAvailableWalletAmountForOrder()
    {
        // التحقق من تفعيل نظام التوزيع من الإعدادات
        $distributionEnabled = \DB::table('settings')
            ->where('key', 'enable_wallet_distribution_system')
            ->value('value') == 1;

        // إذا النظام مفعل وهناك رحلات متبقية ومبلغ محدد
        if ($distributionEnabled && $this->wallet_orders_remaining > 0 && $this->wallet_amount_per_order > 0) {
            // لا نتجاوز الرصيد المتاح
            return min($this->wallet_amount_per_order, $this->balance);
        }

        // إذا النظام معطل أو انتهى التوزيع، نستخدم كامل الرصيد
        return $this->balance;
    }
    /**
     * تقليل عداد الرحلات المتبقية بعد استخدام المحفظة
     */
    public function decrementWalletOrdersRemaining()
    {
        if ($this->wallet_orders_remaining > 0) {
            $this->wallet_orders_remaining--;

            // إذا وصلنا للصفر، نعيد تعيين القيم
            if ($this->wallet_orders_remaining == 0) {
                $this->wallet_amount_per_order = 0;
            }

            $this->save();

            \Log::info("Wallet orders remaining decremented for user {$this->id}. Remaining: {$this->wallet_orders_remaining}");
        }
    }

    /**
     * إلغاء نظام التوزيع والرجوع للوضع الطبيعي
     */
    public function cancelWalletDistribution()
    {
        $this->wallet_amount_per_order = 0;
        $this->wallet_orders_remaining = 0;
        $this->save();

        \Log::info("Wallet distribution cancelled for user {$this->id}");
    }
}
