<?php

namespace Forge\Modules\ForgeTournaments;

use \Forge\Core\Abstracts\DataCollection;
use \Forge\Core\App\App;
use \Forge\Core\Classes\User;
use \Forge\Core\Classes\FieldUtils as FieldUtils;
use \Forge\Core\Classes\Relations\Enums\Directions as RelationDirection;
use \Forge\Core\Classes\Relations\CollectionRelation as CollectionRelation;


class MatchCollection extends DataCollection {
    const COLLECTION_NAME = 'forge-tournaments-match';
    public $permission = "manage.collection.sites";


    protected function setup() {
        $this->preferences['name'] = MatchCollection::COLLECTION_NAME;
        $this->preferences['title'] = i('Matches', 'forge-tournaments');
        $this->preferences['all-title'] = i('Manage match', 'forge-tournaments');
        $this->preferences['add-label'] = i('Add match', 'forge-tournaments');
        $this->preferences['single-item'] = i('Match', 'forge-tournaments');

        $this->custom_fields();

    }

    public function render($item) {
        return "RENDER";
    }

    public static function registerSubTypes() {
    }

     private function custom_fields() {
        $this->addFields([
            [
                'key' => 'node_fields',
                'label' => \i('Which fields are defined', 'forge-tournaments'),
                'values' => '',
                'value' => '',
                'multilang' => false,
                'type' => [$this, 'renderNodeFields'],
                'readonly' => true,
                'order' => 10,
                'position' => 'left',
                'hint' => i('This is automatically defined by the system', 'forge-tournaments')
            ],
            [
                'key' => 'node_data_gathered',
                'label' => \i('List of data gathered for the different participants', 'forge-tournaments'),
                'values' => '',
                'value' => '',
                'multilang' => false,
                'type' => [$this, 'renderNodeDataGathered'],
                'readonly' => true,
                'order' => 12,
                'position' => 'left',
                'hint' => i('...', 'forge-tournaments')
            ]
        ]);
    }

    public function renderNodeFields() {
        return "<ul>
        <li>time  | float | teams</li>
        <li>other  | string | custom provider</li>
        <li>points | integer | teams</li>
        <li>winner | boolean | system (comparison)</li>
</ul>";
    }
    public function renderNodeDataGathered() {
        return '<table class="ft-result-table">
    <thead>
        <tr>
            <th>Data Key</th>
            <th>Source</th>
            <th>Participant 1</th>
            <th>Participant 2</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Time</td>
            <td>Participants</td>
            <td>10:00 [edit]</td>
            <td>11:00 [edit]</td>
            <td>OK</td>
        </tr>
        <tr>
            <td>other</td>
            <td>OTHER PROVIDER</td>
            <td>23543.5 [edit]</td>
            <td class="amended"><del>2345.7</del> 2346.8 [edit]</td>
            <td>OK</td>
        </tr>
        <tr>
            <td>points</td>
            <td>Participants</td>
            <td class="amended"><del>P1: 21 | P2: 29</del> 29 [edit]</td>
            <td class="conflicted">P1: 31 | P2: 29 [resolve]</td>
            <td>Conflicted</td>
        </tr>
        <tr>
            <td>Stylnote</td>
            <td>Admin</td>
            <td class="missing"> -- [add]</td>
            <td class="missing"> -- [add]</td>
            <td>Missing</td>
        </tr>
        <tr>
            <td>Winner</td>
            <td>System (Calculation)</td>
            <td> MISSING DATA (points) </td>
            <td> MISSING DATA (points) </td>
            <td>Missing</td>
        </tr>
    </tbody>
</table>';
    }

    public function itemDependentFields($item) {}

}
