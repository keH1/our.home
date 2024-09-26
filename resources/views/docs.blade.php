<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Документация API</title>
    <!-- Swagger UI CSS -->
    <link rel="stylesheet" href="https://unpkg.com/swagger-ui-dist@4.18.3/swagger-ui.css">
</head>
<body>
<div id="swagger-ui"></div>

<!-- Swagger UI JS -->
<script src="https://unpkg.com/swagger-ui-dist@4.18.3/swagger-ui-bundle.js"></script>
<script src="https://unpkg.com/swagger-ui-dist@4.18.3/swagger-ui-standalone-preset.js"></script>
<script>
    window.onload = function() {
        const ui = SwaggerUIBundle({
            url: "{{ asset('/api/openapi.json') }}", // Путь к вашему OpenAPI JSON
            dom_id: '#swagger-ui',
            presets: [
                SwaggerUIBundle.presets.apis,
                SwaggerUIStandalonePreset
            ],
            layout: "StandaloneLayout",
            deepLinking: true,
        });
        window.ui = ui;
    };
</script>
</body>
</html>
