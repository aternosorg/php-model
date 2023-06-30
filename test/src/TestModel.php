<?php

namespace Aternos\Model\Test\Src;

class TestModel extends \Aternos\Model\GenericModel
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