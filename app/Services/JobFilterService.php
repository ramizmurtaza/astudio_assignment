<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Job;
use Illuminate\Support\Facades\Schema;

class JobFilterService
{
    public function applyFilters(Request $request): Builder
    {
        $query = Job::query();

        if ($request->has('filter')) {
            $filters = $this->parseFilters($request->input('filter'));
            $this->applyQueryFilters($query, $filters);
        }

        return $query;
    }

    private function parseFilters($filterString)
    {
        // Convert the string into structured filter conditions
        // Example: "job_type=full-time AND (languages HAS_ANY (PHP,JavaScript))"
        return $this->parseLogicalOperators($filterString);
    }

    private function applyQueryFilters(Builder &$query, array $filters)
    {
        $query->where(function ($q) use ($filters) {
            $this->applyFilterGroup($q, $filters);
        });
    }

    /**
     * Recursively applies filters and handles AND/OR logic.
     */
    private function applyFilterGroup(Builder &$query, array $filters, $boolean = 'AND')
    {
        $first = true;

        foreach ($filters as $filter) {
            if (isset($filter['type'])) {
                if ($filter['type'] === 'basic') {
                    $this->applyBasicFilter($query, $filter, $first, $boolean);
                } elseif ($filter['type'] === 'relationship') {
                    $this->applyRelationshipFilter($query, $filter, $first, $boolean);
                } elseif ($filter['type'] === 'eav') {
                    $this->applyEAVFilter($query, $filter, $first, $boolean);
                }
                $first = false;
            } elseif (isset($filter['operator'])) {
                // ✅ Ensure the filter contains valid conditions before applying
                if (isset($filter[0]) && is_array($filter[0])) {
                    $query->where(function ($subQuery) use ($filter) {
                        $this->applyFilterGroup($subQuery, $filter[0], $filter['operator']);
                    }, null, null, strtoupper($filter['operator']));
                }
            }
        }
    }


    private function applyBasicFilter(Builder &$query, array $filter, $first, $boolean)
    {
        $method = $first ? 'where' : ($boolean === 'AND' ? 'where' : 'orWhere');

        // ✅ Check if the column exists in the jobs table before querying
        if (!Schema::hasColumn('jobs', $filter['field'])) {
            return; // Skip invalid columns
        }

        if ($filter['operator'] === 'LIKE') {
            $query->$method($filter['field'], 'LIKE', "%{$filter['value']}%");
        } elseif ($filter['operator'] === 'IN') {
            $query->{$method . 'In'}($filter['field'], $filter['value']); // ✅ Correctly handle `IN`
        } elseif ($filter['field'] === 'is_remote') {
            // ✅ Ensure proper boolean handling
            $query->$method($filter['field'], filter_var($filter['value'], FILTER_VALIDATE_BOOLEAN));
        } else {
            $query->$method($filter['field'], $filter['operator'], $filter['value']);
        }
    }

    private function applyRelationshipFilter(Builder &$query, array $filter, $first, $boolean)
    {
        $method = $first ? 'whereHas' : ($boolean === 'AND' ? 'whereHas' : 'orWhereHas');

        if ($filter['relation'] === 'locations') {
            $query->$method('locations', function ($q) use ($filter) {
                $q->where(function ($subQuery) use ($filter) {
                    foreach ($filter['values'] as $location) {
                        $subQuery->orWhere('city', 'LIKE', "%{$location}%")
                            ->orWhere('state', 'LIKE', "%{$location}%")
                            ->orWhere('country', 'LIKE', "%{$location}%");
                    }
                });
            });
        } else {
            $query->$method($filter['relation'], function ($q) use ($filter) {
                $q->whereIn("{$filter['relation']}.name", $filter['values']);
            });
        }
    }

    private function applyEAVFilter(Builder &$query, array $filter, $first, $boolean)
    {
        $method = $first ? 'whereHas' : ($boolean === 'AND' ? 'whereHas' : 'orWhereHas');

        // ✅ Use `jobAttributes()` instead of `attributes()`
        $query->$method('jobAttributes', function ($q) use ($filter) {
            $q->whereHas('attribute', function ($attrQuery) use ($filter) {
                $attrQuery->where('name', $filter['attribute']);
            });

            if ($filter['operator'] === 'LIKE') {
                $q->where('value', 'LIKE', "%{$filter['value']}%");
            } else {
                $q->where('value', $filter['operator'], $filter['value']);
            }
        });
    }

    /**
     * Tokenizes the filter string into an array of conditions, operators, and parentheses.
     */
    private function tokenizeFilterString($filterString)
    {
        // Improved regex:
        // - Matches AND, OR (case insensitive)
        // - Matches conditions (e.g., job_type=full-time, attribute:years_experience>=3)
        // - Handles nested conditions like (languages HAS_ANY (PHP, JavaScript))
        $pattern = '/(\(|\)|\bAND\b|\bOR\b|[\w\.:]+(?:\s*(?:=|!=|>=|<=|>|<|LIKE|IN|HAS_ANY|IS_ANY)\s*(?:\([^)]+\)|[^()\s]+)))/i';

        preg_match_all($pattern, $filterString, $matches);

        // Remove empty elements and trim spaces
        return array_map('trim', array_filter($matches[0]));
    }

    /**
     * Builds a nested condition tree from tokens.
     */
    private function buildConditionTree(&$tokens)
    {
        $conditions = [];
        $currentGroup = [];

        while (!empty($tokens)) {
            $token = array_shift($tokens);

            if ($token === '(') {
                // Recursively process grouped conditions
                $nestedConditions = $this->buildConditionTree($tokens);
                if (!empty($nestedConditions)) {
                    $currentGroup = array_merge($currentGroup, $nestedConditions);
                }
            } elseif ($token === ')') {
                // Return grouped conditions
                return $currentGroup;
            } elseif (strtoupper($token) === 'AND' || strtoupper($token) === 'OR') {
                if (!empty($currentGroup)) {
                    $currentGroup[] = ['operator' => strtoupper($token)];
                }
            } else {
                // Parse condition and flatten structure
                $parsedCondition = $this->parseCondition($token);
                if ($parsedCondition) {
                    $currentGroup[] = $parsedCondition;
                }
            }
        }

        return $currentGroup;
    }




    /**
     * Parses individual conditions like "job_type=full-time" into structured format.
     */
    private function parseCondition($condition)
    {
        preg_match('/([\w\.:]+)\s*(=|!=|>=|<=|>|<|LIKE|IN|HAS_ANY|IS_ANY)\s*(.+)/i', $condition, $matches);

        if (count($matches) < 4) {
            return null; // Invalid condition
        }

        $field = $matches[1];
        $operator = strtoupper($matches[2]);
        $value = trim($matches[3], '()'); // Remove surrounding parentheses for "IN" values

        // Convert comma-separated values into an array for relationships
        if (in_array($operator, ['IN', 'HAS_ANY', 'IS_ANY'])) {
            $value = array_map('trim', explode(',', $value));
        }

        // Fix attribute parsing (remove `attribute:` prefix)
        if (str_starts_with($field, 'attribute:')) {
            return [
                'type' => 'eav',
                'attribute' => str_replace('attribute:', '', $field),
                'operator' => $operator,
                'value' => $value
            ];
        }

        // Check if it's a relationship filter
        if (in_array($field, ['languages', 'locations', 'categories'])) {
            return [
                'type' => 'relationship',
                'relation' => $field,
                'operator' => $operator,
                'values' => $value
            ];
        }

        // Otherwise, it's a basic filter
        return [
            'type' => 'basic',
            'field' => $field,
            'operator' => $operator,
            'value' => $value
        ];
    }




    private function parseLogicalOperators($filterString)
    {
        $tokens = $this->tokenizeFilterString($filterString);
        return $this->buildConditionTree($tokens);
    }
}
