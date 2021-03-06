<?php

namespace SypherLev\Architect;

use SypherLev\Blueprint\Blueprint;
use SypherLev\Blueprint\QueryBuilders\QueryInterface;
use SypherLev\Blueprint\QueryBuilders\SourceInterface;

class Architect extends Blueprint
{
    public function __construct(SourceInterface $source, QueryInterface $query)
    {
        parent::__construct($source, $query);
    }

    public function build($argv, $database_prefix) {
        if(empty($argv)) {
            echo "No table specified. Aborting build...\n\n";
            die;
        }
        $name = array_shift($argv);

        $templateFile = __DIR__.'/ClassTemplates/BasicData.php.tpl';
        if(!empty($argv)) {
            $filename = ltrim(array_shift($argv), '/');
            $templateFile = realpath("/../../../../".$filename);
        }
        if(!file_exists($templateFile)) {
            echo "Template file not found at $templateFile. Aborting build...\n\n";
            die;
        }
        $templateString = file_get_contents($templateFile);

        $chunkName = explode('_', $name);
        $className = "";
        foreach ($chunkName as $c) {
            $className .= ucfirst($c);
        }

        $columns = $this->getTableColumns($name);

        $editArray = [];
        $createArray = [];
        $primary = $this->getPrimaryKey($name);
        foreach ($columns as $col) {
            if($col != $primary) {
                $createArray[] = $col;
            }
            $editArray[] = $col;
        }

        if($primary == "") {
            echo "No primary key detected. Aborting build...\n\n";
            die;
        }
        if(empty($editArray)) {
            echo "No editable columns detected. Aborting build...\n\n";
            die;
        }

        $templateString = str_replace('{name}', $name, $templateString);
        $templateString = str_replace('{database_name}', ucfirst($database_prefix), $templateString);
        $templateString = str_replace('{className}', $className, $templateString);
        $templateString = str_replace('{primary}', $primary, $templateString);
        $templateString = str_replace('{create_columns}', '\''.implode("','", $createArray).'\'', $templateString);
        $templateString = str_replace('{edit_columns}', '\''.implode("','", $editArray).'\'', $templateString);
        $targetFile = 'src'.DIRECTORY_SEPARATOR.'DBAL'.DIRECTORY_SEPARATOR.ucfirst($database_prefix). DIRECTORY_SEPARATOR . $className.'Data.php';
        if(!file_exists('src')) {
            mkdir('src');
        }
        if(!file_exists('src'.DIRECTORY_SEPARATOR.'DBAL')) {
            mkdir('src'.DIRECTORY_SEPARATOR.'DBAL');
        }
        if(!file_exists('src'.DIRECTORY_SEPARATOR.'DBAL'.DIRECTORY_SEPARATOR . ucfirst($database_prefix))) {
            mkdir('src'.DIRECTORY_SEPARATOR.'DBAL'.DIRECTORY_SEPARATOR . ucfirst($database_prefix));
        }
        touch($targetFile);
        if(file_exists($targetFile)) {
            $target_file_string = file_get_contents($targetFile);
            $existing_uses = trim($this->getStringBetween($target_file_string, "// START USES", "// END USES"));
            $existing_functions = trim($this->getStringBetween($target_file_string, "// START CUSTOM", "// END CUSTOM"));
            $existing_construct = trim($this->getStringBetween($target_file_string, "// START CONSTRUCT", "// END CONSTRUCT"));
            $templateString = str_replace('{existing_uses}', $existing_uses, $templateString);
            $templateString = str_replace('{existing_functions}', $existing_functions, $templateString);
            $templateString = str_replace('{existing_construct}', $existing_construct, $templateString);
            file_put_contents($targetFile, $templateString);
            echo "Build complete. File generated at $targetFile.\n\n";
            die;
        }
        else {
            echo "Could not create file $targetFile. Aborting build...\n\n";
            die;
        }
    }

    // credit to Justin Cook for this: http://www.justin-cook.com/2006/03/31/php-parse-a-string-between-two-strings/
    private function getStringBetween($string, $start, $end){
        if(strlen($string) == 0) {
            return "";
        }
        $string = ' ' . $string;
        $ini = strpos($string, $start);
        if ($ini == 0) return '';
        $ini += strlen($start);
        $len = strpos($string, $end, $ini) - $ini;
        return substr($string, $ini, $len);
    }
}