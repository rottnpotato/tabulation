<?php

namespace App\Http\Controllers;

use App\Models\Criteria;
use App\Models\Pageant;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class CriteriaController extends Controller
{
    protected $auditLogService;

    public function __construct(AuditLogService $auditLogService)
    {
        $this->auditLogService = $auditLogService;
    }

    /**
     * Get criteria for a pageant
     */
    public function index($pageantId)
    {
        $pageant = Pageant::findOrFail($pageantId);
        
        // Check if the current user has access to this pageant
        $organizer = Auth::user();
        $hasAccess = DB::table('pageant_organizers')
            ->where('user_id', $organizer->id)
            ->where('pageant_id', $pageantId)
            ->exists();
            
        if (!$hasAccess) {
            abort(403, 'You do not have access to this pageant.');
        }

        $criteria = $pageant->criteria()
            ->orderBy('display_order')
            ->get();

        return response()->json([
            'criteria' => $criteria
        ]);
    }

    /**
     * Store a new criterion
     */
    public function store(Request $request, $pageantId)
    {
        $pageant = Pageant::findOrFail($pageantId);
        
        // Check if the current user has access to this pageant
        $organizer = Auth::user();
        $hasAccess = DB::table('pageant_organizers')
            ->where('user_id', $organizer->id)
            ->where('pageant_id', $pageantId)
            ->exists();
            
        if (!$hasAccess) {
            abort(403, 'You do not have access to this pageant.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'weight' => 'required|integer|min:0|max:100',
            'min_score' => 'nullable|numeric|min:0',
            'max_score' => 'nullable|numeric|gt:min_score',
            'allow_decimals' => 'nullable|boolean',
            'decimal_places' => 'nullable|integer|min:0|max:5',
            'segment_id' => 'nullable|exists:segments,id',
            'category_id' => 'nullable|exists:categories,id',
        ]);

        DB::beginTransaction();
        try {
            // Get the max display order
            $maxOrder = $pageant->criteria()->max('display_order') ?? 0;

            // Create the criterion
            $criterion = new Criteria();
            $criterion->pageant_id = $pageantId;
            $criterion->name = $validated['name'];
            $criterion->description = $validated['description'] ?? null;
            $criterion->weight = $validated['weight'];
            $criterion->min_score = $validated['min_score'] ?? 0;
            $criterion->max_score = $validated['max_score'] ?? 100;
            $criterion->allow_decimals = $validated['allow_decimals'] ?? true;
            $criterion->decimal_places = $validated['decimal_places'] ?? 2;
            $criterion->segment_id = $validated['segment_id'] ?? null;
            $criterion->category_id = $validated['category_id'] ?? null;
            $criterion->display_order = $maxOrder + 1;
            $criterion->save();

            DB::commit();

            // Log the action
            $this->auditLogService->log(
                'CRITERION_CREATED',
                'Criteria',
                $criterion->id,
                "Created criterion '{$criterion->name}' for pageant ID {$pageantId}"
            );

            return response()->json([
                'success' => true,
                'message' => 'Criterion created successfully',
                'criterion' => $criterion
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create criterion: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update a criterion
     */
    public function update(Request $request, $pageantId, $criterionId)
    {
        $pageant = Pageant::findOrFail($pageantId);
        
        // Check if the current user has access to this pageant
        $organizer = Auth::user();
        $hasAccess = DB::table('pageant_organizers')
            ->where('user_id', $organizer->id)
            ->where('pageant_id', $pageantId)
            ->exists();
            
        if (!$hasAccess) {
            abort(403, 'You do not have access to this pageant.');
        }

        $criterion = Criteria::where('pageant_id', $pageantId)
            ->findOrFail($criterionId);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'weight' => 'required|integer|min:0|max:100',
            'min_score' => 'nullable|numeric|min:0',
            'max_score' => 'nullable|numeric|gt:min_score',
            'allow_decimals' => 'nullable|boolean',
            'decimal_places' => 'nullable|integer|min:0|max:5',
            'segment_id' => 'nullable|exists:segments,id',
            'category_id' => 'nullable|exists:categories,id',
            'display_order' => 'nullable|integer|min:0',
        ]);

        DB::beginTransaction();
        try {
            $criterion->name = $validated['name'];
            $criterion->description = $validated['description'] ?? null;
            $criterion->weight = $validated['weight'];
            $criterion->min_score = $validated['min_score'] ?? $criterion->min_score;
            $criterion->max_score = $validated['max_score'] ?? $criterion->max_score;
            $criterion->allow_decimals = $validated['allow_decimals'] ?? $criterion->allow_decimals;
            $criterion->decimal_places = $validated['decimal_places'] ?? $criterion->decimal_places;
            $criterion->segment_id = $validated['segment_id'] ?? $criterion->segment_id;
            $criterion->category_id = $validated['category_id'] ?? $criterion->category_id;
            
            if (isset($validated['display_order'])) {
                $criterion->display_order = $validated['display_order'];
            }
            
            $criterion->save();

            DB::commit();

            // Log the action
            $this->auditLogService->log(
                'CRITERION_UPDATED',
                'Criteria',
                $criterion->id,
                "Updated criterion '{$criterion->name}' for pageant ID {$pageantId}"
            );

            return response()->json([
                'success' => true,
                'message' => 'Criterion updated successfully',
                'criterion' => $criterion
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update criterion: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a criterion
     */
    public function destroy($pageantId, $criterionId)
    {
        $pageant = Pageant::findOrFail($pageantId);
        
        // Check if the current user has access to this pageant
        $organizer = Auth::user();
        $hasAccess = DB::table('pageant_organizers')
            ->where('user_id', $organizer->id)
            ->where('pageant_id', $pageantId)
            ->exists();
            
        if (!$hasAccess) {
            abort(403, 'You do not have access to this pageant.');
        }

        $criterion = Criteria::where('pageant_id', $pageantId)
            ->findOrFail($criterionId);

        $criterionName = $criterion->name;

        DB::beginTransaction();
        try {
            $criterion->delete();

            DB::commit();

            // Log the action
            $this->auditLogService->log(
                'CRITERION_DELETED',
                'Criteria',
                $criterionId,
                "Deleted criterion '{$criterionName}' from pageant ID {$pageantId}"
            );

            return response()->json([
                'success' => true,
                'message' => 'Criterion deleted successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete criterion: ' . $e->getMessage()
            ], 500);
        }
    }
} 