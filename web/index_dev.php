<?php

$app = require_once __DIR__ . '/../app/app.php';

// Turn on debugging
$app['debug'] = true;
$app['auth.enable'] = false;

$app->run();
