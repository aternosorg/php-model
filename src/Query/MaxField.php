<?php

namespace Aternos\Model\Query;

class MaxField extends SelectField
{
    public ?AggregateFunction $function = AggregateFunction::MAX;

    /**
     * MaxField constructor.
     *
     * @param string|null $key
     */
    public function __construct(?string $key = null)
    {
        parent::__construct($key);
        $this->setAlias($key);
    }

    /**
     * @param string|null $key
     * @return static
     */
    public function setKey(?string $key): static
    {
        $this->setAlias($key);
        return parent::setKey($key);
    }
}
