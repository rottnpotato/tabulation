<?php

namespace App\Http\Controllers;

use App\Models\Contestant;
use App\Models\ContestantImage;
use App\Models\Pageant;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class ContestantController extends Controller
{
    protected $auditLogService;

    public function __construct(AuditLogService $auditLogService)
    {
        $this->auditLogService = $auditLogService;
    }

    /**
     * Get contestants for a pageant
     */
    public function index($pageantId)
    {
        $pageant = Pageant::findOrFail($pageantId);
        Gate::authorize('view', $pageant);

        $contestants = $pageant->contestants()
            ->with(['images' => function ($query) {
                $query->orderBy('is_primary', 'desc')
                    ->orderBy('display_order');
            }])
            ->orderBy('number')
            ->get()
            ->map(function ($contestant) {
                $primaryImage = $contestant->images->firstWhere('is_primary', true);
                return [
                    'id' => $contestant->id,
                    'name' => $contestant->name,
                    'number' => $contestant->number,
                    'origin' => $contestant->origin,
                    'age' => $contestant->age,
                    'bio' => $contestant->bio,
                    'photo' => $primaryImage ? asset('storage/' . $primaryImage->image_path) : null,
                    'active' => $contestant->active,
                    'images' => $contestant->images->map(function ($image) {
                        return [
                            'id' => $image->id,
                            'path' => asset('storage/' . $image->image_path),
                            'is_primary' => $image->is_primary,
                            'caption' => $image->caption,
                            'display_order' => $image->display_order,
                        ];
                    }),
                ];
            });

        return response()->json([
            'contestants' => $contestants
        ]);
    }

    /**
     * Store a new contestant
     */
    public function store(Request $request, $pageantId)
    {
        $pageant = Pageant::findOrFail($pageantId);
        Gate::authorize('update', $pageant);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'number' => [
                'required',
                'integer',
                Rule::unique('contestants')->where(function ($query) use ($pageantId) {
                    return $query->where('pageant_id', $pageantId);
                }),
            ],
            'origin' => 'nullable|string|max:255',
            'age' => 'nullable|integer|min:1|max:150',
            'bio' => 'nullable|string',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg|max:10240',
            'metadata' => 'nullable|array',
        ]);

        DB::beginTransaction();
        try {
            // Create the contestant
            $contestant = new Contestant();
            $contestant->pageant_id = $pageantId;
            $contestant->name = $validated['name'];
            $contestant->number = $validated['number'];
            $contestant->origin = $validated['origin'] ?? null;
            $contestant->age = $validated['age'] ?? null;
            $contestant->bio = $validated['bio'] ?? null;
            $contestant->metadata = $validated['metadata'] ?? null;
            $contestant->active = true;
            $contestant->save();

            // Handle images if present
            if ($request->hasFile('images')) {
                $this->handleContestantImages($request->file('images'), $contestant);
            }

            DB::commit();

            // Log the action
            $this->auditLogService->log(
                'CONTESTANT_CREATED',
                'Contestant',
                $contestant->id,
                "Created contestant '{$contestant->name}' for pageant ID {$pageantId}"
            );

            return response()->json([
                'success' => true,
                'message' => 'Contestant created successfully',
                'contestant' => $contestant->load('images')
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create contestant: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show a contestant
     */
    public function show($pageantId, $contestantId)
    {
        $pageant = Pageant::findOrFail($pageantId);
        Gate::authorize('view', $pageant);

        $contestant = Contestant::with('images')
            ->where('pageant_id', $pageantId)
            ->findOrFail($contestantId);

        // Transform the images to include full URLs
        $contestant->images->transform(function ($image) {
            $image->image_url = asset('storage/' . $image->image_path);
            $image->path = asset('storage/' . $image->image_path); // Add path property for frontend
            return $image;
        });

        // Log the action
        $this->auditLogService->log(
            'CONTESTANT_VIEWED',
            'Contestant',
            $contestant->id,
            "Viewed contestant '{$contestant->name}' details"
        );

        return response()->json([
            'contestant' => $contestant
        ]);
    }

    /**
     * Update a contestant
     */
    public function update(Request $request, $pageantId, $contestantId)
    {
        $pageant = Pageant::findOrFail($pageantId);
        Gate::authorize('update', $pageant);

        $contestant = Contestant::where('pageant_id', $pageantId)
            ->findOrFail($contestantId);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'number' => [
                'required',
                'integer',
                Rule::unique('contestants')->where(function ($query) use ($pageantId) {
                    return $query->where('pageant_id', $pageantId);
                })->ignore($contestantId),
            ],
            'origin' => 'nullable|string|max:255',
            'age' => 'nullable|integer|min:1|max:150',
            'bio' => 'nullable|string',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg|max:10240',
            'metadata' => 'nullable|array',
            'active' => 'nullable|boolean',
            'remove_image_ids' => 'nullable|array',
            'remove_image_ids.*' => 'integer',
            'primary_image_id' => 'nullable|integer',
        ]);

        DB::beginTransaction();
        try {
            $contestant->name = $validated['name'];
            $contestant->number = $validated['number'];
            $contestant->origin = $validated['origin'] ?? null;
            $contestant->age = $validated['age'] ?? null;
            $contestant->bio = $validated['bio'] ?? null;
            $contestant->metadata = $validated['metadata'] ?? [];
            
            if (isset($validated['active'])) {
                $contestant->active = $validated['active'];
            }
            
            $contestant->save();

            // Handle image removals if specified
            if (!empty($validated['remove_image_ids'])) {
                $imagesToRemove = ContestantImage::where('contestant_id', $contestantId)
                    ->whereIn('id', $validated['remove_image_ids'])
                    ->get();
                
                foreach ($imagesToRemove as $image) {
                    Storage::disk('public')->delete($image->image_path);
                    $image->delete();
                }
            }

            // Set primary image if specified
            if (!empty($validated['primary_image_id'])) {
                // Reset all images to non-primary
                ContestantImage::where('contestant_id', $contestantId)
                    ->update(['is_primary' => false]);
                
                // Set the new primary image
                ContestantImage::where('contestant_id', $contestantId)
                    ->where('id', $validated['primary_image_id'])
                    ->update(['is_primary' => true]);
            }

            // Handle new images if present
            if ($request->hasFile('images')) {
                $this->handleContestantImages($request->file('images'), $contestant);
            }

            DB::commit();

            // Log the action
            $this->auditLogService->log(
                'CONTESTANT_UPDATED',
                'Contestant',
                $contestant->id,
                "Updated contestant '{$contestant->name}' for pageant ID {$pageantId}"
            );

            return response()->json([
                'success' => true,
                'message' => 'Contestant updated successfully',
                'contestant' => $contestant->load('images')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update contestant: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a contestant
     */
    public function destroy($pageantId, $contestantId)
    {
        $pageant = Pageant::findOrFail($pageantId);
        Gate::authorize('update', $pageant);

        $contestant = Contestant::where('pageant_id', $pageantId)
            ->findOrFail($contestantId);

        $contestantName = $contestant->name;

        // Delete associated images
        foreach ($contestant->images as $image) {
            Storage::disk('public')->delete($image->image_path);
        }

        // Delete the contestant
        $contestant->delete();

        // Log the action
        $this->auditLogService->log(
            'CONTESTANT_DELETED',
            'Contestant',
            $contestantId,
            "Deleted contestant '{$contestantName}' from pageant ID {$pageantId}"
        );

        return response()->json([
            'success' => true,
            'message' => 'Contestant deleted successfully'
        ]);
    }

    /**
     * Helper method to handle contestant image uploads
     */
    private function handleContestantImages($images, Contestant $contestant)
    {
        $primaryImageExists = $contestant->images()->where('is_primary', true)->exists();
        $displayOrder = $contestant->images()->max('display_order') ?? 0;
        
        foreach ($images as $index => $image) {
            $displayOrder++;
            $path = $image->store('contestants/' . $contestant->pageant_id, 'public');
            
            $contestantImage = new ContestantImage();
            $contestantImage->contestant_id = $contestant->id;
            $contestantImage->image_path = $path;
            $contestantImage->display_order = $displayOrder;
            
            // Set the first image as primary if no primary image exists yet
            if (!$primaryImageExists && $index === 0) {
                $contestantImage->is_primary = true;
                $primaryImageExists = true;
            } else {
                $contestantImage->is_primary = false;
            }
            
            $contestantImage->save();
        }
    }
} 