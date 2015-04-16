# Upgrade from 1.x to 2.0

## Database

After upgrading the bundle, you should migrate your database to remove the useless `UNIQUE` index for field `name` from table `craue_config_setting`. The `PRIMARY` index for field `name` will remain.

## Built-in form/template

- If you're overriding the template `modify_form.html.twig` in your project, you probably need to adapt it to changes in the built-in form type. Before, two hidden form fields `name` and `section` were added only to read their `value` variable in the template. They have been removed now. But you can still access the properties of settings in the template via the `value` variable of each `setting` form field:

	before:
	```twig
	{% for setting in form.settings %}
		{{ setting.section.vars.value }}
		{{ setting.name.vars.value }}
	{% endfor %}
	```

	after:
	```twig
	{% for setting in form.settings %}
		{{ setting.vars.value.section }}
		{{ setting.vars.value.name }}
	{% endfor %}
	```

## Services

- If you've used the DI parameter `craue_config.configTemplate.class`, you now need to override the Twig extension with service id `twig.extension.craue_config_template` instead.
- If you've used the DI parameter `craue_config.config.class`, you now need to override the service with id `craue_config` instead.
