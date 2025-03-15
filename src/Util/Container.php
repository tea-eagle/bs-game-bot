<?php

namespace App\Telegram\Util;

/**
 * @deprecated 15.03.2025
 */
class Container
{
    private array $bindings = [];

    public function set(string $key, callable $resolver) {
        $this->bindings[$key] = $resolver;
    }

    public function get(string $key) {
        if (isset($this->bindings[$key])) {
            return $this->bindings[$key]($this);
        }

        return $this->resolve($key);
    }

    private function resolve(string $class) {
        $reflection = new ReflectionClass($class);
        $constructor = $reflection->getConstructor();

        if (!$constructor) {
            return new $class();
        }

        $params = $constructor->getParameters();
        $dependencies = [];

        foreach ($params as $param) {
            $type = $param->getType();
            if (!$type || $type->isBuiltin()) {
                throw new Exception("Не могу разрешить параметр {$param->getName()}");
            }

            $dependencies[] = $this->get($type->getName());
        }

        return $reflection->newInstanceArgs($dependencies);
    }
}
