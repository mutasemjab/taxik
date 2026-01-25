<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WalletDistribution;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WalletDistributionController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:distribution-table', ['only' => ['index']]);
        $this->middleware('permission:distribution-add', ['only' => ['create', 'store']]);
        $this->middleware('permission:distribution-edit', ['only' => ['edit', 'update', 'toggleActivate']]);
        $this->middleware('permission:distribution-delete', ['only' => ['destroy']]);
    }

    /**
     * عرض قائمة التوزيعات
     */
    public function index()
    {
        $distributions = WalletDistribution::latest()->paginate(10);
        
        // التحقق من تفعيل النظام
        $systemEnabled = DB::table('settings')
            ->where('key', 'enable_wallet_distribution_system')
            ->value('value') == 1;

        return view('admin.wallet-distributions.index', compact('distributions', 'systemEnabled'));
    }

    /**
     * عرض صفحة إضافة توزيع جديد
     */
    public function create()
    {
        return view('admin.wallet-distributions.create');
    }

    /**
     * حفظ توزيع جديد
     */
    public function store(Request $request)
    {
        $request->validate([
            'total_amount' => 'required|numeric|min:0.01',
            'number_of_orders' => 'required|integer|min:1',
        ], [
            'total_amount.required' => __('messages.total_amount_required'),
            'total_amount.numeric' => __('messages.total_amount_numeric'),
            'total_amount.min' => __('messages.total_amount_min'),
            'number_of_orders.required' => __('messages.number_of_orders_required'),
            'number_of_orders.integer' => __('messages.number_of_orders_integer'),
            'number_of_orders.min' => __('messages.number_of_orders_min'),
        ]);

        $distribution = WalletDistribution::create([
            'total_amount' => $request->total_amount,
            'number_of_orders' => $request->number_of_orders,
            'activate' => $request->has('activate') ? 1 : 0,
        ]);

        // إذا كان التوزيع مفعل، طبقه على جميع المستخدمين
        if ($distribution->activate == 1) {
            // تعطيل كل التوزيعات الأخرى
            WalletDistribution::where('id', '!=', $distribution->id)->update(['activate' => 0]);
            
            // تطبيق التوزيع على جميع المستخدمين
            $distribution->applyToAllUsers();
        }

        return redirect()->route('wallet-distributions.index')
            ->with('success', __('messages.distribution_created_successfully'));
    }

    /**
     * عرض صفحة تعديل التوزيع
     */
    public function edit($id)
    {
        $distribution = WalletDistribution::findOrFail($id);
        return view('admin.wallet-distributions.edit', compact('distribution'));
    }

    /**
     * تحديث التوزيع
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'total_amount' => 'required|numeric|min:0.01',
            'number_of_orders' => 'required|integer|min:1',
        ], [
            'total_amount.required' => __('messages.total_amount_required'),
            'total_amount.numeric' => __('messages.total_amount_numeric'),
            'total_amount.min' => __('messages.total_amount_min'),
            'number_of_orders.required' => __('messages.number_of_orders_required'),
            'number_of_orders.integer' => __('messages.number_of_orders_integer'),
            'number_of_orders.min' => __('messages.number_of_orders_min'),
        ]);

        $distribution = WalletDistribution::findOrFail($id);
        
        $distribution->update([
            'total_amount' => $request->total_amount,
            'number_of_orders' => $request->number_of_orders,
            'activate' => $request->has('activate') ? 1 : 0,
        ]);

        // إذا كان التوزيع مفعل، طبقه على جميع المستخدمين
        if ($distribution->activate == 1) {
            // تعطيل كل التوزيعات الأخرى
            WalletDistribution::where('id', '!=', $distribution->id)->update(['activate' => 0]);
            
            // تطبيق التوزيع على جميع المستخدمين
            $distribution->applyToAllUsers();
        } else {
            // إذا تم تعطيل التوزيع، احذفه من جميع المستخدمين
            WalletDistribution::removeFromAllUsers();
        }

        return redirect()->route('wallet-distributions.index')
            ->with('success', __('messages.distribution_updated_successfully'));
    }

    /**
     * حذف التوزيع
     */
    public function destroy($id)
    {
        $distribution = WalletDistribution::findOrFail($id);
        
        // إذا كان التوزيع مفعل، احذفه من جميع المستخدمين
        if ($distribution->activate == 1) {
            WalletDistribution::removeFromAllUsers();
        }
        
        $distribution->delete();

        return redirect()->route('wallet-distributions.index')
            ->with('success', __('messages.distribution_deleted_successfully'));
    }

    /**
     * تفعيل/تعطيل توزيع محدد
     */
    public function toggleActivate($id)
    {
        $distribution = WalletDistribution::findOrFail($id);
        
        // عكس الحالة
        $newStatus = $distribution->activate == 1 ? 0 : 1;
        
        if ($newStatus == 1) {
            // تعطيل كل التوزيعات الأخرى
            WalletDistribution::where('id', '!=', $id)->update(['activate' => 0]);
            
            // تطبيق التوزيع على جميع المستخدمين
            $distribution->activate = 1;
            $distribution->save();
            $distribution->applyToAllUsers();
            
            $message = __('messages.distribution_activated_successfully');
        } else {
            // تعطيل التوزيع
            $distribution->activate = 0;
            $distribution->save();
            
            // إزالة التوزيع من جميع المستخدمين
            WalletDistribution::removeFromAllUsers();
            
            $message = __('messages.distribution_deactivated_successfully');
        }

        return redirect()->route('wallet-distributions.index')
            ->with('success', $message);
    }

    /**
     * تفعيل/تعطيل النظام بالكامل
     */
    public function toggleSystem(Request $request)
    {
        $enabled = $request->input('enabled', 0);
        
        DB::table('settings')
            ->updateOrInsert(
                ['key' => 'enable_wallet_distribution_system'],
                ['value' => $enabled, 'updated_at' => now()]
            );
        
        if ($enabled == 0) {
            // إذا تم تعطيل النظام، احذف التوزيع من جميع المستخدمين
            WalletDistribution::removeFromAllUsers();
        } else {
            // إذا تم تفعيل النظام، طبق التوزيع المفعل (إن وجد)
            $activeDistribution = WalletDistribution::where('activate', 1)->first();
            if ($activeDistribution) {
                $activeDistribution->applyToAllUsers();
            }
        }

        $message = $enabled == 1 
            ? __('messages.distribution_system_enabled') 
            : __('messages.distribution_system_disabled');

        return redirect()->route('wallet-distributions.index')
            ->with('success', $message);
    }
}