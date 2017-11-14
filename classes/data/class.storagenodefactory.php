<?php

namespace Forge\Modules\ForgeTournaments\Data;

use Forge\Modules\ForgeTournaments\Interfaces\IDataSchema;

abstract class StorageNodeFactory {
    public static function getStorageNode($ref_type, $ref_id, $schema_id=null) {
        $schema = SchemaProvider::instance()->getSchema($schema_id);
        $storage = new DataSetStorage($ref_type, $ref_id);
        $node = new DataStorageNode($schema, $storage);
        return $node;
    }
}