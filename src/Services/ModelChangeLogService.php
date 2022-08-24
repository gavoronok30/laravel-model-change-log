<?php

namespace ItDevgroup\ModelChangeLog\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use ItDevgroup\ModelChangeLog\Models\ModelChangeLog;
use ItDevgroup\ModelChangeLog\Observers\ClassObserver;

/**
 * Class ModelChangeLogService
 * @package ItDevgroup\ModelChangeLog\Services
 */
class ModelChangeLogService
{
    /**
     * @var bool
     */
    private bool $logEnabled;
    /**
     * @var Model[]
     */
    private array $models;
    /**
     * @var array
     */
    private array $excludeFields;
    /**
     * @var Collection
     */
    private Collection $events;
    /**
     * @var Collection
     */
    private Collection $excludeFieldsForModel;

    public function __construct()
    {
        $this->models = Config::get('model_change_log.models', []);
        $this->excludeFields = Config::get('model_change_log.exclude_fields', []);
        $this->events = collect(Config::get('model_change_log.events', []));
        $this->logEnabled = (bool)Config::get('model_change_log.enabled', false);

        $this->excludeFieldsForModel = collect();
    }

    /**
     * @return void
     */
    public function init(): void
    {
        foreach ($this->models as $model) {
            $model::observe(ClassObserver::class);
        }
    }

    /**
     * @param string $key
     * @param bool $value
     * @return void
     */
    public function eventEnabled(string $key, bool $value = true): void
    {
        $this->events->put($key, $value);
    }

    /**
     * @param bool $value
     * @return void
     */
    public function logEnabled(bool $value = true): void
    {
        $this->logEnabled = $value;
    }

    /**
     * @param Model $model
     * @return void
     */
    public function eventModelCreated(Model $model): void
    {
        if (!$this->logEnabled || !$this->events->get(ModelChangeLog::TYPE_EVENT_CREATE)) {
            return;
        }

        $changes = $model->getAttributes();

        $changeFieldList = [];
        foreach ($changes as $field => $value) {
            $changeFieldList[$field] = [
                null,
                $value
            ];
        }

        $this->saveModel(
            ModelChangeLog::TYPE_EVENT_CREATE,
            $model,
            $changeFieldList
        );
    }

    /**
     * @param Model $model
     * @return void
     */
    public function eventModelUpdated(Model $model): void
    {
        if (!$this->logEnabled || !$this->events->get(ModelChangeLog::TYPE_EVENT_UPDATE)) {
            return;
        }

        $changes = $model->getChanges();
        $changes = $this->excludeFields($model, $changes);

        $changeFieldList = [];
        foreach ($changes as $field => $value) {
            $changeFieldList[$field] = [
                $model->getRawOriginal($field),
                $value
            ];
        }

        $this->saveModel(
            ModelChangeLog::TYPE_EVENT_UPDATE,
            $model,
            $changeFieldList
        );
    }

    /**
     * @param Model $model
     * @return void
     */
    public function eventModelDeleted(Model $model): void
    {
        if (!$this->logEnabled || !$this->events->get(ModelChangeLog::TYPE_EVENT_DELETE)) {
            return;
        }

        $changes = $model->getAttributes();

        $changeFieldList = [];
        foreach ($changes as $field => $value) {
            $changeFieldList[$field] = [
                $value,
                null
            ];
        }

        $this->saveModel(
            ModelChangeLog::TYPE_EVENT_DELETE,
            $model,
            $changeFieldList
        );
    }

    /**
     * @param Model $model
     * @return void
     */
    public function eventModelSoftDeleted(Model $model): void
    {
        if (!$this->logEnabled || !$this->events->get(ModelChangeLog::TYPE_EVENT_SOFT_DELETE)) {
            return;
        }

        $changes = $model->getAttributes();

        $changeFieldList = [
            $model->getDeletedAtColumn() => [
                null,
                $changes[$model->getDeletedAtColumn()]
            ]
        ];

        $this->saveModel(
            ModelChangeLog::TYPE_EVENT_SOFT_DELETE,
            $model,
            $changeFieldList
        );
    }

    /**
     * @param Model $model
     * @return void
     */
    public function eventModelForceDeleted(Model $model): void
    {
        if (!$this->logEnabled || !$this->events->get(ModelChangeLog::TYPE_EVENT_FORCE_DELETE)) {
            return;
        }

        $changes = $model->getAttributes();

        $changeFieldList = [];
        foreach ($changes as $field => $value) {
            $changeFieldList[$field] = [
                $value,
                null
            ];
        }

        $this->saveModel(
            ModelChangeLog::TYPE_EVENT_FORCE_DELETE,
            $model,
            $changeFieldList
        );
    }

    /**
     * @param ModelChangeLog $model
     * @param array $fields
     * @return void
     */
    public function applyChanges(ModelChangeLog $model, array $fields = []): void
    {
        $this->implementChanges($model, $fields, false);
    }

    /**
     * @param ModelChangeLog $model
     * @param array $fields
     * @return void
     */
    public function rollbackChanges(ModelChangeLog $model, array $fields = []): void
    {
        $this->implementChanges($model, $fields);
    }

    /**
     * @param ModelChangeLog $model
     * @param array $fields
     * @param bool $rollback
     * @return void
     */
    private function implementChanges(ModelChangeLog $model, array $fields = [], bool $rollback = true): void
    {
        $relatedModel = $model->model;

        switch ($model->type_event) {
            case ModelChangeLog::TYPE_EVENT_DELETE:
            case ModelChangeLog::TYPE_EVENT_FORCE_DELETE:
            case ModelChangeLog::TYPE_EVENT_SOFT_DELETE:
                if ($rollback) {
                    $relatedModel = $this->restoreRelatedModel($model);
                } else {
                    $relatedModel?->delete();

                    return;
                }
                break;
            case ModelChangeLog::TYPE_EVENT_CREATE:
                if ($rollback) {
                    $relatedModel?->delete();

                    return;
                } else {
                    $relatedModel = $this->restoreRelatedModel($model);
                }
                break;
        }

        $data = [];
        foreach ($model->change_list as $record) {
            if (!empty($fields) && !in_array($record->field, $fields)) {
                continue;
            }

            $data[$record->field] = $rollback ? $record->old : $record->new;
        }

        if (!empty($data)) {
            $relatedModel->setRawAttributes(array_merge($relatedModel->getRawOriginal(), $data));
            $relatedModel->save();
        }
    }

    /**
     * @param Model $model
     * @param array $fields
     * @return array
     */
    private function excludeFields(Model $model, array $fields): array
    {
        if ($this->excludeFieldsForModel->has($model::class)) {
            return $this->excludeFieldsForModel->get($model::class);
        }

        $fields = array_diff_key($fields, array_flip($this->excludeFields));

        if (isset($model->notLogFields) && is_array($model->notLogFields)) {
            $fields = array_diff_key($fields, array_flip($model->notLogFields));
        }

        $this->excludeFieldsForModel->put($model::class, $fields);

        return $fields;
    }

    /**
     * @param string $type
     * @param Model $model
     * @param array $changes
     * @return void
     */
    private function saveModel(string $type, Model $model, array $changes): void
    {
        if (!count($changes)) {
            return;
        }

        /** @var Model $authUser */
        $authUser = Auth::user();

        $modelChangeLog = new ModelChangeLog();
        $modelChangeLog->type_event = $type;
        $modelChangeLog->model()->associate($model);
        $modelChangeLog->user()->associate($authUser);
        $modelChangeLog->changes = $changes;
        $modelChangeLog->save();
    }

    /**
     * @param ModelChangeLog $model
     * @return Model
     */
    private function restoreRelatedModel(ModelChangeLog $model): Model
    {
        $relatedModel = $model->model;
        $className = $model->model_type;
        if (!$relatedModel) {
            $relatedModel = new $className();
            if ($relatedModel->restoreDefaultRawValues) {
                $relatedModel->setRawAttributes($relatedModel->restoreDefaultRawValues);
            }
            $relatedModel->setAttribute($relatedModel->getKeyName(), $model->model_id);
            $relatedModel->syncOriginal();
        } else {
            if (method_exists($relatedModel, 'restore')) {
                $relatedModel->restore();
            }
        }

        return $relatedModel;
    }
}
