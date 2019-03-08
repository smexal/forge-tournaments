<?php

namespace Forge\Modules\ForgeTournaments;

use Forge\Core\Abstracts\DataCollection;
use Forge\Core\App\App;
use Forge\Core\App\Auth;
use Forge\Core\Classes\User;
use Forge\Core\Classes\Utils as CoreUtils;
use Forge\Core\Classes\CollectionItem;
use Forge\Core\Classes\FieldUtils as FieldUtils;
use Forge\Core\Classes\Relations\Enums\Directions as RelationDirection;
use Forge\Core\Classes\Relations\CollectionRelation as CollectionRelation;
use Forge\Modules\ForgeTournaments\Facade\Tournament as TournamentFacade;

use Forge\Modules\ForgeTournaments\Scoring\ScoringProvider;
use Forge\Modules\ForgeTournaments\Fields\FieldProvider;
use Forge\Modules\ForgeTournaments\CollectionSubtypes\Phases\PhaseRegistry;

class PhaseCollection extends NodaDataCollection {
    const COLLECTION_NAME = 'forge-tournaments-phase';
    protected static $PARENT_COLLECTION = TournamentCollection::COLLECTION_NAME;
    
    public $permission = "manage.collection.forge-tournaments-phase";


    protected function setup() {
        $this->preferences['name'] = PhaseCollection::COLLECTION_NAME;
        $this->preferences['title'] = i('Phases', 'forge-tournaments');
        $this->preferences['all-title'] = i('Manage phase', 'forge-tournaments');
        $this->preferences['add-label'] = i('Add phase', 'forge-tournaments');
        $this->preferences['single-item'] = i('Phase', 'forge-tournaments');

        Auth::registerPermissions('api.collection.forge-tournaments-phase.read');
        parent::setup();

    }

    public function customEditContent($item_id) {
        if(array_key_exists('set_participants', $_GET)) {
            if($_GET['set_participants'] == 'fromTournament') {
                $this->setParticipantsFromTournament($item_id);
                App::instance()->redirect(CoreUtils::getUriComponents());
            }
            if($_GET['set_participants'] == 'fromOtherPhase' && is_numeric($_GET['phase'])) {
                $this->setParticipantsFromOtherPhase($item_id, $_GET['phase']);
                App::instance()->redirect(CoreUtils::getUriComponents());
            }
        }

        $html = '';
        $item = new CollectionItem($item_id);
        $phase = Utils::getSubtype('IPhaseType', $item, 'ft_phase_type');
        if($phase) {
            $html .= $phase->render($item);
        }

        if(array_key_exists('calculate_output', $_GET) && $_GET['calculate_output']) {
            $hiraPhase = new Phase($item);
            $phase->setPhase($hiraPhase);
            $phase->populateOutput();
            App::instance()->redirect(CoreUtils::getUriComponents());
        }

        return $html;
    }

    private function setParticipantsFromTournament($item) {
        $itemObj = new CollectionItem($item);
        $tournament = $itemObj->getParent();
        $participants = TournamentCollection::getParticipants($tournament->getID());
        $relation = App::instance()->rd->getRelation('ft_participant_list');
        $relation->setRightItems($item, $participants);
    }

    private function setParticipantsFromOtherPhase($item_id, $otherPhase) {
        $otherPhaseItem = new CollectionItem($otherPhase);
        $participants = $otherPhaseItem->getMeta('ft_participant_output_list', 0);
        $participants = explode(",", $participants);

        $relation = App::instance()->rd->getRelation('ft_participant_list');
        $relation->setRightItems($item_id, $participants);
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

    public function subviewImportParticipants() {
        $return = '';
        $options = [];
        $components = CoreUtils::getUriComponents();
        array_pop($components);
        $url = CoreUtils::getUrl($components, true, ['set_participants' => 'fromTournament']);

        $item = new CollectionItem($components[count($components)-1]);
        $parentItem = $item->getParent();
        $tournament = TournamentFacade::getTournament($parentItem->getID());
        $phases = $tournament->getPhases();

        $options = App::instance()->render(CORE_TEMPLATE_DIR.'assets/', 'list-item', [
            'link' => [ 'url' => $url ],
            'value' => i('Import participants from Tournament list', 'forge-tournaments')
        ]);

        foreach($phases as $phase) {
            // hide own phase...
            if($phase->getID() == $item->getID()) {
                continue;
            }
            $options.= App::instance()->render(CORE_TEMPLATE_DIR.'assets/', 'list-item', [
                'link' => [ 'url' => CoreUtils::getUrl($components, true, ['set_participants' => 'fromOtherPhase', 'phase' => $phase->getID()]) ],
                'value' => sprintf(i('Import Winners from "%1$s"', 'forge-tournaments'), $phase->getMeta('title'))
            ]);
        }


        return App::instance()->render(CORE_TEMPLATE_DIR.'assets/',
            'list',
            [
                'items' => $options
            ]
        );
    }

    public function custom_fields() {
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
            /*[
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
            ],*/
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
            /*[
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

                'order' => 50,
                'position' => 'left',
                'readonly' => true,
                'hint' => i('The next phase after this one is completed', 'forge-tournaments')
            ],*/
            [
                'key' => 'import-participant-link',
                'href' => CoreUtils::getUrl(array_merge(CoreUtils::getUriComponents(), ['importParticipants'])),
                'type' => 'link',
                'name' => i('Import participants', 'forge-tournaments'),
                'classes' => 'btn btn-primary ajax confirm',
                'order' => 21,
                'position' => 'left',
                '__last_phase_state' => PhaseState::READY
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
                'order' => 30,
                'position' => 'left',
                'hint' => \i('You can only add participants when the phase did not already start', 'forge-tournaments'),
                '__last_phase_state' => PhaseState::ASSIGNMENT
            ],
            [
                'key' => 'calculate-output-list',
                'href' => CoreUtils::getUrl(CoreUtils::getUriComponents(), true, ['calculate_output' => 'true']),
                'type' => 'link',
                'name' => i('Calculate Output List', 'forge-tournaments'),
                'classes' => 'btn btn-primary',
                'order' => 31,
                'position' => 'left',
                '__first_phase_state' =>  PhaseState::FINISHED
            ]
        ];


        $fields = $this->setPhaseStateHandlers($fields);
        $this->addFields($fields);

        $fields = parent::inheritedFields();

        foreach($fields as $idx => &$field) {
            if($field['key'] == 'ft_slot_assignment') {
                $field['pool_source_selector'] = 'input[name="ft_participant_list"]';
                $field['data_source_save'] = [$this, 'saveSlotAssignment'];
                // The slot_assignement is not any longer rendered by the field, instead this is done via the subtype-render
                unset($field['readonly']);
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
