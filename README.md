## 

## Description

- customizable automatic logging of any eloquent models
- rollback or apply any past changes through history
- it is possible to disable logging for a separate piece of code
- it is possible to enable or disable individual module events for a separate piece of code
- command to clean up old entries, can be applied in cron

## Install for laravel

**1.** Open file **config/app.php** and search

```
    'providers' => [
        ...
    ]
```

Add to section

```
        \ItDevgroup\ModelChangeLog\Providers\ModelChangeLogProvider::class,
```

Example

```
    'providers' => [
        ...
        \ItDevgroup\ModelChangeLog\Providers\ModelChangeLogProvider::class,
    ]
```

Attention! It is recommended to connect the service provider in the list last

**2.** Run commands

For creating config file

```
php artisan vendor:publish --provider="ItDevgroup\ModelChangeLog\Providers\ModelChangeLogProvider" --tag=config
```

For creating migration file

```
php artisan model:change:log:publish --tag=migration
```

For generate table

```
php artisan migrate
```


**3.** Open config file `config/model_change_log.php` and customize

## Command for clear old entries

```
php artisan model:change:log:clear
```

or run in laravel scheduler in the file `app/Console/Kernel.php`

```
protected function schedule(Schedule $schedule)
{
    ...
    $schedule->command('model:change:log:clear')->daily();
    ...
}
```

## Usage

#### Enable/Disable module for current script

```
ModelChangeLogFacade::logEnabled(
    false
);
```

#### Enable/Disable event for current script

```
\ItDevgroup\ModelChangeLog\Facades\ModelChangeLogFacade::eventEnabled(
    \ItDevgroup\ModelChangeLog\Models\ModelChangeLog::TYPE_EVENT_UPDATE,
    false
);
```

#### Events:

- \ItDevgroup\ModelChangeLog\Models\ModelChangeLog::TYPE_EVENT_CREATE
- \ItDevgroup\ModelChangeLog\Models\ModelChangeLog::TYPE_EVENT_UPDATE
- \ItDevgroup\ModelChangeLog\Models\ModelChangeLog::TYPE_EVENT_DELETE
- \ItDevgroup\ModelChangeLog\Models\ModelChangeLog::TYPE_EVENT_SOFT_DELETE
- \ItDevgroup\ModelChangeLog\Models\ModelChangeLog::TYPE_EVENT_FORCE_DELETE

#### Apply changes from history

```
/**
\ItDevgroup\ModelChangeLog\Facades\ModelChangeLogFacade::applyChanges(
    ModelChangeLog $model,
    array $fields = []
);
*/
\ItDevgroup\ModelChangeLog\Facades\ModelChangeLogFacade::applyChanges($model);
\ItDevgroup\ModelChangeLog\Facades\ModelChangeLogFacade::applyChanges($model, ['first_name', 'last_name']);
```

`$fields` - List of fields to be applied, if not specified, all fields from the history will be applied

#### Rollback changes from history

```
/**
\ItDevgroup\ModelChangeLog\Facades\ModelChangeLogFacade::rollbackChanges(
    ModelChangeLog $model,
    array $fields = []
);
*/
\ItDevgroup\ModelChangeLog\Facades\ModelChangeLogFacade::rollbackChanges($model);
\ItDevgroup\ModelChangeLog\Facades\ModelChangeLogFacade::rollbackChanges($model, ['first_name', 'last_name']);
```

`$fields` - List of fields to be applied, if not specified, all fields from the history will be applied

## Optional modification custom model

#### Get entries from history for current model

If you add trait `\ItDevgroup\ModelChangeLog\Traits\ModelChangeLogTrait`
to your eloquent model, then the history of this model will be available by relationship

```
class User extends Model
{
    use \ItDevgroup\ModelChangeLog\Traits\ModelChangeLogTrait;
    ...
```

```
$list = User::query()->find(1)->modelChangeLog;
$list[0]->change_list;
$list[7]->change_list;
```

#### Not logged fields for current model

```
class User extends Model
{
    public $notLogFields = [
        'password',
        'email_verified_at',
    ];
    ...
```

#### Default Field Value for Restoring a Model

Fields that will be filled with default values (when the model is restored), recommended for required model fields in case these fields are not in the history

```
class User extends Model
{
    public $restoreDefaultRawValues = [
        'email_verified_at' => '2022-01-24 15:00:00',
        'remember_token' => '12345',
    ];
    ...
```

All other work with the history is done through the eloquent model `\ItDevgroup\ModelChangeLog\Models\ModelChangeLog`
