<?php

namespace App\DBAL\{database_name};

/**
* Add your own use clauses here. If you ever need to regenerate this file
* using Architect, elements between START USES and END USES will not be erased.
*/

// START USES

{existing_uses}

// END USES

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

        /**
        * Add your own Patterns, Filters, and Transformations here.
        * If you ever need to regenerate this file using Architect,
        * elements between START CONSTRUCT and END CONSTRUCT will not be erased.
        */

        // START CONSTRUCT

        {existing_construct}

        // END CONSTRUCT
    }

    public function getOneByKey(int $primary_key, string $pattern = 'whole') : \stdClass {
        return $this->select()
            ->withPattern($pattern)
            ->where(['{primary}' => $primary_key])
            ->one();
    }

    public function getOneByColumn(string $column, $value, stirng $pattern = 'whole') : \stdClass {
        return $this->select()
        ->withPattern($pattern)
        ->where([$column => $value])
        ->one();
    }

    public function getManyByColumns(Array $clauses, string $pattern = 'whole', int $limit = 1000, int $offset = 0) : array {
        return $this->select()
            ->withPattern($pattern)
            ->where($clauses)
            ->limit($limit, $offset)
            ->many();
    }

    public function editByKey(int $primary_key, Array $update) : bool {
        return $this->update()
            ->withPattern('edit')
            ->set($update)
            ->where(['{primary}' => $primary_key])
            ->execute();
    }

    public function create(Array $new) : int {
        $check = $this->insert()
            ->withPattern('create')
            ->add($new)
            ->execute();
        if($check) {
            return $this->source->lastInsertId('{name}');
        }
        return 0;
    }

    public function createBatch(Array $batch) : bool {
        if(!empty($batch)) {
            $this->insert()->withPattern('create');
            foreach ($batch as $record) {
                $this->add($record);
            }
            return $this->execute();
        }
        return false;
    }

    /**
     * Add your own functions here. If you ever need to regenerate this file using Architect,
     * functions between START CUSTOM and END CUSTOM will not be erased.
     */

    // START CUSTOM

    {existing_functions}

    // END CUSTOM
}