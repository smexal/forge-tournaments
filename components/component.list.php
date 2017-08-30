<?php

namespace Forge\Modules\ForgeTournaments;

use \Forge\Core\Abstracts\Component;
use \Forge\Core\App\App;
use \Forge\Core\Classes\Media;
use \Forge\Core\Classes\Utils;

class ListComponent extends Component {
    public $settings = [];
    private $prefix = 'forge_tournament_list_';

    public function prefs() {
        $this->settings = [
            [
                'label' => i('Choose an event', 'forge-tournaments'),
                'hint' => i('All tournaments of this event will be displayed') ,
                'key' => $this->prefix.'tournament_list',
                'type' => 'select',
                'callable' => true,
                'values' => [$this, 'getEventListOptionValues']
            ]
        ];
        return [
            'name' => i('Tournament List'),
            'description' => i('Add a list of tournaments', 'forge-tournaments'),
            'id' => 'forge_tournament_list',
            'image' => '',
            'level' => 'inner',
            'container' => false
        ];
    }

    public function getEventListOptionValues() {
        $collection = App::instance()->cm->getCollection('forge-events');
        $items = $collection->items([
            'order' => 'created',
            'order_direction' => 'desc',
            'status' => 'published'
        ]);
        $list = [];
        foreach ($items as $item) {
            $list[$item->id] = $item->getName();
        }

        return ['0' => i('Choose one', 'forge-tournaments')] + $list;
    }

    public function content() {
        $db = App::instance()->db;
        $collection = App::instance()->cm->getCollection('forge-tournaments');
        $items = $collection->items([
            'order' => 'created',
            'order_direction' => 'desc',
            'limit' => 0, // make this variable
            'status' => 'published'
        ]);
        $tournamentItems = [];

        foreach ($items as $item) {
            $thumbnail = new Media($item->getMeta('image_thumbnail'));


            $db->where('tournament_id', $item->id);
            $participants = count($db->get('forge_tournaments_tournament_participant'));

            $enrollmentActive = false;
            $actionURL = $item->url(); // Link to tournament detail
            $actionLabel = i('Details', 'forge-tournaments');

            // Turnier hat noch nicht begonnen oder Anmeldung noch nicht veröffentlicht
            if (new \DateTime($item->getMeta('start_time')) > new \DateTime()) {

                if($item->getMeta('allow_signup')) {
                    $actionURL = Utils::getUrl(['enrollment', $item->slug()]);
                    $actionLabel = i('Enroll', 'forge-tournaments');
                    $enrollmentActive = true;
                }
                $tournamentStatus = i('Upcoming', 'forge-tournaments');
                
            // Turniert läuft zur Zeit
            // TODO: Finde heraus wann ein Turnier noch nicht abgeschlossen ist
            } else if (true) {
                $tournamentStatus = i('Running', 'forge-tournaments');
            // Turnier ist beendet
            } else {
                $tournamentStatus = i('Done', 'forge-tournaments');
            }

            array_push($tournamentItems, [
                'title' => $item->getMeta('title'),
                'date' => $item->getMeta('start_time'),//Utils::dateFormat($item->getMeta('start_time')),
                'url' => $item->url(),
                'action_url' => $actionURL,
                'action_label' => $actionLabel,
                'status' => $tournamentStatus,
                'current_participants' => $participants,
                'max_participants' => $item->getMeta('max_participants'),
                'thumbnail' => $thumbnail->getUrl(),
                'team_size' => $item->getMeta('team_size')
            ]);
        }

        return App::instance()->render(
            DOC_ROOT.'modules/forge-tournaments/templates/components',
            'list',
            [
                'tournaments' => $tournamentItems,
                'enrollment_label' => i('Enrollments', 'forge-tournaments'),
                'start_label' => i('Start', 'forge-tournaments'),
                'status_label' => i('Status', 'forge-tournaments'),
                'enrollment_cta_label' => i('Enroll now', 'forge-tournaments')
            ]
        );
    }

    public function customBuilderContent() {

        return App::instance()->render(CORE_TEMPLATE_DIR.'components/builder/',
            'text',
            [
                'text' => i('Tournament List Form', 'forge-tournaments')
            ]
        );
    }
}
