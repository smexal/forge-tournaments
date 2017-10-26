<?php

namespace Forge\Modules\ForgeTournaments\Calculations;

abstract class Input extends IInput {
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

    public function appendData(IDataSet $data, ICalcNode $node) : array {
        return $data->merge($this->getDataSet());
    }

    public function getStatus() {
        return Input::STATUS_OK;
    }

}