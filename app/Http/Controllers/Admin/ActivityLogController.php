<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    /**
     * Display logs for a specific model
     */
    public function show($modelClass, $modelId)
    {
        $model = "App\\Models\\{$modelClass}"::findOrFail($modelId);
        
        $logs = Activity::forSubject($model)
            ->with('causer')
            ->latest()
            ->paginate(20);

        return view('admin.activity-logs.show', compact('logs', 'model'));
    }

    /**
     * Display all logs
     */
    public function index(Request $request)
    {
        $query = Activity::with('causer', 'subject')
            ->latest();

        // Filter by model type
        if ($request->filled('log_name')) {
            $query->where('log_name', $request->log_name);
        }

        // Filter by event type
        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }

        // Filter by date
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        $logs = $query->paginate(20);

        return view('admin.activity-logs.index', compact('logs'));
    }
}