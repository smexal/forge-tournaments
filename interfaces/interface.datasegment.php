<?php

namespace Forge\Modules\ForgeTournaments\Interfaces;

interface IDataSegment {

    public function __construct($segment_id);

    public function getSegmentID();

    public function setValue($key, $value, $source='__default__');

    public function getValue($key, $source='__default__');

    public function hasSource($key, $source);

    public function getAllData();

    public function getData($key, $source='__default__');

    public function getDataBySource();

    public function getDataOfSource($source='__default__');

    public function merge(IDataSegment $data);

    public function join(IDataSegment $data);
}
