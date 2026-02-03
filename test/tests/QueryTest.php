<?php

namespace Aternos\Model\Test\Tests;

use Aternos\Model\Query\SelectQuery;
use Aternos\Model\Query\WhereCondition;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

class QueryTest extends TestCase
{
    public function testSelectWhereConditionINNotArray()
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage("Value for IN or NOT IN operator must be an array.");
        new SelectQuery(
            new WhereCondition('number', "asdf", 'IN'),
        );
    }

    public function testSelectWhereConditionINEmptyArray()
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage("Value array for IN or NOT IN operator must not be empty.");

        new SelectQuery(
            new WhereCondition('number', [], 'IN'),
        );
    }

    public function testWhereConvertInvalidArrayLength(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Argument $where has an invalid array element with a length of 5.');
        new SelectQuery([
            ['field', '=', 'value', 'extra', 'invalid'],
        ]);
    }
}
