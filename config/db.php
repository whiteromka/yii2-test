<?php

return [
    // тут захардкодил, но в проде лучше использовать db_local.php или .env
    'class' => 'yii\db\Connection',
    'dsn' => 'pgsql:host=pgsql;port=5432;dbname=loans',
    'username' => 'user',
    'password' => 'password',
    'charset' => 'utf8',
];
