<?php

namespace Forge\Modules\ForgeTournaments\Data;

use Forge\Modules\ForgeTournaments\Interfaces\IDataSchema;

class DataStorageNode implements IDataStorageNode {
    private $dataschema;
    private $storage;

    public function __construct(IDataSchema $dataschema, IDataSetStorage $storage) {
        $this->dataschema = $dataschema;
        $this->storage = $storage;
    }

    public function getDataSchema() : IDataSchema {
        return $this->dataschema;
    }

    public function getStorage() : IDataSetStorage {
        return $this->storage;
    }

}