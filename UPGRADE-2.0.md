# Upgrade from 1.x to 2.0

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
