<?php
namespace Forge\Modules\ForgeTournaments;

abstract class State {
    # Phase is not yet ready for usage (basic configuration via system or admin possible)
    const FRESH = 10;
    # Phase is ready for receiving teams
    const OPEN = 20;
    # Admin assigns Teams (group phase / planning)
    # Encounters are generated defining the Phase
    # No more Teams are allowed
    const READY = 30;
    # No more  phase configs possible
    # Participants are assigned to Encounters
    # Encounter results can be recorded 
    const RUNNING = 40;
    # NO Encounter results can be changed anymore by non-admins
    # Admins can alter and fix wrong results
    const FINISHED = 50;
    # Upon completing a phase the winners are moved to the next phase
    const COMPLETED = 60;

    # State groups define which states are allowed to be switched from and to
    const STATE_GROUPS = [
        'prepare' => [State::FRESH, State::OPEN, State::READY],
        'running' => [State::RUNNING, State::FINISHED],
        'done'    => [State::COMPLETED]
    ];

}
