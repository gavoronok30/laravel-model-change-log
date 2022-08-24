<?php

namespace ItDevgroup\ModelChangeLog\Facades;

use Illuminate\Support\Collection;
use ItDevgroup\ModelChangeLog\Services\ModelChangeLogService;
use ReflectionException;
use ReflectionMethod;

/**
 * Class ModelChangeLogHandler
 * @package ItDevgroup\ModelChangeLog\Facades
 */
class ModelChangeLogHandler
{
    /**
     * @var Collection
     */
    private Collection $methods;

    /**
     * @param ModelChangeLogService $service
     */
    public function __construct(
        private ModelChangeLogService $service
    ) {
        $this->methods = collect();
    }

    /**
     * @param string $methodName
     * @param array $arguments
     * @return mixed
     * @throws ReflectionException
     */
    public function __call(string $methodName, array $arguments): mixed
    {
        if (!$this->methods->has($methodName)) {
            $this->methods->put(
                $methodName,
                (new ReflectionMethod($this->service, $methodName))->isPublic()
            );
        }

        if ($this->methods->get($methodName)) {
            return $this->service->$methodName(...$arguments);
        }

        return null;
    }
}
