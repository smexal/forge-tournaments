<?php
namespace Forge\Modules\ForgeTournaments;

abstract class PhaseState {
    /**
     *   Phase is not yet ready for usage (basic configuration via system or admin possible)
     *   Admin Selects: 
     *       - Phase-Type 
     *       - Scoring-Typ
     *       - Max. Participants
     *   Upon exiting to the next phase:
     *       - Enable phasetype specific fields
     */
    const CONFIG_BASIC = 5;
    
    /**
     * Here the Admin configures the phase type specific fields 
     * Admin selects:
     *       - Num Grups / Bracket Type
     *       - Type Specific Config
     *   Upon exiting to the next phase:
     *       - Generate phasetype specific DataSet-Tree (Phase -> Groups -> Encounters (-> Matches))
     *         > Via PhaseBuilder
     */
    const CONFIG_PHASETYPE = 10;
    
    /**
     * Phase can now publicly receive teams via manual registration or via system from previous phase
     */
    const REGISTRATION = 20;

    /**
     * The public assignment phase state is over and the admin can make manual slot assignments
     * Admin does:
     *       - Correct participants in the participant list
     *       - Assings participants to their slots
     * Upon saving or exiting to the next phase:
     *       - Take the slot assignments and distribute the values 
     *         to the corresponding collection tree nodes 
     */
    const ASSIGNMENT = 25;

    /**
     * Lock assignment and give a last overview of the phase which is about to start
     * On entring:
     *       - Make all fields readonly
     * Admin does:
     *       - Review of the configuration and subnode configurations
     * Upon exiting:
     *       - Do nothing
     */
    const READY = 30;
    
    /**
     * Tournament phase has now started the users / moderators / admins / dataproviders can input data
     *
     * Participants do:
     *       - Finish maches and record results
     * Admin does:
     *       - Correct conflicting results
     * System does:
     *       - Send recording invitations to participants for matches 
               which still have missing match results
     *       - Automatically calculate Encounter results an propagates 
               them up the node tree when complete
     *       - Automatically assign the next Encounter when one Encounter is over (bracket phase)
     *       - Inform admin if there are conflicting match results recorded by the participants
     */
    const RUNNING = 40;
    
    /**
     * The tournament phase is over
     * Admin can:
     *       - Ammend results
     * Upon finishing:
     *       - Recalculate whole node collection result tree
     *       - Assign winner to particpant output pool
     */
    const FINISHED = 50;
    
    /** 
     * This phase is completed
     *
     * Upon entering:
     *       - Take participant output pool and assign the participants to the next phase
     */
    const COMPLETED = 60;

    const GROUP_PREPARE = 10;
    const GROUP_RUNNING = 20;
    const GROUP_DONE    = 30;

    # State groups define which states are allowed to be switched from and to
    const STATE_GROUPS = [
        PhaseState::GROUP_PREPARE => [
            PhaseState::CONFIG_BASIC, 
            PhaseState::CONFIG_PHASETYPE, 
            PhaseState::REGISTRATION, 
            PhaseState::ASSIGNMENT, 
            PhaseState::READY
        ],
        PhaseState::GROUP_RUNNING => [
            PhaseState::RUNNING, 
            PhaseState::FINISHED
        ],
        PhaseState::GROUP_DONE    => [
            PhaseState::COMPLETED
        ]
    ];

}