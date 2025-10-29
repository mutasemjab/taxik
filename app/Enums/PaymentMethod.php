<?php
namespace App\Enums;


enum PaymentMethod: string
{
    case Cash = 'cash';
    case Visa = 'visa';
    case Wallet = 'wallet';
}
