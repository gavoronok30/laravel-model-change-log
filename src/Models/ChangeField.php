<?php

namespace ItDevgroup\ModelChangeLog\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ChangeField
 * @package ItDevgroup\ModelChangeLog\Models
 * @property string $field
 * @property string $old
 * @property string $new
 */
class ChangeField extends Model
{
    /**
     * @inheritDoc
     */
    protected $fillable = [
        'field',
        'old',
        'new',
    ];

    /**
     * @param string $field
     * @param string $old
     * @param string $new
     * @return static
     */
    public static function register(
        string $field,
        string $old,
        string $new
    ): static {
        return new static(
            [
                'field' => $field,
                'old' => $old,
                'new' => $new,
            ]
        );
    }
}
