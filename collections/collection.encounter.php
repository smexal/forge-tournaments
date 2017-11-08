<?php

namespace Forge\Modules\ForgeTournaments;

use \Forge\Core\Abstracts\DataCollection;
use \Forge\Core\App\App;
use \Forge\Core\Classes\User;
use \Forge\Core\Classes\FieldUtils as FieldUtils;
use \Forge\Core\Classes\Relations\Enums\Directions as RelationDirection;
use \Forge\Core\Classes\Relations\CollectionRelation as CollectionRelation;


class EncounterCollection extends DataCollection {
    const COLLECTION_NAME = 'forge-tournaments-encounter';
    public $permission = "manage.collection.sites";


    protected function setup() {
        $this->preferences['name'] = EncounterCollection::COLLECTION_NAME;
        $this->preferences['title'] = i('Encounters', 'forge-tournaments');
        $this->preferences['all-title'] = i('Manage encounter', 'forge-tournaments');
        $this->preferences['add-label'] = i('Add encounter', 'forge-tournaments');
        $this->preferences['single-item'] = i('Encounter', 'forge-tournaments');

        $this->custom_fields();

    }

    public function render($item) {
        return "RENDER";
    }

    public static function registerSubTypes() {
    }

    private function custom_fields() {}

    public function itemDependentFields($item) {}

}
