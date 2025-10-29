<?php
namespace App\Enums;


enum StatusPayment: string
{
    case Pending = 'pending';
    case Paid = 'paid';
}