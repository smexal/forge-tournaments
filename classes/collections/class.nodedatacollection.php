<?php

namespace Forge\Modules\ForgeTournaments;

use Forge\Core\Classes\Relations\Enums\DefaultRelations;
use Forge\Core\Abstracts\DataCollection;
use Forge\Core\Classes\CollectionItem;
use Forge\Core\Classes\Relations\Relation;
use Forge\Core\Classes\Relations\Enums\Directions as RelationDirection;
use Forge\Core\Classes\Relations\CollectionRelation as CollectionRelation;

use Forge\Modules\ForgeTournaments\Data\DatasetStorage;
use Forge\Modules\ForgeTournaments\Data\SchemaProvider;
use Forge\Modules\ForgeTournaments\Data\StorageNodeFactory;

class NodaDataCollection extends DataCollection {
    protected static $PARENT_COLLECTION;

    protected function setup() {}

    public static function relations($existing) {
        return array_merge($existing, [
            'ft_participant_list' => new Relation(
                'ft_participant_list',
                RelationDirection::DIRECTED
            )
        ]);
    }

    protected function inheritedFields() {
        $fields = [
            [
                'key' => 'parent_node',
                'label' => \i('Parent Node', 'forge-tournaments'),
                'values' => [],
                'value' => NULL,
                'multilang' => false,

                'type' => 'collection',
                'maxtags'=> 1,
                'collection' => static::$PARENT_COLLECTION,
                'data_source_save' => 'relation',
                'data_source_load' => 'relation',
                'relation' => [
                    'direction' => \Forge\Core\Classes\Relations\Enums\Directions::REVERSED,
                    'identifier' => DefaultRelations::PARENT_OF
                ],

                'order' => 1,
                'position' => 'right',
                'readonly' => true
            ],
            [
                'key' => 'ft_participant_list_size',
                'label' => \i('Participant list size', 'forge-tournaments'),
                'value' => 16,
                'multilang' => false,
                'type' => 'number',
                'order' => 10,
                'position' => 'right',
                'hint' => \i('Define how many participants are allowed. Use -1 for no restriction', 'forge-tournaments'),
            ],
            [
                'key' => 'ft_participant_list',
                'label' => \i('Participant list', 'forge-tournaments'),
                'value' => '',
                'multilang' => false,

                'type' => 'collection',
                /*'maxtags'=> 64, SET BY ft_num_winners*/
                'collection' => ParticipantCollection::COLLECTION_NAME,
                'data_source_save' => 'relation',
                'data_source_load' => 'relation',
                'relation' => [
                    'identifier' => 'ft_participant_list'
                ],

                'order' => 20,
                'position' => 'left',
                'hint' => \i('You can only add participants when the phase did not already start', 'forge-tournaments'),
            ],
        ];

        $schemas = $this->getDataschemaOptions();
        if(count($schemas) > 0) {
            $fields[] = [
                'key' => 'ft_data_schema',
                'label' => \i('Dataschema (field configuration)', 'forge-tournaments'),
                'values' => $schemas,
                'value' => array_keys($schemas)[0],
                'multilang' => false,
                'type' => 'select',
                // This is ALWAYS assigned by the fieldbuilder
                'readonly' => true,
                'order' => 5,
                'position' => 'right',
                'hint' => i('This is automatically assigned by the phasebuilder', 'forge-tournaments')
            ];
        }

        return $fields;
    }

    public function getDataschemaOptions() {
        $schemas = SchemaProvider::instance()->getSchemasForType($this->getNodeType());
        return array_map(function($item) {
            return $item->getName();
        }, $schemas);
    }

    public function getNodeType() {
        $type = str_replace('forge-tournaments-', '', $this->preferences['name']);
        return strtolower($type);
    }


    public function itemDependentFields($item) {
        $this->addUniqueFields([
            // THOSE ARE DEPENDENT ON data_schema
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
                'hint' => i('This is automatically defined by the system', 'forge-tournaments'),
                '__item_id' => $item->getID()
            ],
            // THIS IS LOADED BY THE STORAGE HANDLER
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
                'hint' => i('...', 'forge-tournaments'),
                '__item_id' => $item->getID()
            ]
        ]);

    }

    public function renderNodeFields($args, $value) {
        $item_id = $args['__item_id'];
        $storage_node = StorageNodeFactory::getByCollectionID($item_id);
        $schema = $storage_node->getDataSchema();
        $fields = $schema->getFields();
        $html = '<table class="ft-result-table">
                    <thead>
                        <tr>
                            <th>' . \i('Key', 'forge-tournaments') . '</th>
                            <th>' . \i('Type', 'forge-tournaments') . '</th>
                            <th>' . \i('Source', 'forge-tournaments') . '</th>
                            <th>' . \i('Required', 'forge-tournaments') . '</th>
                        </tr>
                    </thead>';
        foreach($fields as $field) {
            $html .= "<tr>
                        <td>{$field['key']}</td>
                        <td>{$field['type']}</td>
                        <td>{$field['source']}</td>
                        <td>{$field['required']}</td>
                    </tr>";
        }
        return $html;
    }

    public function renderNodeDataGathered($args, $value) {
        $item_id = $args['__item_id'];
        $storage_node = StorageNodeFactory::getByCollectionID($item_id);
        $schema = $storage_node->getDataSchema();
        $fields = $schema->getFields();

        return '<table class="ft-result-table">
    <thead>
        <tr>
            <th>Data Key</th>
            <th>Source</th>
            <th>FOR Participant 1</th>
            <th>FOR Participant 2</th>
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

}
