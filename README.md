# Information

[![Build Status](https://app.travis-ci.com/craue/CraueConfigBundle.svg?branch=master)](https://app.travis-ci.com/craue/CraueConfigBundle)
[![Coverage Status](https://coveralls.io/repos/github/craue/CraueConfigBundle/badge.svg?branch=master)](https://coveralls.io/github/craue/CraueConfigBundle?branch=master)

CraueConfigBundle manages configuration settings stored in the database and makes them accessible via a service in your
Symfony project. These settings are similar to those defined in `parameters.yml` but can be modified at runtime, e.g.
by an admin user.

# Installation

## Get the bundle

Let Composer download and install the bundle by running

```sh
composer require craue/config-bundle
```

in a shell.

## Enable the bundle

If you don't use Symfony Flex, register the bundle manually:

```php
// in config/bundles.php
return [
	// ...
	Craue\ConfigBundle\CraueConfigBundle::class => ['all' => true],
];
```

Or, for Symfony 3.4:

```php
// in app/AppKernel.php
public function registerBundles() {
	$bundles = [
		// ...
		new Craue\ConfigBundle\CraueConfigBundle(),
	];
	// ...
}
```

## Create the table

Preferably you do this by calling

```sh
# in a shell
php bin/console doctrine:migrations:diff
php bin/console doctrine:migrations:migrate
```

or

```sh
# in a shell
php bin/console doctrine:schema:update
```

or however you like.

## Add the route to manage settings (optional)

You can either import the default routing configuration

```yaml
# in app/config/routing.yml
craue_config_settings:
  resource: "@CraueConfigBundle/Resources/config/routing/settings.xml"
  prefix: /settings
```

...or add your own to have full control over the URL pattern.

```yaml
# in app/config/routing.yml
craue_config_settings_modify:
  path: /settings/modify
  defaults:
    _controller: Craue\ConfigBundle\Controller\SettingsController::modifyAction
```

Some CSS is needed to render the form correctly. Install the assets in your project:

```sh
# in a shell
php bin/console assets:install --symlink web
```

# Usage

## Defining settings

This bundle does _not_ provide functionality to create new settings because this would make no sense at runtime.
Those settings will be used in your application and thus code needs to be written for that.
This means that you have to create new settings in the database table `craue_config_setting` yourself, e.g. using a
migration.

## Managing settings' values

If you added the route described above you can manage the values of all defined settings in a simple form.
By default, you can access that form by browsing to `/settings/modify`.
But you probably want to limit access to this form in your security configuration.

## Reading and writing settings

For accessing settings, the bundle provides the service `Craue\ConfigBundle\Util\Config`.
To use it directly in a controller, either add an autowired type-hinted argument to the action...

```php
// in src/Controller/MyController.php
use Craue\ConfigBundle\Util\Config;

public function indexAction(Config $config) {
	// use $config
}
```

...or let your controller extend `Symfony\Bundle\FrameworkBundle\Controller\AbstractController` and make the
service alias `craue_config` available by defining `getSubscribedServices`:

```php
// in src/Controller/MyController.php
use Craue\ConfigBundle\Util\Config;

public function indexAction() {
	// use $this->get('craue_config')
}

public static function getSubscribedServices() {
	return array_merge(parent::getSubscribedServices(), [
		'craue_config' => Config::class,
	]);
}
```

The service defines the following methods:

- `all()` - get an associative array of all defined settings and their values
- `get($name)` - get the value of the specified setting
- `getBySection($section)` - like `all()`, but get only settings within the specified section (or those without a section if explicitly passing `null`)
- `set($name, $value)` - set the value of the specified setting
- `setMultiple([$name1 => $value1, $name2 => $value2])` - set values for multiple settings at once

Keep in mind that each setting has to be present, or an exception will be thrown.

## Usage in Twig templates

The Twig extension in this bundle supports reading settings directly in your template.

```twig
{{ craue_setting('name-of-a-setting') }}
```

# Enable caching (optional)

To reduce the number of database queries, you can set up a cache for settings. First, you have to choose which cache
implementation you'd like to use. Currently, there are adapters available for:
- [DoctrineCacheBundle](https://github.com/doctrine/DoctrineCacheBundle)
- [Symfony Cache component](https://symfony.com/doc/current/components/cache.html)

Refer to the documentation of each implementation for details and read on in the corresponding section below. When
done, `CraueConfigBundle` will automatically cache settings (using the built-in `craue_config_cache_adapter` service).

Keep in mind to clear the cache (if needed) after modifying settings outside of your app (e.g. by Doctrine migrations):

```sh
# in a shell
php bin/console doctrine:cache:clear craue_config_cache
```

<details>
  <summary>Cache implementation: DoctrineCacheBundle</summary>

  Set the parameter `craue_config.cache_adapter.class` appropriately and configure a so-called cache provider with the
  alias `craue_config_cache_provider`:

  ```yaml
  # in app/config/config.yml
  parameters:
    craue_config.cache_adapter.class: Craue\ConfigBundle\CacheAdapter\DoctrineCacheBundleAdapter

  doctrine_cache:
    providers:
      craue_config_cache:
        apc: ~
        namespace: craue_config
        aliases:
          - craue_config_cache_provider
  ```
</details>

<details>
  <summary>Cache implementation: Symfony Cache component</summary>

  Set the parameter `craue_config.cache_adapter.class` appropriately and configure a so-called cache pool with the
  service id `craue_config_cache_provider`:

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
</details>

# Customization

## Redirect to a different page after submitting the built-in form

If you've enabled the build-in form, you can define where to redirect on successfully saving the changes by setting the
target route name:

```yaml
# in app/config/parameters.yml
parameters:
  craue_config.redirectRouteAfterModify: craue_config_settings_modify
```

## Rendering of settings in sections

If you want to render settings in a group (called section here), you'll have to assign those settings a common section
name (in the database). Optionally, you can influence the order of these sections:

```yaml
# in app/config/parameters.yml
parameters:
  craue_config.configTemplate.sectionOrder: [section1, section2, section3]
```

Settings without a section will be rendered at first. Sections without explicit ordering are rendered at last.

## Translation

You can add translations for all settings (and sections) to be shown in the form by adding them to translation files
with the `CraueConfigBundle` domain, e.g.

```yaml
# in app/Resources/CraueConfigBundle/translations/CraueConfigBundle.en.yml
name-of-a-setting: name of the setting

# in app/Resources/CraueConfigBundle/translations/CraueConfigBundle.de.yml
name-of-a-setting: Name der Einstellung
```

## Using a custom entity for settings

The custom entity has to provide a mapping for the field `value`. The class `BaseSetting` defines this field, but no
mapping for it. This allows easy overriding, including the data type. In the following example, the `value` field will
be mapped to a `text` column, which will in turn render the built-in form fields as `textarea`.

So create the entity and its appropriate mapping:

```php
// src/MyCompany/MyBundle/Entity/MySetting.php
use Craue\ConfigBundle\Entity\BaseSetting;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Craue\ConfigBundle\Repository\SettingRepository")
 * @ORM\Table(name="my_setting")
 */
class MySetting extends BaseSetting {

	/**
	 * @var string|null
	 * @ORM\Column(name="value", type="text", nullable=true)
	 */
	protected $value;

	/**
	 * @var string|null
	 * @ORM\Column(name="comment", type="string", nullable=true)
	 */
	protected $comment;

	public function setComment($comment) {
		$this->comment = $comment;
	}

	public function getComment() {
		return $this->comment;
	}

}
```

And make the bundle aware of it:

```yaml
# in app/config/config.yml
craue_config:
  entity_name: MyCompany\MyBundle\Entity\MySetting
```
