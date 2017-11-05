<?php

namespace Lampager\Idiorm\Concerns;

/**
 * Trait HasSnakeAliases
 */
trait HasSnakeAliases
{
    /**
     * @param  string $name
     * @return bool
     */
    protected function hasSnakeMethod($name)
    {
        return $this->performOnSnakeMethod(
            $name,
            static function () {
                return true;
            },
            static function () {
                return false;
            }
        );
    }

    /**
     * @param  string $name
     * @param  array  $args
     * @return mixed
     */
    protected function callSnakeMethod($name, array $args)
    {
        return $this->performOnSnakeMethod(
            $name,
            function (\ReflectionMethod $method) use ($args) {
                return $method->invokeArgs($this, $args);
            },
            static function ($name) {
                throw new \BadMethodCallException('Call to undefined method ' . static::class . '::' . $name . '()');
            }
        );
    }

    /**
     * @param  string   $name
     * @param  callable $handleInvoke
     * @param  callable $handleError
     * @return mixed
     */
    protected function performOnSnakeMethod($name, callable $handleInvoke, callable $handleError)
    {
        static $class;
        if (!$class) {
            $class = new \ReflectionClass(static::class);
        }
        $camel = str_replace('_', '', ucwords($name, '_'));
        if ($class->hasMethod($camel)) {
            $method = $class->getMethod($camel);
            if ($method->isPublic()) {
                return $handleInvoke($method);
            }
            // @codeCoverageIgnoreStart
        }
        // @codeCoverageIgnoreEnd
        return $handleError($name);
    }

    /**
     * @param  string $name
     * @param  array  $args
     * @return mixed
     */
    public function __call($name, array $args)
    {
        return $this->callSnakeMethod($name, $args);
    }
}
