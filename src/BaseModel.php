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
     * Should be added as public property to all inheriting models
     *
     * It's protected to be easily replaced by a property with
     * a different name if that's required
     *
     * @var mixed
     */
    protected mixed $id;

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
     * Get the field name of the unique identifier
     *
     * @return string
     */
    public static function getIdField(): string
    {
        return static::$idField;
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
}