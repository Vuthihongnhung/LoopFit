<?php

class Database
{
    public function connect()
    {
        $config = require __DIR__ . '/../config/database.php';

        $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";

        return new PDO($dsn, $config['username'], $config['password']);
    }
}