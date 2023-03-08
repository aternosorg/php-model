# Changelog

## [Unreleased 3.0.0]

### Changed
- Potentially breaking type changes/additions:
    - The `ModelInterface` ID is now typed as `mixed`. This also affects the
      `ModelInterface->getId(): mixed` and `ModelInterface->setId(mixed $id): static` methods.
    - `ModelInterface->setId(mixed $id): static` now returns `static` instead of `void`.
    - `ModelInterface::get(): ?static` now returns `null` if the model is not found 
       instead of `false`.
    - `ModelCollection` offset functions (from `ArrayAccess`) now have `mixed` types for all 
      arguments to follow the `ArrayAccess` interface.
    - `ModelRegistry->get(): ?ModelInterface` now returns `null` if the model is not found 
       instead of `false`.
    - `GenericModel::select()` and `GenericModel::update()` now have fully typed arguments.
    - `GenericModel->set()` now has the return type `QueryResult`.
- `ModelRegistry->get(string $className, string $id): ?ModelInterface` now takes the class name
  as the first argument instead of the model name. This function is also generic and type hints
  the return type from the first argument.
  
### Added
- `ModelCollection` and its children now have generic type hinting in phpdoc allowing for type
  hinting the model type in the collection.