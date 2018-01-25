<?php

namespace Forge\Modules\ForgeTournaments;

use Forge\Core\Abstracts\DataCollection;
use Forge\Core\App\App;
use Forge\Core\App\Auth;
use Forge\Core\App\CollectionManager;
use Forge\Core\Classes\Builder;
use Forge\Core\Classes\CollectionItem as CollectionItem;
use Forge\Core\Classes\Localization;
use Forge\Core\Classes\Media;
use Forge\Core\Classes\Relations\Enums\Prepares;
use Forge\Core\Classes\User;
use Forge\Core\Classes\Utils as CoreUtils;
use Forge\Modules\ForgeTournaments\Facade\Tournament as TournamentFacade;
use Forge\Modules\ForgeTournaments\Fields\FieldProvider;
use Forge\Modules\ForgeTournaments\Fields\FieldRenderer;
use Forge\Modules\ForgeTournaments\Fields\PhaseList;
use Forge\Modules\TournamentsTeams\TeamsCollection;

class TournamentCollection extends NodaDataCollection {
    const COLLECTION_NAME = 'forge-tournaments';

    private $item = null;
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
        $builder = new Builder('collection', $id, 'defaultTournamentBuilder');
        return $builder->render();
    }

    public function render($item) {
        $this->item = $item;

        $parts = CoreUtils::getUriComponents();
        if(count($parts) > 3 && $parts[3] == 'signup') {
            $signup = new Signup($item);
            return $signup->render();
        }


        $headerImage = new Media($item->getMeta('image_background'));

        $subnavigation = $this->renderSubnavigation();

        $teamSizeText = $item->getMeta('team_size').i(' vs ', 'forge-tournaments').$item->getMeta('team_size');

        $tournament = TournamentFacade::getTournament($item->getID());


        $priceAmount = $item->getMeta('tournament_prices');
        $prices = [];
        if($priceAmount > 0) {
            for($index = 0; $index < $priceAmount; $index++) {
                $prices[] = [
                    'icon' => $index == 0 ? 'ion-trophy' : 'ion-ios-star',
                    'add_class' => $index == 0 ? 'highlight' : '',
                    'title' => $item->getMeta('tournament_prices_'.$index.'_title'),
                    'description' => $item->getMeta('tournament_prices_'.$index.'_description'),
                ];
            }
        }

        $builder = new Builder('collection', $item->id, 'defaultTournamentBuilder');
        $elements = $builder->getBuilderElements(Localization::getCurrentLanguage());

        $builderContent = '';
        foreach($elements as $element) {
            $builderContent.=$element->content();
        }

        $phases = $tournament->getPhases();
        $phaseOverview = [];
        $phaseStates = Utils::getPhaseStates();
        foreach($phases as $phase) {
            $icon = 'ion-arrow-graph-down-right';
            if($phase->getMeta('ft_scoring') == 'team_standard') {
                $icon = 'ion-ios-people-outline';
            }
            if(is_null($phase->getMeta('ft_phase_state'))) {
                continue;
            }
            $phaseOverview[] = [
                'title' => $phase->getMeta('title'),
                'description' => $phase->getMeta('description'),
                'state' => $phaseStates[$phase->getMeta('ft_phase_state')],
                'icon' => $icon
            ];
        }


        return $subnavigation.App::instance()->render(MOD_ROOT.'forge-tournaments/templates/views/',
            'tournament',
            [
                'title' => $item->getMeta('title'),
                'header_image' => $headerImage->getSizedImage(2100, 600),
                'intro' => $item->getMeta('additional_description'),
                'teamsize_label' => i('Teamsize', 'forge-tournaments'),
                'teamsize_value' => $teamSizeText,
                'starttime_label' => i('Start', 'forge-tournaments'),
                'starttime_value' => $item->getMeta('start_time'),
                'participants_label' => i('Participants', 'forge-tournaments'),
                'participants_value' => $item->getMeta('ft_participant_list'),
                'participants_max' => $item->getMeta('max_participants'),
                'checkin_label' => i('Checkin Time', 'forge-tournaments'),
                'checkin_value' => $item->getMeta('checkin_time') ? $item->getMeta('checkin_time') : i('Undefined', 'forge-tournaments'),
                'prices_title' => i('Prices', 'forge-tournaments'),
                'structure_title' => i('Structure', 'forge-tournaments'),
                'responsible_label' => i('Responsible', 'forge-tournaments'),
                'responsible_value' => 'Vaargo',
                'prices' => $prices,
                'phases' => $phaseOverview,
                'builder_content' => $builderContent,
                'signup' => $item->getMeta('allow_signup'),
                'signup_text' => i('Signup now', 'forge-tournaments'),
                'signup_url' => $item->url(false, ['signup'])
            ]
        );
    }

    private function renderSubnavigation($view = 'default') {

        $items = [
            [
                'url' => $this->item->url(),
                'title' => i('General', 'forge-tournaments'),
                'active' => $view == 'default' ? 'active' : ''
            ]
        ];

        if($this->item->getMeta('allow_signup')) {
            $items = array_merge($items, [
               [
                    'url' => $this->item->url().'/participants',
                    'title' => i('Participants', 'forge-tournaments'),
                    'active' => $view == 'participants' ? 'active' : ''
               ]
            ]);
        }

        return App::instance()->render(MOD_ROOT.'forge-tournaments/templates/parts', 'tournament-detail-navigation', [
            'items' => $items
        ]);

    }

    public function participants($item) {
        $this->item = $item;

        $participants = '';
        foreach(self::getParticipants($this->item->id) as $participantID) {
            $participant = new CollectionItem($participantID);
            if($this->item->getMeta('team_size') == 1) {
                $user = new User($participant->getMeta('user'));
                $args = [
                    'username' => $participant->getName(),
                    'avatar' => $user->getAvatar() !== null ? $user->getAvatar() : false
                ];
            } else {
                $team = ParticipantCollection::getTeam($participant);
                $orga = new CollectionItem(TeamsCollection::getOrganization($team));
                $img = new Media($orga->getMeta('logo'));
                $args = [
                    'username' => $participant->getName(),
                    'avatar' => $img ? $img->getUrl() : false
                ];
            }
            $participants.= App::instance()->render(
                MOD_ROOT.'forge-tournaments-teams/templates/parts',
                'memberbox',
                $args
            );
        }

        return $this->renderSubnavigation('participants').App::instance()->render(
            MOD_ROOT.'forge-tournaments/templates/parts', 'participant-list',
            [
                'title' => i('Participants'),
                'participants' => $participants
            ]
        );

        return $return;
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
                'key' => 'checkin_time',
                'label' => i('Checkin time', 'forge-tournaments'),
                'value' => '',
                'multilang' => false,
                'type' => 'datetime',
                'order' => 121,
                'position' => 'right',
            ],
            [
                'key' => 'additional_description',
                'label' => i('Additional description', 'forge-tournaments'),
                'value' => '',
                'multilang' => true,
                'type' => 'wysiwyg',
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
            ],
            [
                'key' => 'tournament_prices',
                'label' => i('Tournament Prices', 'forge-tournaments'),
                'repeater_title' => i('Tournament Price', 'forge-tournaments'),
                'multilang' => true,
                'type' => 'repeater',
                'order' => 30,
                'position' => 'left',
                'subfields' => [
                    [
                        'key' => 'title',
                        'label' => i('Titel', 'forge-tournaments'),
                        'type' => 'text',
                        'multilang' => true
                    ],
                    [
                        'key' => 'description',
                        'label' => i('Description', 'forge-tournaments'),
                        'type' => 'text',
                        'multilang' => true
                    ]
                ]
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

    public static function addParticipant($tournament, $participantID) {
        if (!in_array($participantID, self::getParticipants($tournament))) {
            $relation = App::instance()->rd->getRelation('ft_participant_list');
            $relation->add($tournament, $participantID);
            return true;
        }
        return false;
    }

    public static function getParticipants($tournament) {
        $relation = App::instance()->rd->getRelation('ft_participant_list');
        return $relation->getOfLeft($tournament, Prepares::AS_IDS_RIGHT);
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
