<?php

namespace Forge\Modules\ForgeTournaments;

use \Forge\Core\Abstracts\Component;
use \Forge\Core\App\App;
use \Forge\Core\Classes\Media;



class TeaserComponent extends Component {
    public $settings = [];
    private $prefix = 'forge_tournament_teaser_';

    public function prefs() {
        $this->settings = [
            [
                "label" => i('Choose a tournament', 'forge-tournaments'),
                "hint" => i('') ,
                "key" => $this->prefix."tournament_teaser",
                "type" => "select",
                "callable" => true,
                "values" => [$this, 'getTournamentListOptionValues']
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
            'name' => i('Tournament Teaser'),
            'description' => i('Add a teaser for a tournament', 'forge-tournaments'),
            'id' => 'forge_tournament_teaser',
            'image' => '',
            'level' => 'inner',
            'container' => false
        ];
    }

    public function getTournamentListOptionValues() {
        $collection = App::instance()->cm->getCollection('forge-tournaments');
        $items = $collection->items([
            'order' => 'created',
            'order_direction' => 'desc',
            'status' => 'published'
        ]);
        $tournamentList = [];
        foreach ($items as $item) {
            $tournamentList[$item->id] = $item->getName();
        }

        return ['0' => i('Choose one', 'forge-tournaments')] + $tournamentList;
    }

    public function content() {
        $collection = App::instance()->cm->getCollection('forge-tournaments');
        $item = $collection->getItem($this->getField($this->prefix."tournament_teaser"));
        $big = new Media($item->getMeta('image_big'));

        $tournament = ['team_size' => $item->getMeta('team_size'),
                        'max_participants' => $item->getMeta('max_participants'),
                        'url_enrollment' => $item->url(),
                        'big' => $big->getUrl()];

        return App::instance()->render(
            DOC_ROOT."modules/forge-tournaments/templates/",
            "teaser",
            ['tournament' => $tournament,
                'enrollmentText' => i('Enroll now', 'forge-tournaments')]
            );
    }

    public function customBuilderContent() {

        return App::instance()->render(CORE_TEMPLATE_DIR."components/builder/", "text", [
            'text' => i('Tournament List Form', 'forge-tournaments')
        ]);
    }
}
