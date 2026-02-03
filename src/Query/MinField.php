<?php

namespace Aternos\Model\Query;

class MinField extends SelectField
{
    public ?AggregateFunction $function = AggregateFunction::MIN;

    /**
     * MinField constructor.
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
