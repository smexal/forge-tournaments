<?php

namespace Forge\Modules\ForgeTournaments;

use Forge\Core\Abstracts\DataCollection;
use Forge\Core\App\App;
use Forge\Core\App\Auth;
use Forge\Core\App\CollectionManager;
use Forge\Core\Classes\Media;
use Forge\Core\Classes\User;

use Forge\Core\Classes\CollectionItem as CollectionItem;
use Forge\Core\Classes\Utils as CoreUtils;

use Forge\Modules\ForgeTournaments\Fields\FieldRenderer;
use Forge\Modules\ForgeTournaments\Fields\FieldProvider;

class TournamentCollection extends NodaDataCollection {
    const COLLECTION_NAME = 'forge-tournaments';

    public $permission = 'manage.collection.sites';

    protected function setup() {
        $this->preferences['name'] = TournamentCollection::COLLECTION_NAME; //TODO: make this a class constant
        $this->preferences['title'] = i('Tournaments', 'forge-tournaments');
        $this->preferences['all-title'] = i('Manage tournaments', 'forge-tournaments');
        $this->preferences['add-label'] = i('Add tournament', 'forge-tournaments');
        $this->preferences['single-item'] = i('Tournament', 'forge-tournaments');

        if (is_null(App::instance()->cm)) {
            App::instance()->cm = new CollectionManager();
        }

        $this->custom_fields();
        parent::setup();
    }

    public function customEditContent($id) {
        $collection = App::instance()->cm->getCollection('forge-tournaments');
        $tournament = $collection->getItem($id);

        $bracket = new Bracket($tournament);

        $html = App::instance()->render(
            MOD_ROOT.'forge-tournaments/templates/fields',
            'encounter',
            [
                'encounterRounds' => $bracket->getEncounters(),
            ]
        );
        return $html;
    }

    public function render($item) {

        $thumb = new Media($item->getMeta('image_thumbnail'));
        $background = new Media($item->getMeta('image_background'));
        $big = new Media($item->getMeta('image_big'));

        $db = App::instance()->db;
        $db->where('tournament_id', $item->id);
        $subscribedParticipants = count($db->get('forge_tournaments_tournament_participant'));

        $enrollmentActive = false;
        if($item->getMeta('allow_signup') === 'on') {
            $enrollmentActive = true;
        }

        return App::instance()->render(MOD_ROOT.'forge-tournaments/templates/views/',
            'tournament',
            [
                'enrollment_active' => $enrollmentActive,
                'enrollment_cta_label' => i('Enroll now', 'forge-tournaments'),
                'start_label' => i('Start', 'forge-tournaments'),
                'status_label' => i('Status', 'forge-tournaments'),
                'title' => $item->getMeta('title'),
                'thumbnail' => $thumb->getUrl(),
                'background' => $background->getUrl(),
                'start_date' => $item->getMeta('start_time'),
                'current_participants' => $subscribedParticipants,
                'max_participants' => $item->getMeta('max_participants'),
                'big' => $big->getUrl(),
                'url_enrollment' => CoreUtils::getUrl(['enrollment', $item->slug()]),
                'enrollment_label' => i('Enrollments', 'forge-tournaments'),
                'short' => $item->getMeta('description'),
                'long' => $item->getMeta('additional_description'),
                'prices' => [],
                'additional' => [],
            ]
        );
    }

    private function custom_fields() {
        $userList = [];
        foreach (User::getAll() as $user) {
            array_push($userList, ['value' => $user['id'],
                                    'active' => false,
                                    'text' => $user['username']]);
        }

        $eventList = [];
        $collection = App::instance()->cm->getCollection('forge-events');
        foreach ($collection->items() as $value) {
            $eventList[$value->id] = $value->getName();
        }

        $this->addFields([
            [
                'key' => 'event',
                'label' => i('Event', 'forge-tournaments'),
                'values' => $eventList,
                'multilang' => false,
                'type' => 'select',
                'order' => 10,
                'position' => 'right',
                'hint' => i('Select the corresponding event', 'forge-tournaments')
            ],
            [
                'key' => 'responsibles',
                'label' => i('Responsible persons', 'forge-tournaments'),
                'values' => $userList,
                'multilang' => false,
                'type' => 'multiselect',
                'order' => 20,
                'position' => 'right',
                'hint' => i('Who\'s responsible?', 'forge-tournaments')
            ],
            [
                'key' => 'max_participants',
                'label' => i('Max. participants', 'forge-tournaments'),
                'multilang' => false,
                'type' => 'number',
                'order' => 30,
                'position' => 'right',
                'hint' => i('How many competitors can participate?', 'forge-tournaments')
            ],
            [
                'key' => 'team_competition',
                'label' => i('Team competition', 'forge-tournaments'),
                'multilang' => false,
                'type' => 'checkbox',
                'order' => 40,
                'position' => 'right',
                'hint' => i('Sign-up only for teams?', 'forge-tournaments')
            ],
            [
                'key' => 'allow_signup',
                'label' => i('Allow Tournament signup', 'forge-tournaments'),
                'multilang' => false,
                'type' => 'checkbox',
                'order' => 40,
                'position' => 'right',
                'hint' => ''
            ],
            [
                'key' => 'team_size',
                'label' => i('Team size', 'forge-tournaments'),
                'multilang' => false,
                'type' => 'number',
                'order' => 50,
                'position' => 'right',
            ],
            [
                'key' => 'team_substitutes',
                'label' => i('Team substitutes', 'forge-tournaments'),
                'multilang' => false,
                'type' => 'number',
                'order' => 60,
                'position' => 'right',
                'hint' => i('Amount of substitutes', 'forge-tournaments')
            ],
            [
                'key' => 'game_rules',
                'label' => i('Game rules', 'forge-tournaments'),
                'value' => '',
                'multilang' => true,
                'type' => 'url',
                'order' => 70,
                'position' => 'right',
                'hint' => i('Link to the game rules', 'forge-tournaments')
            ],
            [
                'key' => 'image_big',
                'label' => i('Big image', 'forge-tournaments'),
                'value' => '',
                'multilang' => true,
                'type' => 'image',
                'order' => 90,
                'position' => 'right',
                'hint' => i('Teaser image', 'forge-tournaments')
            ],
            [
                'key' => 'image_thumbnail',
                'label' => i('Thumbnail', 'forge-tournaments'),
                'value' => '',
                'multilang' => false,
                'type' => 'image',
                'order' => 100,
                'position' => 'right',
                'hint' => i('Preview image', 'forge-tournaments')
            ],
            [
                'key' => 'image_background',
                'label' => i('Background image', 'forge-tournaments'),
                'value' => '',
                'multilang' => false,
                'type' => 'image',
                'order' => 110,
                'position' => 'right',
            ],
            [
                'key' => 'start_time',
                'label' => i('Start time', 'forge-tournaments'),
                'value' => '',
                'multilang' => false,
                'type' => 'datetime',
                'order' => 120,
                'position' => 'right',
            ],
            [
                'key' => 'additional_description',
                'label' => i('Additional description', 'forge-tournaments'),
                'value' => '',
                'multilang' => true,
                'type' => 'text',
                'order' => 10,
                'position' => 'left',
                'hint' => i('Describe the tournament a little more, please', 'forge-tournaments')
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
                'hint' => \i('You can only add participants when the phase did not already start', 'forge-tournaments')
            ],
            [
                'key' => 'ft_phase_list', 
                'label' => \i('Phase List', 'forge-quests'),
                'type' => ['\\Forge\\Modules\\ForgeTournaments\\Fields\\PhaseList', 'render'],
                'value' => '',
                'order' => 20,
                'position' => 'left',
                'data_source_save' => ['\\Forge\\Modules\\ForgeTournaments\\Fields\\PhaseList', 'save'],
                'data_source_load' => ['\\Forge\\Modules\\ForgeTournaments\\Fields\\PhaseList', 'load'],
            ]
        ]);

        $fields = parent::inheritedFields();

        foreach($fields as $idx => &$field) {
            if($field['key'] == 'ft_slot_assignment') {
               unset($fields[$idx]);
            }
        }
    }

    public function itemDependentFields($item) {
        parent::itemDependentFields($item);
        foreach($this->customFields as $key => $field) {
            if(in_array($field['key'], ['node_fields', 'node_data_gathered', 'parent_node'])) {
                unset($this->customFields[$key]);
                unset($this->customFields[$key]);
            }
        }
    }


    public function subviewAddPhase($item_id) {
        if (!Auth::allowed("manage.collection.sites")) {
            return;
        }

        if (isset($_REQUEST['ft_submitted']) && $_REQUEST['ft_submitted'] == '1') {
             $collection_id = Utils::makeCollectionItem(
                PhaseCollection::COLLECTION_NAME, 
                App::instance()->db->escape($_REQUEST['ft_phase_title']),
                $item_id,
                [
                    'ft_phase_type' => $_REQUEST['ft_phase_type'],
                    'ft_group_size' => 4,
                    'ft_participant_list_size' => 16
                ]
            );
            App::instance()->redirect(CoreUtils::getUrl(array('manage', 'collections', 'forge-tournaments', 'edit', $item_id)));
            return;
        }

        $item = PoolRegistry::instance()->getPool('tournament')->getInstance($item_id);
        $args = [
            'item_id' => $item,
            'title' => sprintf(\i('New phase for tournament %s', 'forge-tournaments'), $item->getName()),
            'add_phase_text' => \i('Add phase', 'forge-tournaments'),
            'action' => CoreUtils::getUrl(array('manage', 'collections', 'forge-tournaments', 'edit', $item_id, 'addPhase')),
            'fields' => FieldRenderer::renderFields([
                [
                    'type' => 'text',
                    'key' => 'ft_phase_title',
                    'label' => \i('Phase title', 'forge-tournaments')
                ],
                [
                    'type' => 'hidden',
                    'key' => 'ft_submitted',
                    'value'=> '1'
                ],
                FieldProvider::phaseTypeSelect()
            ]),
        ];

        return App::instance()->render(
            MOD_ROOT.'forge-tournaments/templates/views/',
            'tournament_add_phase',
            $args
        );
    }

}