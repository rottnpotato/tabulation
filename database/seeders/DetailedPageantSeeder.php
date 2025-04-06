<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\Category;
use App\Models\Contestant;
use App\Models\Event;
use App\Models\Pageant;
use App\Models\Segment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DetailedPageantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get admin user for creator reference
        $admin = User::where('role', 'admin')->first();
        if (!$admin) {
            $admin = User::create([
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'password' => bcrypt('admin123'),
                'role' => 'admin',
                'email_verified_at' => now()
            ]);
        }

        // Create judges for the pageants
        $judges = [];
        for ($i = 1; $i <= 5; $i++) {
            $judges[] = User::firstOrCreate(
                ['email' => "judge{$i}@example.com"],
                [
                    'name' => "Judge {$i}",
                    'password' => bcrypt('password'),
                    'role' => 'judge',
                    'email_verified_at' => now()
                ]
            );
        }

        // Create organizers
        $organizers = [];
        for ($i = 1; $i <= 3; $i++) {
            $organizers[] = User::firstOrCreate(
                ['email' => "organizer{$i}@example.com"],
                [
                    'name' => "Organizer {$i}",
                    'password' => bcrypt('password'),
                    'role' => 'organizer',
                    'email_verified_at' => now()
                ]
            );
        }

        // 1. Create an Active pageant with complete data
        $activePageant = Pageant::create([
            'name' => 'Miss Universe 2025',
            'description' => 'The most prestigious international beauty pageant showcasing talent, intelligence, and beauty from around the world.',
            'start_date' => Carbon::now()->addDays(10),
            'end_date' => Carbon::now()->addDays(20),
            'venue' => 'Grand Arena Convention Center',
            'location' => 'New York, USA',
            'status' => 'Active',
            'created_by' => $admin->id,
            'scoring_system' => 'percentage'
        ]);

        // Attach organizers to active pageant
        $activePageant->organizers()->attach([$organizers[0]->id, $organizers[1]->id]);

        // Create categories for active pageant
        $activeCategories = [
            [
                'name' => 'Beauty',
                'description' => 'Overall beauty and appearance',
                'weight' => 30,
                'max_score' => 100,
                'scoring_type' => 'percentage',
                'display_order' => 1,
                'active' => true
            ],
            [
                'name' => 'Intelligence',
                'description' => 'Question and answer performance',
                'weight' => 35,
                'max_score' => 100,
                'scoring_type' => 'percentage',
                'display_order' => 2,
                'active' => true
            ],
            [
                'name' => 'Talent',
                'description' => 'Talent showcase performance',
                'weight' => 20,
                'max_score' => 100,
                'scoring_type' => 'percentage',
                'display_order' => 3,
                'active' => true
            ],
            [
                'name' => 'Poise & Posture',
                'description' => 'Elegance and confidence in walk and posture',
                'weight' => 15,
                'max_score' => 100,
                'scoring_type' => 'percentage',
                'display_order' => 4,
                'active' => true
            ]
        ];

        foreach ($activeCategories as $categoryData) {
            Category::create(array_merge(['pageant_id' => $activePageant->id], $categoryData));
        }

        // Create events for active pageant
        $activeEvents = [
            [
                'name' => 'Contestant Registration',
                'description' => 'Official registration and documentation of contestants',
                'type' => 'registration',
                'start_datetime' => Carbon::now()->subDays(5),
                'end_datetime' => Carbon::now()->subDays(3),
                'venue' => 'Grand Arena Convention Center',
                'location' => 'New York, USA',
                'status' => 'Completed',
                'is_milestone' => true,
                'display_order' => 1
            ],
            [
                'name' => 'Contestant Orientation',
                'description' => 'Briefing and introduction to pageant rules',
                'type' => 'setup',
                'start_datetime' => Carbon::now()->subDays(2),
                'end_datetime' => Carbon::now()->subDays(2),
                'venue' => 'Grand Arena Convention Center',
                'location' => 'New York, USA',
                'status' => 'Completed',
                'is_milestone' => true,
                'display_order' => 2
            ],
            [
                'name' => 'Preliminary Interviews',
                'description' => 'First round of interviews with judges',
                'type' => 'preliminary',
                'start_datetime' => Carbon::now()->subDay(),
                'end_datetime' => Carbon::now(),
                'venue' => 'Grand Arena Convention Center',
                'location' => 'New York, USA',
                'status' => 'In Progress',
                'is_milestone' => true,
                'display_order' => 3
            ],
            [
                'name' => 'Talent Showcase',
                'description' => 'Contestants demonstrate their talents',
                'type' => 'preliminary',
                'start_datetime' => Carbon::now()->addDays(3),
                'end_datetime' => Carbon::now()->addDays(3),
                'venue' => 'Grand Arena Convention Center',
                'location' => 'New York, USA',
                'status' => 'Pending',
                'is_milestone' => true,
                'display_order' => 4
            ],
            [
                'name' => 'Evening Gown Competition',
                'description' => 'Formal wear competition',
                'type' => 'preliminary',
                'start_datetime' => Carbon::now()->addDays(5),
                'end_datetime' => Carbon::now()->addDays(5),
                'venue' => 'Grand Arena Convention Center',
                'location' => 'New York, USA',
                'status' => 'Pending',
                'is_milestone' => false,
                'display_order' => 5
            ],
            [
                'name' => 'Swimwear Competition',
                'description' => 'Swimwear runway showcase',
                'type' => 'preliminary',
                'start_datetime' => Carbon::now()->addDays(7),
                'end_datetime' => Carbon::now()->addDays(7),
                'venue' => 'Grand Arena Convention Center',
                'location' => 'New York, USA',
                'status' => 'Pending',
                'is_milestone' => false,
                'display_order' => 6
            ],
            [
                'name' => 'Final Q&A Round',
                'description' => 'Final question and answer session',
                'type' => 'final',
                'start_datetime' => Carbon::now()->addDays(10),
                'end_datetime' => Carbon::now()->addDays(10),
                'venue' => 'Grand Arena Convention Center',
                'location' => 'New York, USA',
                'status' => 'Pending',
                'is_milestone' => true,
                'display_order' => 7
            ],
            [
                'name' => 'Coronation Night',
                'description' => 'Final judging and winner announcement',
                'type' => 'final',
                'start_datetime' => Carbon::now()->addDays(15),
                'end_datetime' => Carbon::now()->addDays(15),
                'venue' => 'Grand Arena Convention Center',
                'location' => 'New York, USA',
                'status' => 'Pending',
                'is_milestone' => true,
                'display_order' => 8
            ]
        ];

        foreach ($activeEvents as $eventData) {
            Event::create(array_merge(['pageant_id' => $activePageant->id], $eventData));
        }

        // Create segments for active pageant
        $activeSegments = [
            [
                'name' => 'Evening Gown',
                'description' => 'Formal wear segment showcasing elegance and poise',
                'type' => 'evening_gown',
                'start_datetime' => Carbon::now()->addDays(5),
                'end_datetime' => Carbon::now()->addDays(5),
                'weight' => 25,
                'max_score' => 100,
                'scoring_type' => 'percentage',
                'status' => 'Pending',
                'display_order' => 1,
                'active' => true
            ],
            [
                'name' => 'Swimwear',
                'description' => 'Swimwear segment showcasing fitness and confidence',
                'type' => 'swimwear',
                'start_datetime' => Carbon::now()->addDays(7),
                'end_datetime' => Carbon::now()->addDays(7),
                'weight' => 25,
                'max_score' => 100,
                'scoring_type' => 'percentage',
                'status' => 'Pending',
                'display_order' => 2,
                'active' => true
            ],
            [
                'name' => 'Talent',
                'description' => 'Talent showcase segment demonstrating unique abilities',
                'type' => 'talent',
                'start_datetime' => Carbon::now()->addDays(3),
                'end_datetime' => Carbon::now()->addDays(3),
                'weight' => 25,
                'max_score' => 100,
                'scoring_type' => 'percentage',
                'status' => 'Pending',
                'display_order' => 3,
                'active' => true
            ],
            [
                'name' => 'Question & Answer',
                'description' => 'Final Q&A segment testing communication and intelligence',
                'type' => 'qa',
                'start_datetime' => Carbon::now()->addDays(10),
                'end_datetime' => Carbon::now()->addDays(10),
                'weight' => 25,
                'max_score' => 100,
                'scoring_type' => 'percentage',
                'status' => 'Pending',
                'display_order' => 4,
                'active' => true
            ]
        ];

        foreach ($activeSegments as $segmentData) {
            Segment::create(array_merge(['pageant_id' => $activePageant->id], $segmentData));
        }

        // Add judges to active pageant
        $activePageant->judges()->attach([
            $judges[0]->id => ['role' => 'head_judge', 'active' => true],
            $judges[1]->id => ['role' => 'judge', 'active' => true],
            $judges[2]->id => ['role' => 'judge', 'active' => true],
            $judges[3]->id => ['role' => 'guest_judge', 'active' => true]
        ]);

        // Create contestants for active pageant
        $activeContestants = [
            [
                'name' => 'Sophia Anderson',
                'number' => 1,
                'origin' => 'California, USA',
                'age' => 25,
                'photo' => '/images/contestants/sophia.jpg',
                'bio' => 'Graduated with honors in Environmental Science, passionate about sustainability.',
                'scores' => json_encode(['average' => 92.5]),
                'active' => true
            ],
            [
                'name' => 'Isabella Martinez',
                'number' => 2,
                'origin' => 'Madrid, Spain',
                'age' => 24,
                'photo' => '/images/contestants/isabella.jpg',
                'bio' => 'Professional dancer with a degree in International Relations.',
                'scores' => json_encode(['average' => 88.7]),
                'active' => true
            ],
            [
                'name' => 'Olivia Johnson',
                'number' => 3,
                'origin' => 'London, UK',
                'age' => 26,
                'photo' => '/images/contestants/olivia.jpg',
                'bio' => 'Medical student and volunteer for healthcare missions in developing countries.',
                'scores' => json_encode(['average' => 90.1]),
                'active' => true
            ],
            [
                'name' => 'Amara Okafor',
                'number' => 4,
                'origin' => 'Lagos, Nigeria',
                'age' => 23,
                'photo' => '/images/contestants/amara.jpg',
                'bio' => 'Entrepreneur who founded a tech startup focusing on education.',
                'scores' => json_encode(['average' => 85.4]),
                'active' => true
            ],
            [
                'name' => 'Mei Li',
                'number' => 5,
                'origin' => 'Beijing, China',
                'age' => 25,
                'photo' => '/images/contestants/mei.jpg',
                'bio' => 'Classical pianist and advocate for arts education.',
                'scores' => json_encode(['average' => 87.2]),
                'active' => true
            ]
        ];

        foreach ($activeContestants as $contestantData) {
            Contestant::create(array_merge(['pageant_id' => $activePageant->id], $contestantData));
        }

        // Create activities for active pageant
        $activeActivities = [
            [
                'user_id' => $admin->id,
                'action_type' => 'PAGEANT_CREATED',
                'entity_type' => 'Pageant',
                'entity_id' => $activePageant->id,
                'description' => "Created pageant 'Miss Universe 2025'",
                'created_at' => Carbon::now()->subDays(15),
                'updated_at' => Carbon::now()->subDays(15)
            ],
            [
                'user_id' => $organizers[0]->id,
                'action_type' => 'PAGEANT_STATUS_CHANGED',
                'entity_type' => 'Pageant',
                'entity_id' => $activePageant->id,
                'description' => "Changed pageant status from 'Draft' to 'Setup'",
                'created_at' => Carbon::now()->subDays(14),
                'updated_at' => Carbon::now()->subDays(14)
            ],
            [
                'user_id' => $organizers[0]->id,
                'action_type' => 'EVENT_CREATED',
                'entity_type' => 'Event',
                'entity_id' => 1,
                'description' => "Added event 'Contestant Registration'",
                'created_at' => Carbon::now()->subDays(13),
                'updated_at' => Carbon::now()->subDays(13)
            ],
            [
                'user_id' => $organizers[1]->id,
                'action_type' => 'CONTESTANT_ADDED',
                'entity_type' => 'Contestant',
                'entity_id' => 1,
                'description' => "Added contestant 'Sophia Anderson'",
                'created_at' => Carbon::now()->subDays(7),
                'updated_at' => Carbon::now()->subDays(7)
            ],
            [
                'user_id' => $organizers[1]->id,
                'action_type' => 'CONTESTANT_ADDED',
                'entity_type' => 'Contestant',
                'entity_id' => 2,
                'description' => "Added contestant 'Isabella Martinez'",
                'created_at' => Carbon::now()->subDays(7),
                'updated_at' => Carbon::now()->subDays(7)
            ],
            [
                'user_id' => $organizers[0]->id,
                'action_type' => 'JUDGE_ASSIGNED',
                'entity_type' => 'User',
                'entity_id' => $judges[0]->id,
                'description' => "Assigned Judge {$judges[0]->name} as head judge",
                'created_at' => Carbon::now()->subDays(5),
                'updated_at' => Carbon::now()->subDays(5)
            ],
            [
                'user_id' => $organizers[0]->id,
                'action_type' => 'EVENT_COMPLETED',
                'entity_type' => 'Event',
                'entity_id' => 1,
                'description' => "Completed event 'Contestant Registration'",
                'created_at' => Carbon::now()->subDays(3),
                'updated_at' => Carbon::now()->subDays(3)
            ],
            [
                'user_id' => $judges[0]->id,
                'action_type' => 'SCORE_UPDATED',
                'entity_type' => 'Contestant',
                'entity_id' => 1,
                'description' => "Updated scores for 'Sophia Anderson'",
                'created_at' => Carbon::now()->subDays(1),
                'updated_at' => Carbon::now()->subDays(1)
            ],
            [
                'user_id' => $organizers[0]->id,
                'action_type' => 'PAGEANT_STATUS_CHANGED',
                'entity_type' => 'Pageant',
                'entity_id' => $activePageant->id,
                'description' => "Changed pageant status from 'Setup' to 'Active'",
                'created_at' => Carbon::now()->subDays(2),
                'updated_at' => Carbon::now()->subDays(2)
            ],
            [
                'user_id' => $admin->id,
                'action_type' => 'EVENT_UPDATED',
                'entity_type' => 'Event',
                'entity_id' => 3,
                'description' => "Updated event 'Preliminary Interviews' status to 'In Progress'",
                'created_at' => Carbon::now()->subDay(),
                'updated_at' => Carbon::now()->subDay()
            ]
        ];

        foreach ($activeActivities as $activityData) {
            Activity::create(array_merge(['pageant_id' => $activePageant->id], $activityData));
        }

        // 2. Create a Setup pageant with minimal data
        $setupPageant = Pageant::create([
            'name' => 'Miss World 2025',
            'description' => 'Beauty with a purpose - celebrating humanitarian work alongside beauty and talent.',
            'start_date' => Carbon::now()->addMonths(2),
            'end_date' => Carbon::now()->addMonths(2)->addDays(10),
            'venue' => 'Royal Convention Center',
            'location' => 'London, UK',
            'status' => 'Setup',
            'created_by' => $admin->id,
            'scoring_system' => '1-10'
        ]);

        // Attach organizers to setup pageant
        $setupPageant->organizers()->attach([$organizers[2]->id]);

        // Create events for setup pageant
        $setupEvents = [
            [
                'name' => 'Planning Meeting',
                'description' => 'Initial planning and coordination',
                'type' => 'setup',
                'start_datetime' => Carbon::now()->subDays(3),
                'end_datetime' => Carbon::now()->subDays(3),
                'venue' => 'Royal Convention Center',
                'location' => 'London, UK',
                'status' => 'Completed',
                'is_milestone' => true,
                'display_order' => 1
            ],
            [
                'name' => 'Contestant Registration Opens',
                'description' => 'Opening of contestant registration portal',
                'type' => 'registration',
                'start_datetime' => Carbon::now(),
                'end_datetime' => Carbon::now()->addDays(30),
                'venue' => 'Online',
                'location' => 'Worldwide',
                'status' => 'In Progress',
                'is_milestone' => true,
                'display_order' => 2
            ],
            [
                'name' => 'Venue Setup',
                'description' => 'Preparation of venue for pageant',
                'type' => 'setup',
                'start_datetime' => Carbon::now()->addMonths(1)->subWeek(),
                'end_datetime' => Carbon::now()->addMonths(1),
                'venue' => 'Royal Convention Center',
                'location' => 'London, UK',
                'status' => 'Pending',
                'is_milestone' => false,
                'display_order' => 3
            ]
        ];

        foreach ($setupEvents as $eventData) {
            Event::create(array_merge(['pageant_id' => $setupPageant->id], $eventData));
        }

        // Create categories for setup pageant
        $setupCategories = [
            [
                'name' => 'Beauty',
                'description' => 'Overall beauty and appearance',
                'weight' => 25,
                'max_score' => 10,
                'scoring_type' => 'scale',
                'display_order' => 1,
                'active' => true
            ],
            [
                'name' => 'Humanitarian Project',
                'description' => 'Assessment of contestant\'s humanitarian work',
                'weight' => 30,
                'max_score' => 10,
                'scoring_type' => 'scale',
                'display_order' => 2,
                'active' => true
            ],
            [
                'name' => 'Talent',
                'description' => 'Performance in talent showcase',
                'weight' => 25,
                'max_score' => 10,
                'scoring_type' => 'scale',
                'display_order' => 3,
                'active' => true
            ],
            [
                'name' => 'Interview',
                'description' => 'Performance in interview rounds',
                'weight' => 20,
                'max_score' => 10,
                'scoring_type' => 'scale',
                'display_order' => 4,
                'active' => true
            ]
        ];

        foreach ($setupCategories as $categoryData) {
            Category::create(array_merge(['pageant_id' => $setupPageant->id], $categoryData));
        }

        // Add one judge to setup pageant
        $setupPageant->judges()->attach([
            $judges[4]->id => ['role' => 'head_judge', 'active' => true]
        ]);

        // Create activities for setup pageant
        $setupActivities = [
            [
                'user_id' => $admin->id,
                'action_type' => 'PAGEANT_CREATED',
                'entity_type' => 'Pageant',
                'entity_id' => $setupPageant->id,
                'description' => "Created pageant 'Miss World 2025'",
                'created_at' => Carbon::now()->subDays(5),
                'updated_at' => Carbon::now()->subDays(5)
            ],
            [
                'user_id' => $organizers[2]->id,
                'action_type' => 'PAGEANT_STATUS_CHANGED',
                'entity_type' => 'Pageant',
                'entity_id' => $setupPageant->id,
                'description' => "Changed pageant status from 'Draft' to 'Setup'",
                'created_at' => Carbon::now()->subDays(4),
                'updated_at' => Carbon::now()->subDays(4)
            ],
            [
                'user_id' => $organizers[2]->id,
                'action_type' => 'EVENT_CREATED',
                'entity_type' => 'Event',
                'entity_id' => 1,
                'description' => "Added event 'Planning Meeting'",
                'created_at' => Carbon::now()->subDays(4),
                'updated_at' => Carbon::now()->subDays(4)
            ],
            [
                'user_id' => $organizers[2]->id,
                'action_type' => 'EVENT_COMPLETED',
                'entity_type' => 'Event',
                'entity_id' => 1,
                'description' => "Completed event 'Planning Meeting'",
                'created_at' => Carbon::now()->subDays(3),
                'updated_at' => Carbon::now()->subDays(3)
            ],
            [
                'user_id' => $organizers[2]->id,
                'action_type' => 'JUDGE_ASSIGNED',
                'entity_type' => 'User',
                'entity_id' => $judges[4]->id,
                'description' => "Assigned Judge {$judges[4]->name} as head judge",
                'created_at' => Carbon::now()->subDays(2),
                'updated_at' => Carbon::now()->subDays(2)
            ],
            [
                'user_id' => $organizers[2]->id,
                'action_type' => 'EVENT_CREATED',
                'entity_type' => 'Event',
                'entity_id' => 2,
                'description' => "Added event 'Contestant Registration Opens'",
                'created_at' => Carbon::now()->subDay(),
                'updated_at' => Carbon::now()->subDay()
            ],
            [
                'user_id' => $organizers[2]->id,
                'action_type' => 'EVENT_UPDATED',
                'entity_type' => 'Event',
                'entity_id' => 2,
                'description' => "Updated event 'Contestant Registration Opens' status to 'In Progress'",
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]
        ];

        foreach ($setupActivities as $activityData) {
            Activity::create(array_merge(['pageant_id' => $setupPageant->id], $activityData));
        }

        // Update the Updates.md file to document the seeder
        $this->updateUpdatesFile();
    }

    /**
     * Add entry to Updates.md file
     */
    private function updateUpdatesFile(): void
    {
        $updatesPath = base_path('Updates.md');
        
        if (!file_exists($updatesPath)) {
            file_put_contents($updatesPath, "# Laravel 12 with Inertia.js and Vue Conversion\n\n## Updates\n\n");
        }
        
        $timestamp = Carbon::now()->format('Y-m-d h:iA');
        $content = file_get_contents($updatesPath);
        
        $newEntry = "- **{$timestamp}**: Created detailed pageant seed data:\n";
        $newEntry .= "  - Added two fully populated pageants with real data for all fields\n";
        $newEntry .= "  - Created 'Miss Universe 2025' active pageant with contestants, events, judges, and activities\n";
        $newEntry .= "  - Created 'Miss World 2025' setup pageant showing initial configuration state\n";
        $newEntry .= "  - Added proper relationships between models including categories, events, segments\n";
        $newEntry .= "  - Created realistic timeline of events with appropriate status progression\n";
        $newEntry .= "  - Added varied scoring systems to demonstrate different evaluation methods\n";
        $newEntry .= "  - Generated complete activity logs showing pageant progression\n";
        $newEntry .= "  - Ensured all required data is available for PageantDetails component\n";
        $newEntry .= "  - Populated contestants with realistic profile information and scores\n";
        $newEntry .= "  - Created proper event phases to demonstrate timeline functionality\n\n";
        
        $content = str_replace("<CURRENT_CURSOR_POSITION>", $newEntry . "<CURRENT_CURSOR_POSITION>", $content);
        file_put_contents($updatesPath, $content);
    }
} 