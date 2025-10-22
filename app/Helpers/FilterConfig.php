<?php

namespace App\Helpers;

use InvalidArgumentException;

class FilterConfig
{
    public const ALLOWED_TYPES = [
        'toggle',
        'range',
        'date_range',
        'checkboxes',
        'radio',
        'buttons',
        'tree'
    ];

    public string $label;
    public string $type;
    public bool $multiple = false;
    public bool $quickFilterDesktop = false;
    public bool $quickFilterMobile = false;
    public array $options = [];
    public $min = null;
    public $max = null;
    public ?string $minDate = null;
    public ?string $maxDate = null;

    public function __construct(string $label, string $type)
    {
        $this->validateType($type);
        $this->label = $label;
        $this->type = $type;
    }

    /**
     * Валидирует тип фильтра
     *
     * @param string $type
     * @throws InvalidArgumentException
     */
    private function validateType(string $type): void
    {
        if (!in_array($type, self::ALLOWED_TYPES)) {
            throw new InvalidArgumentException(
                "Invalid filter type '{$type}'. Allowed types: " . implode(', ', self::ALLOWED_TYPES)
            );
        }
    }

    public function setMultiple(bool $multiple = true): self
    {
        $this->multiple = $multiple;
        return $this;
    }

    public function setQuickFilter(bool $desktop = false, bool $mobile = false): self
    {
        $this->quickFilterDesktop = $desktop;
        $this->quickFilterMobile = $mobile;
        return $this;
    }

    public function setOptions(array $options): self
    {
        $this->options = $options;
        return $this;
    }

    public function setRange($min, $max): self
    {
        $this->min = $min;
        $this->max = $max;
        return $this;
    }

    public function setDateRange(string $minDate, string $maxDate): self
    {
        $this->minDate = $minDate;
        $this->maxDate = $maxDate;
        return $this;
    }

    public function toArray(): array
    {
        $filter = [
            'label' => $this->label,
            'type' => $this->type,
        ];

        if ($this->type !== 'toggle' && $this->type !== 'range' && $this->type !== 'date_range') {
            $filter['multiple'] = $this->multiple;
        }

        if (!empty($this->options)) {
            $filter['options'] = $this->options;
        }

        if ($this->min !== null && $this->max !== null) {
            $filter['min'] = $this->min;
            $filter['max'] = $this->max;
        }

        if ($this->minDate && $this->maxDate) {
            $filter['minDate'] = $this->minDate;
            $filter['maxDate'] = $this->maxDate;
        }

        if ($this->quickFilterDesktop) {
            $filter['quickFilterDesktop'] = true;
        }

        if ($this->quickFilterMobile) {
            $filter['quickFilterMobile'] = true;
        }

        return $filter;
    }

    /**
     * Получает список допустимых типов фильтров
     *
     * @return array
     */
    public static function getAllowedTypes(): array
    {
        return self::ALLOWED_TYPES;
    }
}
