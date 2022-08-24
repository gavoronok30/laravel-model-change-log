<?php

namespace ItDevgroup\ModelChangeLog\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use ItDevgroup\ModelChangeLog\Models\ModelChangeLog;

/**
 * Class ModelChangeLogClearCommand
 * @package ItDevgroup\ModelChangeLog\Console\Commands
 */
class ModelChangeLogClearCommand extends Command
{
    private const DEFAULT_DAYS = 30;

    /**
     * @var string
     */
    protected $signature = 'model:change:log:clear';
    /**
     * @var string
     */
    protected $description = 'Model change log package: clear old data in log';

    /**
     * @return void
     */
    public function handle()
    {
        $days = Config::get('model_change_log.scheduler.clear_after_days');
        if ($days <= 0) {
            $days = self::DEFAULT_DAYS;
        }

        $date = Carbon::now()->subDays($days);

        ModelChangeLog::query()
            ->where('created_at', '<', $date)
            ->delete();
    }
}
