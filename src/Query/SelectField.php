<?php

namespace Aternos\Model\Query;

/**
 * Class SelectField
 *
 * @package Aternos\Model\Query
 */
class SelectField extends Field
{
    /** @deprecated */
    const AggregateFunction COUNT = AggregateFunction::COUNT;
    /** @deprecated */
    const AggregateFunction SUM = AggregateFunction::SUM;
    /** @deprecated */
    const AggregateFunction AVERAGE = AggregateFunction::AVERAGE;
    /** @deprecated */
    const AggregateFunction MIN = AggregateFunction::MIN;
    /** @deprecated */
    const AggregateFunction MAX = AggregateFunction::MAX;

    /**
     * @var string|null
     */
    public ?string $alias = null;

    public ?AggregateFunction $function = null;

    /**
     * @var bool
     */
    public bool $raw = false;

    /**
     * @param string|null $alias
     * @return $this
     */
    public function setAlias(?string $alias): static
    {
        $this->alias = $alias;
        return $this;
    }

    /**
     * @param AggregateFunction $function
     * @return $this
     */
    public function setFunction(AggregateFunction $function): static
    {
        $this->function = $function;
        return $this;
    }

    /**
     * @param bool $raw
     * @return $this
     */
    public function setRaw(bool $raw = true): static
    {
        $this->raw = $raw;
        return $this;
    }
}
