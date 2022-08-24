<?php

namespace ItDevgroup\ModelChangeLog\Providers;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\ServiceProvider;
use ItDevgroup\ModelChangeLog\Console\Commands\ModelChangeLogClearCommand;
use ItDevgroup\ModelChangeLog\Console\Commands\ModelChangeLogPublishCommand;
use ItDevgroup\ModelChangeLog\Services\ModelChangeLogService;

/**
 * Class ModelChangeLogProvider
 * @package ItDevgroup\ModelChangeLog\Providers
 */
class ModelChangeLogProvider extends ServiceProvider
{
    /**
     * @return void
     * @throws BindingResolutionException
     */
    public function boot()
    {
        /** @var ModelChangeLogService $service */
        $service = $this->app->make(ModelChangeLogService::class);
        $service->init();
    }

    /**
     * @return void
     */
    public function register()
    {
        $this->loadCustomCommands();
        $this->loadCustomConfig();
        $this->loadCustomPublished();
        $this->loadCustomClasses();
    }

    /**
     * @return void
     */
    private function loadCustomCommands()
    {
        if ($this->app->runningInConsole()) {
            $this->commands(
                [
                    ModelChangeLogPublishCommand::class,
                    ModelChangeLogClearCommand::class,
                ]
            );
        }
    }

    /**
     * @return void
     */
    private function loadCustomConfig()
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/model_change_log.php', 'model_change_log');
    }

    /**
     * @return void
     */
    private function loadCustomPublished()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes(
                [
                    __DIR__ . '/../../config' => base_path('config')
                ],
                'config'
            );
            $this->publishes(
                [
                    __DIR__ . '/../../migration' => database_path('migrations')
                ],
                'migration'
            );
        }
    }

    /**
     * @return void
     */
    private function loadCustomClasses()
    {
        $this->app->singleton(ModelChangeLogService::class);
    }
}
