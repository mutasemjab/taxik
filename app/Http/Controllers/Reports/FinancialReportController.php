<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\DriverRegistrationPayment;
use App\Models\CardUsage;
use App\Models\FinancialReport;
use App\Models\POS;
use App\Models\WalletTransaction;
use App\Models\WithdrawalRequest;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class FinancialReportController extends Controller
{
    public function index(Request $request)
    {
        $drivers = Driver::select('id', 'name', 'phone')->orderBy('name')->get();
        
        $report = null;
        
        if ($request->has('start_date') && $request->has('end_date')) {
            $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'driver_id' => 'nullable|exists:drivers,id'
            ]);
            
            $startDate = Carbon::parse($request->start_date)->startOfDay();
            $endDate = Carbon::parse($request->end_date)->endOfDay();
            
            // استخدام FinancialReport Model
            $report = FinancialReport::getDriversFinancialReport($startDate, $endDate, $request->driver_id);
        }
        
        return view('reports.financial-reports.index', compact('drivers', 'report'));
    }
    
    /**
     * عرض تفاصيل السائق المالية
     */
    public function driverDetails($driverId, Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);
        
        $startDate = Carbon::parse($request->start_date)->startOfDay();
        $endDate = Carbon::parse($request->end_date)->endOfDay();
        
        // جلب بيانات السائق
        $driver = Driver::findOrFail($driverId);
        
        // جلب التفاصيل المالية للسائق
        $driversReport = FinancialReport::getDriversFinancialReport($startDate, $endDate, $driverId);
        $details = $driversReport['drivers'][0]; // أول عنصر لأننا نطلب سائق واحد
        
        // جلب دفعات التسجيل
        $registrationPayments = DriverRegistrationPayment::where('driver_id', $driverId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->with('admin')
            ->orderBy('created_at', 'desc')
            ->get();
        
        // جلب استخدامات البطاقات
        $cardUsages = CardUsage::where('driver_id', $driverId)
            ->whereBetween('used_at', [$startDate, $endDate])
            ->with(['cardNumber.card.pos'])
            ->orderBy('used_at', 'desc')
            ->get();
        
        // جلب معاملات المحفظة
        $walletTransactions = WalletTransaction::where('driver_id', $driverId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->with('admin')
            ->orderBy('created_at', 'desc')
            ->get();
        
        // جلب طلبات السحب
        $withdrawalRequests = WithdrawalRequest::where('driver_id', $driverId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->with('admin')
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('reports.financial-reports.driver-details', compact(
            'driver',
            'details',
            'registrationPayments',
            'cardUsages',
            'walletTransactions',
            'withdrawalRequests'
        ));
    }
    
    public function posReport(Request $request)
    {
        $posPoints = POS::select('id', 'name', 'phone')->orderBy('name')->get();
        
        $report = null;
        
        if ($request->has('start_date') && $request->has('end_date')) {
            $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'pos_id' => 'nullable|exists:p_o_s,id'
            ]);
            
            $startDate = Carbon::parse($request->start_date)->startOfDay();
            $endDate = Carbon::parse($request->end_date)->endOfDay();
            
            // استخدام FinancialReport Model
            $report = FinancialReport::getPOSFinancialReport($startDate, $endDate, $request->pos_id);
        }
        
        return view('reports.financial-reports.pos-report', compact('posPoints', 'report'));
    }
    
  
    
    /**
     * تصدير تقرير السائقين إلى Excel
     */
    public function exportDriversReport(Request $request)
    {
        // سيتم إضافة كود التصدير لاحقاً
        return redirect()->back()->with('info', 'Export feature coming soon');
    }
    
    /**
     * تصدير تقرير السائقين إلى PDF
     */
    public function exportDriversReportPDF(Request $request)
    {
        // سيتم إضافة كود التصدير لاحقاً
        return redirect()->back()->with('info', 'PDF export feature coming soon');
    }
    
    /**
     * تصدير تقرير نقاط البيع إلى Excel
     */
    public function exportPOSReport(Request $request)
    {
        // سيتم إضافة كود التصدير لاحقاً
        return redirect()->back()->with('info', 'Export feature coming soon');
    }
}