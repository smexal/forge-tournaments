<?php

namespace Forge\Modules\ForgeTournaments;

use \Forge\Core\Abstracts\DataCollection;
use \Forge\Core\App\App;
use \Forge\Core\Classes\User;
use \Forge\Core\Classes\FieldUtils as FieldUtils;
use \Forge\Core\Classes\Relations\Enums\Directions as RelationDirection;
use \Forge\Core\Classes\Relations\CollectionRelation as CollectionRelation;

use \Forge\Modules\ForgeTournaments\CollectionSubtypes\Participants\UserParticipant;
use \Forge\Modules\ForgeTournaments\CollectionSubtypes\Participants\TeamParticipant;

class ParticipantCollection extends DataCollection {
    const COLLECTION_NAME = 'forge-tournaments-participant';
    public $permission = "manage.collection.sites";


    protected function setup() {
        $this->preferences['name'] = ParticipantCollection::COLLECTION_NAME;
        $this->preferences['title'] = i('Participants', 'forge-tournaments');
        $this->preferences['all-title'] = i('Manage participant', 'forge-tournaments');
        $this->preferences['add-label'] = i('Add participant', 'forge-tournaments');
        $this->preferences['single-item'] = i('Participant', 'forge-tournaments');

        $this->custom_fields();

    }

    public function render($item) {
        return "RENDER";
    }

    public static function relations($existing) {
        return array_merge($existing, [
           /* 'ft_prev_phase' => new CollectionRelation(
                'ft_prev_phase', 
                ParticipantCollection::COLLECTION_NAME, 
                ParticipantCollection::COLLECTION_NAME, 
                RelationDirection::DIRECTED
            ),
            'ft_next_phase' => new CollectionRelation(
                'ft_next_phase', 
                ParticipantCollection::COLLECTION_NAME, 
                ParticipantCollection::COLLECTION_NAME, 
                RelationDirection::DIRECTED
            )*/
        ]);
    }

    public static function registerSubTypes() {
        BaseRegistry::registerTypes('IParticipantType', FORGE_TOURNAMENTS_COLLECTION_SUBTYPES['IParticipantType']);
    }

    private function custom_fields() {
        $this->addFields([
            [
                'key' => 'ft_participant_type',
                'label' => \i('Type of participant', 'forge-tournaments'),
                'values' => Utils::getParticipantTypes(),
                'value' => ParticipantTypes::USER,
                'multilang' => false,
                'type' => 'select',
                'order' => 10,
                'position' => 'right',
                'hint' => i('Select the participant status', 'forge-tournaments')
            ]
        ]);
    }

    public function itemDependentFields($item) {
        $participant = Utils::getSubtype('IParticipantType', $item, 'ft_participant_type');
        if(!is_null($participant)) {
            $new_fields = $participant->fields($item);
            $this->addFields($new_fields);
            $this->customFields = $participant->modifyFields($this->customFields);
        }
    }

    public function processModifyParticipantType($field, $item, $value) {
        $phase_status = $item->getMeta('ft_phase_status');
        if($phase_status > State::OPEN) {
            $field['readonly'] = true;
        }
        return $field;
    }
}

?>
