<?php
namespace Forge\Modules\ForgeTournaments;

abstract class State {
    # Phase is not yet ready for usage (basic configuration via system or admin possible)
    const FRESH = 10;
    # Phase is ready for receiving teams
    const OPEN = 20;
    # No more Teams are allowed
    # Admin assigns Teams (group phase / planning)
    const READY = 30;
    # No more  phase configs possible
    # Encounters are generated upon entering
    # Encounter results can be recorded 
    const RUNNING = 40;
    # NO Encounter results can be changed anymore by non-admins
    # Admins can alter and fix wrong results
    const FINISHED = 50;
    # Upon completing a phase the winners are moved to the next phase
    const COMPLETED = 60;
}
