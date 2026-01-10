<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class UserBan extends Model
{
    use HasFactory;

    const BAN_REASONS = [
        'violation_terms' => [
            'en' => 'Violation of Terms and Conditions',
            'ar' => 'انتهاك الشروط والأحكام'
        ],
        'poor_behavior' => [
            'en' => 'Poor Behavior',
            'ar' => 'سلوك سيء'
        ],
        'fraud' => [
            'en' => 'Fraudulent Activity',
            'ar' => 'نشاط احتيالي'
        ],
        'multiple_complaints' => [
            'en' => 'Multiple Complaints',
            'ar' => 'شكاوى متعددة'
        ],
        'payment_issues' => [
            'en' => 'Payment Issues',
            'ar' => 'مشاكل الدفع'
        ],
        'spam' => [
            'en' => 'Spam or Abuse',
            'ar' => 'إساءة استخدام'
        ],
        'unprofessional_conduct' => [
            'en' => 'Unprofessional Conduct',
            'ar' => 'سلوك غير مهني'
        ],
        'other' => [
            'en' => 'Other',
            'ar' => 'أخرى'
        ],
    ];

    protected $fillable = [
        'user_id',
        'admin_id',
        'ban_reason',
        'ban_description',
        'banned_at',
        'ban_until',
        'is_permanent',
        'is_active',
        'unbanned_at',
        'unbanned_by',
        'unban_reason',
    ];

    protected $casts = [
        'banned_at' => 'datetime',
        'ban_until' => 'datetime',
        'unbanned_at' => 'datetime',
        'is_permanent' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Relationships
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    public function unbannedByAdmin()
    {
        return $this->belongsTo(Admin::class, 'unbanned_by');
    }

    /**
     * Check if ban is expired
     */
    public function isExpired()
    {
        if ($this->is_permanent) {
            return false;
        }

        if ($this->ban_until && Carbon::now()->greaterThan($this->ban_until)) {
            return true;
        }

        return false;
    }

    /**
     * Get ban status text
     */
    public function getStatusText($lang = 'en')
    {
        if (!$this->is_active) {
            return $lang === 'ar' ? 'تم الرفع' : 'Lifted';
        }

        if ($this->is_permanent) {
            return $lang === 'ar' ? 'دائم' : 'Permanent';
        }

        if ($this->isExpired()) {
            return $lang === 'ar' ? 'منتهي' : 'Expired';
        }

        return $lang === 'ar' ? 'نشط' : 'Active';
    }

    /**
     * Get ban reason text
     */
    public function getReasonText($lang = 'en')
    {
        if (isset(self::BAN_REASONS[$this->ban_reason])) {
            return self::BAN_REASONS[$this->ban_reason][$lang] ?? self::BAN_REASONS[$this->ban_reason]['en'];
        }
        return $this->ban_reason;
    }

    /**
     * Get remaining ban time
     */
    public function getRemainingTime($lang = 'en')
    {
        if ($this->is_permanent) {
            return $lang === 'ar' ? 'دائم' : 'Permanent';
        }

        if (!$this->ban_until) {
            return $lang === 'ar' ? 'غير متاح' : 'N/A';
        }

        if ($this->isExpired()) {
            return $lang === 'ar' ? 'منتهي' : 'Expired';
        }

        return Carbon::now()->diffForHumans($this->ban_until, true);
    }

    /**
     * Scope for active bans
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for permanent bans
     */
    public function scopePermanent($query)
    {
        return $query->where('is_permanent', true);
    }

    /**
     * Scope for temporary bans
     */
    public function scopeTemporary($query)
    {
        return $query->where('is_permanent', false);
    }
}