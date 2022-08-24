<?php

namespace ItDevgroup\ModelChangeLog\Observers;

use Illuminate\Database\Eloquent\Model;
use ItDevgroup\ModelChangeLog\Services\ModelChangeLogService;

/**
 * Class ClassObserver
 * @package ItDevgroup\ModelChangeLog\Observers
 */
class ClassObserver
{
    /**
     * @param ModelChangeLogService $service
     */
    public function __construct(
        private ModelChangeLogService $service
    ) {
    }

    /**
     * @param Model $model
     * @return void
     */
    public function created(Model $model)
    {
        $this->service->eventModelCreated($model);
    }

    /**
     * @param Model $model
     * @return void
     */
    public function updated(Model $model)
    {
        $this->service->eventModelUpdated($model);
    }

    /**
     * @param Model $model
     * @return void
     */
    public function deleted(Model $model)
    {
        if (method_exists($model, 'getDeletedAtColumn')) {
            $this->service->eventModelSoftDeleted($model);
        } else {
            $this->service->eventModelDeleted($model);
        }
    }

    /**
     * @param Model $model
     * @return void
     */
    public function forceDeleted(Model $model)
    {
        $this->service->eventModelForceDeleted($model);
    }
}
