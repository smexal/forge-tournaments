<?php

namespace Forge\Modules\ForgeTournaments;

use \Forge\Core\Abstracts\DataCollection;
use \Forge\Core\App\App;
use \Forge\Core\App\CollectionManager;
use \Forge\Core\Classes\Media;
use \Forge\Core\Classes\User;
use \Forge\Core\Classes\Utils;

class TournamentCollection extends DataCollection {
    public $permission = 'manage.collection.sites';

    protected function setup() {
        $this->preferences['name'] = 'forge-tournaments'; //TODO: make this a class constant
        $this->preferences['title'] = i('Tournaments', 'forge-tournaments');
        $this->preferences['all-title'] = i('Manage tournaments', 'forge-tournaments');
        $this->preferences['add-label'] = i('Add tournament', 'forge-tournaments');
        $this->preferences['single-item'] = i('Tournament', 'forge-tournaments');

        if (is_null(App::instance()->cm)) {
            App::instance()->cm = new CollectionManager();
        }

        $this->custom_fields();
    }

    public function customEditContent($id) {
        $collection = App::instance()->cm->getCollection('forge-tournaments');
        $tournament = $collection->getItem($id);

        $bracket = new Bracket($tournament);

        return App::instance()->render(
            MOD_ROOT.'forge-tournaments/templates/fields',
            'encounter',
            [
                'encounterRounds' => $bracket->getEncounters(),
            ]
        );
    }

    public function render($item) {

        $thumb = new Media($item->getMeta('image_thumbnail'));
        $background = new Media($item->getMeta('image_background'));
        $big = new Media($item->getMeta('image_big'));

        $db = App::instance()->db;
        $db->where('tournament_id', $item->id);
        $subscribedParticipants = count($db->get('forge_tournaments_tournament_participant'));

        return App::instance()->render(MOD_ROOT.'forge-tournaments/templates/views/',
            'tournament',
            [
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
                'url_enrollment' => Utils::getUrl(['enrollment', $item->slug()]),
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
                'value' => 16,
                'multilang' => false,
                'type' => 'number',
                'order' => 30,
                'position' => 'right',
                'hint' => i('How many competitors can participate?', 'forge-tournaments')
            ],
            [
                'key' => 'team_competition',
                'label' => i('Team competition', 'forge-tournaments'),
                'value' => 'on',
                'multilang' => false,
                'type' => 'checkbox',
                'order' => 40,
                'position' => 'right',
                'hint' => i('Sign-up only for teams?', 'forge-tournaments')
            ],
            [
                'key' => 'team_size',
                'label' => i('Team size', 'forge-tournaments'),
                'value' => 8,
                'multilang' => false,
                'type' => 'number',
                'order' => 50,
                'position' => 'right',
            ],
            [
                'key' => 'team_substitutes',
                'label' => i('Team substitutes', 'forge-tournaments'),
                'value' => 2,
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
            // [
            //     'key' => 'encounters',
            //     'label' => i('Manage bracket', 'forge-tournaments'),
            //     'value' => '',
            //     'multilang' => false,
            //     'type' => '\Forge\Modules\ForgeTournaments\EncounterField::field',
            //     'process:load' =>
            //     'order' => 20,
            //     'position' => 'left'
            // ]
        ]);
    }
}

?>
