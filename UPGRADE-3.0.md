# Upgrade from 2.x to 3.0

## Types

Type declarations were added throughout the codebase for properties, parameters, and return values.
If you use custom implementations (like a custom entity), you'll probably encounter PHP errors if their type declarations don't match those of the base class.
Update your extended/overridden code to include the appropriate type declarations.

## Cache

- The following elements have been removed:
  - parameter `craue_config.cache_adapter.class`
  - service `craue_config_cache_adapter`
  - interface `Craue\ConfigBundle\CacheAdapter\CacheAdapterInterface`
  - class `Craue\ConfigBundle\CacheAdapter\NullAdapter`
  - class `Craue\ConfigBundle\CacheAdapter\SymfonyCacheComponentAdapter`

- Previously, you had to configure the parameter `craue_config.cache_adapter.class` and override the service `craue_config_cache_provider`.
  Now, configure the cache pool `craue_config_cache` instead.

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

	after (preserving the namespace):
	```yaml
	# in app/config/config.yml
	framework:
	  cache:
	    pools:
	      craue_config_cache:
	        adapter: my_craue_config_filesystem_cache_adapter
	
	services:
	  my_craue_config_filesystem_cache_adapter:
	    parent: cache.adapter.filesystem
	    public: false
	    tags:
	      - { name: cache.pool, namespace: craue_config }
	```

	after (if the namespace is not important):
	```yaml
	# in app/config/config.yml
	framework:
	  cache:
	    pools:
	      craue_config_cache:
	        adapter: cache.adapter.filesystem
	```
