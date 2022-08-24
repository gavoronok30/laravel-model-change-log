<?php

namespace ItDevgroup\ModelChangeLog\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use ItDevgroup\ModelChangeLog\Models\ModelChangeLog;

/**
 * Trait ModelChangeLogTrait
 * @package ItDevgroup\ModelChangeLog\Traits
 * @property ModelChangeLog[] $modelChangeLog
 */
trait ModelChangeLogTrait
{
    /**
     * @return MorphMany
     */
    public function modelChangeLog(): MorphMany
    {
        return $this->morphMany(
            ModelChangeLog::class,
            'model',
            'model_type',
            'model_id'
        )->orderBy('created_at', 'desc');
    }
}
