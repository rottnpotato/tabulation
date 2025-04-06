<?php

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\Pageant;
use App\Models\User;
use App\Services\AuditLogService;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PageantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get or create admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => bcrypt('admin123'),
                'role' => 'admin',
                'email_verified_at' => now()
            ]
        );
        
        // Create organizers
        $organizers = [];
        for ($i = 1; $i <= 5; $i++) {
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
        
        // Create tabulators
        for ($i = 1; $i <= 3; $i++) {
            User::firstOrCreate(
                ['email' => "tabulator{$i}@example.com"],
                [
                    'name' => "Tabulator {$i}",
                    'password' => bcrypt('password'),
                    'role' => 'tabulator',
                    'email_verified_at' => now()
                ]
            );
        }
        
        // Create judges
        for ($i = 1; $i <= 8; $i++) {
            User::firstOrCreate(
                ['email' => "judge{$i}@example.com"],
                [
                    'name' => "Judge {$i}",
                    'password' => bcrypt('password'),
                    'role' => 'judge',
                    'email_verified_at' => now()
                ]
            );
        }
        
        // Sample pageant data
        $pageants = [
            [
                'name' => 'Miss Universe 2025',
                'description' => 'The most prestigious international beauty pageant',
                'start_date' => Carbon::now()->addMonths(2),
                'end_date' => Carbon::now()->addMonths(2)->addDays(7),
                'venue' => 'Grand Arena',
                'location' => 'New York, USA',
                'status' => 'Active',
                'created_by' => $admin->id,
                'organizers' => [$organizers[0]->id, $organizers[1]->id]
            ],
            [
                'name' => 'Miss World 2025',
                'description' => 'Beauty with a purpose',
                'start_date' => Carbon::now()->addMonths(3),
                'end_date' => Carbon::now()->addMonths(3)->addDays(5),
                'venue' => 'Royal Theatre',
                'location' => 'London, UK',
                'status' => 'Setup',
                'created_by' => $admin->id,
                'organizers' => [$organizers[1]->id]
            ],
            [
                'name' => 'Miss International 2025',
                'description' => 'Celebrating global diversity and culture',
                'start_date' => Carbon::now()->addMonths(5),
                'end_date' => Carbon::now()->addMonths(5)->addDays(3),
                'venue' => 'Tokyo Dome',
                'location' => 'Tokyo, Japan',
                'status' => 'Draft',
                'created_by' => $admin->id,
                'organizers' => [$organizers[2]->id]
            ],
            [
                'name' => 'Miss Earth 2024',
                'description' => 'Promoting environmental awareness',
                'start_date' => Carbon::now()->subMonths(1),
                'end_date' => Carbon::now()->subMonths(1)->addDays(10),
                'venue' => 'Manila Arena',
                'location' => 'Manila, Philippines',
                'status' => 'Completed',
                'created_by' => $admin->id,
                'organizers' => [$organizers[3]->id, $organizers[4]->id]
            ],
            [
                'name' => 'Miss Grand International 2024',
                'description' => 'Stop the war and violence',
                'start_date' => Carbon::now()->subMonths(2),
                'end_date' => Carbon::now()->subMonths(2)->addDays(7),
                'venue' => 'Impact Arena',
                'location' => 'Bangkok, Thailand',
                'status' => 'Completed',
                'created_by' => $admin->id,
                'organizers' => [$organizers[0]->id]
            ],
            [
                'name' => 'Miss Supranational 2024',
                'description' => 'Empowering women globally',
                'start_date' => Carbon::now()->subMonths(3),
                'end_date' => Carbon::now()->subMonths(3)->addDays(5),
                'venue' => 'Strzelecki Park Amphitheatre',
                'location' => 'Krynica-Zdrój, Poland',
                'status' => 'Archived',
                'created_by' => $admin->id,
                'organizers' => [$organizers[2]->id]
            ],
            [
                'name' => 'Miss Tourism 2023',
                'description' => 'Promoting travel and cultural exchange',
                'start_date' => Carbon::now()->subMonths(8),
                'end_date' => Carbon::now()->subMonths(8)->addDays(5),
                'venue' => 'Marina Bay Sands',
                'location' => 'Singapore',
                'status' => 'Archived',
                'created_by' => $admin->id,
                'organizers' => [$organizers[1]->id, $organizers[4]->id]
            ],
            [
                'name' => 'Ms. Universe 2023',
                'description' => 'Celebrating beauty at all ages',
                'start_date' => Carbon::now()->subMonths(6),
                'end_date' => Carbon::now()->subMonths(6)->addDays(3),
                'venue' => 'Convention Center',
                'location' => 'Las Vegas, USA',
                'status' => 'Cancelled',
                'created_by' => $admin->id,
                'organizers' => [$organizers[3]->id]
            ],
        ];
        
        // Create pageants and attach organizers
        foreach ($pageants as $pageantData) {
            $organizerIds = $pageantData['organizers'];
            unset($pageantData['organizers']);
            
            $pageant = Pageant::create($pageantData);
            $pageant->organizers()->attach($organizerIds);
            
            // Create audit log for pageant creation
            AuditLog::create([
                'user_id' => $admin->id,
                'user_role' => 'admin',
                'action_type' => 'PAGEANT_CREATED',
                'target_entity' => 'Pageant',
                'target_id' => $pageant->id,
                'details' => "Created pageant '{$pageant->name}'",
                'ip_address' => '127.0.0.1',
                'created_at' => $pageant->created_at,
                'updated_at' => $pageant->created_at
            ]);
            
            // Add additional audit logs for status changes if not in draft status
            if ($pageant->status !== 'Draft') {
                AuditLog::create([
                    'user_id' => $admin->id,
                    'user_role' => 'admin',
                    'action_type' => 'PAGEANT_STATUS_CHANGED',
                    'target_entity' => 'Pageant',
                    'target_id' => $pageant->id,
                    'details' => "Changed pageant '{$pageant->name}' status from 'Draft' to 'Setup'",
                    'ip_address' => '127.0.0.1',
                    'created_at' => $pageant->created_at->addDays(1),
                    'updated_at' => $pageant->created_at->addDays(1)
                ]);
            }
            
            if (in_array($pageant->status, ['Active', 'Completed', 'Archived', 'Cancelled'])) {
                AuditLog::create([
                    'user_id' => $admin->id,
                    'user_role' => 'admin',
                    'action_type' => 'PAGEANT_STATUS_CHANGED',
                    'target_entity' => 'Pageant',
                    'target_id' => $pageant->id,
                    'details' => "Changed pageant '{$pageant->name}' status from 'Setup' to '{$pageant->status}'",
                    'ip_address' => '127.0.0.1',
                    'created_at' => $pageant->created_at->addDays(2),
                    'updated_at' => $pageant->created_at->addDays(2)
                ]);
            }
            
            // Add organizing logs
            foreach ($organizerIds as $organizerId) {
                $organizer = User::find($organizerId);
                AuditLog::create([
                    'user_id' => $admin->id,
                    'user_role' => 'admin',
                    'action_type' => 'ORGANIZER_ASSIGNED',
                    'target_entity' => 'Pageant',
                    'target_id' => $pageant->id,
                    'details' => "Assigned organizer '{$organizer->name}' to pageant '{$pageant->name}'",
                    'ip_address' => '127.0.0.1',
                    'created_at' => $pageant->created_at->addHours(2),
                    'updated_at' => $pageant->created_at->addHours(2)
                ]);
            }
        }
        
        // Create a pageant with edit permission granted
        $pageantWithPermission = Pageant::create([
            'name' => 'Miss Charity 2024',
            'description' => 'Pageant dedicated to charitable causes',
            'start_date' => Carbon::now()->subMonths(2),
            'end_date' => Carbon::now()->subMonths(2)->addDays(3),
            'venue' => 'City Auditorium',
            'location' => 'Boston, USA',
            'status' => 'Unlocked_For_Edit',
            'created_by' => $admin->id,
            'is_edit_permission_granted' => true,
            'edit_permission_expires_at' => Carbon::now()->addDays(7),
            'edit_permission_granted_to' => $organizers[0]->id,
        ]);
        
        $pageantWithPermission->organizers()->attach([$organizers[0]->id]);
        
        AuditLog::create([
            'user_id' => $admin->id,
            'user_role' => 'admin',
            'action_type' => 'PAGEANT_CREATED',
            'target_entity' => 'Pageant',
            'target_id' => $pageantWithPermission->id,
            'details' => "Created pageant '{$pageantWithPermission->name}'",
            'ip_address' => '127.0.0.1',
            'created_at' => $pageantWithPermission->created_at,
            'updated_at' => $pageantWithPermission->created_at
        ]);
        
        AuditLog::create([
            'user_id' => $admin->id,
            'user_role' => 'admin',
            'action_type' => 'PAGEANT_STATUS_CHANGED',
            'target_entity' => 'Pageant',
            'target_id' => $pageantWithPermission->id,
            'details' => "Changed pageant '{$pageantWithPermission->name}' status from 'Draft' to 'Completed'",
            'ip_address' => '127.0.0.1',
            'created_at' => $pageantWithPermission->created_at->addDays(5),
            'updated_at' => $pageantWithPermission->created_at->addDays(5)
        ]);
        
        AuditLog::create([
            'user_id' => $admin->id,
            'user_role' => 'admin',
            'action_type' => 'GRANT_EDIT_PERMISSION',
            'target_entity' => 'Pageant',
            'target_id' => $pageantWithPermission->id,
            'details' => "Granted edit permission for pageant '{$pageantWithPermission->name}' to user '{$organizers[0]->name}' until {$pageantWithPermission->edit_permission_expires_at}",
            'ip_address' => '127.0.0.1',
            'created_at' => Carbon::now()->subDays(1),
            'updated_at' => Carbon::now()->subDays(1)
        ]);
        
        // Add some system action logs
        AuditLog::create([
            'user_id' => null,
            'user_role' => 'SYSTEM',
            'action_type' => 'SYSTEM_BACKUP',
            'target_entity' => null,
            'target_id' => null,
            'details' => "Automated system backup completed successfully",
            'ip_address' => '127.0.0.1',
            'created_at' => Carbon::now()->subDays(3),
            'updated_at' => Carbon::now()->subDays(3)
        ]);
        
        AuditLog::create([
            'user_id' => null,
            'user_role' => 'SYSTEM',
            'action_type' => 'PERMISSION_EXPIRY_WARNING',
            'target_entity' => 'Pageant',
            'target_id' => $pageantWithPermission->id,
            'details' => "Edit permission for pageant '{$pageantWithPermission->name}' will expire in 7 days",
            'ip_address' => '127.0.0.1',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);
    }
}
