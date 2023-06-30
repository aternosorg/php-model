<?php

namespace Aternos\Model\Driver\Test;

use Aternos\Model\ModelInterface;
use Aternos\Model\ModelRegistry;
use Aternos\Model\Query\DeleteQuery;
use Aternos\Model\Query\GroupField;
use Aternos\Model\Query\OrderField;
use Aternos\Model\Query\Query;
use Aternos\Model\Query\QueryResult;
use Aternos\Model\Query\SelectField;
use Aternos\Model\Query\SelectQuery;
use Aternos\Model\Query\UpdateQuery;
use Aternos\Model\Query\WhereGroup;

class TestTable
{
    /**
     * @var TestTableEntry[]
     */
    protected array $entries = [];

    /**
     * @param string $name
     */
    public function __construct(protected string $name)
    {
    }

    /**
     * @param array $entries
     * @return $this
     */
    public function addArrayEntries(array $entries): static
    {
        foreach ($entries as $entry) {
            $this->addEntry(new TestTableEntry($entry));
        }
        return $this;
    }

    /**
     * @param TestTableEntry ...$entry
     * @return $this
     */
    public function addEntry(TestTableEntry ...$entry): static
    {
        $this->entries = array_merge($this->entries, $entry);
        return $this;
    }

    /**
     * @param Query $query
     * @return QueryResult
     */
    public function query(Query $query): QueryResult
    {
        $entries = $this->findEntries($query->getWhere(), $query->getLimit()?->start, $query->getLimit()?->length);
        if ($order = $query->getOrder()) {
            $entries = $this->orderEntries($entries, $order);
        }

        if ($query instanceof SelectQuery) {
            $clonedEntries = [];
            foreach ($entries as $entry) {
                $clonedEntries[] = clone $entry;
            }
            $entries = $this->groupAndAggregateEntries($clonedEntries, $query->getGroup(), $query->getFields());
        }

        $queryResult = new QueryResult(true);
        foreach ($entries as $entry) {
            if ($query instanceof SelectQuery) {
                /** @var class-string<ModelInterface> $modelClass */
                $modelClass = $query->modelClassName;
                $model = $modelClass::getModelFromData($entry->getDataForFields($query->getFields()));
                $queryResult->add($model);
            }
            if ($query instanceof UpdateQuery) {
                $entry->update($query->getFields());
            }
            if ($query instanceof DeleteQuery) {
                $this->deleteEntry($entry);
            }
        }
        if ($query instanceof UpdateQuery || $query instanceof DeleteQuery) {
            $queryResult->setAffectedRows(count($entries));
        }

        return $queryResult;
    }

    /**
     * @param TestTableEntry[] $entries
     * @param GroupField[]|null $group
     * @param SelectField[]|null $fields
     * @return TestTableEntry[]
     */
    public function groupAndAggregateEntries(array $entries, ?array $group, ?array $fields): array
    {
        $groups = [];
        foreach ($entries as $entry) {
            foreach ($groups as $currentGroup) {
                if ($currentGroup->matches($entry)) {
                    $currentGroup->addEntry($entry);
                    continue 2;
                }
            }
            $currentGroup = new TestTableEntryGroup($entry, $group);
            $groups[] = $currentGroup;
        }


        foreach ($groups as $currentGroup) {
            $currentGroup->aggregateAndAlias($fields, !empty($group));
        }

        $newEntries = [];
        foreach ($groups as $currentGroup) {
            foreach ($currentGroup->getEntries() as $entry) {
                $newEntries[] = $entry;
            }
        }
        return $newEntries;
    }

    /**
     * @param WhereGroup|null $where
     * @param int|null $offset
     * @param int|null $limit
     * @return TestTableEntry[]
     */
    protected function findEntries(?WhereGroup $where, ?int $offset = null, ?int $limit = null): array
    {
        $entries = [];
        if ($offset === null) {
            $offset = 0;
        }
        foreach ($this->entries as $entry) {
            if (!$entry->matchesWhereGroup($where)) {
                continue;
            }
            if ($offset > 0) {
                $offset--;
                continue;
            }
            $entries[] = $entry;
            if ($limit !== null && count($entries) >= $limit) {
                break;
            }
        }
        return $entries;
    }

    /**
     * @param TestTableEntry $a
     * @param TestTableEntry $b
     * @param OrderField[] $order
     * @return int
     */
    protected function compareEntries(TestTableEntry $a, TestTableEntry $b, array $order): int
    {
        foreach ($order as $orderField) {
            $aValue = $a->getField($orderField->field);
            $bValue = $b->getField($orderField->field);
            if ($aValue === $bValue) {
                continue;
            }
            if ($orderField->direction === OrderField::ASCENDING) {
                return $aValue > $bValue ? 1 : -1;
            } else {
                return $aValue < $bValue ? 1 : -1;
            }
        }
        return 0;
    }

    /**
     * @param array $entries
     * @param OrderField[] $order
     * @return TestTableEntry[]
     */
    protected function orderEntries(array $entries, array $order): array
    {
        usort($entries, function (TestTableEntry $a, TestTableEntry $b) use ($order) {
            return $this->compareEntries($a, $b, $order);
        });
        return $entries;
    }

    /**
     * @return $this
     */
    public function clear(): static
    {
        $this->entries = [];
        ModelRegistry::getInstance()->clearModel($this->name);
        return $this;
    }

    /**
     * @param mixed $id
     * @param string $idField
     * @return TestTableEntry|null
     */
    public function getById(mixed $id, string $idField = "id"): ?TestTableEntry
    {
        foreach ($this->entries as $entry) {
            if ($entry->hasId($id, $idField)) {
                return $entry;
            }
        }
        return null;
    }

    /**
     * @param TestTableEntry $entry
     * @return $this
     */
    public function deleteEntry(TestTableEntry $entry): static
    {
        foreach ($this->entries as $key => $tableEntry) {
            if ($tableEntry === $entry) {
                unset($this->entries[$key]);
                break;
            }
        }
        return $this;
    }
}