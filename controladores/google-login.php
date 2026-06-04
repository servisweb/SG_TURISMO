<?php

require_once __DIR__ . '/../config/google.php';

$login_url = $client->createAuthUrl();

header('Location: ' . filter_var($login_url, FILTER_SANITIZE_URL));
exit;