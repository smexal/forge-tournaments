<?php
namespace Forge\Modules\ForgeTournaments;

abstract class PhaseState {
    # Phase is not yet ready for usage (basic configuration via system or admin possible)
    const FRESH = 10;
    # Phase is ready for receiving teams
    const OPEN = 20;
    # Admin assigns Teams (group phase / planning)
    # Encounters are generated defining the Phase
    # Participants are assigned to Slots by Admin / Algorithm
    # No more Teams are allowed
    const READY = 30;
    # No more phase configs possible
    # Encounter results can be recorded 
    const RUNNING = 40;
    # No Encounter results can be changed anymore by non-admins
    # Admins can still alter and fix wrong results
    const FINISHED = 50;
    # Upon completing a phase the winners are moved to the next phase
    const COMPLETED = 60;

    const GROUP_PREPARE = 10;
    const GROUP_RUNNING = 20;
    const GROUP_DONE    = 30;

    # State groups define which states are allowed to be switched from and to
    const STATE_GROUPS = [
        PhaseState::GROUP_PREPARE => [PhaseState::FRESH, PhaseState::OPEN, PhaseState::READY],
        PhaseState::GROUP_RUNNING => [PhaseState::RUNNING, PhaseState::FINISHED],
        PhaseState::GROUP_DONE    => [PhaseState::COMPLETED]
    ];

}
