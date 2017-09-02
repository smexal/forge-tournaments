<?php

namespace Forge\Modules\ForgeTournaments;

use \Forge\Core\Abstracts\DataCollection;
use \Forge\Core\App\App;
use \Forge\Core\Classes\User;
use \Forge\Core\Classes\CollectionItem;
use \Forge\Core\Classes\FieldUtils as FieldUtils;
use \Forge\Core\Classes\Relations\Enums\Directions as RelationDirection;
use \Forge\Core\Classes\Relations\CollectionRelation as CollectionRelation;

use \Forge\Modules\ForgeTournaments\CollectionSubtypes\Phases\PhaseRegistry;

class PhaseCollection extends DataCollection {
    const COLLECTION_NAME = 'forge-tournaments-phase';
    public $permission = "manage.collection.sites";


    protected function setup() {
        $this->preferences['name'] = PhaseCollection::COLLECTION_NAME; //TODO: make this a class constant
        $this->preferences['title'] = i('Phases', 'forge-tournaments');
        $this->preferences['all-title'] = i('Manage phase', 'forge-tournaments');
        $this->preferences['add-label'] = i('Add phase', 'forge-tournaments');
        $this->preferences['single-item'] = i('Phase', 'forge-tournaments');

        $this->custom_fields();

    }

    public function customEditContent($item_id) {
        $html = '';
        $item = new CollectionItem($item_id);
        $phase = Utils::getSubtype('IPhaseType', $item, 'ft_phase_type');
        if($phase) {
            $html .= $phase->render($item);
        }
        return $html;
    }

    public static function relations($existing) {
        return array_merge($existing, [
            'ft_prev_phase' => new CollectionRelation(
                'ft_prev_phase', 
                PhaseCollection::COLLECTION_NAME, 
                PhaseCollection::COLLECTION_NAME, 
                RelationDirection::DIRECTED
            ),
            'ft_next_phase' => new CollectionRelation(
                'ft_next_phase', 
                PhaseCollection::COLLECTION_NAME, 
                PhaseCollection::COLLECTION_NAME, 
                RelationDirection::DIRECTED
            )
        ]);
    }

    public static function registerSubTypes() {
        BaseRegistry::registerTypes('IPhaseType', FOREGE_TOURNAMENTS_COLLECTION_SUBTYPES['IPhaseType']);
    }

    private function custom_fields() {
        $this->addFields([
            [
                'key' => 'ft_phase_status',
                'label' => \i('Lifecycle', 'forge-tournaments'),
                'values' => Utils::getPhaseStates(),
                'value' => State::FRESH,
                'multilang' => false,
                'type' => 'select',
                'order' => 10,
                'position' => 'right',
                'hint' => i('Select the phase status', 'forge-tournaments')
            ],
            [
                'key' => 'ft_phase_type',
                'label' => \i('Phase type', 'forge-tournaments'),
                'values' => Utils::getPhaseTypes(),
                'value' => PhaseType::REGISTRATION,
                'multilang' => false,
                'type' => 'select',
                'order' => 10,
                'position' => 'left',
                'hint' => i('Select the phase type', 'forge-tournaments'),
                'process:modifyField' => [$this, 'processModifyPhaseType'],
            ],
            [
                'key' => 'ft_next_phase',
                'label' => \i('Next Phase', 'forge-tournaments'),
                'values' => Utils::getPhaseTypes(),
                'value' => NULL,
                'multilang' => false,

                'type' => 'collection',
                'maxtags'=> 1,
                'collection' => PhaseCollection::COLLECTION_NAME,
                'data_source' => 'relation',
                'relation' => [
                    'identifier' => 'ft_next_phase'
                ],

                'order' => 10,
                'position' => 'left',
                'readonly' => true,
                'hint' => i('The next phase after this one is completed', 'forge-tournaments'),
                'process:modifyField' => [$this, 'processModifyPhaseType'],
            ],
             [
                'key' => 'participant_pool',
                'label' => \i('Participant Pool', 'forge-tournaments'),
                'value' => '',
                'multilang' => false,
                'type' => 'tags',
                'order' => 20,
                'position' => 'left',
                'hint' => \i('You can only add participants when the phase did not already start', 'forge-tournaments')
            ]
        ]);
    }

    public function itemDependentFields($item) {
        $phase = Utils::getSubtype('IPhaseType', $item, 'ft_phase_type');
        if(!is_null($phase)) {
            $new_fields = $phase->fields($item);
            $this->addFields($new_fields);
            $this->customFields = $phase->modifyFields($this->customFields);
        }
    }

    public function processModifyPhaseType($field, $item, $value) {
        $phase_status = $item->getMeta('ft_phase_status');
        if($phase_status > State::OPEN) {
            $field['readonly'] = true;
        }
        return $field;
    }

}
