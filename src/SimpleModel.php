<?php

namespace Aternos\Model;

use Aternos\Model\Driver\DriverRegistry;
use Aternos\Model\Driver\Mysqli\Mysqli;

/**
 * Class SimpleModel
 *
 * Very basic model implementing only the mysqli driver (not even registry).
 * Can be used for simple tasks, but is more of a demonstration.
 *
 * @package Aternos\Model
 */
abstract class SimpleModel extends BaseModel
{
    /**
     * @param string $id
     * @param bool $update
     * @return ModelInterface|bool
     */
    public static function get(string $id, bool $update = false)
    {
        $model = new static($id);
        if (DriverRegistry::getInstance()->getDriver(Mysqli::ID)->get($model)) {
            return $model;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function save(): bool
    {
        return DriverRegistry::getInstance()->getDriver(Mysqli::ID)->save($this);
    }

    /**
     * @return bool
     */
    public function delete(): bool
    {
        return DriverRegistry::getInstance()->getDriver(Mysqli::ID)->delete($this);
    }
}