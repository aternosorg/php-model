<?php

namespace Aternos\Model\Query;

use UnexpectedValueException;

interface Validatable
{
    /**
     * Validate that this object is correctly configured
     * @return void
     * @throws UnexpectedValueException if the object is not valid
     */
    public function validate(): void;
}
