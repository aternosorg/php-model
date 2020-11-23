<?php


namespace Aternos\Model\Driver;

/**
 * Class Driver
 * @package Aternos\Model\Driver
 */
abstract class Driver implements DriverInterface
{
    protected string $id = "driver";

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     * @return Driver
     */
    public function setId(string $id): Driver
    {
        $this->id = $id;
        return $this;
    }
}