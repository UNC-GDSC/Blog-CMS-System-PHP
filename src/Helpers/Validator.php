<?php

namespace App\Helpers;

/**
 * Input validation helper
 */
class Validator
{
    private $data = [];
    private $errors = [];
    private $rules = [];

    /**
     * Create validator instance
     *
     * @param array $data Data to validate
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Add validation rule for a field
     *
     * @param string $field Field name
     * @param string $rules Pipe-separated rules (e.g., 'required|min:3|max:100')
     * @param string $label Human-readable field name for error messages
     * @return self
     */
    public function rule($field, $rules, $label = null)
    {
        $this->rules[$field] = [
            'rules' => explode('|', $rules),
            'label' => $label ?? ucfirst(str_replace('_', ' ', $field))
        ];
        return $this;
    }

    /**
     * Validate all rules
     *
     * @return bool
     */
    public function validate()
    {
        $this->errors = [];

        foreach ($this->rules as $field => $config) {
            $value = $this->data[$field] ?? null;
            $label = $config['label'];

            foreach ($config['rules'] as $rule) {
                $this->applyRule($field, $value, $rule, $label);
            }
        }

        return empty($this->errors);
    }

    /**
     * Apply single validation rule
     *
     * @param string $field Field name
     * @param mixed $value Field value
     * @param string $rule Rule string
     * @param string $label Field label
     * @return void
     */
    private function applyRule($field, $value, $rule, $label)
    {
        // Parse rule and parameter (e.g., "min:3" => rule: min, param: 3)
        $parts = explode(':', $rule, 2);
        $ruleName = $parts[0];
        $param = $parts[1] ?? null;

        switch ($ruleName) {
            case 'required':
                if (empty($value) && $value !== '0') {
                    $this->errors[$field][] = "{$label} is required";
                }
                break;

            case 'min':
                if (!empty($value) && strlen($value) < $param) {
                    $this->errors[$field][] = "{$label} must be at least {$param} characters";
                }
                break;

            case 'max':
                if (!empty($value) && strlen($value) > $param) {
                    $this->errors[$field][] = "{$label} must not exceed {$param} characters";
                }
                break;

            case 'email':
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->errors[$field][] = "{$label} must be a valid email address";
                }
                break;

            case 'numeric':
                if (!empty($value) && !is_numeric($value)) {
                    $this->errors[$field][] = "{$label} must be a number";
                }
                break;

            case 'alpha':
                if (!empty($value) && !ctype_alpha(str_replace(' ', '', $value))) {
                    $this->errors[$field][] = "{$label} must contain only letters";
                }
                break;

            case 'alphanumeric':
                if (!empty($value) && !ctype_alnum(str_replace(' ', '', $value))) {
                    $this->errors[$field][] = "{$label} must contain only letters and numbers";
                }
                break;

            case 'url':
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_URL)) {
                    $this->errors[$field][] = "{$label} must be a valid URL";
                }
                break;
        }
    }

    /**
     * Get validation errors
     *
     * @return array
     */
    public function errors()
    {
        return $this->errors;
    }

    /**
     * Get first error for a field
     *
     * @param string $field Field name
     * @return string|null
     */
    public function firstError($field)
    {
        return $this->errors[$field][0] ?? null;
    }

    /**
     * Check if validation passed
     *
     * @return bool
     */
    public function passes()
    {
        return $this->validate();
    }

    /**
     * Check if validation failed
     *
     * @return bool
     */
    public function fails()
    {
        return !$this->validate();
    }

    /**
     * Sanitize string input
     *
     * @param string $value
     * @return string
     */
    public static function sanitize($value)
    {
        return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Sanitize array of inputs
     *
     * @param array $data
     * @return array
     */
    public static function sanitizeArray(array $data)
    {
        return array_map([self::class, 'sanitize'], $data);
    }
}
