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
            'ft_participant_output_pool' => new CollectionRelation(
                'ft_participant_output_pool', 
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
        $this->addFields([
            /*[
                'key' => 'ft_tournament',
                'label' => \i('Tournament', 'forge-tournaments'),
                'values' => '',

                'type' => 'collection',
                'maxtags'=> 1,
                'collection' => TournamentCollection::COLLECTION_NAME,
                'data_source' => 'relation',
                'relation' => [
                    'identifier' => 'ft_phases',
                    // Todo display reversed relation so the user can see to which parent the phase belongs
                    // 'dire'
                ],

                'value' => '',
                'multilang' => false,
                'order' => 10,
                'position' => 'right',
                'hint' => i('Select a tournament', 'forge-tournaments')
            ], */
            [
                'key' => 'ft_phase_status',
                'type' => 'select',
                'label' => \i('Lifecycle', 'forge-tournaments'),
                'values' => Utils::getPhaseStates(),
                'value' => PhaseState::FRESH,
                'multilang' => false,
                'order' => 10,
                'position' => 'right',
                'hint' => i('Select the phase status', 'forge-tournaments')
            ],
            [
                'key' => 'ft_phase_type',
                'type' => 'select',
                'label' => \i('Phase type', 'forge-tournaments'),
                'values' => Utils::getPhaseTypes(),
                'value' => PhaseType::REGISTRATION,
                'multilang' => false,
                'order' => 10,
                'position' => 'left',
                'hint' => i('Select the phase type', 'forge-tournaments'),
                '__last_phase_state' =>  PhaseState::FRESH
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
                'data_source' => 'relation',
                'relation' => [
                    'identifier' => 'ft_next_phase'
                ],

                'order' => 10,
                'position' => 'left',
                'readonly' => true,
                'hint' => i('The next phase after this one is completed', 'forge-tournaments')
            ],
            [
                'key' => 'ft_participant_pool_size',
                'label' => \i('Participant pool size', 'forge-tournaments'),
                'value' => 16,
                'multilang' => false,
                'type' => 'number',
                'order' => 10,
                'position' => 'right',
                'hint' => \i('Define how many participants are allowed. Use -1 for no restriction', 'forge-tournaments'),
                '__last_phase_state' => PhaseState::FRESH
            ],
            [
                'key' => 'participant_pool',
                'label' => \i('Participant Pool', 'forge-tournaments'),
                'value' => '',
                'multilang' => false,

                'type' => 'collection',
                /*'maxtags'=> 64,*/
                'collection' => ParticipantCollection::COLLECTION_NAME,
                'data_source' => 'relation',
                'relation' => [
                    'identifier' => 'ft_participant_output_pool'
                ],

                'order' => 20,
                'position' => 'left',
                'hint' => \i('You can only add participants when the phase did not already start', 'forge-tournaments'),
                '__last_phase_state' => PhaseState::OPEN
            ],
            [
                'key' => 'ft_scoring',
                'label' => \i('Scoring type', 'forge-tournaments'),
                'values' => Utils::getScoringOptions(),
                'value' => Utils::getDefaultScoringID(),
                'multilang' => false,
                'type' => 'select',
                'order' => 10,
                'position' => 'right',
                'hint' => '',
                '__last_phase_state' => PhaseState::OPEN
            ],
            [
                'key' => 'ft_num_winners',
                'label' => \i('How many participants are reaching the next phase?', 'forge-tournaments'),
                'value' => 8,
                'multilang' => false,
                'type' => 'number',
                'order' => 10,
                'position' => 'right',
                'hint' => \i('Ensure the following phase has at least as many total slots available', 'forge-tournaments'),
                '__last_phase_state' => PhaseState::FRESH
            ]
        ]);
        parent::custom_fields();
    }

    public function itemDependentFields($item) {
        $scoring = $item->getMeta('ft_scoring');
        $scoring = $scoring ? $scoring : Utils::getDefaultScoringID();
        $scoring = ScoringProvider::instance()->getScoring($scoring);
        $match_handling = [
            'versus' => [
                'bo3' => \i('Best of 3', 'forge-tournaments'), 
                'bo5' => \i('Best of 5', 'forge-tournaments'),
                'bo1' => \i('Best of 1', 'forge-tournaments')
            ],
            'performance' => [
                'single_result' => \i('Single result', 'forge-tournaments')
            ]
        ];
        $mh_options = $match_handling[$scoring['config']['match_handling']];
        $this->addUniqueFields([
            [
                'key' => 'match_handling',
                'label' => \i('How are the matches handled?', 'forge-tournaments'),
                'values' => $mh_options,
                'value' => count($mh_options) ? reset($mh_options) : null,
                'multilang' => false,
                'type' => 'select',
                'order' => 10,
                'position' => 'right',
                'grouped' => false,
                'hint' => \i('Define how the winners of an encounter are determined', 'forge-tournaments')
            ]
        ]);


        $phase = Utils::getSubtype('IPhaseType', $item, 'ft_phase_type');
        if(!is_null($phase)) {
            $new_fields = $phase->fields($item);
            error_log(print_r($new_fields, 1));
            $this->addUniqueFields($new_fields);
            $this->customFields = $phase->modifyFields($this->customFields);
        }


/*
[
                'key' => 'ft_phase_status',
                'type' => 'select',
                'label' => \i('Lifecycle', 'forge-tournaments'),
                'values' => Utils::getPhaseStates(),
                'value' => PhaseState::FRESH,
                'multilang' => false,
                'order' => 10,
                'position' => 'right',
                'hint' => i('Select the phase status', 'forge-tournaments')
            ],

*/
        foreach($this->customFields as &$field) {
            if(isset($field['__last_phase_state'])) {
                $field['process:modifyField'] = [$this, 'processModifyPhaseType'];
            }
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
        }
    }

    public function processModifyPhaseType($field, $item, $value) {
        $phase_status = $item->getMeta('ft_phase_status');
        if($phase_status > $field['__last_phase_state']) {
            $field['readonly'] = true;
        }
        return $field;
    }

    public function subviewCreator() {
        if (!Auth::allowed("manage.collection.sites")) {
            return;
        }
    }

}
