<?php

namespace ItDevgroup\ModelChangeLog\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;

/**
 * Class ModelChangeLog
 * @package ItDevgroup\ModelChangeLog\Models
 * @property-read int $id
 * @property string $type_event
 * @property string $model_type
 * @property int $model_id
 * @property string $user_model
 * @property int $user_id
 * @property array $changes
 * @property Carbon $created_at
 * @property Model $model
 * @property Model $user
 * @property ChangeField[] $change_list
 *
 * @method static Builder|static query()
 */
class ModelChangeLog extends Model
{
    /**
     * Type event list
     * @type string
     */
    public const TYPE_EVENT_CREATE = 'create';
    public const TYPE_EVENT_UPDATE = 'update';
    public const TYPE_EVENT_DELETE = 'delete';
    public const TYPE_EVENT_SOFT_DELETE = 'soft_delete';
    public const TYPE_EVENT_FORCE_DELETE = 'force_delete';
    /**
     * @inheritDoc
     */
    public const UPDATED_AT = null;

    /**
     * @inheritDoc
     */
    protected $casts = [
        'changes' => 'array',
        'created_at' => 'datetime',
    ];
    /**
     * @inheritDoc
     */
    protected $hidden = [
        'model_type',
        'model_id',
        'user_model',
        'user_id',
        'changes',
    ];
    /**
     * @inheritDoc
     */
    protected $appends = [
        'change_list'
    ];

    /**
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->table = Config::get('model_change_log.table');

        parent::__construct($attributes);
    }

    /**
     * @return MorphTo
     */
    public function model(): MorphTo
    {
        return $this->morphTo(null, 'model_type', 'model_id')->withTrashed();
    }

    /**
     * @return MorphTo
     */
    public function user(): MorphTo
    {
        return $this->morphTo(null, 'user_model', 'user_id');
    }

    /**
     * @return ChangeField[]|Collection
     */
    public function getChangeListAttribute(): array|Collection
    {
        $data = collect();

        foreach ($this->getAttribute('changes') as $fieldName => $values) {
            $data->push(ChangeField::register($fieldName, $values[0], $values[1]));
        }

        return $data;
    }
}
