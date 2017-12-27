<?php

namespace Forge\Modules\ForgeTournaments;

use \Forge\Core\App\App;
use \Forge\Core\Classes\User;
use Forge\Core\Classes\CollectionItem;

use \Forge\Core\Classes\FieldUtils as FieldUtils;
use \Forge\Core\Classes\Relations\Enums\Directions as RelationDirection;
use \Forge\Core\Classes\Relations\CollectionRelation as CollectionRelation;



class GroupCollection extends NodaDataCollection {
    const COLLECTION_NAME = 'forge-tournaments-group';
    protected static $PARENT_COLLECTION = PhaseCollection::COLLECTION_NAME;

    public $permission = "manage.collection.sites";


    protected function setup() {
        $this->preferences['name'] = GroupCollection::COLLECTION_NAME;
        $this->preferences['title'] = i('Groups', 'forge-tournaments');
        $this->preferences['all-title'] = i('Manage group', 'forge-tournaments');
        $this->preferences['add-label'] = i('Add group', 'forge-tournaments');
        $this->preferences['single-item'] = i('Group', 'forge-tournaments');

        $this->custom_fields();
        parent::setup();

    }

    public function customEditContent($item_id) {
        return $this->render(new CollectionItem($item_id));
    }

    public function render($item) : string {
        $html = '';
        $group = PoolRegistry::instance()->getPool('group')->getInstance($item->id, $item);
        $encounters = $group->getEncounters();
        foreach($encounters as $encounter) {
            $encounter_item = $encounter->getItem();
            $html .= "<h5>";
            $html .= "<a href=\"" . $encounter_item->url(true) . "\" target=\"_blank\">{$encounter_item->getName()}</a>";
            $html .= "</h5>";
            
            $html .= Output::participantList($encounter->getSlotAssignment());
            $html .= "<br />";
        }
        return $html;
    }

    public static function registerSubTypes() {
    }

    protected function custom_fields() {
        $this->addFields(parent::inheritedFields());
    }

    public function itemDependentFields($item) {

        parent::itemDependentFields($item);
    }

}
