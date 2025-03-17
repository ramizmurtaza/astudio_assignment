<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Job;
use Illuminate\Support\Facades\Schema;

class JobFilterService
{
    /**
     * Applies filters to the job query based on the request.
     * 
     * @param Request $request
     * @return Builder
     */
    public function applyFilters(Request $request): Builder
    {
        $query = Job::query();

        // Check if filters are provided in the request
        if ($request->has('filter')) {
            $filters = $this->parseFilters($request->input('filter'));
            $this->applyQueryFilters($query, $filters);
        }

        return $query;
    }

    /**
     * Parses the filter string into structured filter conditions.
     * 
     * @param string $filterString
     * @return array
     */
    private function parseFilters($filterString)
    {
        // Parse logical operators and build condition tree
        return $this->parseLogicalOperators($filterString);
    }

    /**
     * Applies filters to the query using the structured filter conditions.
     * 
     * @param Builder $query
     * @param array $filters
     * @return void
     */
    private function applyQueryFilters(Builder &$query, array $filters)
    {
        // Apply filters within a closure to handle complex conditions
        $query->where(function ($q) use ($filters) {
            $this->applyFilterGroup($q, $filters);
        });
    }

    /**
     * Recursively applies filters to the query and handles AND/OR logic.
     * 
     * @param Builder $query
     * @param array $filters
     * @param string $boolean
     * @return void
     */
    private function applyFilterGroup(Builder &$query, array $filters, $boolean = 'AND')
    {
        $first = true;

        foreach ($filters as $filter) {
            if (isset($filter['type'])) {
                // Apply filter based on type
                if ($filter['type'] === 'basic') {
                    $this->applyBasicFilter($query, $filter, $first, $boolean);
                } elseif ($filter['type'] === 'relationship') {
                    $this->applyRelationshipFilter($query, $filter, $first, $boolean);
                } elseif ($filter['type'] === 'eav') {
                    $this->applyEAVFilter($query, $filter, $first, $boolean);
                }
                $first = false;
            } elseif (isset($filter['operator'])) {
                // Apply operator logic for complex filters
                if (isset($filter[0]) && is_array($filter[0])) {
                    $query->where(function ($subQuery) use ($filter) {
                        $this->applyFilterGroup($subQuery, $filter[0], $filter['operator']);
                    }, null, null, strtoupper($filter['operator']));
                }
            }
        }
    }

    /**
     * Applies a basic filter to the query (e.g., field = value, LIKE, IN, etc.).
     * 
     * @param Builder $query
     * @param array $filter
     * @param bool $first
     * @param string $boolean
     * @return void
     */
    private function applyBasicFilter(Builder &$query, array $filter, $first, $boolean)
    {
        $method = $first ? 'where' : ($boolean === 'AND' ? 'where' : 'orWhere');

        // Ensure the column exists before querying
        if (!Schema::hasColumn('jobs', $filter['field'])) {
            return;
        }

        // Apply specific operator conditions
        if ($filter['operator'] === 'LIKE') {
            $query->$method($filter['field'], 'LIKE', "%{$filter['value']}%");
        } elseif ($filter['operator'] === 'IN') {
            $query->{$method . 'In'}($filter['field'], $filter['value']);
        } elseif ($filter['field'] === 'is_remote') {
            $query->$method($filter['field'], filter_var($filter['value'], FILTER_VALIDATE_BOOLEAN));
        } else {
            $query->$method($filter['field'], $filter['operator'], $filter['value']);
        }
    }

    /**
     * Applies a relationship filter to the query (e.g., locations, languages).
     * 
     * @param Builder $query
     * @param array $filter
     * @param bool $first
     * @param string $boolean
     * @return void
     */
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

    /**
     * Applies an EAV (Entity-Attribute-Value) filter to the query.
     * 
     * @param Builder $query
     * @param array $filter
     * @param bool $first
     * @param string $boolean
     * @return void
     */
    private function applyEAVFilter(Builder &$query, array $filter, $first, $boolean)
    {
        $method = $first ? 'whereHas' : ($boolean === 'AND' ? 'whereHas' : 'orWhereHas');

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
     * Tokenizes the filter string into individual tokens for parsing.
     * 
     * @param string $filterString
     * @return array
     */
    private function tokenizeFilterString($filterString)
    {
        // Regex pattern to match conditions and logical operators
        $pattern = '/(\(|\)|\bAND\b|\bOR\b|[\w\.:]+(?:\s*(?:=|!=|>=|<=|>|<|LIKE|IN|HAS_ANY|IS_ANY)\s*(?:\([^)]+\)|[^()\s]+)))/i';

        preg_match_all($pattern, $filterString, $matches);

        // Return filtered and trimmed tokens
        return array_map('trim', array_filter($matches[0]));
    }

    /**
     * Builds a condition tree from the tokenized filter string.
     * 
     * @param array $tokens
     * @return array
     */
    private function buildConditionTree(&$tokens)
    {
        $conditions = [];
        $currentGroup = [];

        while (!empty($tokens)) {
            $token = array_shift($tokens);

            if ($token === '(') {
                // Recursively handle nested conditions
                $nestedConditions = $this->buildConditionTree($tokens);
                if (!empty($nestedConditions)) {
                    $currentGroup = array_merge($currentGroup, $nestedConditions);
                }
            } elseif ($token === ')') {
                return $currentGroup;
            } elseif (strtoupper($token) === 'AND' || strtoupper($token) === 'OR') {
                if (!empty($currentGroup)) {
                    $currentGroup[] = ['operator' => strtoupper($token)];
                }
            } else {
                $parsedCondition = $this->parseCondition($token);
                if ($parsedCondition) {
                    $currentGroup[] = $parsedCondition;
                }
            }
        }

        return $currentGroup;
    }

    /**
     * Parses an individual condition (e.g., job_type=full-time) into structured format.
     * 
     * @param string $condition
     * @return array|null
     */
    private function parseCondition($condition)
    {
        preg_match('/([\w\.:]+)\s*(=|!=|>=|<=|>|<|LIKE|IN|HAS_ANY|IS_ANY)\s*(.+)/i', $condition, $matches);

        if (count($matches) < 4) {
            return null;
        }

        $field = $matches[1];
        $operator = strtoupper($matches[2]);
        $value = trim($matches[3], '()');

        if (in_array($operator, ['IN', 'HAS_ANY', 'IS_ANY'])) {
            $value = array_map('trim', explode(',', $value));
        }

        // Handle EAV (Entity-Attribute-Value) parsing
        if (str_starts_with($field, 'attribute:')) {
            return [
                'type' => 'eav',
                'attribute' => str_replace('attribute:', '', $field),
                'operator' => $operator,
                'value' => $value
            ];
        }

        // Handle relationship filters
        if (in_array($field, ['languages', 'locations', 'categories'])) {
            return [
                'type' => 'relationship',
                'relation' => $field,
                'operator' => $operator,
                'values' => $value
            ];
        }

        // Handle basic filters
        return [
            'type' => 'basic',
            'field' => $field,
            'operator' => $operator,
            'value' => $value
        ];
    }

    /**
     * Parses logical operators in the filter string and builds the condition tree.
     * 
     * @param string $filterString
     * @return array
     */
    private function parseLogicalOperators($filterString)
    {
        $tokens = $this->tokenizeFilterString($filterString);
        return $this->buildConditionTree($tokens);
    }
}
