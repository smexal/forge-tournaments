<?php

namespace Forge\Modules\ForgeTournaments\Calculations\Inputs;

use Forge\Modules\ForgeTournaments\Interfaces\IDataSet;
use Forge\Modules\ForgeTournaments\Interfaces\IDataSegment;

class DataSet implements IDataSet {
    private $data_segments = [];

    public function __construct(array $data_segments = []) {
        $this->addDataSegments($data_segments);
    }

    public function getSegmentIDs() {
        return array_keys($this->data_segments);
    }

    public function addDataSegments(array $data_segments) {
        foreach($data_segments as $data_segments) {
            $this->addDataSegment($data_segments);
        }
    }
    public function addDataSegment(IDataSegment $data) {
        if(!$this->hasDataSegment($data->getSegmentID())) {
            $this->data_segments[$data->getSegmentID()] = $data;
        } else {
            $this->data_segments[$data->getSegmentID()]->merge($data);
        }
    }

    public function hasDataSegment($segment_id) : bool {
        return isset($this->data_segments[$segment_id]);
    }

    public function getDataSegment($segment_id) {
        return $this->hasDataSegment($segment_id) ? $this->data_segments[$segment_id] : null;
    }

    public function getAllDataSegments() : array {
        return $this->data_segments;
    }

    public function merge(IDataSet $data) {
        foreach($data->getAllDataSegments() as $new_data_segment) {
            $this->addDataSegment($new_data_segment);
        }
        return $this;
    }

    public function join(IDataSet $data) {
        $new_ds = new DataSet();
        foreach($this->getAllDataSegments() as $new_data_segment) {
            $new_ds->addDataSegment($new_data_segment);
        }
        foreach($data->getAllDataSegments() as $new_data_segment) {
            $new_ds->addDataSegment($new_data_segment);
        }
        //var_dump($new_ds);
        return $new_ds;
    }

}