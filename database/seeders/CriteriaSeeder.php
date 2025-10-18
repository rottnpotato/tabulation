<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Pageant;
use App\Models\Segment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CriteriaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all pageants
        $pageants = Pageant::all();

        foreach ($pageants as $pageant) {
            // Get segments for this pageant
            $segments = $pageant->segments;

            // Get categories for this pageant
            $categories = $pageant->categories;

            if ($segments->isEmpty() && $categories->isEmpty()) {
                // If no segments and categories, create general criteria
                $this->createGeneralCriteria($pageant);

                continue;
            }

            // If we have segments but no categories, create criteria for segments
            if ($segments->isNotEmpty() && $categories->isEmpty()) {
                foreach ($segments as $segment) {
                    $this->createCriteriaForSegment($pageant, $segment);
                }

                continue;
            }

            // If we have categories, create criteria for each category
            if ($categories->isNotEmpty()) {
                foreach ($categories as $category) {
                    $this->createCriteriaForCategory($pageant, $category);
                }
            }
        }
    }

    /**
     * Create general criteria for a pageant without segments or categories
     */
    private function createGeneralCriteria($pageant)
    {
        $generalCriteria = [
            [
                'name' => 'Beauty',
                'description' => 'Overall physical appearance and presence',
                'weight' => 30,
                'display_order' => 1,
            ],
            [
                'name' => 'Poise and Bearing',
                'description' => 'Elegance and composure while presenting',
                'weight' => 25,
                'display_order' => 2,
            ],
            [
                'name' => 'Communication Skills',
                'description' => 'Ability to articulate thoughts clearly',
                'weight' => 25,
                'display_order' => 3,
            ],
            [
                'name' => 'Intelligence',
                'description' => 'Quick thinking and problem solving abilities',
                'weight' => 20,
                'display_order' => 4,
            ],
        ];

        foreach ($generalCriteria as $criteria) {
            DB::table('criteria')->insert([
                'pageant_id' => $pageant->id,
                'segment_id' => null,
                'category_id' => null,
                'name' => $criteria['name'],
                'description' => $criteria['description'],
                'weight' => $criteria['weight'],
                'min_score' => 0,
                'max_score' => $this->getMaxScoreBySystem($pageant->scoring_system),
                'allow_decimals' => $this->allowDecimalsBySystem($pageant->scoring_system),
                'decimal_places' => $this->decimalPlacesBySystem($pageant->scoring_system),
                'display_order' => $criteria['display_order'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Create criteria for a specific segment
     */
    private function createCriteriaForSegment($pageant, $segment)
    {
        // Common criteria types based on segment type
        $criteriaBySegmentType = [
            'Evening Gown' => [
                ['name' => 'Grace and Elegance', 'weight' => 35, 'display_order' => 1],
                ['name' => 'Stage Presence', 'weight' => 25, 'display_order' => 2],
                ['name' => 'Gown Appropriateness', 'weight' => 25, 'display_order' => 3],
                ['name' => 'Overall Impression', 'weight' => 15, 'display_order' => 4],
            ],
            'Swimwear' => [
                ['name' => 'Physique and Fitness', 'weight' => 40, 'display_order' => 1],
                ['name' => 'Poise and Confidence', 'weight' => 30, 'display_order' => 2],
                ['name' => 'Stage Walk', 'weight' => 20, 'display_order' => 3],
                ['name' => 'Overall Appearance', 'weight' => 10, 'display_order' => 4],
            ],
            'Talent' => [
                ['name' => 'Technical Ability', 'weight' => 35, 'display_order' => 1],
                ['name' => 'Stage Presence', 'weight' => 25, 'display_order' => 2],
                ['name' => 'Creativity', 'weight' => 20, 'display_order' => 3],
                ['name' => 'Overall Performance', 'weight' => 20, 'display_order' => 4],
            ],
            'Interview' => [
                ['name' => 'Content of Answers', 'weight' => 40, 'display_order' => 1],
                ['name' => 'Communication Skills', 'weight' => 30, 'display_order' => 2],
                ['name' => 'Personality', 'weight' => 20, 'display_order' => 3],
                ['name' => 'Confidence', 'weight' => 10, 'display_order' => 4],
            ],
            'National Costume' => [
                ['name' => 'Costume Design', 'weight' => 35, 'display_order' => 1],
                ['name' => 'Cultural Representation', 'weight' => 30, 'display_order' => 2],
                ['name' => 'Presentation', 'weight' => 25, 'display_order' => 3],
                ['name' => 'Overall Impact', 'weight' => 10, 'display_order' => 4],
            ],
        ];

        // Default criteria if segment type doesn't match any predefined types
        $defaultCriteria = [
            ['name' => 'Overall Performance', 'weight' => 40, 'display_order' => 1],
            ['name' => 'Presentation Skills', 'weight' => 30, 'display_order' => 2],
            ['name' => 'Stage Presence', 'weight' => 30, 'display_order' => 3],
        ];

        // Select criteria based on segment name or use default
        $criteriaToCreate = $criteriaBySegmentType[$segment->name] ?? $defaultCriteria;

        // Assign criteria directly to the segment
        foreach ($criteriaToCreate as $criteria) {
            DB::table('criteria')->insert([
                'pageant_id' => $pageant->id,
                'segment_id' => $segment->id,
                'category_id' => null,
                'name' => $criteria['name'],
                'description' => 'Criteria for '.$segment->name.' segment',
                'weight' => $criteria['weight'],
                'min_score' => 0,
                'max_score' => $this->getMaxScoreBySystem($pageant->scoring_system),
                'allow_decimals' => $this->allowDecimalsBySystem($pageant->scoring_system),
                'decimal_places' => $this->decimalPlacesBySystem($pageant->scoring_system),
                'display_order' => $criteria['display_order'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Create criteria for a specific category
     */
    private function createCriteriaForCategory($pageant, $category)
    {
        // Default criteria for categories
        $defaultCriteria = [
            ['name' => 'Content', 'weight' => 40, 'display_order' => 1],
            ['name' => 'Presentation', 'weight' => 30, 'display_order' => 2],
            ['name' => 'Technique', 'weight' => 30, 'display_order' => 3],
        ];

        // Try to get criteria from category if available (stored as JSON)
        $categoryJsonCriteria = [];
        if (! empty($category->criteria)) {
            try {
                $categoryJsonCriteria = is_string($category->criteria)
                    ? json_decode($category->criteria, true)
                    : $category->criteria;

                if (! empty($categoryJsonCriteria) && is_array($categoryJsonCriteria)) {
                    // Format JSON criteria to match our expected structure
                    $formattedCriteria = [];
                    $displayOrder = 1;

                    foreach ($categoryJsonCriteria as $key => $value) {
                        $name = is_numeric($key) && isset($value['name']) ? $value['name'] : $key;
                        $weight = isset($value['weight']) ? $value['weight'] :
                                 (is_numeric($value) ? $value : 100 / count($categoryJsonCriteria));

                        $formattedCriteria[] = [
                            'name' => $name,
                            'weight' => $weight,
                            'display_order' => $displayOrder++,
                        ];
                    }

                    if (! empty($formattedCriteria)) {
                        $defaultCriteria = $formattedCriteria;
                    }
                }
            } catch (\Exception $e) {
                // If JSON parsing fails, use default criteria
            }
        }

        // Insert criteria for the category
        foreach ($defaultCriteria as $criteria) {
            DB::table('criteria')->insert([
                'pageant_id' => $pageant->id,
                'segment_id' => null,
                'category_id' => $category->id,
                'name' => $criteria['name'],
                'description' => 'Criteria for '.$category->name.' category',
                'weight' => $criteria['weight'],
                'min_score' => 0,
                'max_score' => $this->getMaxScoreBySystem($pageant->scoring_system),
                'allow_decimals' => $this->allowDecimalsBySystem($pageant->scoring_system),
                'decimal_places' => $this->decimalPlacesBySystem($pageant->scoring_system),
                'display_order' => $criteria['display_order'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Get max score based on scoring system
     */
    private function getMaxScoreBySystem($scoringSystem)
    {
        switch ($scoringSystem) {
            case '1-5':
                return 5;
            case '1-10':
                return 10;
            case 'points':
                return 50;
            case 'percentage':
            default:
                return 100;
        }
    }

    /**
     * Determine if decimals are allowed based on scoring system
     */
    private function allowDecimalsBySystem($scoringSystem)
    {
        return in_array($scoringSystem, ['percentage', '1-10']);
    }

    /**
     * Determine decimal places based on scoring system
     */
    private function decimalPlacesBySystem($scoringSystem)
    {
        return in_array($scoringSystem, ['percentage', '1-10', 'points']) ? 2 : 0;
    }
}
