<?php
class ForgeTournamentCollection extends DataCollection {
  public $permission = "manage.collection.sites";

  private $item_id = null;
  private $user_list = [];
  private $event_list = [];

  protected function setup() {
    $this->preferences['name'] = 'forge-tournaments';
    $this->preferences['title'] = i('Tournaments', 'forge-tournaments');
    $this->preferences['all-title'] = i('Manage tournaments', 'forge-tournaments');
    $this->preferences['add-label'] = i('Add tournament', 'forge-tournaments');
    $this->preferences['single-item'] = i('Tournament', 'forge-tournaments');

    foreach (User::getAll() as $user) {
        array_push($this->user_list, ["value" => $user["id"], 
                                        "active" => false,
                                        "text" => $user["username"]]);
    }

    if (is_null(App::instance()->cm)) {
        App::instance()->cm = new CollectionManager();
    }

    $collection = App::instance()->cm->getCollection("forge-events");
    foreach ($collection->items() as $value) {
        $this->event_list[$value->id] = $value->getName();
    }

    $this->custom_fields();
  }

  public function render($item) {
  }

  public function customEditContent($id) {
    $this->item_id = $id;

    $return = '';

    return $return;
  }

  private function custom_fields() {
    $this->addFields([
        [
            'key' => 'event',
            'label' => i('Event', 'forge-tournaments'),
            'values' => $this->event_list,
            'multilang' => false,
            'type' => 'select',
            'order' => 10,
            'position' => 'right',
            'hint' => i('Select the corresponding event', 'forge-tournaments')
        ],
        [
            'key' => 'responsibles',
            'label' => i('Responsible persons', 'forge-tournaments'),
            'values' => $this->user_list,
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
            'type' => 'text',
            'order' => 30,
            'position' => 'right',
            'hint' => i('How many competitors can participate?', 'forge-tournaments')
        ],
        [
            'key' => 'team_competition',
            'label' => i('Team competition', 'forge-tournaments'),
            'value' => "on",
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
            'type' => 'text',
            'order' => 50,
            'position' => 'right',
        ],
        [
            'key' => 'team_substitutes',
            'label' => i('Team substitutes', 'forge-tournaments'),
            'value' => 2,
            'multilang' => false,
            'type' => 'text',
            'order' => 50,
            'position' => 'right',
            'hint' => i('Amount of substitutes', 'forge-tournaments')
        ],
        [
            'key' => 'game_rules',
            'label' => i('Game rules', 'forge-tournaments'),
            'value' => "",
            'multilang' => true,
            'type' => 'text',
            'order' => 60,
            'position' => 'right',
            'hint' => i('Link to the game rules', 'forge-tournaments')
        ],
        [
            'key' => 'additional_description',
            'label' => i('Additional description', 'forge-tournaments'),
            'value' => "",
            'multilang' => true,
            'type' => 'text',
            'order' => 60,
            'position' => 'left',
            'hint' => i('Describe the tournament a little more, please', 'forge-tournaments')
        ],
        [
            'key' => 'image_big',
            'label' => i('Big image', 'forge-tournaments'),
            'value' => "",
            'multilang' => true,
            'type' => 'image',
            'order' => 70,
            'position' => 'right',
            'hint' => i('Teaser image', 'forge-tournaments')
        ],
        [
            'key' => 'image_thumbnail',
            'label' => i('Thumbnail', 'forge-tournaments'),
            'value' => "",
            'multilang' => false,
            'type' => 'image',
            'order' => 80,
            'position' => 'right',
            'hint' => i('Preview image', 'forge-tournaments')
        ],
        [
            'key' => 'image_background',
            'label' => i('Background image', 'forge-tournaments'),
            'value' => "",
            'multilang' => false,
            'type' => 'image',
            'order' => 60,
            'position' => 'right',
        ],
        // Datum & Uhrzeit des Beginns
        [
            'key' => 'start_time',
            'label' => i('Start time', 'forge-tournaments'),
            'value' => "",
            'multilang' => false,
            'type' => 'text',
            'order' => 60,
            'position' => 'right',
        ],
    ]);
  }
}

?>
