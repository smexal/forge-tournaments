<?php

namespace Forge\Modules\ForgeTournaments\Interfaces;

interface IDataSegment {
    
    public function getSegmentID();

    public function setValue($key, $value);

    public function getValue($key, $value);

    public function getData() : array ;

    public function merge(IDataSegment $data);

    public function join(IDataSegment $data);

}
