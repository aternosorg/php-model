<?php

namespace Aternos\Model\Driver\Test;

use Aternos\Model\ModelInterface;
use Aternos\Model\Query\Field;
use Aternos\Model\Query\SelectField;
use Aternos\Model\Query\UpdateField;
use Aternos\Model\Query\WhereCondition;
use Aternos\Model\Query\WhereGroup;

class TestTableEntry implements \ArrayAccess
{
    public function __construct(protected array $data)
    {
    }

    /**
     * @param mixed $id
     * @param string $idField
     * @return bool
     */
    public function hasId(mixed $id, string $idField = "id"): bool
    {
        return isset($this->data[$idField]) && $this->data[$idField] === $id;
    }

    /**
     * @param WhereGroup|null $where
     * @return bool
     */
    public function matchesWhereGroup(?WhereGroup $where): bool
    {
        if ($where === null) {
            return true;
        }

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

        return match (strtolower($where->operator)) {
            "=", "is" => $dataValue === $whereValue,
            "!=", "is not" => $dataValue !== $whereValue,
            ">" => $dataValue > $whereValue,
            ">=" => $dataValue >= $whereValue,
            "<" => $dataValue < $whereValue,
            "<=" => $dataValue <= $whereValue,
            "like" => $this->matchesLike($whereValue, $dataValue),
            "not like" => !$this->matchesLike($whereValue, $dataValue),
            "in" => in_array($dataValue, $whereValue, true),
            "not in" => !in_array($dataValue, $whereValue, true),
            default => false,
        };
    }

    /**
     * @param string $likePattern
     * @param string $value
     * @return bool
     */
    protected function matchesLike(string $likePattern, string $value): bool
    {
        $likePattern = preg_replace("#(?<!\\\\)%#", ".*", preg_quote($likePattern));
        $likePattern = preg_replace("#(?<!\\\\)_#", ".", $likePattern);
        $likePattern = str_replace("\\%", "%", $likePattern);
        $likePattern = str_replace("\\_", "_", $likePattern);
        return preg_match("#" . $likePattern . "#si", $value) === 1;
    }

    /**
     * @param SelectField[]|null $fields
     * @return array
     */
    public function getDataForFields(?array $fields): array
    {
        if ($fields === null) {
            return $this->data;
        }
        $data = [];
        foreach ($fields as $field) {
            $key = $field->alias ?? $field->key;
            if (isset($this->data[$key])) {
                $data[$key] = $this->data[$key];
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

    /**
     * @param ModelInterface $model
     * @return ModelInterface
     */
    public function applyToModel(ModelInterface $model): ModelInterface
    {
        return $model->applyData($this->data);
    }

    /**
     * @param ModelInterface $model
     * @return $this
     */
    public function applyFromModel(ModelInterface $model): static
    {
        $this->data = get_object_vars($model);
        return $this;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param string $field
     * @return mixed|null
     */
    public function getField(string $field): mixed
    {
        return $this->data[$field] ?? null;
    }

    /**
     * @param string $field
     * @param mixed $value
     * @return $this
     */
    public function setField(string $field, mixed $value): static
    {
        $this->data[$field] = $value;
        return $this;
    }

    /**
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists(mixed $offset): bool
    {
        return isset($this->data[$offset]);
    }

    /**
     * @param mixed $offset
     * @return mixed|null
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->data[$offset] ?? null;
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     * @return void
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->data[$offset] = $value;
    }

    /**
     * @param mixed $offset
     * @return void
     */
    public function offsetUnset(mixed $offset): void
    {
        unset($this->data[$offset]);
    }
}