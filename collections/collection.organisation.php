<?php

namespace Forge\Modules\ForgeTournaments;

use \Forge\Core\Abstracts\DataCollection;
use \Forge\Core\App\App;
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

    public function render($item) {
        $members = [];
        $_members = [];
        $_members = array_merge($members, is_array($item->getMeta('admins')) ? $item->getMeta('admins') : [$item->getMeta('admins')]);
        $_members = array_merge($_members, is_array($item->getMeta('members')) ? $item->getMeta('members') : [$item->getMeta('members')]);
        foreach ($_members as $member) {
            $user = new User($member);
            array_push($members, [
                'name' => $user->get('username')
            ]);
        }

        return App::instance()->render(MOD_ROOT.'forge-tournaments/templates/views',
            'organisation',
            [
                'title' => $item->getName(),
                'description' => $item->getMeta('description'),
                'url' => $item->getMeta('url'),
                'members' => $members
            ]
        );
    }

    private function custom_fields() {
        $userList = [];
        foreach (User::getAll() as $user) {
            array_push($userList, ["value" => $user["id"],
                                            "active" => false,
                                            "text" => $user["username"]]);
        }

        $this->addFields([
            [
                'key' => 'admins',
                'label' => i('Admins', 'forge-tournaments'),
                'values' => $userList,
                'multilang' => false,
                'type' => 'multiselect',
                'order' => 20,
                'position' => 'right',
                'hint' => i('Who\'s responsible?', 'forge-tournaments')
            ],
            [
                'key' => 'key',
                'label' => i('Key', 'forge-tournaments'),
                'value' => "",
                'multilang' => true,
                'type' => 'text',
                'order' => 50,
                'position' => 'right',
                'hint' => i('Short key', 'forge-tournaments')
            ],
            [
                'key' => 'url',
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
