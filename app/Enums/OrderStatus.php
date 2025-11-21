<?php
namespace App\Enums;


enum OrderStatus: string
{
    case Pending = 'pending';
    case DriverAccepted = 'accepted';
    case DriverGoToUser = 'on_the_way';
    case UserWithDriver = 'started';
    case waitingPayment = 'waiting_payment';
    case Delivered = 'completed';
    case UserCancelOrder = 'user_cancel_order';
    case DriverCancelOrder = 'driver_cancel_order';
    case Arrived = 'arrived';
    case CancelCronJob = 'cancel_cron_job'; // NEW

}
