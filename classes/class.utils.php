<?php

namespace Forge\Modules\ForgeTournaments;

use Forge\Core\App\App;
use Forge\Core\Classes\CollectionItem;

use Forge\Modules\ForgeTournaments\CollectionSubtypes\Phases\PhaseRegistry;
use Forge\Modules\ForgeTournaments\Scoring\ScoringProvider;

abstract class Utils {

    public static function getPhaseStates() {
        return \triggerModifier(FORGE_TOURNAMENT_NS . '/phase_states', [
            PhaseState::CONFIG_BASIC     => \i('Configuration', 'forge-tournaments'),
            /*PhaseState::CONFIG_PHASETYPE => \i('Phasetype configuration', 'forge-tournaments'),
            PhaseState::REGISTRATION     => \i('Registration', 'forge-tournaments'),
            PhaseState::ASSIGNMENT       => \i('Assignment', 'forge-tournaments'),*/
            PhaseState::READY            => \i('Ready', 'forge-tournaments'),
            PhaseState::RUNNING          => \i('Running', 'forge-tournaments'),
            PhaseState::FINISHED         => \i('Finished', 'forge-tournaments'),
            /*PhaseState::COMPLETED        => \i('Completed', 'forge-tournaments')*/
        ]);
    }

    public static function getNextPhaseState($state) {
        $states = static::getPhaseStates();
        $keys = array_keys($states);
        // Jump over all states which are smaller than the searched one
        // Return if a state is reached which is bigger 
        for($i = 0; $i < count($keys); $i++) {
            if($keys[$i] <= $state) {
                continue;
            }
            return $keys[$i];
        }
        // No bigger state found
        return null;
    }

    public static function getPrevPhaseState($state) {
        $states = static::getPhaseStates();
        $keys = array_keys($states);
        // Jump over all states which are bigger than the searched one
        // Return if a state is reached which is smaller
        for($i = count($keys) - 1; $i >= 0; $i--) {
            if($keys[$i] >= $state) {
                continue;
            }
            return $keys[$i];
        }
        // No smaller state found
        return null;
    }

    public static function getStateGroups() {
        return \triggerModifier(FORGE_TOURNAMENT_NS . '/phase_state_groups', PhaseState::STATE_GROUPS);
    }

    public static function getSubtype($type, $item, $meta_key) {
        if(!$item) {
            throw new \Exception("Can not handle empty item for getting SubType");
        }

        $reg_key = $item->getMeta($meta_key);
        return BaseRegistry::getRegistry($type)->get($reg_key);
    }

    public static function getPhaseTypes() {
        $phases = PhaseRegistry::instance()->getAll();
        $list = [];
        foreach($phases as $key => $phase) {
            $list[$key] = $phase::name();
        }
        return $list;
    }


    public static function getParticipantTypes() {
        return \triggerModifier(FORGE_TOURNAMENT_NS . '/phase_states', [
            ParticipantTypes::USER => \i('User', 'forge-tournaments'),
            ParticipantTypes::TEAM => \i('Team', 'forge-tournaments')
        ]);
    }

    public static function getScoringOptions() {
        $scorings = ScoringProvider::instance()->getAllScorings();
        return array_map(function($item) {
            return $item['name'];
        }, $scorings);
    }

    public static function getDefaultScoringID() {
        $scorings = static::getScoringOptions();
        $option = array_keys($scorings)[0];
        return $option;
    }

    public static function makeCollectionItem($c_name, $name, $parent_id=null, $set_metas=[]) {
        $collection = App::instance()->cm->getCollection($c_name);
        $args = [
            'name' => $name,
            'type' => $c_name
        ];
        if(is_numeric($parent_id)) {
            $args['parent'] = $parent_id;
        }

        $metas = [];
        $fields = $collection->fields();
        foreach($fields as $field) {
            if(isset($field['data_source_save'])) {
                continue;
            }
            if(!isset($field['value'])) {
                continue;
            }
            $metas[$field['key']] = [
                'value' => $field['value']
            ];
        }

        foreach($set_metas as $key => $value) {
            $metas[$key] = [
                'value' => $value
            ];
        }
        $item = new CollectionItem(CollectionItem::create($args, $metas));
        return $item;
    }


}
