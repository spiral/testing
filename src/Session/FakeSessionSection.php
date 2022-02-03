<?php

declare(strict_types=1);

namespace Spiral\Testing\Session;

use Spiral\Session\SessionSectionInterface;

class FakeSessionSection implements SessionSectionInterface
{
    public function __construct(private string $name, private array $data)
    {
    }

    public function __get(string $name)
    {
        return $this->get($name);
    }

    public function __set(string $name, $value): void
    {
        $this->set($name, $value);
    }

    public function __isset(string $name)
    {
        return $this->has($name);
    }

    public function __unset(string $name): void
    {
        $this->delete($name);
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->getAll());
    }

    public function offsetExists(mixed $offset): bool
    {
        return $this->has($offset);
    }

    public function offsetGet(mixed $offset)
    {
        return $this->get($offset);
    }

    public function offsetSet(mixed $offset, mixed $value)
    {
        $this->set($offset, $value);
    }

    public function offsetUnset(mixed $offset)
    {
        $this->delete($offset);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getAll(): array
    {
        return $this->data;
    }

    public function set(string $name, $value): void
    {
        $this->data[$name] = $value;
    }

    public function has(string $name): bool
    {
        return isset($this->data[$name]);
    }

    public function get(string $name, $default = null)
    {
        if (! $this->has($name)) {
            return $default;
        }

        return $this->data[$name];
    }

    public function pull(string $name, $default = null)
    {
        $value = $this->get($name, $default);
        $this->delete($name);

        return $value;
    }

    public function delete(string $name)
    {
        unset($this->data[$this->name][$name]);
    }

    public function clear()
    {
        $this->data = [];
    }
}
