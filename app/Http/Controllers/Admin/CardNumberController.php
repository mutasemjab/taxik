<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\User;
use App\Models\Driver;
use App\Models\Order;
use Illuminate\Support\Facades\Validator;

use App\Models\Card;
use App\Models\CardNumber;
use App\Models\POS;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CardNumberController extends Controller
{
     public function toggleStatus(CardNumber $cardNumber)
    {
        try {
            $newStatus = $cardNumber->status == CardNumber::STATUS_USED 
                ? CardNumber::STATUS_NOT_USED 
                : CardNumber::STATUS_USED;
            
            $cardNumber->update(['status' => $newStatus]);
            
            $message = $newStatus == CardNumber::STATUS_USED 
                ? __('messages.card_number_marked_used')
                : __('messages.card_number_marked_unused');
            
            return redirect()->back()->with('success', $message);
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', __('messages.error_updating_status') . ': ' . $e->getMessage());
        }
    }

    /**
     * Toggle the activate status of a card number (active/inactive)
     */
    public function toggleActivate(CardNumber $cardNumber)
    {
        try {
            $newActivate = $cardNumber->activate == CardNumber::ACTIVATE_ACTIVE 
                ? CardNumber::ACTIVATE_INACTIVE 
                : CardNumber::ACTIVATE_ACTIVE;
            
            $cardNumber->update(['activate' => $newActivate]);
            
            $message = $newActivate == CardNumber::ACTIVATE_ACTIVE 
                ? __('messages.card_number_activated')
                : __('messages.card_number_deactivated');
            
            return redirect()->back()->with('success', $message);
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', __('messages.error_updating_activate') . ': ' . $e->getMessage());
        }
    }
}