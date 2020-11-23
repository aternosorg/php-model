<?php

namespace Aternos\Model\Query;

/**
 * Class SelectField
 *
 * @package Aternos\Model\Query
 */
class SelectField extends Field
{
    const COUNT = 0,
        SUM = 1,
        AVERAGE = 2;

    /**
     * @var string|null
     */
    public ?string $alias = null;

    /**
     * @var int|null
     */
    public ?int $function = null;

    /**
     * @var bool
     */
    public bool $raw = false;

    /**
     * @param string|null $alias
     * @return SelectField
     */
    public function setAlias(?string $alias): SelectField
    {
        $this->alias = $alias;
        return $this;
    }

    /**
     * @param int|null $function
     * @return SelectField
     */
    public function setFunction(?int $function): SelectField
    {
        $this->function = $function;
        return $this;
    }

    /**
     * @param bool $raw
     * @return SelectField
     */
    public function setRaw(bool $raw = true): SelectField
    {
        $this->raw = $raw;
        return $this;
    }
}