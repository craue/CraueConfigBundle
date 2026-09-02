# Upgrade from 2.x to 3.0

## Types

Type declarations were added throughout the codebase for properties, parameters, and return values.
If you use custom implementations (like a custom entity), you'll probably encounter PHP errors if their type declarations don't match those of the base class.
Update your extended/overridden code to include the appropriate type declarations.

## Cache

Cache configuration was simplified.
The parameter `craue_config.cache_adapter.class` was removed.
Previously, you had to override the service `craue_config_cache_provider`. Now, override `craue_config_cache` instead.

before:
```yaml
# in app/config/config.yml
parameters:
  craue_config.cache_adapter.class: Craue\ConfigBundle\CacheAdapter\SymfonyCacheComponentAdapter

services:
  craue_config_cache_provider:
    class: Symfony\Component\Cache\Adapter\FilesystemAdapter
    public: false
    arguments:
      - 'craue_config'
      - 0
      - '%kernel.cache_dir%'
```

after:
```yaml
# in app/config/config.yml
services:
  craue_config_cache:
    class: Symfony\Component\Cache\Adapter\FilesystemAdapter
    public: false
    arguments:
      - 'craue_config'
      - 0
      - '%kernel.cache_dir%'
```
