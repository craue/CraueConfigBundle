# Upgrade from 1.x to 2.0

## Template

- If you're overriding the template `modify_form.html.twig` in your project, you probably need to adapt it to changes in the built-in form type, which is not adding the form fields `name` and `section` anymore, but passes the variables `name` and `section` to the template instead.

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
		{{ setting.vars.section }}
		{{ setting.vars.name }}
	{% endfor %}
	```
