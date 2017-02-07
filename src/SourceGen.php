<?php

namespace Sypherlev\Architect;

use SypherLev\Blueprint\QueryBuilders\MySql\MySqlSource;

class SourceGen
{
    public $driver;
    public $host;
    public $database;
    public $user;
    public $pass;

    public function generateSource($identifier) {
        $this->driver = isset($_ENV[$identifier.'_engine']) ? $_ENV[$identifier.'_engine'] : '';
        $this->host = isset($_ENV[$identifier.'_host']) ? $_ENV[$identifier.'_host'] : '';
        $this->database = isset($_ENV[$identifier.'_dbname']) ? $_ENV[$identifier.'_dbname'] : '';
        $this->user = isset($_ENV[$identifier.'_username']) ? $_ENV[$identifier.'_username'] : '';
        $this->pass = isset($_ENV[$identifier.'_password']) ? $_ENV[$identifier.'_password'] : '';
        try {
            $dns = $this->driver . ':dbname=' . $this->database . ";host=" . $this->host;
            $pdo = new \PDO($dns, $this->user, $this->pass);
            return new MySqlSource($pdo);
        }
        catch (\Exception $e) {
            echo "Connection error: ".$e->getMessage()."\n\n";
            die;
        }
    }
}