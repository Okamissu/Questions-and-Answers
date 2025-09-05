<?php

use App\Kernel;

require_once dirname(__DIR__) . '/vendor/autoload_runtime.php';

// Get the request URI relative to your app
$basePath = '/~20_kobylarz/qa-app';
$requestUri = $_SERVER['REQUEST_URI'];
$relativeUri = substr($requestUri, strlen($basePath));

// Serve React for root or non-API requests
if ($relativeUri === '/' || (strpos($relativeUri, '/api') !== 0 && !file_exists(__DIR__ . $relativeUri))) {
    include __DIR__ . '/index.html';
    exit;
}

// Otherwise, let Symfony handle the request
return function (array $context) {
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
