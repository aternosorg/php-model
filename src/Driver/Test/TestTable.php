<?php

namespace Aternos\Model\Driver\Test;

use Aternos\Model\ModelInterface;
use Aternos\Model\ModelRegistry;
use Aternos\Model\Query\DeleteQuery;
use Aternos\Model\Query\Direction;
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
        $entries = $this->findEntries($query->getWhere());

        if ($query instanceof SelectQuery) {
            $clonedEntries = [];
            foreach ($entries as $entry) {
                $clonedEntries[] = clone $entry;
            }
            $entries = $this->groupAndAggregateEntries($clonedEntries, $query->getGroup(), $query->getFields());

            if ($query->isDistinct() && $query->getFields()) {
                $distinctEntries = [];
                foreach ($entries as $entry) {
                    foreach ($distinctEntries as $distinctEntry) {
                        $same = true;
                        foreach ($query->getFields() as $field) {
                            $key = $field->key;
                            if ($distinctEntry->getField($key) !== $entry->getField($key)) {
                                $same = false;
                                break;
                            }
                        }

                        if ($same) {
                            continue 2;
                        }
                    }

                    $distinctEntries[] = clone $entry;
                }
                $entries = $distinctEntries;
            }
        }

        if ($query->getOrder() !== null) {
            $entries = $this->orderEntries($entries, $query->getOrder());
        }

        $entries = array_slice($entries, $query->getLimit()?->start ?? 0, $query->getLimit()?->length);

        $queryResult = new QueryResult();
        foreach ($entries as $entry) {
            if ($query instanceof SelectQuery) {
                /** @var class-string<ModelInterface> $modelClass */
                $modelClass = $query->modelClassName;
                $entryData = $entry->getDataForFields($query->getFields());
                $model = $modelClass::getModelFromData($entryData);
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
     * @return TestTableEntry[]
     */
    protected function findEntries(?WhereGroup $where): array
    {
        /** @var TestTableEntry[] $entries */
        $entries = [];
        foreach ($this->entries as $entry) {
            if (!$entry->matchesWhereGroup($where)) {
                continue;
            }

            $entries[] = $entry;
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
            if ($orderField->direction === Direction::ASCENDING) {
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
        return array_find($this->entries, fn($entry) => $entry->hasId($id, $idField));
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
