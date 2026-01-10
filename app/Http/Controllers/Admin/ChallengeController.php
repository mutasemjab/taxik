<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Challenge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ChallengeController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:challenge-table')->only('index', 'show');
        $this->middleware('permission:challenge-add')->only('create', 'store');
        $this->middleware('permission:challenge-edit')->only('edit', 'update');
        $this->middleware('permission:challenge-delete')->only('destroy');
    }

    /**
     * Display a listing of challenges
     */
    public function index(Request $request)
    {
        $query = Challenge::query();

        // Search
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title_en', 'like', "%{$search}%")
                    ->orWhere('title_ar', 'like', "%{$search}%");
            });
        }

        // Filter by type
        if ($request->has('challenge_type') && $request->challenge_type != '') {
            $query->where('challenge_type', $request->challenge_type);
        }

        // Filter by status
        if ($request->has('is_active') && $request->is_active != '') {
            $query->where('is_active', $request->is_active);
        }

        $challenges = $query->orderBy('created_at', 'desc')->paginate(15)->appends($request->all());

        return view('admin.challenges.index', compact('challenges'));
    }

    /**
     * Show the form for creating a new challenge
     */
    public function create()
    {
        $challengeTypes = Challenge::CHALLENGE_TYPES;
        return view('admin.challenges.create', compact('challengeTypes'));
    }

    /**
     * Store a newly created challenge
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title_en' => 'required|string|max:255',
            'title_ar' => 'required|string|max:255',
            'description_en' => 'required|string',
            'description_ar' => 'required|string',
            'challenge_type' => 'required|in:referral,trips,spending',
            'target_count' => 'required|integer|min:1',
            'reward_amount' => 'required|numeric|min:0',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'max_completions_per_user' => 'required|integer|min:1',
            'icon' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('challenges.create')
                ->withErrors($validator)
                ->withInput();
        }

        $challengeData = $request->except('icon');
        $challengeData['is_active'] = $request->has('is_active') ? 1 : 0;

        // Handle icon upload
        if ($request->hasFile('icon')) {
            $challengeData['icon'] = uploadImage('assets/admin/uploads/challenges', $request->icon);
        }

        Challenge::create($challengeData);

        return redirect()
            ->route('challenges.index')
            ->with('success', 'Challenge created successfully');
    }

    /**
     * Display the specified challenge
     */
    public function show($id)
    {
        $challenge = Challenge::with('userProgress.user')->findOrFail($id);
        
        // Statistics
        $stats = [
            'total_participants' => $challenge->userProgress()->distinct('user_id')->count(),
            'total_completions' => $challenge->userProgress()->where('times_completed', '>', 0)->sum('times_completed'),
            'total_rewards_given' => $challenge->userProgress()->where('times_completed', '>', 0)->sum('times_completed') * $challenge->reward_amount,
            'in_progress' => $challenge->userProgress()->where('is_completed', false)->where('current_count', '>', 0)->count(),
        ];

        return view('admin.challenges.show', compact('challenge', 'stats'));
    }

    /**
     * Show the form for editing the challenge
     */
    public function edit($id)
    {
        $challenge = Challenge::findOrFail($id);
        $challengeTypes = Challenge::CHALLENGE_TYPES;
        
        return view('admin.challenges.edit', compact('challenge', 'challengeTypes'));
    }

    /**
     * Update the challenge
     */
    public function update(Request $request, $id)
    {
        $challenge = Challenge::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'title_en' => 'required|string|max:255',
            'title_ar' => 'required|string|max:255',
            'description_en' => 'required|string',
            'description_ar' => 'required|string',
            'challenge_type' => 'required|in:referral,trips,spending',
            'target_count' => 'required|integer|min:1',
            'reward_amount' => 'required|numeric|min:0',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'max_completions_per_user' => 'required|integer|min:1',
            'icon' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('challenges.edit', $id)
                ->withErrors($validator)
                ->withInput();
        }

        $challengeData = $request->except('icon');
        $challengeData['is_active'] = $request->has('is_active') ? 1 : 0;

        // Handle icon upload
        if ($request->hasFile('icon')) {
            // Delete old icon if exists
            if ($challenge->icon && file_exists('assets/admin/uploads/challenges/' . $challenge->icon)) {
                unlink('assets/admin/uploads/challenges/' . $challenge->icon);
            }
            $challengeData['icon'] = uploadImage('assets/admin/uploads/challenges', $request->icon);
        }

        $challenge->update($challengeData);

        return redirect()
            ->route('challenges.index')
            ->with('success', 'Challenge updated successfully');
    }

    /**
     * Remove the challenge
     */
    public function destroy($id)
    {
        $challenge = Challenge::findOrFail($id);

        // Delete icon if exists
        if ($challenge->icon && file_exists('assets/admin/uploads/challenges/' . $challenge->icon)) {
            unlink('assets/admin/uploads/challenges/' . $challenge->icon);
        }

        $challenge->delete();

        return redirect()
            ->route('challenges.index')
            ->with('success', 'Challenge deleted successfully');
    }

    /**
     * View challenge participants
     */
    public function participants($id)
    {
        $challenge = Challenge::findOrFail($id);
        $participants = $challenge->userProgress()
            ->with('user')
            ->orderBy('times_completed', 'desc')
            ->orderBy('current_count', 'desc')
            ->paginate(20);

        return view('admin.challenges.participants', compact('challenge', 'participants'));
    }
}