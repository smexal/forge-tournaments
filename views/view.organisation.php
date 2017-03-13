<?php

namespace Forge\Modules\ForgeTournaments;

use \Forge\Core\Abstracts\View;
use \Forge\Core\App\App;
use \Forge\Core\Classes\Fields;
use \Forge\Core\Classes\Media;

class OrganisationView extends View {
    public $name = 'organisation';
    public $allowNavigation = true;

    public function content($parts = []) {
        $collection = App::instance()->cm->getCollection('forge-tournaments-organisations');

        $organisations = $this->getOrganisationsForUser($collection);
        foreach ($organisations as $org) {

        }

        return App::instance()->render(MOD_ROOT."forge-tournaments/templates/",
            "organisation",
            [
                'title' => i('Your organisations', 'forge-tournaments'),
                'organisations' => $organisations
            ]
        );
    }
}
