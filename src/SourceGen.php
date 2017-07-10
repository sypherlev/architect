<?php

namespace SypherLev\Architect;

use SypherLev\Blueprint\QueryBuilders\MySql\MySqlQuery;
use SypherLev\Blueprint\QueryBuilders\MySql\MySqlSource;
use SypherLev\Blueprint\QueryBuilders\Postgres\PostgresQuery;
use SypherLev\Blueprint\QueryBuilders\Postgres\PostgresSource;

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
            if($this->driver == 'mysql') {
                return new MySqlSource($pdo);
            }
            if($this->driver == 'pgsql') {
                return new PostgresSource($pdo);
            }
            throw new \Exception("Could not create an appropriate source object for this driver.");
        }
        catch (\Exception $e) {
            echo "Connection error: ".$e->getMessage()."\n\n";
            die;
        }
    }

    public function getQueryObject() {
        if($this->driver == 'mysql') {
            return new MySqlQuery();
        }
        if($this->driver == 'pgsql') {
            return new PostgresQuery();
        }
        throw new \Exception("Could not create an appropriate query object for this driver.");
    }
}