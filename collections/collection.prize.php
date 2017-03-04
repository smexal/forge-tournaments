<?php

namespace Forge\Modules\ForgeTournaments;

use \Forge\Core\Abstracts\DataCollection;
use \Forge\Core\App\App;
use \Forge\Core\App\CollectionManager;

class PrizeCollection extends DataCollection {
    public $permission = "manage.collection.sites";

    protected function setup() {
        $this->preferences['name'] = 'forge-tournaments-prize';
        $this->preferences['title'] = i('Prize', 'forge-tournaments');
        $this->preferences['all-title'] = i('Manage prizes', 'forge-tournaments');
        $this->preferences['add-label'] = i('Add prizes', 'forge-tournaments');
        $this->preferences['single-item'] = i('Prize', 'forge-tournaments');

        if (is_null(App::instance()->cm)) {
            App::instance()->cm = new CollectionManager();
        }

        $this->custom_fields();
    }

    private function custom_fields() {
        $eventList = [];
        $collection = App::instance()->cm->getCollection("forge-events");
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
                'key' => 'sponsor',
                'label' => i('Sponsor', 'forge-tournaments'),
                'value' => "",
                'multilang' => true,
                'type' => 'text',
                'order' => 100,
                'position' => 'right',
                'hint' => i('Who sponsored this prize?', 'forge-tournaments')
            ],
            [
                'key' => 'image',
                'label' => i('Image', 'forge-tournaments'),
                'value' => "",
                'multilang' => true,
                'type' => 'image',
                'order' => 100,
                'position' => 'right',
                'hint' => i('Add a picture of the prize', 'forge-tournaments')
            ],
            [
                'key' => 'amount',
                'label' => i('Amount', 'forge-tournaments'),
                'value' => "",
                'multilang' => false,
                'type' => 'number',
                'order' => 60,
                'position' => 'right',
                'hint' => i('How many of these prizes are available?', 'forge-tournaments')
            ],
            [
                'key' => 'value',
                'label' => i('Value', 'forge-tournaments'),
                'value' => "",
                'multilang' => false,
                'type' => 'number',
                'order' => 110,
                'position' => 'right',
                'hint' => i('How much is the fish?', 'forge-tournaments')
            ],
            [
                'key' => 'website',
                'label' => i('Website', 'forge-tournaments'),
                'value' => "",
                'multilang' => true,
                'type' => 'url',
                'order' => 120,
                'position' => 'right',
                'hint' => i('Link to the website', 'forge-tournaments')
            ],
        ]);
    }
}

?>
