<?php

namespace Forge\Modules\ForgeTournaments;

use \Forge\Core\Abstracts\DataCollection;
use \Forge\Core\App\App;
use \Forge\Core\Classes\User;
use \Forge\Core\Classes\FieldUtils as FieldUtils;
use \Forge\Core\Classes\Relations\Enums\Directions as RelationDirection;
use \Forge\Core\Classes\Relations\CollectionRelation as CollectionRelation;

class ParticipantCollection extends DataCollection {
    const COLLECTION_NAME = 'forge-tournaments-participant';
    public $permission = "manage.collection.sites";


    protected function setup() {
        $this->preferences['name'] = ParticipantCollection::COLLECTION_NAME; //TODO: make this a class constant
        $this->preferences['title'] = i('Participants', 'forge-tournaments');
        $this->preferences['all-title'] = i('Manage phase', 'forge-tournaments');
        $this->preferences['add-label'] = i('Add phase', 'forge-tournaments');
        $this->preferences['single-item'] = i('Participant', 'forge-tournaments');

        $this->custom_fields();

    }

    public function render($item) {
        return "RENDER";
    }

    public static function relations($existing) {
        return array_merge($existing, [
            'ft_prev_phase' => new CollectionRelation(
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
            )
        ]);
    }

    public static function registerParticipantTypes() {
        $ns = '\\Forge\\Modules\\ForgeTournaments\\Participants\\';
        ParticipantRegistry::instance()->register($ns . 'KOParticipant');
        ParticipantRegistry::instance()->register($ns . 'GroupParticipant');
        ParticipantRegistry::instance()->register($ns . 'RegistrationParticipant');
        ParticipantRegistry::instance()->register($ns . 'PerformanceParticipant');
    }

    private function custom_fields() {
        $this->addFields([
            [
                'key' => 'ft_phase_status',
                'label' => \i('Lifecycle', 'forge-tournaments'),
                'values' => Utils::getParticipantStates(),
                'value' => State::FRESH,
                'multilang' => false,
                'type' => 'select',
                'order' => 10,
                'position' => 'right',
                'hint' => i('Select the phase status', 'forge-tournaments')
            ],
            [
                'key' => 'ft_phase_type',
                'label' => \i('Participant type', 'forge-tournaments'),
                'values' => Utils::getParticipantTypes(),
                'value' => ParticipantType::REGISTRATION,
                'multilang' => false,
                'type' => 'select',
                'order' => 10,
                'position' => 'left',
                'hint' => i('Select the phase type', 'forge-tournaments'),
                'process:modifyField' => [$this, 'processModifyParticipantType'],
            ],
            [
                'key' => 'ft_next_phase',
                'label' => \i('Next Participant', 'forge-tournaments'),
                'values' => Utils::getParticipantTypes(),
                'value' => NULL,
                'multilang' => false,

                'type' => 'collection',
                'maxtags'=> 1,
                'collection' => ParticipantCollection::COLLECTION_NAME,
                'data_source' => 'relation',
                'relation' => [
                    'identifier' => 'ft_next_phase'
                ],

                'order' => 10,
                'position' => 'left',
                'readonly' => true,
                'hint' => i('The next phase after this one is completed', 'forge-tournaments'),
                'process:modifyField' => [$this, 'processModifyParticipantType'],
            ],
        ]);
    }

    public function itemDependentFields($item) {
        $phase_key = $item->getMeta('ft_phase_type');
        $phase = ParticipantRegistry::instance()->get($phase_key);
        if(is_null($phase)) {
            return;
        }

        $new_fields = $phase->fields($item);
        $this->addFields($new_fields);
        $this->customFields = $phase->modifyFields($this->customFields);
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
