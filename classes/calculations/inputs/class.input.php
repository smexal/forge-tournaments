<?php

namespace Forge\Modules\ForgeTournaments\Calculations\Inputs;

use Forge\Modules\ForgeTournaments\Interfaces\IInput;
use Forge\Modules\ForgeTournaments\Interfaces\IDataSet;
use Forge\Modules\ForgeTournaments\Interfaces\INode;

abstract class Input implements IInput {
    const STATUS_OPEN = 0x0;
    const STATUS_OK = 0x1;
    const STATUS_CONFLICT = 0x2;

    protected $key = null;
    
    // IDataSet
    protected $data = null;

    public function __construct($key) {
        $this->key = $key;
    }

    public function setKey($key) {
        $this->key = $key;
    }

    public function getKey() : string {
        return $this->key;
    }

    public function getDataSet() : IDataSet {
        return $this->data;
    }

    public function appendData(IDataSet $data, INode $node) : IDataSet {
        return $data->join($this->getDataSet());
    }

    public function getStatus() {
        return Input::STATUS_OK;
    }

}