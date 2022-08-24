<?php

namespace ItDevgroup\ModelChangeLog\Facades;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Facade;
use ItDevgroup\ModelChangeLog\Models\ModelChangeLog;

/**
 * Class ModelChangeLogFacade
 * @package ItDevgroup\ModelChangeLog\Facades
 * @method static void logEnabled(bool $value = true)
 * @method static void eventEnabled(string $key, bool $value = true)
 * @method static void eventModelCreate(Model $model)
 * @method static void eventModelUpdate(Model $model)
 * @method static void eventModelDelete(Model $model)
 * @method static void eventModelSoftDelete(Model $model)
 * @method static void eventModelForceDelete(Model $model)
 * @method static void applyChanges(ModelChangeLog $model, array $fields = [])
 * @method static void rollbackChanges(ModelChangeLog $model, array $fields = [])
 */
class ModelChangeLogFacade extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return ModelChangeLogHandler::class;
    }
}
