<?php

namespace Aternos\Model\Driver\Features;

use Aternos\Model\ModelInterface;

/**
 * Interface DeletableInterface
 *
 * @package Aternos\Model\Driver\Features
 */
interface DeletableInterface
{
    /**
     * Delete the model
     *
     * @param ModelInterface $model
     * @return bool
     */
    public function delete(ModelInterface $model): bool;
}