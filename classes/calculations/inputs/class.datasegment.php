<?php

namespace Forge\Modules\ForgeTournaments\Calculations\Inputs;

use Forge\Modules\ForgeTournaments\Interfaces\IDataSegment;


class DataSegment implements IDataSegment{
    private $segment_id = null;
    private $data = [];

    public function __construct($segment_id, $data) {
        $this->segment_id = $segment_id;
        $this->data = $data;
    }

    public function getSegmentID() {
        return $this->segment_id;
    }

    public function setValue($key, $value) {
        $this->data[$key] = $value;
    }

    public function getValue($key) {
        return $this->data[$key];
    }

    public function getData() : array {
        return $this->data;
    }

    public function merge(IDataSegment $data) {
        if(is_null($data)) {
            return;
        }
        $this->data = array_merge($this->data, $data->getData());
        return $this;
    }

    public function join(IDataSegment $data) {
        $new_ds = new IDataSegment($this->segment_id, $data);
        $new_ds->merge(array_merge($this->data, $data->getData()));
        return $this;
    }

}