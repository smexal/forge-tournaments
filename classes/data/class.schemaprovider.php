<?php

namespace Forge\Modules\ForgeTournaments\Data;

use Forge\Modules\ForgeTournaments\Interfaces\IDataSchema;
use Forge\Modules\ForgeTournaments\Interfaces\ISchemaProvider;

use Forge\Core\Traits\Singleton;

class SchemaProvider implements ISchemaProvider {
    use Singleton;

    private $schemas = [];

    protected function __construct() {
        $this->addSchema(new DataSchema('null_schema', [], []));
    }

    public function addSchema(IDataSchema $schema) {
        $this->schemas[$schema->getID()] = $schema;
    }

    public function getAllSchemas() {
        return $this->schemas;
    }

    public function getSchemasForType($type) {
        return array_filter($this->schemas, function($item) use ($type) {
            return $item->isNodeTypeSupported($type);
        });
    }

    public function getSchema($id) {
        if(isset($this->schemas[$id])) {
            return $this->schemas[$id];
        }
        return $this->getSchema('null_schema');
    }

}