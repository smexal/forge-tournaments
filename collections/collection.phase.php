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
                'key' => 'ft_phase_status',
                'type' => 'select',
                'label' => \i('Lifecycle', 'forge-tournaments'),
                'values' => Utils::getPhaseStates(),
                'value' => PhaseState::FRESH,
                'multilang' => false,
                'order' => 3,
                'position' => 'right',
                'hint' => i('Select the phase status', 'forge-tournaments'),
                'data_source_save' => [$this, 'savePhaseStatus']
            ],
            [
                'key' => 'ft_phase_type',
                'type' => 'select',
                'label' => \i('Phase type', 'forge-tournaments'),
                'values' => Utils::getPhaseTypes(),
                'value' => PhaseTypes::REGISTRATION,
                'multilang' => false,
                'order' => 4,
                'position' => 'right',
                'hint' => i('Select the phase type', 'forge-tournaments'),
                '__last_phase_status' =>  PhaseState::FRESH,
            ],
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
                '__first_phase_status' =>  PhaseState::OPEN,
                '__last_phase_status' => PhaseState::OPEN
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
                '__last_phase_status' => PhaseState::FRESH
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
                '__last_phase_status' => PhaseState::OPEN
            ],
        ];

        $fields = $this->setPhaseStateHandlers($fields);
        $this->addFields($fields);

        $fields = parent::inheritedFields();

        foreach($fields as &$field) {
            if($field['key'] == 'ft_participant_list_size') {
                $field['__last_phase_status'] = PhaseState::FRESH;
            }
            if($field['key'] == 'ft_participant_list') {
                $field['__first_phase_status'] = PhaseState::RUNNING;
                $field['__last_phase_status'] = PhaseState::RUNNING;
            }

            if($field['key'] == 'ft_data_schema') {
            }
        }

        $fields = $this->setPhaseStateHandlers($fields);

        $this->addFields($fields);
    }

    public function setPhaseStateHandlers($fields) {
        foreach($fields as &$field) {
            if(isset($field['__first_phase_status']) || isset($field['__last_phase_status'])) {
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

        $phase = Utils::getSubtype('IPhaseType', $item, 'ft_phase_type');
        if(!is_null($phase)) {
            $new_fields = $phase->fields($item);
            $this->addUniqueFields($new_fields);
            $this->customFields = $phase->modifyFields($this->customFields);
        }


        $this->customFields = $this->setPhaseStateHandlers($this->customFields);

        /*foreach($this->customFields as &$field) {
            if(isset($field['__first_phase_status']) || isset($field['__last_phase_status'])) {
                $field['process:modifyField'] = [$this, 'processModifyPhaseType'];
            }

            // Lockdown phase switch to only allow inside the group
            // And forward to the next phase state group
            if($field['key'] == 'ft_phase_status') {
                $phase_status = $item->getMeta('ft_phase_status');
                foreach(PhaseState::STATE_GROUPS as $gkey => $group) {
                    if(!in_array($phase_status, $group)) {
                        foreach($group as $gstate) {
                            if(array_key_exists($gstate, $field['values'])) {
                                unset($field['values'][$gstate]);
                            }
                        }
                    } else {
                        // STATE_GROUPS have to be ordered correctly !
                        break;
                    }
                }
            }
        }*/
    }

    public function processModifyPhaseType($field, $item, $value) {
        $phase_status = $item->getMeta('ft_phase_status');
        // error_log($field['key']);
        if(isset($field['__first_phase_status']) && $phase_status < $field['__first_phase_status']) {
            // error_log("$phase_status < {$field['__first_phase_status']}");
            $field['readonly'] = true;
        } else if(isset($field['__last_phase_status']) && $phase_status > $field['__last_phase_status']) {
            // error_log("$phase_status < {$field['__last_phase_status']}");
            $field['readonly'] = true;
        } else {
            // error_log("$phase_status -> NONE");
        }
        return $field;
    }

    public function savePhaseStatus($item, $field, $value, $lang) {
        $phase = PoolRegistry::instance()->getPool('phase')->getInstance($item->id, $item);
        $phase->changeStatus($value);
    }

    public function subviewCreator() {
        return 'asdf';
        if (!Auth::allowed("manage.collection.sites")) {
            return;
        }
    }

}
