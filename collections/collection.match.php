<?php

namespace Forge\Modules\ForgeTournaments;

use \Forge\Core\App\App;
use \Forge\Core\Classes\User;
use \Forge\Core\Classes\FieldUtils as FieldUtils;
use \Forge\Core\Classes\Relations\Enums\Directions as RelationDirection;
use \Forge\Core\Classes\Relations\CollectionRelation as CollectionRelation;


class MatchCollection extends NodaDataCollection {
    const COLLECTION_NAME = 'forge-tournaments-match';
    public $permission = "manage.collection.sites";


    protected function setup() {
        $this->preferences['name'] = MatchCollection::COLLECTION_NAME;
        $this->preferences['title'] = i('Matches', 'forge-tournaments');
        $this->preferences['all-title'] = i('Manage match', 'forge-tournaments');
        $this->preferences['add-label'] = i('Add match', 'forge-tournaments');
        $this->preferences['single-item'] = i('Match', 'forge-tournaments');

        $this->custom_fields();
        parent::setup();

    }

    public function render($item) {
        return "RENDER";
    }

    public static function registerSubTypes() {
    }

    protected function custom_fields() {
        parent::custom_fields();
    }


    public function itemDependentFields($item) {

        parent::itemDependentFields($item);
    }

}
