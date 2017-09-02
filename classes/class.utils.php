<?php

namespace Forge\Modules\ForgeTournaments;

use Forge\Modules\ForgeTournaments\CollectionSubtypes\Phases\PhaseRegistry;

abstract class Utils {

    public static function getPhaseStates() {
        return \triggerModifier(FORGE_TOURNAMENT_NS . '/participant_types', [
            State::FRESH     => \i('Fresh', 'forge-tournaments'),
            State::OPEN      => \i('Open', 'forge-tournaments'),
            State::READY     => \i('Ready', 'forge-tournaments'),
            State::RUNNING   => \i('Running', 'forge-tournaments'),
            State::FINISHED  => \i('Finished', 'forge-tournaments'),
            State::COMPLETED => \i('Completed', 'forge-tournaments')
        ]);
    }

    public static function getStateGroups() {
        return \triggerModifier(FORGE_TOURNAMENT_NS . '/phase_state_groups', State::STATE_GROUPS);
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


}