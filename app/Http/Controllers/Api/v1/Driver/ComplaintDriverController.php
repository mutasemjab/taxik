<?php


namespace App\Http\Controllers\Api\v1\Driver;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\Order;
use App\Traits\Responses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ComplaintDriverController extends Controller
{
    use Responses;

    public function index(Request $request)
    {
        $driver = Auth::guard('driver-api')->user();
        
        $validator = Validator::make($request->all(), [
            'status' => 'sometimes|in:1,2,3',
            'type' => 'sometimes|in:by_me,against_me,all',
            'per_page' => 'sometimes|integer|min:5|max:100',
            'sort_by' => 'sometimes|in:date,status',
            'sort_direction' => 'sometimes|in:asc,desc'
        ]);
        
        if ($validator->fails()) {
            return $this->error_response('Validation error', $validator->errors());
        }
        
        $type = $request->type ?? 'all';
        
        if ($type == 'by_me') {
            // Complaints created by the driver
            $query = Complaint::where('driver_id', $driver->id)
                ->whereNull('user_id');
        } elseif ($type == 'against_me') {
            // Complaints against the driver
            $query = Complaint::where('driver_id', $driver->id)
                ->whereNotNull('user_id');
        } else {
            // All complaints related to the driver
            $query = Complaint::where('driver_id', $driver->id);
        }
        
        // Filter by status if provided
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        // Apply sorting
        $sortBy = $request->sort_by ?? 'created_at';
        $sortDirection = $request->sort_direction ?? 'desc';
        
        if ($sortBy === 'date') {
            $sortBy = 'created_at';
        }
        
        $query->orderBy($sortBy, $sortDirection);
        
        // Pagination
        $perPage = $request->per_page ?? 15;
        $complaints = $query->paginate($perPage);
        
        // Transform data to include status name
        $complaints->getCollection()->transform(function ($complaint) {
            $complaint->status_name = $complaint->getStatusNameAttribute();
            return $complaint;
        });
        
        $responseData = [
            'complaints' => $complaints,
            'meta' => [
                'current_page' => $complaints->currentPage(),
                'last_page' => $complaints->lastPage(),
                'per_page' => $complaints->perPage(),
                'total' => $complaints->total()
            ]
        ];
        
        return $this->success_response('Complaints retrieved successfully', $responseData);
    }
}
