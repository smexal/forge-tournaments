<?php

namespace Forge\Modules\ForgeTournaments;

use \Forge\Core\Abstracts\DataCollection;
use \Forge\Core\Classes\User;



class OrganisationCollection extends DataCollection {
    public $permission = "manage.collection.sites";

    protected function setup() {
        $this->preferences['name'] = 'forge-tournaments-organisations'; //TODO: make this a class constant
        $this->preferences['title'] = i('Organisations', 'forge-tournaments');
        $this->preferences['all-title'] = i('Manage organisations', 'forge-tournaments');
        $this->preferences['add-label'] = i('Add organisation', 'forge-tournaments');
        $this->preferences['single-item'] = i('Organisation', 'forge-tournaments');

        $this->custom_fields();
    }

    private function custom_fields() {
        $userList = [];
        // foreach (User::getAll() as $user) {
        //     array_push($userList, ["value" => $user["id"],
        //                                     "active" => false,
        //                                     "text" => $user["username"]]);
        // }

        $this->addFields([
            [
                'key' => 'website',
                'label' => i('Website', 'forge-tournaments'),
                'value' => "",
                'multilang' => true,
                'type' => 'url',
                'order' => 60,
                'position' => 'right',
                'hint' => i('Link to the website', 'forge-tournaments')
            ],
            [
                'key' => 'image_logo',
                'label' => i('Logo', 'forge-tournaments'),
                'value' => "",
                'multilang' => true,
                'type' => 'image',
                'order' => 70,
                'position' => 'right',
                'hint' => i('Logo', 'forge-tournaments')
            ],
        ]);
    }
}

?>
