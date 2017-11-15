<?php

namespace Forge\Modules\ForgeTournaments\Data;

use Forge\Modules\ForgeTournaments\Interfaces\IDataSetStorage;
use Forge\Modules\ForgeTournaments\Interfaces\IDataSet;

class DataSetStorage implements IDataSetStorage {
    private $ref_type;
    private $ref_id;
    private $storage_handler = null;
    public function __construct($ref_type, $ref_id, $storage_handler=null) {
        $this->ref_type = $ref_type;
        $this->ref_id = $ref_id;
        if(!is_null($storage_handler)) {
            $this->storage_handler = $storage_handler;
        } else {
            $this->storage_handler = \App::instance()->db;
        }
    }

    public function setStorageHandler($sh) {
        $this->storage_handler = $sh;
    }
    public function getStorageHandler() {
        return $this->storage_handler;
    }

    public function save(IDataSet $set) {
        $db = $this->storage_handler;
        $insert_list = [];
        foreach($set->getAllDataSegments() as $segment) {
            foreach($segment->getAllData() as $key => $source_data) {
                foreach($source_data as $source => $value) {
                    $insert_list[] = array(
                        'ref_id' => $this->ref_id,
                        'ref_type' => $this->ref_type,
                        'group' => $segment->getSegmentID(),
                        'source' => $source,
                        'key' => $key,
                        'value' => $value
                    );
                }
            }
        }
        $db->insertMultiple("ft_datastorage", $insert_list);
    }

    public function loadAll() {
        $db = $this->storage_handler;
        $db->where('ref_type', $this->ref_type);
        $db->where('ref_id', $this->ref_id);
        $db->orderBy('changed', 'ASC');
        $list = $db->get('ft_datastorage');
        $list = $this->buildDataSets($list);
        return $list;
    }

    public function buildDataSets(array $list_items) {
        $grouped_data = [];
        foreach($list_items as $item) {
            if(!isset($grouped_data[$item['group']])) {
                $grouped_data[$item['group']] = [];
            }
            if(!isset($grouped_data[$item['group']][$item['key']])) {
                $grouped_data[$item['group']][$item['key']] = [];
            }
            $grouped_data[$item['group']][$item['key']][$item['source']] = $item['value'];
        }
        $dataset = new DataSet();
        foreach($grouped_data as $segment_id => $segment_data) {
            $data_segment = new DataSegment($segment_id);
            $data_segment->setMultisourceData($segment_data);
            $dataset->addDataSegment($data_segment);
        }
        return $dataset;
    }

}