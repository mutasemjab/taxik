<?php

namespace App\Jobs;

use App\Http\Controllers\Admin\FCMController as AdminFCMController;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Http\Controllers\FCMController;
use App\Models\Notification;

class SendBulkNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $title;
    public $body;
    public $type;
    public $notificationId;

    /**
     * Create a new job instance.
     */
    public function __construct($title, $body, $type, $notificationId)
    {
        $this->title = $title;
        $this->body = $body;
        $this->type = $type;
        $this->notificationId = $notificationId;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        // Send notification via FCM
        AdminFCMController::sendMessageToAll($this->title, $this->body);
        
        // Optionally update notification status
        $notification = Notification::find($this->notificationId);
        if ($notification) {
            $notification->update(['sent' => true]);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception)
    {
        // Log the error or update notification status
        \Log::error('Failed to send notification: ' . $exception->getMessage());
    }
}