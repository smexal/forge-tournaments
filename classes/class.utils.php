<?php

namespace Forge\Modules\ForgeTournaments;

use Forge\Modules\ForgeTournaments\CollectionSubtypes\Phases\PhaseRegistry;
use Forge\Modules\ForgeTournaments\Scoring\ScoringProvider;

abstract class Utils {

    public static function getPhaseStates() {
        return \triggerModifier(FORGE_TOURNAMENT_NS . '/participant_types', [
            PhaseState::FRESH     => \i('Fresh', 'forge-tournaments'),
            PhaseState::OPEN      => \i('Open', 'forge-tournaments'),
            PhaseState::READY     => \i('Ready', 'forge-tournaments'),
            PhaseState::RUNNING   => \i('Running', 'forge-tournaments'),
            PhaseState::FINISHED  => \i('Finished', 'forge-tournaments'),
            PhaseState::COMPLETED => \i('Completed', 'forge-tournaments')
        ]);
    }

    public static function getStateGroups() {
        return \triggerModifier(FORGE_TOURNAMENT_NS . '/phase_state_groups', PhaseState::STATE_GROUPS);
    }

    public function getSubtype($type, $item, $meta_key) {
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


}