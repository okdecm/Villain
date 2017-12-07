<{{name}}{% if attributes|length > 0 %} {{attributes}}{% endif %}{% if is_self_closing %}/>{% else %}>{% if content|length > 0 %}
	{{content}}
{% endif %}</{{name}}>