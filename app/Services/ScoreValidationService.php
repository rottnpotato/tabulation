<?php

namespace App\Services;

use App\Models\Criteria;

class ScoreValidationService
{
    /**
     * Validate a score against its criteria
     */
    public function validateScore(float $score, Criteria $criteria): array
    {
        $errors = [];

        // Range validation
        if ($score < $criteria->min_score || $score > $criteria->max_score) {
            $errors[] = [
                'type' => 'range',
                'message' => "Score must be between {$criteria->min_score} and {$criteria->max_score}",
                'expected_range' => [
                    'min' => $criteria->min_score,
                    'max' => $criteria->max_score,
                ],
                'received' => $score,
            ];
        }

        // Decimal validation
        if (! $criteria->allow_decimals && floor($score) != $score) {
            $errors[] = [
                'type' => 'decimal_not_allowed',
                'message' => 'Decimal values are not allowed for this criteria',
                'received' => $score,
            ];
        }

        // Decimal places validation
        if ($criteria->allow_decimals && $criteria->decimal_places > 0) {
            $scoreStr = (string) $score;
            if (strpos($scoreStr, '.') !== false) {
                $decimalPart = substr($scoreStr, strpos($scoreStr, '.') + 1);
                if (strlen($decimalPart) > $criteria->decimal_places) {
                    $errors[] = [
                        'type' => 'too_many_decimals',
                        'message' => "Maximum {$criteria->decimal_places} decimal places allowed",
                        'max_decimal_places' => $criteria->decimal_places,
                        'received_decimal_places' => strlen($decimalPart),
                        'received' => $score,
                    ];
                }
            }
        }

        return $errors;
    }

    /**
     * Validate multiple scores
     */
    public function validateScores(array $scores, $criteria): array
    {
        $errors = [];
        $criteriaById = $criteria->keyBy('id');

        foreach ($scores as $criteriaId => $score) {
            if (! $criteriaById->has($criteriaId)) {
                $errors["scores.{$criteriaId}"] = [
                    'type' => 'invalid_criteria',
                    'message' => 'Invalid criteria ID provided',
                    'criteria_id' => $criteriaId,
                ];

                continue;
            }

            if (! is_numeric($score)) {
                $errors["scores.{$criteriaId}"] = [
                    'type' => 'not_numeric',
                    'message' => 'Score must be a number',
                    'received' => $score,
                ];

                continue;
            }

            $criterion = $criteriaById->get($criteriaId);
            $validationErrors = $this->validateScore((float) $score, $criterion);

            if (! empty($validationErrors)) {
                $errors["scores.{$criteriaId}"] = $validationErrors[0]; // Take first error
            }
        }

        return $errors;
    }

    /**
     * Get scoring system constraints
     */
    public function getScoringSystemConstraints(string $scoringSystem): array
    {
        return match ($scoringSystem) {
            'percentage' => [
                'min' => 0,
                'max' => 100,
                'allow_decimals' => true,
                'default_decimal_places' => 2,
            ],
            '1-10' => [
                'min' => 1,
                'max' => 10,
                'allow_decimals' => true,
                'default_decimal_places' => 1,
            ],
            '1-5' => [
                'min' => 1,
                'max' => 5,
                'allow_decimals' => false,
                'default_decimal_places' => 0,
            ],
            'points' => [
                'min' => 0,
                'max' => 50,
                'allow_decimals' => true,
                'default_decimal_places' => 2,
            ],
            default => [
                'min' => 0,
                'max' => 100,
                'allow_decimals' => true,
                'default_decimal_places' => 2,
            ]
        };
    }

    /**
     * Validate score string format for specific criteria
     */
    public function validateScoreFormat(string $scoreString, Criteria $criteria): array
    {
        $errors = [];

        // Check if numeric
        if (! is_numeric($scoreString)) {
            $errors[] = [
                'type' => 'not_numeric',
                'message' => 'Score must be a valid number',
                'received' => $scoreString,
            ];

            return $errors; // Return early if not numeric
        }

        $score = (float) $scoreString;

        // Validate against criteria
        return $this->validateScore($score, $criteria);
    }

    /**
     * Get validation rules for a set of criteria (for Laravel validation)
     */
    public function getValidationRules($criteria): array
    {
        $rules = [];

        foreach ($criteria as $criterion) {
            $ruleArray = ['required', 'numeric'];

            // Add min/max rules
            $ruleArray[] = "min:{$criterion->min_score}";
            $ruleArray[] = "max:{$criterion->max_score}";

            // Add decimal rules if needed
            if (! $criterion->allow_decimals) {
                $ruleArray[] = 'integer';
            } elseif ($criterion->decimal_places > 0) {
                $ruleArray[] = "regex:/^\d+(\.\d{1,{$criterion->decimal_places}})?$/";
            }

            $rules["scores.{$criterion->id}"] = $ruleArray;
        }

        return $rules;
    }

    /**
     * Get custom validation messages for criteria
     */
    public function getValidationMessages($criteria): array
    {
        $messages = [];

        foreach ($criteria as $criterion) {
            $fieldKey = "scores.{$criterion->id}";

            $messages["{$fieldKey}.required"] = "Score for '{$criterion->name}' is required.";
            $messages["{$fieldKey}.numeric"] = "Score for '{$criterion->name}' must be a number.";
            $messages["{$fieldKey}.min"] = "Score for '{$criterion->name}' must be at least {$criterion->min_score}.";
            $messages["{$fieldKey}.max"] = "Score for '{$criterion->name}' cannot exceed {$criterion->max_score}.";
            $messages["{$fieldKey}.integer"] = "Score for '{$criterion->name}' must be a whole number (no decimals allowed).";

            if ($criterion->allow_decimals && $criterion->decimal_places > 0) {
                $messages["{$fieldKey}.regex"] = "Score for '{$criterion->name}' can have maximum {$criterion->decimal_places} decimal places.";
            }
        }

        return $messages;
    }
}
