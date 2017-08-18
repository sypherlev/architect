<?php

namespace App\DBAL;

use SypherLev\Blueprint\Blueprint;
use SypherLev\Blueprint\Elements\Pattern;
use SypherLev\Blueprint\QueryBuilders\QueryInterface;
use SypherLev\Blueprint\QueryBuilders\SourceInterface;

class {className}Data extends Blueprint
{
    public function __construct(SourceInterface $source, QueryInterface $query)
    {
        parent::__construct($source, $query);

        $this->addPattern('whole', function() {
            return (new Pattern())
                ->table('{name}')
                ->columns(['*']);
        });

        $this->addPattern('create', function() {
            return (new Pattern())
                ->table('{name}')
                ->columns([{create_columns}]);
        });

        $this->addPattern('edit', function() {
            return (new Pattern())
                ->table('{name}')
                ->columns([{edit_columns}]);
        });
    }

    public function getOneByKey($primary_key, $pattern = 'whole') {
        return $this->select()
            ->withPattern($pattern)
            ->where(['{primary}' => $primary_key])
            ->one();
    }

    public function getOneByColumn($column, $value, $pattern = 'whole') {
        return $this->select()
        ->withPattern($pattern)
        ->where([$column => $value])
        ->one();
    }

    public function getManyByColumns(Array $clauses, $pattern = 'whole', $limit = 1000, $offset = 0) {
        return $this->select()
            ->withPattern($pattern)
            ->where($clauses)
            ->limit($limit, $offset)
            ->many();
    }

    public function editByKey($primary_key, Array $update) {
        return $this->update()
            ->withPattern('edit')
            ->set($update)
            ->where(['{primary}' => $primary_key])
            ->execute();
    }

    public function create(Array $new) {
        $check = $this->insert()
            ->withPattern('create')
            ->add($new)
            ->execute();
        if($check) {
            return $this->source->lastInsertId();
        }
        return false;
    }

    public function createBatch(Array $batch) {
        if(!empty($batch)) {
            $this->insert()->withPattern('create');
            foreach ($batch as $record) {
                $this->add($record);
            }
            return $this->execute();
        }
        return false;
    }
}