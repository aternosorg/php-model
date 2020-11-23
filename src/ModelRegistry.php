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
     */
    public function save(ModelInterface $model)
    {
        $this->registry[$model::getName()][$model->getId()] = $model;
    }

    /**
     * Get a model by name and id from the registry
     *
     * @param string $name
     * @param string $id
     * @return bool|ModelInterface
     */
    public function get(string $name, string $id)
    {
        if (!isset($this->registry[$name])) {
            return false;
        }

        if (!isset($this->registry[$name][$id])) {
            return false;
        }

        return $this->registry[$name][$id];
    }

    /**
     * Delete a model from the registry
     *
     * @param ModelInterface $model
     */
    public function delete(ModelInterface $model)
    {
        unset($this->registry[$model::getName()][$model->getId()]);
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