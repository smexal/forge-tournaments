<?php

namespace Forge\Modules\ForgeTournaments;

use \Forge\Core\Abstracts\Component;
use \Forge\Core\App\App;
use \Forge\Core\Classes\Media;
use \Forge\Core\Classes\Utils;

use function \Forge\Core\Classes\i;

class ForgeTournamentList extends Component {
    public $settings = [];
    private $prefix = 'forge_tournament_list_';

    public function prefs() {
        $this->settings = [
            [
                "label" => i('Choose an event', 'forge-tournaments'),
                "hint" => i('All tournaments of this event will be displayed') ,
                "key" => $this->prefix."tournament_list",
                "type" => "select",
                "callable" => true,
                "values" => [$this, 'getEventListOptionValues']
            ],
            [
                "label" => i('Is teamsize important?', 'forge-tournaments'),
                "hint" => i('Show/hide the teamsize') ,
                "key" => $this->prefix."tournament_teamsize_visible",
                "type" => "checkbox",
                "value" => true
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
        $eventList = [];
        foreach ($items as $item) {
            $eventList[$item->id] = $item->getName();
        }

        return ['0' => i('Choose one', 'forge-tournaments')] + $eventList;
    }

    public function content() {
        $collection = App::instance()->cm->getCollection('forge-tournaments');
        $items = $collection->items([
            'order' => 'created',
            'order_direction' => 'desc',
            'limit' => 4, // make this variable
            'status' => 'published'
        ]);
        $tournamentItems = [];

        foreach ($items as $item) {
            $thumbnail = new Media($item->getMeta('image_thumbnail'));
            array_push($tournamentItems, [
                'title' => $item->getMeta('title'),
                'date' => $item->getMeta('start_time'),//Utils::dateFormat($item->getMeta('start_time')),
                'url' => $item->url(),
                'url_enrollment' => $item->url(),
                'current_participants' => 0,
                'max_participants' => $item->getMeta('max_participants'),
                'thumbnail' => $thumbnail->getUrl(),
                'team_size' => $item->getMeta('team_size')
            ]);
        }

        return App::instance()->render(
            DOC_ROOT."modules/forge-tournaments/templates/",
            "list",
            ['tournaments' => $tournamentItems,
            'enrollment_label' => i('Enrollments', 'forge-tournaments'),
            'start_label' => i('Start', 'forge-tournaments'),
            'status_label' => i('Status', 'forge-tournaments'),
            'enrollment_cta_label' => i('Enroll now', 'forge-tournaments')]
        );
    }

    public function customBuilderContent() {

        return App::instance()->render(CORE_TEMPLATE_DIR."components/builder/", "text", [
            'text' => i('Tournament List Form', 'forge-tournaments')
        ]);
    }
}
