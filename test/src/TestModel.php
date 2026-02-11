<?php

namespace Aternos\Model\Test\Src;

use Aternos\Model\GenericModel;

class TestModel extends GenericModel
{
    public mixed $id;
    public ?string $text = null;
    public ?int $number = null;

    /**
     * @inheritDoc
     */
    public static function getName(): string
    {
        return "test";
    }
}