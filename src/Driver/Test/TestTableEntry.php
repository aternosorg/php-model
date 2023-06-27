<?php

namespace Aternos\Model\Driver\Test;

use Aternos\Model\Query\Field;
use Aternos\Model\Query\UpdateField;
use Aternos\Model\Query\WhereCondition;
use Aternos\Model\Query\WhereGroup;

class TestTableEntry
{
    public function __construct(protected array $data)
    {
    }

    /**
     * @param WhereGroup $where
     * @return bool
     */
    public function matchesWhereGroup(WhereGroup $where): bool
    {
        $matches = true;
        foreach ($where as $condition) {
            if ($condition instanceof WhereGroup) {
                $matches = $this->matchesWhereGroup($condition);
            } else if ($condition instanceof WhereCondition) {
                $matches = $this->matchesWhereCondition($condition);
            }
            if ($where->conjunction === WhereGroup::AND && !$matches) {
                return false;
            }
            if ($where->conjunction === WhereGroup::OR && $matches) {
                return true;
            }
        }
        return $where->conjunction === WhereGroup::AND;
    }

    /**
     * @param WhereCondition $where
     * @return bool
     */
    public function matchesWhereCondition(WhereCondition $where): bool
    {
        $dataValue = null;
        if (isset($this->data[$where->field])) {
            $dataValue = $this->data[$where->field];
        }
        $whereValue = $where->value;

        return match ($where->operator) {
            "=", "is" => $dataValue === $whereValue,
            "!=", "is not" => $dataValue !== $whereValue,
            ">" => $dataValue > $whereValue,
            ">=" => $dataValue >= $whereValue,
            "<" => $dataValue < $whereValue,
            "<=" => $dataValue <= $whereValue,
            "like" => preg_match("/" . str_replace("%", ".*", preg_quote($whereValue)) . "/", $dataValue) === 1,
            "not like" => preg_match("/" . str_replace("%", ".*", preg_quote($whereValue)) . "/", $dataValue) !== 1,
            default => false,
        };
    }

    /**
     * @param Field[]|null $fields
     * @return array
     */
    public function getDataForFields(?array $fields): array
    {
        if ($fields === null) {
            return $this->data;
        }
        $data = [];
        foreach ($fields as $field) {
            if (isset($this->data[$field->key])) {
                $data[$field->key] = $this->data[$field->key];
            }
        }
        return $data;
    }

    /**
     * @param UpdateField[] $updates
     * @return static
     */
    public function update(array $updates): static
    {
        foreach ($updates as $update) {
            $this->data[$update->key] = $update->value;
        }
        return $this;
    }
}