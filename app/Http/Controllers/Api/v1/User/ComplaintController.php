<?php


namespace App\Http\Controllers\Api\v1\User;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\Order;
use App\Models\Setting;
use App\Traits\Responses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ComplaintController extends Controller
{
    use Responses;

    public function index(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'status' => 'sometimes|in:1,2,3',
            'per_page' => 'sometimes|integer|min:5|max:100',
            'sort_by' => 'sometimes|in:date,status',
            'sort_direction' => 'sometimes|in:asc,desc'
        ]);

        if ($validator->fails()) {
            return $this->error_response('Validation error', $validator->errors());
        }

        $query = Complaint::where('user_id', $user->id);

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
            $complaint->status_name = $complaint->getStatusLabelAttribute();
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

    /**
     * Store a newly created complaint
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'order_id' => 'sometimes|exists:orders,id',
            'driver_id' => 'sometimes|exists:drivers,id'
        ]);

        if ($validator->fails()) {
            return $this->error_response('Validation error', $validator->errors());
        }

        // If order_id is provided, verify it belongs to the user
        if ($request->has('order_id')) {
            $order = Order::find($request->order_id);

            if (!$order || $order->user_id != $user->id) {
                return $this->error_response('Order not found or does not belong to you', null);
            }

            // If driver_id is not provided, get it from the order
            if (!$request->has('driver_id') && $order->driver_id) {
                $request->merge(['driver_id' => $order->driver_id]);
            }
        }

        $complaint = new Complaint();
        $complaint->subject = $request->subject;
        $complaint->description = $request->description;
        $complaint->user_id = $user->id;
        $complaint->order_id = $request->order_id;
        $complaint->driver_id = $request->driver_id;
        $complaint->status = Complaint::STATUS_PENDING;
        $complaint->save();

        // Add status name to response
        $complaint->status_name = $complaint->getStatusLabelAttribute();

        return $this->success_response('Complaint submitted successfully', $complaint);
    }

    /**
     * Display the specified complaint
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = Auth::user();

        $complaint = Complaint::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$complaint) {
            return $this->error_response('Complaint not found', null);
        }

        // Add status name and load relationships
        $complaint->status_name = $complaint->getStatusLabelAttribute();
        $complaint->load(['driver:id,name,phone,photo', 'order:id,order_number,created_at,total']);

        return $this->success_response('Complaint details retrieved successfully', $complaint);
    }

    /**
     * Update the specified complaint
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();

        $complaint = Complaint::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$complaint) {
            return $this->error_response('Complaint not found', null);
        }

        // Only allow updates if the complaint is still pending
        if ($complaint->status != Complaint::STATUS_PENDING) {
            return $this->error_response('Cannot update complaint that is already being processed', null);
        }

        $validator = Validator::make($request->all(), [
            'subject' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string'
        ]);

        if ($validator->fails()) {
            return $this->error_response('Validation error', $validator->errors());
        }

        if ($request->has('subject')) {
            $complaint->subject = $request->subject;
        }

        if ($request->has('description')) {
            $complaint->description = $request->description;
        }

        $complaint->save();

        // Add status name to response
        $complaint->status_name = $complaint->getStatusLabelAttribute();

        return $this->success_response('Complaint updated successfully', $complaint);
    }

    /**
     * Remove the specified complaint
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = Auth::user();

        $complaint = Complaint::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$complaint) {
            return $this->error_response('Complaint not found', null);
        }

        // Only allow deletion if the complaint is still pending
        if ($complaint->status != Complaint::STATUS_PENDING) {
            return $this->error_response('Cannot delete complaint that is already being processed', null);
        }

        $complaint->delete();

        return $this->success_response('Complaint deleted successfully', null);
    }
}
