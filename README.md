# Information

CraueConfigBundle manages configuration settings stored in the database and makes them accessible via a service.
These settings are similar to those defined in `parameters.ini` but can be modified at runtime, e.g. by an admin user.

This bundle should be used in conjunction with Symfony2.

# Installation

## Add CraueConfigBundle to your vendor directory

Either by using a Git submodule:

	git submodule add https://github.com/craue/CraueConfigBundle.git vendor/bundles/Craue/ConfigBundle

Or by using the `deps` file:

	[CraueConfigBundle]
	git=https://github.com/craue/CraueConfigBundle.git
	target=bundles/Craue/ConfigBundle

## Add CraueConfigBundle to your application kernel

	// app/AppKernel.php
	public function registerBundles() {
		$bundles = array(
			// ...
			new Craue\ConfigBundle\CraueConfigBundle(),
		);
		// ...
	}

## Register the Craue namespace

	// app/autoload.php
	$loader->registerNamespaces(array(
		// ...
		'Craue' => __DIR__.'/../vendor/bundles',
	));

## Create the table

Preferably, you do this by calling

	// in a shell
	php app/console doctrine:schema:update

or

	// in a shell
	php app/console doctrine:migrations:diff
	php app/console doctrine:migrations:migrate

or however you like.

## Add the route to manage settings (optional)

You can either import the default routing configuration

	// config/routing.yml
	craue_config_settings:
	  resource: "@CraueConfigBundle/Resources/config/routing/settings.xml"
	  prefix: /settings

...or add your own to have full control over the URL pattern.

	// config/routing.yml
	craue_config_settings_modify:
	  pattern: /settings/modify
	  defaults:
	    _controller: CraueConfigBundle:Settings:modify

# Usage

## Defining settings

This bundle does _not_ provide functionality to create new settings because this would make no sense at runtime.
Those settings will be used in your application and thus code needs to be written for that.
This means that you have to create new settings in the database table `craue_config_setting` yourself.

## Managing settings' values

If you added the route described above you can manage the values of all defined settings in a simple form.
By default, you can access that form by browsing to `app_dev.php/settings/modify`.
But you probably want to limit access to this form in your security configuration.

## Reading settings

The bundle provides a service called `craue_config`. Inside of a controller you can call

	$this->get('craue_config')->get('name-of-a-setting')

to retrieve the value of the setting `name-of-a-setting`. Furthermore, you can call

	$this->get('craue_config')->all()

to get an associative array of all defined settings and their values.

# Customization

## Translation

You can add translations for all settings to be shown in the form by adding them to translation files, e.g.

	# app/Resources/translations/CraueConfigBundle/CraueConfigBundle.en.yml
	name-of-a-setting: name of the setting

	# app/Resources/translations/CraueConfigBundle/CraueConfigBundle.de.yml
	name-of-a-setting: Name der Einstellung

## Rendering of settings in sections

If you want to render settings in a group (called section here), you'll have to assign those settings a common section
name (in the database). Optionally, you can influence the order of these sections:

	; app/config/parameters.ini
	craue_config.configTemplate.sectionOrder[]="section1"
	craue_config.configTemplate.sectionOrder[]="section2"
	craue_config.configTemplate.sectionOrder[]="section3"

Settings without a section will be rendered at first. Sections without explicit ordering are rendered at last.
