<?php

require_once __DIR__ . '/../vendor/autoload.php';

$client = new Google\Client();

$client->setClientId('807733899944-vbdou4ttpoins0arrglttdeclos1of3o.apps.googleusercontent.com');

$client->setClientSecret('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');

$client->setRedirectUri('http://localhost:8000/controladores/google-callback.php');

$client->addScope('email');
$client->addScope('profile');