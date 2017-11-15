<html{% if language|length > 0 %} lang="{{language}}"{% endif %}>
	<head>
		<title>{{title}}</title>

		{{head}}
	</head>

	<body{% if body_class|length > 0 %} {{body_class}}{% endif %}>
		{{body}}
	</body>
</html>