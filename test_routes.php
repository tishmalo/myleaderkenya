<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$routes = $app->make('router')->getRoutes();

foreach ($routes as $route) {
    if (str_contains($route->uri(), 'location-message')) {
        echo "URI: " . $route->uri() . "\n";
        echo "Method: " . implode('|', $route->methods()) . "\n";
        echo "Middleware: " . implode(', ', $route->gatherMiddleware()) . "\n";
        echo "---\n";
    }
}
