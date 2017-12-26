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
                'key' => 'ft_slot_assignment',
                'label' => \i('Slot assignment', 'forge-tournaments'),
                'multilang' => false,

                'type' => ['\\Forge\\Modules\\ForgeTournaments\\Fields\\SlotAssignment', 'render'],
                'data_source_save' => ['\\Forge\\Modules\\ForgeTournaments\\Fields\\SlotAssignment', 'save'],
                'data_source_load' => ['\\Forge\\Modules\\ForgeTournaments\\Fields\\SlotAssignment', 'load'],
                'pool_source_selector' => 'input[name="ft_slot_assignment_pool"]',
                'prepare_template' => ['\\Forge\\Modules\\ForgeTournaments\\Fields\\SlotAssignment', 'prepareGroup'],

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

        foreach($this->customFields as &$field) {
            if($field['key'] == 'ft_slot_assignment') {
                $field['slot_count'] = $item->getMeta('ft_participant_list_size');
            }
        }

    }

    public function renderNodeFields($args, $value) {
        $item_id = $args['__item_id'];
        $storage_node = StorageNodeFactory::getByCollectionID($item_id);
        $schema = $storage_node->getDataSchema();
        $fields = $schema->getFields();
        $html = '<div class="form-group ft-result-table-wrapper">
                 <table class="ft-result-table">
                    <thead>
                        <tr>
                            <th>' . \i('Key', 'forge-tournaments') . '</th>
                            <th>' . \i('Type', 'forge-tournaments') . '</th>
                            <th>' . \i('Source', 'forge-tournaments') . '</th>
                            <th>' . \i('Required', 'forge-tournaments') . '</th>
                            <th>' . \i('Config', 'forge-tournaments') . '</th>
                        </tr>
                    </thead>
                    </tbody>';
        foreach($fields as $field) {
            $field_config = isset($field['field_config']) ? json_encode($field['field_config']): '';
            $html .= "<tr>
                        <td>{$field['key']}</td>
                        <td>{$field['type']}</td>
                        <td>{$field['source']}</td>
                        <td>{$field['required']}</td>
                        <td>{$field_config}</td>
                      </tr>";
        }
        $html .= '</tbody>
                </table>
              </div>';



        return $html;
    }

    public function getTournamentEntity($item) {
        $node_type = $this->getNodeType();
        $entity = PoolRegistry::instance()->getPool($node_type)->getInstance($item->getID(), $item);
        return $entity;
    }

    public function saveSlotAssignment($item, $field, $value, $lang) {

    }

    public function renderNodeDataGathered($args, $value) {
        $item_id = $args['__item_id'];
        $storage_node = StorageNodeFactory::getByCollectionID($item_id);
        $schema = $storage_node->getDataSchema();
        $fields = $schema->getFields();
        $data_sets = $storage_node->getStorage()->loadAll();
        
        $entity = $this->getTournamentEntity(new CollectionItem($item_id));
        $participants = $entity->getSlotAssignment();

        $html = '<div class="form-group ft-result-table-wrapper">
                     <table class="ft-result-table">
                        <thead>
                            <tr>
                                <th>' . \i('Data Key', 'forge-tournaments') . '</th>
                                <th>' . \i('Source', 'forge-tournaments') . '</th>';
        for($i = 0; $i < $participants->numSlots(); $i++) {
            $participant = $participants->getSlot($i);
            $p_name = is_null($participant) ? \i('Not yet set', 'forge-tournaments') : $participant->getName();
            $html .=            '<th>' . $p_name . '</th>';
        }

        $html .= '              <th>Statw</th>
                            </tr>
                        </thead>
                       <tbody>';
        foreach($fields as $field) {
            $html .= "<tr>
                        <td>{$field['key']}</td>
                        <td>{$field['source']}</td>";

            for($i = 0; $i < $participants->numSlots(); $i++) {
                $participant = $participants->getSlot($i);
                if(is_null($participant)) {
                    $html .= '<td> - [Add] </td>';
                } else {
                    $html .= '<td>is open</td>';
                }
            }
            $html .=    "<td>OK</td>
                    </tr>";

        }
        return $html .= '</tbody></table></div>';
    }

}
