<?php

namespace Aternos\Model;

use Aternos\Model\Driver\DriverFactory;

/**
 * Class Model
 *
 * @author Matthias Neid
 * @package Aternos\Model
 */
abstract class Model
{
    /**
     * @var string
     */
    public $id;

    public function save()
    {
        $factory = $this->getDriverFactory();

        if (!$this->id) {
            $this->id = $this->generateId();
            $factory->assembleRegistryDriver()->save($this);
        }

        $factory->assembleRelationalDriver()->save($this);
        $factory->assembleCacheDriver()->save($this);
        $factory->assembleSearchDriver()->save($this);

        return true;
    }

    protected function generateId(): string
    {
        return "";
    }

    protected function getDriverFactory()
    {
        return DriverFactory::getInstance();
    }
}