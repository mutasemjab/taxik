<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UserChallengeProgress;
use Carbon\Carbon;

class CheckExpiredChallenges extends Command
{
    protected $signature = 'challenges:check-expired';
    protected $description = 'Check and mark expired challenges as inactive';

    public function handle()
    {
        $expiredProgress = UserChallengeProgress::whereHas('challenge', function($q) {
            $q->where('end_date', '<', Carbon::now())
              ->where('is_active', true);
        })->where('is_completed', false)->get();

        foreach ($expiredProgress as $progress) {
            $progress->challenge->update(['is_active' => false]);
        }

        $this->info('Expired challenges checked successfully!');
    }
}