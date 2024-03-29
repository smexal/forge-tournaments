<?php

namespace Forge\Modules\ForgeTournaments;

use \Forge\Core\App\App;
use \Forge\Core\Classes\User;
use \Forge\Core\Classes\FieldUtils as FieldUtils;
use \Forge\Core\Classes\Relations\Enums\Directions as RelationDirection;
use \Forge\Core\Classes\Relations\CollectionRelation as CollectionRelation;


class EncounterCollection extends NodaDataCollection {
    const COLLECTION_NAME = 'forge-tournaments-encounter';
    protected static $PARENT_COLLECTION = GroupCollection::COLLECTION_NAME;

    public $permission = "manage.collection.forge-tournaments-encounter";


    protected function setup() {
        $this->preferences['name'] = EncounterCollection::COLLECTION_NAME;
        $this->preferences['title'] = i('Encounters', 'forge-tournaments');
        $this->preferences['all-title'] = i('Manage encounter', 'forge-tournaments');
        $this->preferences['add-label'] = i('Add encounter', 'forge-tournaments');
        $this->preferences['single-item'] = i('Encounter', 'forge-tournaments');
        parent::setup();

    }

    public function render($item) {
        return "RENDER";
    }

    public static function registerSubTypes() {
    }

    public function custom_fields() {
        $this->addFields(parent::inheritedFields());
    }

    public function itemDependentFields($item) {

        parent::itemDependentFields($item);
    }

}
