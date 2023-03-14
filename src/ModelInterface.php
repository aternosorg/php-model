<?php

namespace Aternos\Model;

use Aternos\Model\Query\{Query, QueryResult};

/**
 * Interface ModelInterface
 *
 * @package Aternos\Model\Driver
 */
interface ModelInterface
{
    /**
     * Get the name of the model used as table name etc.
     *
     * @return string
     */
    public static function getName(): string;

    /**
     * Return the caching time for the model (in seconds)
     *
     * @return int
     */
    public static function getCacheTime(): int;

    /**
     * Get the id of the model
     *
     * @return mixed
     */
    public function getId(): mixed;

    /**
     * Set the id of the model
     *
     * @param string $id
     * @return $this
     */
    public function setId(mixed $id): static;

    /**
     * Get the name of the id field
     *
     * @return string
     */
    public static function getIdField(): string;

    /**
     * Get a new model object from a raw data array
     *
     * @param array $rawData
     * @return static|null
     */
    public static function getModelFromData(array $rawData): ?static;

    /**
     * Get a model by id
     *
     * @param string $id
     * @param bool $update
     * @return static|bool
     */
    public static function get(string $id, bool $update = false): ?static;

    /**
     * Query a model
     *
     * @param Query $query
     * @return mixed
     */
    public static function query(Query $query): QueryResult;

    /**
     * Save a model
     *
     * @return bool
     */
    public function save(): bool;

    /**
     * Delete a model
     *
     * @return bool
     */
    public function delete(): bool;

    /**
     * Get a field value, even if it's not part of the defined model
     *
     * @param string $key
     * @return mixed
     */
    public function getField(string $key): mixed;

    /**
     * Apply data to the model
     *
     * @param array $rawData
     * @return $this
     */
    public function applyData(array $rawData): static;
}