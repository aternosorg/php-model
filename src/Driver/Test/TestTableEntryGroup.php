<?php

namespace Aternos\Model\Driver\Test;

use Aternos\Model\Query\GroupField;
use Aternos\Model\Query\SelectField;

class TestTableEntryGroup
{
    /**
     * @var TestTableEntry[]
     */
    protected array $entries = [];

    /**
     * @var array<string, mixed>
     */
    protected array $conditions = [];

    /**
     * @param TestTableEntry $entry
     * @param GroupField[]|null $groupFields
     */
    public function __construct(TestTableEntry $entry, ?array $groupFields = [])
    {
        if ($groupFields) {
            foreach ($groupFields as $groupField) {
                $this->conditions[$groupField->key] = $entry[$groupField->key];
            }
        }
        $this->addEntry($entry);
    }

    /**
     * @param TestTableEntry $entry
     * @return $this
     */
    public function addEntry(TestTableEntry $entry): static
    {
        $this->entries[] = $entry;
        return $this;
    }

    /**
     * @param TestTableEntry $entry
     * @return bool
     */
    public function matches(TestTableEntry $entry): bool
    {
        foreach ($this->conditions as $key => $value) {
            if ($entry[$key] !== $value) {
                return false;
            }
        }
        return true;
    }

    /**
     * @return array
     */
    public function getEntries(): array
    {
        return $this->entries;
    }

    /**
     * @param SelectField[]|null $fields
     * @param bool $collapse
     * @return $this
     */
    public function aggregateAndAlias(?array $fields, bool $collapse = true): static
    {
        if ($fields === null) {
            $fields = [];
        }

        $aggregatedEntry = null;
        // counter for average fields to calculate average at the end
        $averageFields = [];
        foreach ($this->entries as $entry) {
            if ($aggregatedEntry === null) {

                // initialize aggregated entry
                $aggregatedEntry = $entry;
                foreach ($fields as $field) {
                    // handle alias for initial entry
                    $key = $field->alias ?? $field->key;
                    $aggregatedEntry[$key] = $entry[$field->key] ?? null;

                    // init functions
                    if ($field->function === SelectField::COUNT) {
                        $aggregatedEntry[$key] = 1;
                    } elseif ($field->function === SelectField::AVERAGE) {
                        $averageFields[$key] = 1;
                    }
                }
                continue;
            }
            foreach ($fields as $field) {
                // handle alias
                $key = $field->alias ?? $field->key;
                $entry[$key] = $entry[$field->key] ?? null;

                // handle functions
                if ($field->function === SelectField::SUM) {
                    $aggregatedEntry[$key] += $entry[$field->key] ?? 0;
                } elseif ($field->function === SelectField::COUNT) {
                    // ++ does not work with ArrayAccess
                    $aggregatedEntry[$key] += 1;
                } elseif ($field->function === SelectField::AVERAGE) {
                    $averageFields[$key]++;
                    $aggregatedEntry[$key] += $entry[$field->key] ?? 0;
                }
            }
        }

        // calculate averages
        foreach ($averageFields as $key => $count) {
            $aggregatedEntry[$key] /= $count;
        }

        // we have to collapse if there is an active function
        foreach ($fields as $field) {
            if ($field->function !== null) {
                $collapse = true;
            }
        }

        // collapse to a single entry
        // should always happen for grouped groups
        // should only happen for default group if there is a function
        if ($collapse) {
            $this->entries = [$aggregatedEntry];
        }

        return $this;
    }
}