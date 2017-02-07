<?php

namespace Sypherlev\Architect;

use SypherLev\Blueprint\Blueprint;
use SypherLev\Blueprint\QueryBuilders\QueryInterface;
use SypherLev\Blueprint\QueryBuilders\SourceInterface;

class Architect extends Blueprint
{
    public function __construct(SourceInterface $source, QueryInterface $query)
    {
        parent::__construct($source, $query);
    }

    public function build($argv) {
        if(empty($argv)) {
            echo "No table specified. Aborting build...\n\n";
            die;
        }
        $name = array_shift($argv);

        $templateFile = 'BasicData.php.tpl';
        if(!empty($argv)) {
            $filename = ltrim(array_shift($argv), '/');
            $templateFile = realpath("/../../../../".$filename);
        }
        if(!file_exists($templateFile)) {
            echo "Template file not found at $templateFile. Aborting build...\n\n";
            die;
        }
        $templateString = file_get_contents($templateFile);
        $className = ucfirst($name);

        $sql = "SHOW COLUMNS FROM $name";

        $columns = $this->source->raw($sql, [':table' => $name], 'fetchAll');

        $editArray = [];
        $createArray = [];
        $primary = "";
        foreach ($columns as $col) {
            if($col->Key == 'PRI') {
                $primary = $col->id;
                if(strpos($col->extra, 'auto_increment') === false) {
                    $createArray[] = $col->id;
                    $editArray[] = $col->id;
                }
            }
            else {
                $createArray[] = $col->id;
                $editArray[] = $col->id;
            }
        }

        if($primary == "") {
            echo "No primary key detected. Aborting build...\n\n";
            die;
        }
        if(empty($createArray) || empty($editArray)) {
            echo "No primary key detected. Aborting build...\n\n";
            die;
        }

        $templateString = str_replace('{name}', $name, $templateString);
        $templateString = str_replace('{classname}', $className, $templateString);
        $templateString = str_replace('{primary}', $primary, $templateString);
        $templateString = str_replace('{create_columns}', '\''.implode("','", $createArray).'\'', $templateString);
        $templateString = str_replace('{edit_columns}', '\''.implode("','", $editArray).'\'', $templateString);
        $targetFile = 'src'.DIRECTORY_SEPARATOR.'DBAL'.$className.'Data.php';
        if(!file_exists('src')) {
            mkdir('src');
        }
        if(!file_exists('src'.DIRECTORY_SEPARATOR.'DBAL')) {
            mkdir('src'.DIRECTORY_SEPARATOR.'DBAL');
        }
        touch($targetFile);
        if(file_exists($targetFile)) {
            file_put_contents($targetFile, $templateString);
            echo "Build complete. File generated at $targetFile.\n\n";
            die;
        }
        else {
            echo "Could not create file $targetFile. Aborting build...\n\n";
            die;
        }
    }
}