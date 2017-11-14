<?php

namespace Forge\Modules\ForgeTournaments\Data;

use Forge\Modules\ForgeTournaments\Interfaces\IDataSegment;


class DataSegment implements IDataSegment {
    const DEFAULT_SOURCE = '__default__';

    private $segment_id = null;
    private $data = [];

    public function __construct($segment_id) {
        $this->segment_id = $segment_id;
    }


    public function getSegmentID() {
        return $this->segment_id;
    }

    public function addData($data, $source='__default__') {
        foreach($data as $key => $value) {
            $this->setValue($key, $value, $source);
        }
    }

    public function setValue($key, $value, $source='__default__') {
        if(!isset($this->data[$key])) {
            $this->data[$key] = [];
        }
        $this->data[$key][$source] = $value;
    }

    public function getValue($key, $source='__default__') {
        return $this->data[$key][$source];
    }

    public function hasSource($key, $source) {
        return isset($this->data[$key]) && isset($this->data[$key][$source]);
    }

    public function getAllData() {
        return $this->data;
    }

    public function getData($key, $source='__default__') {
        if($this->hasSource($key, $source)) {
            return $this->data[$key][$source];
        }
        return null;
    }

    public function getDataBySource() {
        $source_data = [];
        foreach($this->data as $key => $key_group) {
            foreach($key_group as $source => $value) {
                if(!isset($source_data[$source])) {
                    $source_data[$source] = [];
                }
                $source_data[$source][$key] = $value;
            }
        }
        return $source_data;
    }

    public function getDataOfSource($source='__default__') {
        $source_data = [];
        foreach($this->data as $key => $key_group) {
            foreach($key_group as $source => $value) {
                if($source !== $source) {
                    continue;
                }
                $source_data[$key] = $value;
            }
        }
        return $source_data;
    }

    public function merge(IDataSegment $data) {
        if(is_null($data)) {
            return;
        }

        foreach($data->getAllData() as $key => $source_data) {
            if(!isset($this->data[$key])) {
                $this->data[$key] = [];
            }
            $this->data[$key]= array_merge($this->data[$key], $source_data);
        }
        return $this;
    }

    public function join(IDataSegment $data) {
        $new_ds = new DataSegment($this->segment_id, $data);
        $new_ds->merge($this);
        $new_ds->merge($data);
        return $this;
    }

}