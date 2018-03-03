<?php

namespace Forge\Modules\ForgeTournaments\Data;

use \Forge\Core\App\App;
use Forge\Modules\ForgeTournaments\Interfaces\IDatasetStorage;
use Forge\Modules\ForgeTournaments\Interfaces\IDataSet;

class DatasetStorage implements IDatasetStorage {
    private static $instances = [];

    private $ref_type;
    private $ref_id;
    private $storage_handler;

    public static function getInstance($ref_type, $ref_id, $storage_handler=null) {
        if(is_null($storage_handler)) {
            $storage_handler = App::instance()->db;
        }

        $key = $ref_type . $ref_id . spl_object_hash($storage_handler);
        if(isset(static::$instances[$key])) {
            return static::$instances[$key];
        }
        static::$instances[$key] = new DatasetStorage($ref_type, $ref_id, $storage_handler);
        return static::$instances[$key];
    }

    private function __construct($ref_type, $ref_id, $storage_handler) {
        $this->ref_type = $ref_type;
        $this->ref_id = $ref_id;
        $this->storage_handler = $storage_handler;
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
        foreach($insert_list as $i) {
            $db->insert("ft_datastorage", $i);
        }
    }

    public function deleteAll() {
        $db = $this->storage_handler;
        $db->where('ref_type', $this->ref_type);
        $db->where('ref_id', $this->ref_id);
        return $db->delete('ft_datastorage');
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