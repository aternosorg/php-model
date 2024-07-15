<?php

namespace Aternos\Model\Test\Tests;

use Aternos\Model\Query\DeleteQuery;
use Aternos\Model\Query\Generator\SQL;
use Aternos\Model\Query\Limit;
use Aternos\Model\Query\SelectField;
use Aternos\Model\Query\SelectQuery;
use Aternos\Model\Query\UpdateQuery;
use Aternos\Model\Query\WhereCondition;
use Aternos\Model\Query\WhereGroup;
use Aternos\Model\Test\Src\TestModel;
use PHPUnit\Framework\TestCase;

class SQLTest extends TestCase
{
    protected SQL $sql;

    protected function setUp(): void
    {
        $this->sql = new SQL("addslashes");
    }

    public function testSelect()
    {
        $query = new SelectQuery();
        $query->modelClassName = TestModel::class;

        $this->assertEquals("SELECT * FROM `test`", $this->sql->generate($query));
    }

    public function testSelectWhereCondition()
    {
        $query = new SelectQuery(
            new WhereCondition('text', 'value'),
        );
        $query->modelClassName = TestModel::class;

        $this->assertEquals("SELECT * FROM `test` WHERE (`text` = 'value')", $this->sql->generate($query));
    }

    public function testSelectWhereConditionNumber()
    {
        $query = new SelectQuery(
            new WhereCondition('number', 1),
        );
        $query->modelClassName = TestModel::class;

        $this->assertEquals("SELECT * FROM `test` WHERE (`number` = 1)", $this->sql->generate($query));
    }

    public function testSelectWhereConditionFloat()
    {
        $query = new SelectQuery(
            new WhereCondition('number', 1.5),
        );
        $query->modelClassName = TestModel::class;

        $this->assertEquals("SELECT * FROM `test` WHERE (`number` = 1.5)", $this->sql->generate($query));
    }

    public function testSelectWhereConditionOperator()
    {
        $query = new SelectQuery(
            new WhereCondition('text', 'value', '!='),
        );
        $query->modelClassName = TestModel::class;

        $this->assertEquals("SELECT * FROM `test` WHERE (`text` != 'value')", $this->sql->generate($query));
    }

    public function testSelectWhereConditionNull()
    {
        $query = new SelectQuery(
            new WhereCondition('text', null),
        );
        $query->modelClassName = TestModel::class;

        $this->assertEquals("SELECT * FROM `test` WHERE (`text` IS NULL)", $this->sql->generate($query));
    }

    public function testSelectWhereConditionNotNull()
    {
        $query = new SelectQuery(
            new WhereCondition('text', null, '!='),
        );
        $query->modelClassName = TestModel::class;

        $this->assertEquals("SELECT * FROM `test` WHERE (`text` IS NOT NULL)", $this->sql->generate($query));
    }

    public function testSelectWhereConditionIN()
    {
        $query = new SelectQuery(
            new WhereCondition('number', [1.5, 5, "a", ["b", "c", "d"]], 'IN'),
        );
        $query->modelClassName = TestModel::class;

        $this->assertEquals("SELECT * FROM `test` WHERE (`number` IN (1.5, 5, 'a', ('b', 'c', 'd')))", $this->sql->generate($query));
    }

    public function testSelectWhereArray()
    {
        $query = new SelectQuery([
            'number' => 1,
            'text' => 'value',
        ]);
        $query->modelClassName = TestModel::class;

        $this->assertEquals("SELECT * FROM `test` WHERE (`number` = 1 AND `text` = 'value')", $this->sql->generate($query));
    }

    public function testSelectWhereArrayOperator()
    {
        $query = new SelectQuery([
            ['number', 0.5],
            ['number', '<', 1],
            'text' => 'value',
        ]);
        $query->modelClassName = TestModel::class;

        $this->assertEquals("SELECT * FROM `test` WHERE (`number` = 0.5 AND `number` < 1 AND `text` = 'value')", $this->sql->generate($query));
    }

    public function testSelectWhereGroup()
    {
        $query = new SelectQuery(
            new WhereGroup([
                new WhereCondition('number', 1, '<'),
                new WhereCondition('number', 0, '>'),
            ]),
        );
        $query->modelClassName = TestModel::class;

        $this->assertEquals("SELECT * FROM `test` WHERE (`number` < 1 AND `number` > 0)", $this->sql->generate($query));
    }

    public function testSelectWhereGroupOR()
    {
        $query = new SelectQuery(
            new WhereGroup([
                new WhereCondition('number', 1, '>'),
                new WhereCondition('number', 0, '<'),
            ], WhereGroup::OR),
        );
        $query->modelClassName = TestModel::class;

        $this->assertEquals("SELECT * FROM `test` WHERE (`number` > 1 OR `number` < 0)", $this->sql->generate($query));
    }

    public function testSelectSingleField()
    {
        $query = new SelectQuery(fields: ['text']);
        $query->modelClassName = TestModel::class;

        $this->assertEquals("SELECT `text` FROM `test`", $this->sql->generate($query));
    }

    public function testSelectSelectFields()
    {
        $query = new SelectQuery(fields: ['text', 'number']);
        $query->modelClassName = TestModel::class;

        $this->assertEquals("SELECT `text`, `number` FROM `test`", $this->sql->generate($query));
    }

    public function testSelectOrder()
    {
        $query = new SelectQuery(order: [
            'number' => 'ASC',
            'text' => 'DESC',
        ]);
        $query->modelClassName = TestModel::class;

        $this->assertEquals("SELECT * FROM `test` ORDER BY `number` ASC, `text` DESC", $this->sql->generate($query));
    }

    public function testSelectCount()
    {
        $query = new SelectQuery(fields: [
            (new SelectField('number'))->setFunction(SelectField::COUNT),
        ]);
        $query->modelClassName = TestModel::class;

        $this->assertEquals("SELECT COUNT(`number`) FROM `test`", $this->sql->generate($query));
    }

    public function testSelectCountStar()
    {
        $query = new SelectQuery(fields: [
            (new SelectField('*'))->setFunction(SelectField::COUNT),
        ]);
        $query->modelClassName = TestModel::class;

        $this->assertEquals("SELECT COUNT(*) FROM `test`", $this->sql->generate($query));
    }

    public function testSelectSum()
    {
        $query = new SelectQuery(fields: [
            (new SelectField('number'))->setFunction(SelectField::SUM),
        ]);
        $query->modelClassName = TestModel::class;

        $this->assertEquals("SELECT SUM(`number`) FROM `test`", $this->sql->generate($query));
    }

    public function testSelectSumAs()
    {
        $query = new SelectQuery(fields: [
            (new SelectField('number'))->setFunction(SelectField::SUM)->setAlias('sum'),
        ]);
        $query->modelClassName = TestModel::class;

        $this->assertEquals("SELECT SUM(`number`) AS `sum` FROM `test`", $this->sql->generate($query));
    }

    public function testSelectAVG()
    {
        $query = new SelectQuery(fields: [
            (new SelectField('number'))->setFunction(SelectField::AVERAGE),
        ]);
        $query->modelClassName = TestModel::class;

        $this->assertEquals("SELECT AVG(`number`) FROM `test`", $this->sql->generate($query));
    }

    public function testSelectLimitNumber()
    {
        $query = new SelectQuery(limit:100);
        $query->modelClassName = TestModel::class;

        $this->assertEquals("SELECT * FROM `test` LIMIT 0, 100", $this->sql->generate($query));
    }

    public function testSelectLimitArray()
    {
        $query = new SelectQuery(limit:[5, 100]);
        $query->modelClassName = TestModel::class;

        $this->assertEquals("SELECT * FROM `test` LIMIT 5, 100", $this->sql->generate($query));
    }

    public function testSelectLimit()
    {
        $query = new SelectQuery(limit:new Limit(100, 5));
        $query->modelClassName = TestModel::class;

        $this->assertEquals("SELECT * FROM `test` LIMIT 5, 100", $this->sql->generate($query));
    }

    public function testSelectGroup()
    {
        $query = new SelectQuery(group:['number', 'text']);
        $query->modelClassName = TestModel::class;

        $this->assertEquals("SELECT * FROM `test` GROUP BY `number`, `text`", $this->sql->generate($query));
    }

    public function testDelete()
    {
        $query = new DeleteQuery();
        $query->modelClassName = TestModel::class;

        /** @noinspection SqlWithoutWhere */
        $this->assertEquals("DELETE FROM `test`", $this->sql->generate($query));
    }

    public function testDeleteLimit()
    {
        $query = new DeleteQuery(limit: 100);
        $query->modelClassName = TestModel::class;

        /** @noinspection SqlWithoutWhere */
        $this->assertEquals("DELETE FROM `test` LIMIT 100", $this->sql->generate($query));
    }

    public function testUpdate()
    {
        $query = new UpdateQuery(['text' => 'value']);
        $query->modelClassName = TestModel::class;

        /** @noinspection SqlWithoutWhere */
        $this->assertEquals("UPDATE `test` SET `text` = 'value'", $this->sql->generate($query));
    }
}
