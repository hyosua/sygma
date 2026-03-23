<?php

// Forcer la base de test avant le démarrage de Laravel,
// car les variables docker-compose (DB_DATABASE=sygma) ont la priorité sur phpunit.xml.
putenv('DB_DATABASE=sygma_test');
$_ENV['DB_DATABASE'] = 'sygma_test';
$_SERVER['DB_DATABASE'] = 'sygma_test';

require __DIR__ . '/../vendor/autoload.php';
