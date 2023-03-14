<?php

namespace Aternos\Model;

/**
 * Class BaseModel
 *
 * Contains all non-driver related functions such as id and
 * changed fields, use this to build models with custom driver
 * logic, but without writing everything from scratch
 *
 * @package Aternos\Model
 */
abstract class BaseModel implements ModelInterface
{
    /**
     * Name of the field used as unique identifier
     *
     * @var string
     */
    protected static string $idField = "id";

    /**
     * Length of the random generated unique identifier
     *
     * @var int
     */
    protected static int $idLength = 16;

    /**
     * Should be added as public property to all inheriting models
     *
     * It's protected to be easily replaced by a property with
     * a different name if that's required
     *
     * @var mixed
     */
    protected mixed $id;

    /**
     * Additional fields that aren't part of the model, but occur in the result
     *
     * e.g. results of calculations, aliased fields etc.
     *
     * @var array
     */
    protected array $additionalFields = [];

    /**
     * Get the field name of the unique identifier
     *
     * @return string
     */
    public static function getIdField(): string
    {
        return static::$idField;
    }

    /**
     * @param array $rawData
     * @return static|null
     */
    public static function getModelFromData(array $rawData): ?static
    {
        $model = new static();
        return $model->applyData($rawData);
    }

    /**
     * Model constructor.
     *
     * @param mixed $id
     */
    public function __construct(mixed $id = null)
    {
        if ($id) {
            $this->setId($id);
        }
    }

    /**
     * Get the unique identifier of the model
     *
     * @return mixed
     */
    public function getId(): mixed
    {
        if (!isset($this->{static::$idField})) {
            return null;
        }
        return $this->{static::$idField};
    }

    /**
     * Set the unique identifier
     *
     * @param mixed $id
     * @return $this
     */
    public function setId(mixed $id): static
    {
        $this->{static::$idField} = $id;
        return $this;
    }

    /**
     * Generate an unique identifier for the model
     */
    protected function generateId()
    {
        $characters = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $charactersLength = strlen($characters);
        do {
            $id = '';
            for ($i = 0; $i < static::$idLength; $i++) {
                $id .= $characters[rand(0, $charactersLength - 1)];
            }
        } while (static::get($id));

        $this->setId($id);
    }

    /**
     * Apply data to the model
     *
     * @param array $rawData
     * @return $this
     */
    public function applyData(array $rawData): static
    {
        foreach ($rawData as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            } else {
                $this->additionalFields[$key] = $value;
            }
        }
        return $this;
    }

    /**
     * Get a field value, even if it's not part of the defined model
     *
     * @param string $key
     * @return mixed
     */
    public function getField(string $key): mixed
    {
        if (property_exists($this, $key)) {
            return $this->{$key};
        } else {
            return $this->additionalFields[$key] ?? null;
        }
    }
}