<?php

namespace Forge\Modules\ForgeTournaments;

use Forge\Core\Abstracts\DataCollection;
use Forge\Core\App\App;
use Forge\Core\App\Auth;
use Forge\Core\Classes\User;
use Forge\Core\Classes\CollectionItem;
use Forge\Core\Classes\FieldUtils as FieldUtils;
use Forge\Core\Classes\Relations\Enums\Directions as RelationDirection;
use Forge\Core\Classes\Relations\CollectionRelation as CollectionRelation;

use Forge\Modules\ForgeTournaments\Scoring\ScoringProvider;
use Forge\Modules\ForgeTournaments\Fields\FieldProvider;
use Forge\Modules\ForgeTournaments\CollectionSubtypes\Phases\PhaseRegistry;

class PhaseCollection extends NodaDataCollection {
    const COLLECTION_NAME = 'forge-tournaments-phase';
    protected static $PARENT_COLLECTION = TournamentCollection::COLLECTION_NAME;
    
    public $permission = "manage.collection.sites";


    protected function setup() {
        $this->preferences['name'] = PhaseCollection::COLLECTION_NAME;
        $this->preferences['title'] = i('Phases', 'forge-tournaments');
        $this->preferences['all-title'] = i('Manage phase', 'forge-tournaments');
        $this->preferences['add-label'] = i('Add phase', 'forge-tournaments');
        $this->preferences['single-item'] = i('Phase', 'forge-tournaments');

        $this->custom_fields();
        parent::setup();

    }

    public function customEditContent($item_id) {
        $html = '';
        $item = new CollectionItem($item_id);
        $phase = Utils::getSubtype('IPhaseType', $item, 'ft_phase_type');
        if($phase) {
            $html .= $phase->render($item);
        }
        return $html;
    }

    public static function relations($existing) {
        return array_merge($existing, [
            'ft_next_phase' => new CollectionRelation(
                'ft_next_phase', 
                PhaseCollection::COLLECTION_NAME, 
                PhaseCollection::COLLECTION_NAME, 
                RelationDirection::DIRECTED
            ),
            'ft_participant_output_list' => new CollectionRelation(
                'ft_participant_output_list', 
                PhaseCollection::COLLECTION_NAME, 
                PhaseCollection::COLLECTION_NAME, 
                RelationDirection::DIRECTED
            )
        ]);
    }

    public static function registerSubTypes() {
        BaseRegistry::registerTypes('IPhaseType', FORGE_TOURNAMENTS_COLLECTION_SUBTYPES['IPhaseType']);
    }

    protected function custom_fields() {
        $fields = [
            [
                'key' => 'ft_phase_state',
                
                'type' => ['\\Forge\\Modules\\ForgeTournaments\\Fields\\PhaseSteps', 'render'],

                'label' => \i('Phase State', 'forge-tournaments'),
                'values' => Utils::getPhaseStates(),
                'value' => PhaseState::CONFIG_BASIC,
                'multilang' => false,
                'order' => 3,
                'position' => 'right',
                'hint' => \i('Click above to progress to the next state or to revert back', 'forge-tournaments'),
                'data_source_save' => [$this, 'savePhaseState']
            ],
            FieldProvider::phaseTypeSelect([
                '__last_phase_state' =>  PhaseState::CONFIG_BASIC
            ]),
            [
                'key' => 'ft_scoring',
                'label' => \i('Scoring type', 'forge-tournaments'),
                'values' => Utils::getScoringOptions(),
                'value' => Utils::getDefaultScoringID(),
                'multilang' => false,
                'type' => 'select',
                'order' => 4,
                'position' => 'right',
                'hint' => '',
                '__first_phase_state' =>  PhaseState::CONFIG_PHASETYPE,
                '__last_phase_state' => PhaseState::CONFIG_PHASETYPE
            ],
            [
                'key' => 'ft_num_winners',
                'label' => \i('How many participants are reaching the next phase?', 'forge-tournaments'),
                'value' => 8,
                'multilang' => false,
                'type' => 'number',
                'order' => 7,
                'position' => 'right',
                'hint' => \i('Ensure the following phase has at least as many total slots available', 'forge-tournaments'),
                '__last_phase_state' => PhaseState::CONFIG_BASIC
            ],
            [
                'key' => 'ft_next_phase',
                'label' => \i('Next Phase', 'forge-tournaments'),
                'values' => [],
                'value' => NULL,
                'multilang' => false,

                'type' => 'collection',
                'maxtags'=> 1,
                'collection' => PhaseCollection::COLLECTION_NAME,
                'data_source_save' => 'relation',
                'data_source_load' => 'relation',
                'relation' => [
                    'identifier' => 'ft_next_phase'
                ],

                'order' => 10,
                'position' => 'left',
                'readonly' => true,
                'hint' => i('The next phase after this one is completed', 'forge-tournaments')
            ],
            [
                'key' => 'ft_participant_list_size',
                'label' => \i('Participant list size', 'forge-tournaments'),
                'value' => 16,
                'multilang' => false,
                'type' => 'number',
                'order' => 10,
                'position' => 'right',
                'hint' => \i('Define how many participants are allowed. Use -1 for no restriction', 'forge-tournaments'),
                '__last_phase_state' => PhaseState::CONFIG_BASIC
            ],
            [
                'key' => 'ft_participant_list',
                'label' => \i('Participant list', 'forge-tournaments'),
                'value' => '',
                'multilang' => false,

                'type' => 'collection',
                /*'maxtags'=> 64, SET BY ft_num_winners*/
                'collection' => ParticipantCollection::COLLECTION_NAME,
                'data_source_save' => 'relation',
                'data_source_load' => 'relation',
                'relation' => [
                    'identifier' => 'ft_participant_list'
                ],

                'order' => 20,
                'position' => 'left',
                'hint' => \i('You can only add participants when the phase did not already start', 'forge-tournaments'),
                '__first_phase_state' => PhaseState::RUNNING,
                '__last_phase_state' => PhaseState::RUNNING
            ],
            [
                'key' => 'ft_participant_output_list',
                'label' => \i('Participant output list', 'forge-tournaments'),
                'value' => '',
                'multilang' => false,

                'type' => 'collection',

                /*'maxtags'=> 64, SET BY ft_num_winners*/
                'collection' => ParticipantCollection::COLLECTION_NAME,
                'order' => 20,
                'position' => 'left',
                'hint' => \i('You can only add participants when the phase did not already start', 'forge-tournaments'),
                '__last_phase_state' => PhaseState::ASSIGNMENT
            ],
        ];


        $fields = $this->setPhaseStateHandlers($fields);
        $this->addFields($fields);

        $fields = parent::inheritedFields();

        foreach($fields as $idx => &$field) {
            if($field['key'] == 'ft_slot_assignment') {
                $field['pool_source_selector'] = 'input[name="ft_participant_list"]';
                $field['data_source_save'] = [$this, 'saveSlotAssignment'];
                // The slot_assignement is not any longer rendered by the field, instead this is done via the subtype-render
                $field['readonly'] = false;
                $field['__last_phase_state_remove'] = PhaseState::ASSIGNMENT;
            }

            if($field['key'] == 'ft_data_schema') {
            }
        }

        $fields = $this->setPhaseStateHandlers($fields);

        $this->addFields($fields);
    }

    public function setPhaseStateHandlers($fields) {
        foreach($fields as &$field) {
            if(isset($field['__first_phase_state']) || 
               isset($field['__last_phase_state'])  ||
               isset($field['__last_phase_state_remove'])  
            ) {
                $field['process:modifyField'] = [$this, 'processModifyPhaseType'];
            }
        }
        return $fields;
    }

    public function itemDependentFields($item) {
        parent::itemDependentFields($item);
        
        $scoring = $item->getMeta('ft_scoring');
        $scoring = $scoring ? $scoring : Utils::getDefaultScoringID();
        $scoring = ScoringProvider::instance()->getScoring($scoring);

        $phase = PoolRegistry::instance()->getPool('phase')->getInstance($item->id, $item);
        $phase_state = $phase->getState();
        $subtype = Utils::getSubtype('IPhaseType', $item, 'ft_phase_type');

        if(!is_null($subtype)) {
            $new_fields = $subtype->fields($item);
            $this->addUniqueFields($new_fields);
            
            $to_remove = [];
            foreach($this->customFields as $key => $field) {
                if(isset($field['__first_phase_state_remove']) 
                    && $phase_state < $field['__first_phase_state_remove']) {
                    unset($this->customFields[$key]);
                } else if(isset($field['__last_phase_state_remove']) && $phase_state > $field['__last_phase_state_remove']) {
                    unset($this->customFields[$key]);
                }
            }
            $this->customFields = $subtype->modifyFields($this->customFields, $item);
        }
        $this->customFields = $this->setPhaseStateHandlers($this->customFields);
    }

    public function processModifyPhaseType($field, $item, $value) {
        $phase_state = $item->getMeta('ft_phase_state');

        if(isset($field['__first_phase_state']) && $phase_state < $field['__first_phase_state']) {
            $field['readonly'] = true;
        } else if(isset($field['__last_phase_state']) && $phase_state > $field['__last_phase_state']) {
            $field['readonly'] = true;
        }
        return $field;
    }

    public function savePhaseState($item, $field, $value, $lang) {
        $phase = PoolRegistry::instance()->getPool('phase')->getInstance($item->id, $item);
        $phase->changeState($value);
    }

    public function saveSlotAssignment($item, $field, $value, $lang) { 
        \Forge\Modules\ForgeTournaments\Fields\SlotAssignment::save($item, $field, $value, $lang);
    }

    public function subviewCreator() {
        return 'asdf';
        if (!Auth::allowed("manage.collection.sites")) {
            return;
        }
    }

}
