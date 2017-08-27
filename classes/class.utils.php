<?php

namespace Forge\Modules\ForgeTournaments;

use \Forge\Modules\ForgeTournaments\Phases\PhaseRegistry;

abstract class Utils {


    public static function getPhaseStates() {
        return \triggerModifier(FORGE_TOURNAMENT_NS . '/phase_states', [
            State::FRESH     => \i('Fresh', 'forge-tournaments'),
            State::OPEN      => \i('Open', 'forge-tournaments'),
            State::READY     => \i('Ready', 'forge-tournaments'),
            State::RUNNING   => \i('Running', 'forge-tournaments'),
            State::FINISHED  => \i('Finished', 'forge-tournaments'),
            State::COMPLETED => \i('Completed', 'forge-tournaments')
        ]);
    }

    public static function getPhaseTypes() {
        $phases = PhaseRegistry::instance()->getAll();
        $list = [];
        foreach($phases as $key => $phase) {
            $list[$key] = $phase::name();
        }
        return $list;
    }
}