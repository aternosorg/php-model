<?php

namespace Aternos\Model\Test\Tests;

use Aternos\Model\Driver\DriverRegistry;
use Aternos\Model\Driver\Test\TestDriver;
use Aternos\Model\Query\CountField;
use Aternos\Model\Query\DeleteQuery;
use Aternos\Model\Query\OrderField;
use Aternos\Model\Query\SelectField;
use Aternos\Model\Query\SumField;
use Aternos\Model\Query\WhereCondition;
use Aternos\Model\Query\WhereGroup;
use Aternos\Model\Test\Src\TestModel;
use Exception;
use PHPUnit\Framework\TestCase;

class TestDriverTest extends TestCase
{
    /**
     * @return void
     */
    protected function setUp(): void
    {
        $testData = "ABCDEFGHIJ";
        foreach (str_split($testData) as $i => $char) {
            TestModel::addTestEntry([
                "id" => $i . $char,
                "text" => $char,
                "number" => $i
            ]);
        }
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testGet(): void
    {
        $model = TestModel::get("1B");
        $this->assertEquals("1B", $model->id);
        $this->assertEquals("B", $model->text);
        $this->assertEquals(1, $model->number);
    }

    public function testGetOnNonExistingTable(): void
    {
        /** @var TestDriver $driver */
        $driver = DriverRegistry::getInstance()->getDriver("test");
        $driver->clearTables();

        $model = TestModel::get("1B");
        $this->assertNull($model);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testGetNull(): void
    {
        $model = TestModel::get("DOES_NOT_EXIST");
        $this->assertNull($model);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testDelete(): void
    {
        $model = TestModel::get("1B");
        $this->assertTrue($model->delete());
        $this->assertNull(TestModel::get("1B"));
    }

    /**
     * @return void
     */
    public function testDeleteNull(): void
    {
        $model = new TestModel();
        $this->assertFalse($model->delete());
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testSaveNew(): void
    {
        $model = new TestModel();
        $model->id = "10K";
        $model->text = "K";
        $model->number = 10;
        $this->assertTrue($model->save());
        $getModel = TestModel::get("10K");
        $this->assertEquals("10K", $getModel->id);
        $this->assertEquals("K", $getModel->text);
        $this->assertEquals(10, $getModel->number);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testSaveExisting(): void
    {
        $model = TestModel::get("1B");
        $model->text = "Z";
        $this->assertTrue($model->save());
        $getModel = TestModel::get("1B");
        $this->assertEquals("1B", $getModel->id);
        $this->assertEquals("Z", $getModel->text);
        $this->assertEquals(1, $getModel->number);
    }

    public function testSelect(): void
    {
        $models = TestModel::select();
        $this->assertCount(10, $models);
        $this->assertEquals("1B", $models[1]->id);
        $this->assertEquals("B", $models[1]->text);
        $this->assertEquals(1, $models[1]->number);
        $this->assertEquals("9J", $models[9]->id);
        $this->assertEquals("J", $models[9]->text);
        $this->assertEquals(9, $models[9]->number);
    }

    public function testSelectWhere(): void
    {
        $models = TestModel::select(["number" => 1]);
        $this->assertCount(1, $models);
        $this->assertEquals("1B", $models[0]->id);
        $this->assertEquals("B", $models[0]->text);
        $this->assertEquals(1, $models[0]->number);
    }

    public function testSelectWhereOr(): void
    {
        $models = TestModel::select(new WhereGroup([
            new WhereCondition("number", 1),
            new WhereCondition("number", 2)
        ], WhereGroup::OR));
        $this->assertCount(2, $models);
        $this->assertEquals("1B", $models[0]->id);
        $this->assertEquals("B", $models[0]->text);
        $this->assertEquals(1, $models[0]->number);
        $this->assertEquals("2C", $models[1]->id);
        $this->assertEquals("C", $models[1]->text);
        $this->assertEquals(2, $models[1]->number);
    }

    public function testSelectNotEqual(): void
    {
        $models = TestModel::select([["number", "!=", 1]]);
        $this->assertCount(9, $models);
        foreach ($models as $model) {
            $this->assertNotEquals(1, $model->number);
        }
    }

    public function testSelectGreaterThan(): void
    {
        $models = TestModel::select([["number", ">", 5]]);
        $this->assertCount(4, $models);
        foreach ($models as $model) {
            $this->assertGreaterThan(1, $model->number);
        }
    }

    public function testSelectGreaterThanOrEqual(): void
    {
        $models = TestModel::select([["number", ">=", 5]]);
        $this->assertCount(5, $models);
        foreach ($models as $model) {
            $this->assertGreaterThanOrEqual(1, $model->number);
        }
    }

    public function testSelectLessThan(): void
    {
        $models = TestModel::select([["number", "<", 5]]);
        $this->assertCount(5, $models);
        foreach ($models as $model) {
            $this->assertLessThan(5, $model->number);
        }
    }

    public function testSelectLessThanOrEqual(): void
    {
        $models = TestModel::select([["number", "<=", 5]]);
        $this->assertCount(6, $models);
        foreach ($models as $model) {
            $this->assertLessThanOrEqual(5, $model->number);
        }
    }

    public function testSelectLike(): void
    {
        $models = TestModel::select([["text", "LIKE", "A"]]);
        $this->assertCount(1, $models);
        $this->assertEquals("0A", $models[0]->id);
        $this->assertEquals("A", $models[0]->text);
        $this->assertEquals(0, $models[0]->number);
    }

    public function testSelectNotLike(): void
    {
        $models = TestModel::select([["text", "NOT LIKE", "A"]]);
        $this->assertCount(9, $models);
        foreach ($models as $model) {
            $this->assertNotEquals("A", $model->text);
        }
    }

    public function testSelectLikeStart(): void
    {
        $model = new TestModel();
        $model->id = "10TEST";
        $model->text = "TEST";
        $model->number = 10;
        $model->save();

        $models = TestModel::select([["text", "LIKE", "T%"]]);
        $this->assertCount(1, $models);
        $this->assertEquals("10TEST", $models[0]->id);
        $this->assertEquals("TEST", $models[0]->text);
        $this->assertEquals(10, $models[0]->number);
    }

    public function testSelectLikeEnd(): void
    {
        $model = new TestModel();
        $model->id = "10TEST";
        $model->text = "TEST";
        $model->number = 10;
        $model->save();

        $models = TestModel::select([["text", "LIKE", "%T"]]);
        $this->assertCount(1, $models);
        $this->assertEquals("10TEST", $models[0]->id);
        $this->assertEquals("TEST", $models[0]->text);
        $this->assertEquals(10, $models[0]->number);
    }

    public function testSelectLikeMiddle(): void
    {
        $model = new TestModel();
        $model->id = "10TEST";
        $model->text = "TEST";
        $model->number = 10;
        $model->save();

        $models = TestModel::select([["text", "LIKE", "%ES%"]]);
        $this->assertCount(1, $models);
        $this->assertEquals("10TEST", $models[0]->id);
        $this->assertEquals("TEST", $models[0]->text);
        $this->assertEquals(10, $models[0]->number);
    }

    public function testSelectLikeStartEnd(): void
    {
        $model = new TestModel();
        $model->id = "10TEST";
        $model->text = "TEST";
        $model->number = 10;
        $model->save();

        $models = TestModel::select([["text", "LIKE", "T%T"]]);
        $this->assertCount(1, $models);
        $this->assertEquals("10TEST", $models[0]->id);
        $this->assertEquals("TEST", $models[0]->text);
        $this->assertEquals(10, $models[0]->number);
    }

    public function testSelectLikeSingleChar(): void
    {
        $model = new TestModel();
        $model->id = "10TEST";
        $model->text = "TEST";
        $model->number = 10;
        $model->save();

        $model = new TestModel();
        $model->id = "11TESAT";
        $model->text = "TESAT";
        $model->number = 11;
        $model->save();

        $models = TestModel::select([["text", "LIKE", "TE_T"]]);
        $this->assertCount(1, $models);
        $this->assertEquals("10TEST", $models[0]->id);
        $this->assertEquals("TEST", $models[0]->text);
        $this->assertEquals(10, $models[0]->number);
    }

    public function testSelectLikeLineBreak(): void
    {
        $model = new TestModel();
        $model->id = "10TEST";
        $model->text = "TE\nST";
        $model->number = 10;
        $model->save();

        $models = TestModel::select([["text", "LIKE", "T%T"]]);
        $this->assertCount(1, $models);
        $this->assertEquals("10TEST", $models[0]->id);
        $this->assertEquals("TE\nST", $models[0]->text);
        $this->assertEquals(10, $models[0]->number);
    }

    public function testSelectLikeEscaping(): void
    {
        $model = new TestModel();
        $model->id = "10T%T";
        $model->text = "T%T";
        $model->number = 10;
        $model->save();

        $model = new TestModel();
        $model->id = "11TEST";
        $model->text = "TEST";
        $model->number = 11;
        $model->save();


        $models = TestModel::select([["text", "LIKE", "T\\%T"]]);
        $this->assertCount(1, $models);
        $this->assertEquals("10T%T", $models[0]->id);
        $this->assertEquals("T%T", $models[0]->text);
        $this->assertEquals(10, $models[0]->number);
    }

    public function testSelectOrder(): void
    {
        $models = TestModel::select(order: ["number" => OrderField::DESCENDING]);
        $this->assertCount(10, $models);
        $this->assertEquals("9J", $models[0]->id);
        $this->assertEquals("J", $models[0]->text);
        $this->assertEquals(9, $models[0]->number);
        $this->assertEquals("0A", $models[9]->id);
        $this->assertEquals("A", $models[9]->text);
        $this->assertEquals(0, $models[9]->number);
    }

    public function testSelectLimit(): void
    {
        $models = TestModel::select(limit: 5);
        $this->assertCount(5, $models);
        $this->assertEquals("0A", $models[0]->id);
        $this->assertEquals("A", $models[0]->text);
        $this->assertEquals(0, $models[0]->number);
        $this->assertEquals("4E", $models[4]->id);
        $this->assertEquals("E", $models[4]->text);
        $this->assertEquals(4, $models[4]->number);
    }

    public function testSelectLimitOffset(): void
    {
        $models = TestModel::select(limit: [5, 5]);
        $this->assertCount(5, $models);
        $this->assertEquals("5F", $models[0]->id);
        $this->assertEquals("F", $models[0]->text);
        $this->assertEquals(5, $models[0]->number);
        $this->assertEquals("9J", $models[4]->id);
        $this->assertEquals("J", $models[4]->text);
        $this->assertEquals(9, $models[4]->number);
    }

    public function testSelectFields(): void
    {
        $models = TestModel::select(fields: ["id", "number"]);
        $this->assertTrue($models->wasSuccessful());
        $this->assertCount(10, $models);
        $this->assertEquals("1B", $models[1]->id);
        $this->assertNull($models[1]->text);
        $this->assertEquals(1, $models[1]->number);
        $this->assertEquals("9J", $models[9]->id);
        $this->assertNull($models[9]->text);
        $this->assertEquals(9, $models[9]->number);
    }

    public function testSelectCount(): void
    {
        $count = TestModel::count();
        $this->assertEquals(10, $count);
    }

    public function testSelectSum(): void
    {
        $result = TestModel::select(fields: [new SumField("number")]);
        $this->assertTrue($result->wasSuccessful());
        $this->assertEquals(45, $result[0]->number);

        // test if data wasn't changed
        $models = TestModel::select();
        $this->assertCount(10, $models);
        foreach ($models as $model) {
            $this->assertNotEquals(45, $model->number);
        }
    }

    public function testSelectAverage(): void
    {
        $result = TestModel::select(fields: [
            (new SelectField("number"))
                ->setAlias("average")
                ->setFunction(SelectField::AVERAGE)]);
        $this->assertTrue($result->wasSuccessful());
        $this->assertEquals(4.5, $result[0]->getField("average"));
    }

    public function testSelectGroup(): void
    {
        $model = new TestModel();
        $model->id = "1K";
        $model->text = "K";
        $model->number = 1;
        $model->save();

        $models = TestModel::select(group: ["number"]);
        $this->assertTrue($models->wasSuccessful());
        $this->assertCount(10, $models);
    }

    public function testSelectGroupCount(): void
    {
        $model = new TestModel();
        $model->id = "1K";
        $model->text = "K";
        $model->number = 1;
        $model->save();

        $models = TestModel::select(fields: [new CountField(), new SelectField("number")], group: ["number"]);
        $this->assertTrue($models->wasSuccessful());
        $this->assertCount(10, $models);
        foreach ($models as $model) {
            if ($model->number === 1) {
                $this->assertEquals(2, $model->getField("count"));
            } else {
                $this->assertEquals(1, $model->getField("count"));
            }
        }
    }

    public function testSelectGroupSum(): void
    {
        $model = new TestModel();
        $model->id = "5K";
        $model->text = "K";
        $model->number = 5;
        $model->save();

        $models = TestModel::select(fields: [
            (new SumField("number"))->setAlias("sum"),
            new SelectField("number")],
            group: ["number"]
        );
        $this->assertTrue($models->wasSuccessful());
        $this->assertCount(10, $models);
        foreach ($models as $model) {
            if ($model->number === 5) {
                $this->assertEquals(10, $model->getField("sum"));
            } else {
                $this->assertEquals($model->number, $model->getField("sum"));
            }
        }
    }

    public function testSelectGroupAverage(): void
    {
        $model = new TestModel();
        $model->id = "5A";
        $model->text = "A";
        $model->number = 5;
        $model->save();

        $models = TestModel::select(fields: [
            (new SelectField("number"))
                ->setAlias("average")
                ->setFunction(SelectField::AVERAGE),
            new SelectField("number"),
            new SelectField("text"),
        ], group: ["text"]);

        $this->assertTrue($models->wasSuccessful());
        $this->assertCount(10, $models);
        foreach ($models as $model) {
            if ($model->text === "A") {
                $this->assertEquals(2.5, $model->getField("average"));
            } else {
                $this->assertEquals($model->number, $model->getField("average"));
            }
        }
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testUpdate(): void
    {
        TestModel::disableRegistry();
        $model = TestModel::get("1B");
        $this->assertEquals("B", $model->text);

        $model = new TestModel();
        $model->id = "2B";
        $model->text = "B";
        $model->number = 2;
        $model->save();

        $result = TestModel::update(["text" => "C"], ["text" => "B"]);
        $this->assertTrue($result->wasSuccessful());
        $this->assertEquals(2, $result->getAffectedRows());

        $model = TestModel::get("1B");
        $this->assertEquals("C", $model->text);
        $model = TestModel::get("2B");
        $this->assertEquals("C", $model->text);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testDeleteQuery(): void
    {
        TestModel::disableRegistry();
        $this->assertNotNull(TestModel::get("1B"));

        $model = new TestModel();
        $model->id = "2B";
        $model->text = "B";
        $model->number = 2;
        $model->save();

        $result = TestModel::query(new DeleteQuery(["text" => "B"]));
        $this->assertTrue($result->wasSuccessful());
        $this->assertEquals(2, $result->getAffectedRows());

        $model = TestModel::get("1B");
        $this->assertNull($model);
        $model = TestModel::get("2B");
        $this->assertNull($model);
    }

    /**
     * @return void
     */
    protected function tearDown(): void
    {
        TestModel::clearTestEntries();
    }
}