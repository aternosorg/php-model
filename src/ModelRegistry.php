<?php

namespace Aternos\Model;

/**
 * Class ModelRegistry
 *
 * @package Aternos\Model
 */
class ModelRegistry
{
    /**
     * Registry array with all models
     *
     * @var array
     */
    protected array $registry = [];

    /**
     * Add a model to the registry
     *
     * @param ModelInterface $model
     * @return void
     */
    public function save(ModelInterface $model): void
    {
        $this->registry[$model::getName()][$model->getId()] = $model;
    }

    /**
     * Get a model by name and id from the registry
     *
     * @template TModel of ModelInterface
     * @param class-string<TModel|ModelInterface> $className
     * @param string $id
     * @return null|TModel
     */
    public function get(string $className, string $id): ?ModelInterface
    {
        $name = $className::getName();
        if (!isset($this->registry[$name])) {
            return null;
        }

        if (!isset($this->registry[$name][$id])) {
            return null;
        }

        return $this->registry[$name][$id];
    }

    /**
     * Delete a model from the registry
     *
     * @param ModelInterface $model
     * @return void
     */
    public function delete(ModelInterface $model): void
    {
        unset($this->registry[$model::getName()][$model->getId()]);
    }

    /**
     * @param string $modelName
     * @return void
     */
    public function clearModel(string $modelName): void
    {
        if (!isset($this->registry[$modelName])) {
            return;
        }
        unset($this->registry[$modelName]);
    }

    /**
     * @return void
     */
    public function clearAll(): void
    {
        $this->registry = [];
    }

    /**
     * @var ModelRegistry|null
     */
    protected static ?ModelRegistry $instance = null;

    /**
     * @return ModelRegistry
     */
    public static function getInstance(): ModelRegistry
    {
        if (self::$instance === null) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Prohibited for singleton
     */
    protected function __clone()
    {
    }

    /**
     * Prohibited for singleton
     */
    protected function __construct()
    {
    }
}