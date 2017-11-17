<?php

namespace Forge\Modules\ForgeTournaments\Data;

use Forge\Modules\ForgeTournaments\Interfaces\IDataSchema;
use Forge\Modules\ForgeTournaments\Interfaces\IDatasetStorage;

class DataStorageNode {
    private $dataschema;
    private $storage;

    public function __construct(IDataSchema $dataschema, IDatasetStorage $storage) {
        $this->dataschema = $dataschema;
        $this->storage = $storage;
    }

    public function getDataSchema() : IDataSchema {
        return $this->dataschema;
    }

    public function getStorage() : IDatasetStorage {
        return $this->storage;
    }

}