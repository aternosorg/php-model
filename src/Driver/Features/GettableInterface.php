<?php

namespace Aternos\Model\Driver\Features;

use Aternos\Model\ModelInterface;

/**
 * Interface GettableInterface
 *
 * @package Aternos\Model\Driver\Features
 */
interface GettableInterface
{
    /**
     * Get the model
     *
     * @param class-string<ModelInterface> $modelClass
     * @param mixed $id
     * @param ModelInterface|null $model
     * @return ModelInterface|null
     */
    public function get(string $modelClass, mixed $id, ?ModelInterface $model = null): ?ModelInterface;
}