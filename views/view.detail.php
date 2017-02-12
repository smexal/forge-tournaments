<?php

namespace Forge\Modules\ForgeTournaments;

use \Forge\Core\Abstracts\View;
use \Forge\Core\App\App;
use \Forge\Core\Classes\Fields;
use \Forge\Core\Classes\Media;



class DetailView extends View {
    public $name = 'tournament-detail';
    public $allowNavigation = true;
    private $tournament = null;

    public function additionalNavigationForm() {
        $tournaments = App::instance()->cm->getCollection('forge-tournaments')->items();
        $values = [];
        foreach($tournaments as $tournament) {
            $values[$tournament->slug()] = $tournament->getMeta('title');
        }
        $formfields = Fields::select([
            'key' => 'add-to-url',
            'label' => i('Select the tournament, that you want to display.'),
            'values' => $values
        ]);
        return ["form" => $formfields];
    }

    public function content($parts = []) {
        $collection = App::instance()->cm->getCollection('forge-tournaments');
        $this->tournament = $collection->getBySlug($parts[0]);

        $thumb = new Media($this->tournament->getMeta('image_thumbnail'));
        $background = new Media($this->tournament->getMeta('image_background'));
        $big = new Media($this->tournament->getMeta('image_big'));

        return App::instance()->render(MOD_ROOT."forge-tournaments/templates/", "detail",
            ['enrollment_cta_label' => i('Enroll now', 'forge-tournaments'),
            'start_label' => i('Start', 'forge-tournaments'),
            'status_label' => i('Status', 'forge-tournaments'),
            'title' => $this->tournament->getMeta('title'),
            'thumbnail' => $thumb->getUrl(),
            'background' => $background->getUrl(),
            'start_date' => $this->tournament->getMeta('start_time'),
            'current_participants' => 0,
            'max_participants' => $this->tournament->getMeta('max_participants'),
            'big' => $big->getUrl(),
            'url_enrollment' => $this->tournament->url(),
            'enrollment_label' => i('Enrollments', 'forge-tournaments'),
            'short' => $this->tournament->getMeta('description'),
            'long' => $this->tournament->getMeta('additional_description'),
            'prices' => [],
            'additional' => [],
            ]
        );
    }
}
