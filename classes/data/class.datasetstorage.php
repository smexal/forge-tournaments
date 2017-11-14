<?php

namespace Forge\Modules\ForgeTournaments\Data;

use Forge\Modules\ForgeTournaments\Interfaces\IDataSchema;

class DataSetStorage implements IDataSetStorage {
    private $ref_type;
    private $ref_id;

    public function __construct($ref_type, $ref_id) {
        $this->ref_type = $ref_type;
        $this->ref_id = $ref_id;
    }

    public function save(IDataSet $set) {
        $db = App::instance()->db;
        $list = [];
        foreach($set->getAllDataSegments() as $segment) {
            foreach($segment->getAllData() as $key => $source_data) {
                foreach($source_data as $source => $value) {
                    $list[] = array(
                        'ref_id' => $this->ref_id,
                        'ref_type' => $this->ref_type,
                        'group' => $segment->getSegmentID(),
                        'source' => $segment->getSource()
                    );
                }
            }
        }
        $category = $db->insert("ft_datastorage", $insert);
    }

    public function loadAll() {
        $db = App::instance()->db;
        $db->where('ref_type', $this->ref_type);
        $db->where('ref_id', $this->ref_id);
        $db->orderBy('changed', 'ASC');
        $list = $db->get('ft_datastorage');
        $list = $this->buildDataSets($list);
        return $list;
    }

    public function buildDataSets(array $list) {
        $grouped_data = [];
        foreach($list as $list) {
            if(!isset($grouped_data[$list['group']])) {
                $grouped_data[$list['group']] = [];
            }
            if(!isset($grouped_data[$list['group']][$list['key']])) {
                $grouped_data[$list['group']][$list['key']] = [];
            }
            $grouped_data[$list['group']][$list['key']][$list['source']] = $list['value'];
        }

        $dataset = new DataSet();
        foreach($grouped_data as $segment_id => $segment_data) {
            $dataset->addDataSegment(new DataSegment($segment_id, $segment_data));
        }
        return $dataset;
    }

}