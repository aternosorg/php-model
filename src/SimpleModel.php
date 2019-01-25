<?php

namespace Aternos\Model;

use Aternos\Model\Driver\DriverFactory;

/**
 * Class SimpleModel
 *
 * Very basic model implementing only the nosql driver (not even registry).
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
        if (DriverFactory::getInstance()->assembleNoSQLDriver()->get($model)) {
            return $model;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function save(): bool
    {
        return DriverFactory::getInstance()->assembleNoSQLDriver()->save($this);
    }

    /**
     * @return bool
     */
    public function delete(): bool
    {
        return DriverFactory::getInstance()->assembleNoSQLDriver()->delete($this);
    }
}