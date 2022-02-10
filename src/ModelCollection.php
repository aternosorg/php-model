<?php

namespace Aternos\Model;

/**
 * Class ModelCollection
 *
 * @package Aternos\Model
 */
class ModelCollection implements \Iterator, \Countable, \ArrayAccess
{
    protected array $models = [];
    protected int $iterator = 0;

    /**
     * Add a model
     *
     * @param ModelInterface $model
     */
    public function add(ModelInterface $model)
    {
        $this->models[] = $model;
    }

    /**
     * Return the current element
     *
     * @return ModelInterface
     */
    public function current(): ModelInterface
    {
        return $this->models[$this->iterator];
    }

    /**
     * Move forward to next element
     *
     * @return void
     */
    public function next(): void
    {
        $this->iterator++;
    }

    /**
     * Return the key of the current element
     *
     * @return int
     */
    public function key(): int
    {
        return $this->iterator;
    }

    /**
     * Checks if current position is valid
     *
     * @return boolean
     */
    public function valid(): bool
    {
        return array_key_exists($this->iterator, $this->models);
    }

    /**
     * Rewind the Iterator to the first element
     *
     * @return void
     */
    public function rewind(): void
    {
        $this->iterator = 0;
    }

    /**
     * Count elements of an object
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->models);
    }

    /**
     * Whether an offset exists
     *
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        return isset($this->models[$offset]);
    }

    /**
     * Offset to retrieve
     *
     * @param mixed $offset
     * @return ModelInterface
     */
    public function offsetGet($offset): ModelInterface
    {
        return $this->models[$offset];
    }

    /**
     * Offset to set
     *
     * @param $offset
     * @param $value
     */
    public function offsetSet($offset, $value): void
    {
        $this->models[$offset] = $value;
    }

    /**
     * Offset to unset
     *
     * @param $offset
     */
    public function offsetUnset($offset): void
    {
        unset($this->models[$offset]);
    }

}