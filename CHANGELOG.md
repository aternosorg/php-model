# Changelog

## [Unreleased 3.0.0]

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
    - `GettableInterface->get(string $modelClass, mixed $id): ?ModelInterface` no longer applies
      data to a given model, but creates a new model using `ModelInterface::getModelFromData(array $rawData): ?static`
      It also returns `null` if the model is not found instead of `false`.
- `ModelRegistry->get(string $className, string $id): ?ModelInterface` now takes the class name
  as the first argument instead of the model name. This function is also generic and type hints
  the return type from the first argument.
  
### Added
- `ModelCollection` and its children now have generic type hinting in phpdoc allowing for type
  hinting the model type in the collection.
- `GenericModel` now has the ability to define different `GenericModel::$variants` which are child
  classes of a basic model to separate different subtypes of a model. Each variant can different 
  `GenericModel::$filters` which are key value pairs that identify the type. Direct calls to get and 
  query functions will automatically filter the results to the correct variant. Calls to the shared
  parent model will return all variants but of the correct subtype.