<?php

namespace Forge\Modules\ForgeTournaments\Calculations;

class CollectionInput extends IInput {
    private $collection_id = null;
    private $metadata_keys = [];

    public function __construct($key, $collection_id, $metadata_keys) {
        parent::__construct($key);
        $this->collection_id = $collection_id;
        $this->metadata_keys = $metadata_keys;
    }

    public function appendData() : array {
        if(!is_null(parent::getData())) {
            return parent::getData();
        }
        $item = new CollectionItem($this->collection_item);
        $team_ids = $item->getMeta('_team_ids');
        $data_set = [];
        foreach($team_ids as $id) {
            $team_data = new Data($id);
            foreach($this->meta_data_keys as $key) {
                $team_key = '_team-' . $id . '_' . $key;
                $team_data->setValue($key, $item->getMeta($team_key, false));
            }
            $data_set[$id] = $team_data;
        } 
        $this->data = $data_set;
        return parent::getData();
    }

    public function getStatus() {
        
    }

}