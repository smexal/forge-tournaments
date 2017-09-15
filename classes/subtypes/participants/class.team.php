<?php

namespace Forge\Modules\ForgeTournaments\CollectionSubtypes\Participants;

use \Forge\Modules\ForgeTournaments\Interfaces\IParticipantType;

use \Forge\Core\Classes\CollectionItem;

class TeamParticipant extends BaseParticipant implements IParticipantType {

    public static function identifier() : string {
        return \Forge\Modules\ForgeTournaments\ParticipantTypes::TEAM;
    }

    public static function name() : string {
        return i('Team participant', 'forge-tournaments');
    }

    public function fields($item=null) : array {
        return [
            [
                'key' => 'custom_team_participant_field',
                'label' => \i('Custom Team-Participant Field', 'forge-tournaments'),
                'value' => '',
                'multilang' => false,
                // TODO: Add team field?
                'type' => 'tags',
                'order' => 100,
                'position' => 'left',
                'hint' => \i('My Field only appears when i am a user participant', 'forge-tournaments')
            ]
        ];
    }

    public function render(CollectionItem $item) : string {
        return 'SHOW list and roles of Team USER Data here ... redirect rendering?';
    }

}