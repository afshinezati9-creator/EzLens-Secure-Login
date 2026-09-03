<?php

namespace EzLens\ProductOptions\Services;

if (!defined('ABSPATH')) {
    exit;
}

final class ConditionEvaluator {
    private const OPERATORS = [
        'equals', 'not_equals', 'contains', 'not_contains',
        'greater_than', 'less_than', 'greater_or_equal', 'less_or_equal',
        'empty', 'not_empty',
    ];

    public function matches($conditions, array $values): bool {
        if (!is_array($conditions) || empty($conditions)) return true;

        $rules = isset($conditions['rules']) && is_array($conditions['rules'])
            ? $conditions['rules']
            : $conditions;
        if (empty($rules)) return true;

        $results = [];
        foreach ($rules as $rule) {
            if (!is_array($rule)) continue;

            $field = sanitize_key($rule['field'] ?? $rule['field_key'] ?? '');
            $operator = sanitize_key($rule['operator'] ?? 'equals');
            if ($field === '' || !in_array($operator, self::OPERATORS, true)) continue;

            $results[] = $this->compare(
                $values[$field] ?? '',
                $rule['value'] ?? '',
                $operator
            );
        }

        if (!$results) return true;
        $logic = sanitize_key($conditions['logic'] ?? 'all');
        return $logic === 'any'
            ? in_array(true, $results, true)
            : !in_array(false, $results, true);
    }

    private function compare($actual, $expected, string $operator): bool {
        if (is_array($actual)) {
            $actual = array_map('strval', $actual);
            if (in_array($operator, ['equals', 'contains'], true)) {
                return in_array((string) $expected, $actual, true);
            }
            if ($operator === 'not_contains') {
                return !in_array((string) $expected, $actual, true);
            }
            $actual = implode(',', $actual);
        }

        $actual = (string) $actual;
        $expected = is_array($expected) ? implode(',', $expected) : (string) $expected;

        switch ($operator) {
            case 'not_equals': return $actual !== $expected;
            case 'contains': return strpos($actual, $expected) !== false;
            case 'not_contains': return strpos($actual, $expected) === false;
            case 'greater_than': return is_numeric($actual) && is_numeric($expected) && (float) $actual > (float) $expected;
            case 'less_than': return is_numeric($actual) && is_numeric($expected) && (float) $actual < (float) $expected;
            case 'greater_or_equal': return is_numeric($actual) && is_numeric($expected) && (float) $actual >= (float) $expected;
            case 'less_or_equal': return is_numeric($actual) && is_numeric($expected) && (float) $actual <= (float) $expected;
            case 'empty': return $actual === '';
            case 'not_empty': return $actual !== '';
            case 'equals': return $actual === $expected;
        }

        return false;
    }
}
