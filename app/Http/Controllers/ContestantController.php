<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreatePairRequest;
use App\Http\Requests\StoreContestantRequest;
use App\Http\Requests\UpdateContestantRequest;
use App\Models\Contestant;
use App\Models\ContestantImage;
use App\Models\Pageant;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

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
            ->with([
                'images' => function ($query) {
                    $query->orderBy('is_primary', 'desc')
                        ->orderBy('display_order');
                },
            ])
            ->orderBy('number')
            ->orderBy('gender')
            ->get()
            ->map(function ($contestant) {
                $primaryImage = $contestant->images->firstWhere('is_primary', true);
                $partner = $contestant->pairPartner();

                return [
                    'id' => $contestant->id,
                    'name' => $contestant->name,
                    'number' => $contestant->number,
                    'gender' => $contestant->gender,
                    'origin' => $contestant->origin,
                    'age' => $contestant->age,
                    'bio' => $contestant->bio,
                    'photo' => $primaryImage ? asset('storage/'.$primaryImage->image_path) : null,
                    'active' => $contestant->active,
                    'is_pair' => (bool) $contestant->is_pair,
                    'is_paired' => $contestant->isPaired(),
                    'pair_id' => $contestant->pair_id,
                    'partner' => $partner ? [
                        'id' => $partner->id,
                        'name' => $partner->name,
                        'number' => $partner->number,
                        'gender' => $partner->gender,
                    ] : null,
                    'images' => $contestant->images->map(function ($image) {
                        return [
                            'id' => $image->id,
                            'path' => asset('storage/'.$image->image_path),
                            'is_primary' => $image->is_primary,
                            'caption' => $image->caption,
                            'display_order' => $image->display_order,
                        ];
                    }),
                ];
            });

        return response()->json([
            'contestants' => $contestants,
        ]);
    }

    /**
     * Store a new contestant
     */
    public function store(StoreContestantRequest $request, $pageantId)
    {
        try {
            $pageant = Pageant::findOrFail($pageantId);
        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pageant not found',
                ], 404);
            }
            abort(404, 'Pageant not found');
        }

        try {
            Gate::authorize('update', $pageant);
        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized action',
                ], 403);
            }
            abort(403, 'Unauthorized');
        }

        $validated = $request->validated();

        DB::beginTransaction();
        try {
            // Create the contestant
            $contestant = new Contestant;
            $contestant->pageant_id = $pageantId;
            $contestant->name = $validated['name'];
            $contestant->number = $validated['number'];
            $contestant->gender = $validated['gender'];
            $contestant->origin = $validated['origin'] ?? null;
            $contestant->age = $validated['age'] ?? null;
            $contestant->bio = $validated['bio'] ?? null;
            $contestant->metadata = $validated['metadata'] ?? null;
            $contestant->active = true;
            $contestant->save();

            // Handle images if present
            if ($request->hasFile('images')) {
                $this->handleContestantImages($request->file('images'), $contestant);

                // Update the main photo field with the primary image
                $primaryImage = $contestant->primaryImage();
                if ($primaryImage) {
                    $contestant->photo = '/storage/'.$primaryImage->image_path;
                    $contestant->save();
                }
            }

            DB::commit();

            // Log the action
            $this->auditLogService->log(
                'CONTESTANT_CREATED',
                'Contestant',
                $contestant->id,
                "Created contestant '{$contestant->name}' for pageant ID {$pageantId}"
            );

            // Build response payload consistent with index/show
            $primaryImage = $contestant->primaryImage();
            $responseContestant = [
                'id' => $contestant->id,
                'name' => $contestant->name,
                'number' => $contestant->number,
                'gender' => $contestant->gender,
                'origin' => $contestant->origin,
                'age' => $contestant->age,
                'bio' => $contestant->bio,
                'photo' => $primaryImage ? asset('storage/'.$primaryImage->image_path) : null,
                'active' => $contestant->active,
                'is_pair' => (bool) $contestant->is_pair,
                'members' => [],
                'members_text' => null,
                'images' => $contestant->images->map(function ($image) {
                    return [
                        'id' => $image->id,
                        'path' => asset('storage/'.$image->image_path),
                        'is_primary' => $image->is_primary,
                        'caption' => $image->caption,
                        'display_order' => $image->display_order,
                    ];
                })->values(),
            ];

            // If this was an Inertia request, respond with a redirect (required by Inertia)
            if ($request->header('X-Inertia')) {
                return to_route('organizer.pageant.contestants-management', ['id' => $pageantId])
                    ->with('success', "Contestant '{$contestant->name}' created.");
            }

            // Otherwise, return JSON for axios/AJAX consumers
            // If this was an Inertia request, respond with a redirect (required by Inertia)
            if ($request->header('X-Inertia')) {
                return to_route('organizer.pageant.contestants-management', ['id' => $pageantId])
                    ->with('success', "Contestant '{$contestant->name}' updated.");
            }

            // Otherwise, return JSON for axios/AJAX consumers
            return response()->json([
                'success' => true,
                'contestant' => $responseContestant,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to create contestant: '.$e->getMessage(),
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

        $contestant = Contestant::with(['images', 'members:id,name,number,photo'])
            ->where('pageant_id', $pageantId)
            ->findOrFail($contestantId);

        // Transform the images to include full URLs
        $contestant->images->transform(function ($image) {
            $image->image_url = asset('storage/'.$image->image_path);
            $image->path = asset('storage/'.$image->image_path); // Add path property for frontend

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
            'contestant' => array_merge($contestant->toArray(), [
                'is_pair' => (bool) $contestant->is_pair,
                'members' => $contestant->is_pair ? $contestant->members->map(function ($m) {
                    return [
                        'id' => $m->id,
                        'name' => $m->name,
                        'number' => $m->number,
                        'photo' => $m->photo ? asset($m->photo) : null,
                    ];
                })->values()->all() : [],
                'members_text' => $contestant->is_pair ? $contestant->members->pluck('name')->implode(' & ') : null,
            ]),
        ]);
    }

    /**
     * Create a pair from two existing contestants within the same pageant
     */
    public function storePair(CreatePairRequest $request, int $pageantId)
    {
        try {
            $pageant = Pageant::findOrFail($pageantId);
        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pageant not found',
                ], 404);
            }
            abort(404, 'Pageant not found');
        }

        try {
            Gate::authorize('update', $pageant);
        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized action',
                ], 403);
            }
            abort(403, 'Unauthorized');
        }

        $validated = $request->validated();

        // Load members and validate they belong to the same pageant
        $members = Contestant::whereIn('id', $validated['member_ids'])->get();

        if ($members->count() !== 2) {
            return response()->json(['success' => false, 'message' => 'Exactly two members are required.'], 422);
        }

        foreach ($members as $member) {
            if ($member->pageant_id !== (int) $pageantId) {
                return response()->json(['success' => false, 'message' => 'Members must belong to this pageant.'], 422);
            }
            // Ensure member not already in another pair
            if ($member->isPaired()) {
                return response()->json(['success' => false, 'message' => "{$member->name} is already a member of another pair."], 422);
            }
        }

        // Enforce opposite genders and same contestant number
        $genders = $members->pluck('gender')->filter()->values();
        if ($genders->count() !== 2 || $genders->unique()->count() !== 2 || ! $genders->contains('male') || ! $genders->contains('female')) {
            return response()->json(['success' => false, 'message' => 'Selected members must be one male and one female.'], 422);
        }

        $numbers = $members->pluck('number')->unique();
        if ($numbers->count() !== 1) {
            return response()->json(['success' => false, 'message' => 'Selected members must share the same contestant number.'], 422);
        }

        DB::beginTransaction();
        try {
            // Generate a unique pair ID (UUID)
            $pairId = (string) \Illuminate\Support\Str::uuid();

            // Link both contestants with the same pair_id
            foreach ($members as $member) {
                $member->pair_id = $pairId;
                $member->save();
            }

            DB::commit();

            // Build response with both members
            $responseMembers = $members->map(function ($m) use ($pairId) {
                $primaryImage = $m->primaryImage();

                return [
                    'id' => $m->id,
                    'name' => $m->name,
                    'number' => $m->number,
                    'gender' => $m->gender,
                    'origin' => $m->origin,
                    'age' => $m->age,
                    'bio' => $m->bio,
                    'photo' => $primaryImage ? asset('storage/'.$primaryImage->image_path) : null,
                    'active' => $m->active,
                    'is_paired' => true,
                    'pair_id' => $pairId,
                ];
            })->values();

            // If this was an Inertia request, respond with a redirect
            if ($request->header('X-Inertia')) {
                return to_route('organizer.pageant.contestants-management', ['id' => $pageantId])
                    ->with('success', 'Pair created successfully.');
            }

            // Otherwise, return JSON
            return response()->json([
                'success' => true,
                'message' => 'Pair created successfully',
                'members' => $responseMembers,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to create pair: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a pair contestant
     */
    public function destroyPair(int $pageantId, int $pairId)
    {
        $pageant = Pageant::findOrFail($pageantId);
        Gate::authorize('update', $pageant);

        $pair = Contestant::where('pageant_id', $pageantId)->where('is_pair', true)->findOrFail($pairId);
        $name = $pair->name;
        $pair->delete();

        return response()->json(['success' => true, 'message' => "Pair '{$name}' deleted."]);
    }

    /**
     * Unpair/break a pair - removes the pair_id linkage from both contestants
     */
    public function unpair(Request $request, int $pageantId, int $contestantId)
    {
        $pageant = Pageant::findOrFail($pageantId);
        Gate::authorize('update', $pageant);

        $contestant = Contestant::where('pageant_id', $pageantId)->findOrFail($contestantId);

        if (! $contestant->isPaired()) {
            return response()->json(['success' => false, 'message' => 'This contestant is not part of a pair.'], 422);
        }

        $pairId = $contestant->pair_id;
        $partner = $contestant->pairPartner();

        DB::beginTransaction();
        try {
            // Remove pair_id from both contestants
            $contestant->pair_id = null;
            $contestant->save();

            if ($partner) {
                $partner->pair_id = null;
                $partner->save();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pair successfully broken.',
                'contestants' => [$contestant->id, $partner?->id],
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to unpair contestants: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update a contestant
     */
    public function update(UpdateContestantRequest $request, $pageantId, $contestantId)
    {
        try {
            $pageant = Pageant::findOrFail($pageantId);
        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pageant not found',
                ], 404);
            }
            abort(404, 'Pageant not found');
        }

        try {
            Gate::authorize('update', $pageant);
        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized action',
                ], 403);
            }
            abort(403, 'Unauthorized');
        }

        $contestant = Contestant::where('pageant_id', $pageantId)
            ->findOrFail($contestantId);

        $validated = $request->validated();

        DB::beginTransaction();
        try {
            $contestant->name = $validated['name'];
            $contestant->number = $validated['number'];
            $contestant->gender = $validated['gender'];
            $contestant->origin = $validated['origin'] ?? null;
            $contestant->age = $validated['age'] ?? null;
            $contestant->bio = $validated['bio'] ?? null;
            $contestant->metadata = $validated['metadata'] ?? [];

            if (isset($validated['active'])) {
                $contestant->active = $validated['active'];
            }

            $contestant->save();

            // Handle image removals if specified
            if (! empty($validated['remove_image_ids'])) {
                $imagesToRemove = ContestantImage::where('contestant_id', $contestantId)
                    ->whereIn('id', $validated['remove_image_ids'])
                    ->get();

                foreach ($imagesToRemove as $image) {
                    Storage::disk('public')->delete($image->image_path);
                    $image->delete();
                }
            }

            // Set primary image if specified
            if (! empty($validated['primary_image_id'])) {
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

            // Update the main photo field with the current primary image for backward compatibility
            $primaryImage = $contestant->primaryImage();
            if ($primaryImage) {
                $contestant->photo = '/storage/'.$primaryImage->image_path;
                $contestant->save();
            } elseif ($contestant->images()->count() === 0) {
                // If no images left, clear the photo field
                $contestant->photo = null;
                $contestant->save();
            }

            DB::commit();

            // Log the action
            $this->auditLogService->log(
                'CONTESTANT_UPDATED',
                'Contestant',
                $contestant->id,
                "Updated contestant '{$contestant->name}' for pageant ID {$pageantId}"
            );

            // Build response payload consistent with index/show
            $primaryImage = $contestant->primaryImage();
            $responseContestant = [
                'id' => $contestant->id,
                'name' => $contestant->name,
                'number' => $contestant->number,
                'gender' => $contestant->gender,
                'origin' => $contestant->origin,
                'age' => $contestant->age,
                'bio' => $contestant->bio,
                'photo' => $primaryImage ? asset('storage/'.$primaryImage->image_path) : null,
                'active' => $contestant->active,
                'is_pair' => (bool) $contestant->is_pair,
                'members' => $contestant->is_pair ? $contestant->members->map(function ($m) {
                    return [
                        'id' => $m->id,
                        'name' => $m->name,
                        'number' => $m->number,
                        'photo' => $m->photo ? asset($m->photo) : null,
                    ];
                })->values() : [],
                'members_text' => $contestant->is_pair ? $contestant->members->pluck('name')->implode(' & ') : null,
                'images' => $contestant->images->map(function ($image) {
                    return [
                        'id' => $image->id,
                        'path' => asset('storage/'.$image->image_path),
                        'is_primary' => $image->is_primary,
                        'caption' => $image->caption,
                        'display_order' => $image->display_order,
                    ];
                })->values(),
            ];

            return response()->json([
                'success' => true,
                'contestant' => $responseContestant,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to update contestant: '.$e->getMessage(),
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

        return back()->with('success', 'Contestant deleted successfully');
    }

    /**
     * Delete a specific photo from a contestant
     */
    public function deletePhoto($pageantId, $contestantId, $photoIndex)
    {
        $pageant = Pageant::findOrFail($pageantId);
        Gate::authorize('update', $pageant);

        $contestant = Contestant::where('pageant_id', $pageantId)
            ->findOrFail($contestantId);

        // Get the contestant's images ordered by display_order
        $images = $contestant->images()
            ->orderBy('display_order')
            ->get();

        // Validate photo index
        if (! is_numeric($photoIndex) || $photoIndex < 0 || $photoIndex >= $images->count()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid photo index',
            ], 400);
        }

        $image = $images[$photoIndex];
        $wasPrimary = $image->is_primary;

        DB::beginTransaction();
        try {
            // Delete the physical file
            Storage::disk('public')->delete($image->image_path);

            // Delete the database record
            $image->delete();

            // If this was the primary image, set another image as primary if available
            if ($wasPrimary && $images->count() > 1) {
                $nextPrimary = $contestant->images()
                    ->orderBy('display_order')
                    ->first();

                if ($nextPrimary) {
                    $nextPrimary->is_primary = true;
                    $nextPrimary->save();

                    // Update the main photo field for backward compatibility
                    $contestant->photo = '/storage/'.$nextPrimary->image_path;
                    $contestant->save();
                }
            } elseif ($images->count() === 1) {
                // No images left, clear the photo field
                $contestant->photo = null;
                $contestant->save();
            }

            DB::commit();

            // Log the action
            $this->auditLogService->log(
                'CONTESTANT_PHOTO_DELETED',
                'Contestant',
                $contestant->id,
                "Deleted photo from contestant '{$contestant->name}' (index: {$photoIndex})"
            );

            return response()->json([
                'success' => true,
                'message' => 'Photo deleted successfully',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete photo: '.$e->getMessage(),
            ], 500);
        }
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
            $path = $image->store('contestants/'.$contestant->pageant_id, 'public');

            $contestantImage = new ContestantImage;
            $contestantImage->contestant_id = $contestant->id;
            $contestantImage->image_path = $path;
            $contestantImage->display_order = $displayOrder;

            // Set the first image as primary if no primary image exists yet
            if (! $primaryImageExists && $index === 0) {
                $contestantImage->is_primary = true;
                $primaryImageExists = true;

                // Update the main photo field for backward compatibility
                $contestant->photo = '/storage/'.$path;
                $contestant->save();
            } else {
                $contestantImage->is_primary = false;
            }

            $contestantImage->save();
        }
    }
}
