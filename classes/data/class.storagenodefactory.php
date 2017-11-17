<?php

namespace Forge\Modules\ForgeTournaments\Data;

use Forge\Modules\ForgeTournaments\Interfaces\IDataSchema;

use \Forge\Core\Classes\CollectionItem;
use Forge\Modules\ForgeTournaments\Data\DatasetStorage;
use Forge\Modules\ForgeTournaments\Data\DataStorageNode;

abstract class StorageNodeFactory {
    private static $instances = [];

    public static function getByCollectionID($item_id) {
        $item = new CollectionItem($item_id);
        
        $ref_id = $item->getID();
        $ref_type = str_replace('forge-tournaments-', '', $item->getType());
        $ref_type = strtolower($ref_type);
        $schema_id = $item->getMeta('data_schema');
        return static::getInstance($ref_type, $ref_id, $schema_id);
    }

    public static function getInstance($ref_type, $ref_id, $schema_id) {
        $key = implode('.', [$ref_type, $ref_id, $schema_id]);
        if(isset(static::$instances[$key])) {
            return static::$instances[$key];
        }
        $schema = SchemaProvider::instance()->getSchema($schema_id);
        $storage = DatasetStorage::getInstance($ref_type, $ref_id);
        $node = new DataStorageNode($schema, $storage);
        static::$instances[$key] = $node;
        return static::$instances[$key];
    }

}