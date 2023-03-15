# Changelog

## [3.0.0]

### Main breaking changes
#### ModelInterface ID type
The `ModelInterface` ID is now typed as `mixed`. Any model extending `GenericModel`
  have to change the type of the `$id` property to `mixed`:
```php
class MyModel extends GenericModel
{
    public $id;
}
```
has to be changed to
```php
class MyModel extends GenericModel
{
    public mixed $id;
}
```

#### QueryResult getter
The magic getter function of `QueryResult` was removed. They have to be accessed using the correct
array key (usually `[0]`).
```php
$queryResult = MyModel::select(["field" => "value"]);
$value = $queryResult->field;
```
has to be changed to
```php
$queryResult = MyModel::select(["field" => "value"]);
$value = $queryResult[0]?->field;
```

#### Dynamic properties
Dynamic properties are deprecated since PHP 8.2. Unknown fields are now stored in `BaseModel->$additionalFields`
and have to be accessed using the new `ModelInterface->getField(string $key): mixed` function.
```php
$queryResult = MyModel::select(fields: [(new \Aternos\Model\Query\SelectField("field"))->setAlias("alias")]);
$value = $queryResult[0]?->alias;
```
has to be changed to
```php
$queryResult = MyModel::select(fields: [(new \Aternos\Model\Query\SelectField("field"))->setAlias("alias")]);
$value = $queryResult[0]?->getField("alias");
```

### Changed
- Potentially breaking type changes/additions:
    - The `ModelInterface` ID is now typed as `mixed`. This also affects the
      `ModelInterface->getId(): mixed` and `ModelInterface->setId(mixed $id): static` methods.
    - `ModelInterface->setId(mixed $id): static` now returns `static` instead of `void`.
    - `ModelInterface::get(): ?static` now returns `null` if the model is not found 
       instead of `false`.
    - `ModelInterface::getIdField()` and `ModelInterface::getCacheTime()` are now static.
    - `ModelCollection` offset functions (from `ArrayAccess`) now have `mixed` types for all 
      arguments to follow the `ArrayAccess` interface.
    - `ModelRegistry->get(): ?ModelInterface` now returns `null` if the model is not found 
       instead of `false`.
    - `BaseModel->__construct(mixed $id)` now has the typed argument `mixed $id`.
    - `GenericModel::select()` and `GenericModel::update()` now have fully typed arguments.
    - `GenericModel->set()` now has the return type `QueryResult`.
    - All `Query` classes now have proper typing for their arguments and return types.
    - `GettableInterface->get(string $modelClass, mixed $id, ?ModelInterface $model = null): ?ModelInterface` only optionally
      applies data to a given model, usually it creates a new model using `ModelInterface::getModelFromData(array $rawData): ?static`
      It also returns `null` if the model is not found instead of `false`.
- `ModelRegistry->get(string $className, string $id): ?ModelInterface` now takes the class name
  as the first argument instead of the model name. This function is also generic and type hints
  the return type from the first argument.
- Fields that aren't defined in the model are no longer set as dynamic properties. They
  have to be retrieved using the new `ModelInterface->getField(string $key): mixed` function.
  
### Added
- `ModelCollection` and its children now have generic type hinting in phpdoc allowing for type
  hinting the model type in the collection.
- `GenericModel` now has the ability to define different `GenericModel::$variants` which are child
  classes of a basic model to separate different subtypes of a model. Each variant can different 
  `GenericModel::$filters` which are key value pairs that identify the type. Direct calls to get and 
  query functions will automatically filter the results to the correct variant. Calls to the shared
  parent model will return all variants but of the correct subtype.
- Added `CountField` and `GenericModel::count()` for easier count queries.
- Added `SumField` and `AverageField`.
- Added `GenericModel::reload(): static` to reload the model from the database.

### Removed
- Removed `SimpleModel`.
- Removed magic getter from `QueryResult`.