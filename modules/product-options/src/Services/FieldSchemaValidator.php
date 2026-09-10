<?php

namespace EzLens\ProductOptions\Services;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Validates and normalizes Product Options field schemas.
 * Keeps untrusted builder payloads constrained to supported field shapes.
 */
final class FieldSchemaValidator {
    /**
     * Keys starting with "_code_" are reserved internal fields (builder metadata).
     * They are skipped by renderers, sanitizers, validators, and pricing loops.
     * @see EzLens_Product_Options_Helpers::is_code_field_key()
     */
    private const ALLOWED_TYPES = [
        'text','email','phone','textarea','number','select','radio','checkbox',
        'image_select','color','date','time','upload','heading','divider','spacer',
        'group','html'
    ];

    public function normalize($fields) {
        if (!is_array($fields)) return [];
        $normalized = [];
        foreach ($fields as $field) {
            $item = $this->normalize_field($field);
            if ($item !== null) $normalized[] = $item;
        }
        return $normalized;
    }

    private function normalize_field($field) {
        if (!is_array($field)) return null;

        $type = sanitize_key($field['type'] ?? 'text');
        if (!in_array($type, self::ALLOWED_TYPES, true)) return null;

        $name = sanitize_key($field['name'] ?? '');
        if ($name === '') return null;

        $result = $field;
        $result['name'] = $name;
        $result['type'] = $type;
        $result['label'] = sanitize_text_field($field['label'] ?? '');
        $result['placeholder'] = sanitize_text_field($field['placeholder'] ?? '');
        $result['description'] = sanitize_textarea_field($field['description'] ?? '');
        $result['required'] = !empty($field['required']);
        $result['price'] = $this->positive_number($field['price'] ?? 0);

        if (isset($field['options'])) {
            $result['options'] = $this->normalize_options($field['options']);
        }

        if (isset($field['conditions']) || isset($field['conditional_logic'])) {
            $conditions = $field['conditions'] ?? $field['conditional_logic'];
            $result['conditions'] = $this->normalize_conditions($conditions);
            unset($result['conditional_logic']);
        }

        if (!empty($field['children']) && is_array($field['children'])) {
            $result['children'] = $this->normalize($field['children']);
        } elseif ($type === 'group') {
            $result['children'] = [];
        }

        return $result;
    }

    private function normalize_options($options) {
        if (!is_array($options)) return [];
        $result = [];
        foreach ($options as $option) {
            if (!is_array($option)) continue;
            $value = sanitize_text_field($option['value'] ?? '');
            if ($value === '') continue;
            $result[] = [
                'value' => $value,
                'label' => sanitize_text_field($option['label'] ?? $value),
                'price' => $this->positive_number($option['price'] ?? 0),
            ];
        }
        return $result;
    }

    private function normalize_conditions($conditions) {
        if (!is_array($conditions)) return [];
        $logic = (($conditions['logic'] ?? 'all') === 'any') ? 'any' : 'all';
        $rules_source = isset($conditions['rules']) && is_array($conditions['rules'])
            ? $conditions['rules'] : $conditions;
        $rules = [];
        foreach ($rules_source as $rule) {
            if (!is_array($rule)) continue;
            $field = sanitize_key($rule['field'] ?? $rule['field_key'] ?? '');
            $operator = sanitize_key($rule['operator'] ?? 'equals');
            if ($field === '' || !in_array($operator, [
                'equals','not_equals','contains','not_contains','greater_than','less_than',
                'greater_or_equal','less_or_equal','empty','not_empty'
            ], true)) continue;
            $rules[] = [
                'field' => $field,
                'operator' => $operator,
                'value' => is_scalar($rule['value'] ?? '') ? sanitize_text_field((string) ($rule['value'] ?? '')) : '',
            ];
        }
        return ['logic' => $logic, 'rules' => $rules];
    }

    private function positive_number($value) {
        $number = is_numeric($value) ? (float) $value : 0;
        return $number > 0 ? $number : 0;
    }
}
