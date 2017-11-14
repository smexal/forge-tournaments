<?php

namespace Forge\Modules\ForgeTournaments\Data;

use Forge\Core\Traits\Singleton;

use Forge\Modules\ForgeTournaments\Interfaces\ISchemaProvider;

class SchemaProvider implements ISchemaProvider {
    use Singleton;

    private $schemas = [];

    public function addSchema($id, IDataSchema $schema) {
        $this->schemas[$id] = $schema;
    }

    public function getSchema($id) {
        return $this->schemas[$id];
    }

}