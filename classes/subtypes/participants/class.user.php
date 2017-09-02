<?php

namespace Forge\Modules\ForgeTournaments\CollectionSubtypes\Participants;

use \Forge\Modules\ForgeTournaments\Interfaces\IParticipantType;

use \Forge\Core\Classes\CollectionItem;

class UserParticipant extends BaseParticipant implements IParticipantType {

    public static function identifier() : string {
        return \Forge\Modules\ForgeTournaments\ParticipantTypes::USER;
    }

    public static function name() : string {
        return i('User participant', 'forge-tournaments');
    }

    public function fields($item=null) : array {
        return [
            [
                'key' => 'custom_user_participant_field',
                'label' => \i('Custom User Participant Field', 'forge-tournaments'),
                'value' => '',
                'multilang' => false,
                // TODO: Add user field?
                'type' => 'tags',
                'order' => 100,
                'position' => 'left',
                'hint' => \i('My Field only appears when i am a user participant', 'forge-tournaments')
            ]
        ];
    }

    public function render(CollectionItem $item) : string {
        return 'SHOW USER PROFILE here ... redirect rendering?';
    }

}