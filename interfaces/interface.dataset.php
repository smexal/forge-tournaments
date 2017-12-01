<?php

namespace Forge\Modules\ForgeTournaments\Interfaces;

interface IDataSet {
    
    public function getSegmentIDs();

    public function addDataSegments(array $data_segments);

    public function addDataSegment(IDataSegment $data);

    public function hasDataSegment($segment_id) : bool ;

    public function getDataSegment($segment_id);

    public function getAllDataSegments() : array;

    public function merge(IDataSet $data);

    public function join(IDataSet $data);
}
