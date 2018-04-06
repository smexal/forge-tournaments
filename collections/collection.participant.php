<?php

namespace Forge\Modules\ForgeTournaments;

use Forge\Core\Abstracts\CollectionQuery;
use Forge\Core\Classes\CollectionItem;
use Forge\Core\Classes\Relations\Enums\Prepares;
use Forge\Core\Classes\User;
use Forge\Modules\TournamentsTeams\OrganizationsCollection;
use Forge\Modules\TournamentsTeams\TeamsCollection;
use Forge\Core\Abstracts\DataCollection;
use Forge\Core\App\App;
use Forge\Core\App\Auth;
use Forge\Core\Classes\FieldUtils as FieldUtils;
use Forge\Core\Classes\Relations\CollectionRelation as CollectionRelation;
use Forge\Core\Classes\Relations\Enums\Directions as RelationDirection;
use Forge\Modules\ForgeTournaments\CollectionSubtypes\Participants\TeamParticipant;
use Forge\Modules\ForgeTournaments\CollectionSubtypes\Participants\UserParticipant;

class ParticipantCollection extends DataCollection {
    const COLLECTION_NAME = 'forge-tournaments-participant';
    public $permission = "manage.collection.forge-tournaments-participant";


    protected function setup() {
        $this->preferences['name'] = ParticipantCollection::COLLECTION_NAME;
        $this->preferences['title'] = i('Participants', 'forge-tournaments');
        $this->preferences['all-title'] = i('Manage participant', 'forge-tournaments');
        $this->preferences['add-label'] = i('Add participant', 'forge-tournaments');
        $this->preferences['single-item'] = i('Participant', 'forge-tournaments');

        Auth::registerPermissions('api.collection.forge-tournaments-participant.read');

        $this->custom_fields();

    }

    public function render($item) {
        return "RENDER";
    }

    public static function relations($existing) {
        return array_merge($existing, [
           /* 'ft_prev_phase' => new CollectionRelation(
                'ft_prev_phase', 
                ParticipantCollection::COLLECTION_NAME, 
                ParticipantCollection::COLLECTION_NAME, 
                RelationDirection::DIRECTED
            ),
            'ft_next_phase' => new CollectionRelation(
                'ft_next_phase', 
                ParticipantCollection::COLLECTION_NAME, 
                ParticipantCollection::COLLECTION_NAME, 
                RelationDirection::DIRECTED
            )*/
        ]);
    }

    private function custom_fields() {
        $users = [];
        $users[0] = i('Choose a user', 'ftt');
        foreach(User::getAll() as $user) {
            $users[$user['id']] = $user['username'].' ('.$user['email'].')';
        }

        $this->addFields([
            [
                'key' => 'ftt_participant_teams',
                'label' => \i('Teams', 'ftt'),
                'values' => [],
                'value' => NULL,
                'multilang' => false,
                'type' => 'collection',
                'maxtags'=> 1,
                'collection' => 'forge-teams',
                'data_source_save' => 'relation',
                'data_source_load' => 'relation',
                'relation' => [
                    'identifier' => 'ftt_participant_teams'
                ],
                'order' => 10,
                'position' => 'left',
                'readonly' => false,
                'hint' => i('Assigned Teams for this organization', 'ftt')
            ],
            [
                'key' => 'user',
                'label' => i('User', 'ftt'),
                'multilang' => false,
                'type' => 'select',
                'chosen' => true,
                'order' => 30,
                'position' => 'left',
                'hint' => i('Direct relation to a user', 'ftt'),
                'values' => $users
            ]
        ]);
    }

    /**
     * Creates a participant for either an user or a team,
     * and always returns the id of the participant.
     * @param  CollectionItem   $team
     * @param  User             $user
     * @return int              Participant ID
     */
    public static function createIfNotExists($team = null, $user = null) {

        if(! is_null($user)) {
            return self::createUserParticipantIfNotExists($user);
        }
        if(! is_null($team)) {
            return self::createTeamParticipantIfNotExists($team);
        }

    }

    public static function createUserParticipantIfNotExists($user) {
        if(! is_object($user)) {
            $user = new User($user);
        }
        $found = CollectionQuery::items([
            'name' => 'forge-tournaments-participant',
            'meta_query' => [
                'user' => $user->get('id')
            ]
        ]);

        // "member" item from this user already exists.
        if(count($found) > 0) {
            return $found[0]->getID();
        }

        $args = [
            'author' => $user->get('id'),
            'name' => $user->get('username'),
            'type' => 'forge-tournaments-participant'
        ];

        $meta = [
            [
                'keyy' => 'user',
                'value' => $user->get('id'),
                'lang' => 0
            ]
        ];

        return CollectionItem::create($args, $meta);
    }

    public static function getTeam($participant) {
        if(! is_object($participant)) {
            $participant = new CollectionItem($participant);
        }

        $relation = App::instance()->rd->getRelation('ftt_participant_teams');
        $results = $relation->getOfLeft($participant->id, Prepares::AS_IDS_RIGHT);
        if(array_key_exists(0, $results)) {
            return $results[0];
        }
        return;
    }

    public static function createTeamParticipantIfNotExists($team) {
        if(! is_object($team)) {
            $team = new CollectionItem($team);
        }

        $relation = App::instance()->rd->getRelation('ftt_participant_teams');
        $results = $relation->getOfRight($team->id, Prepares::AS_IDS_LEFT);

        if(count($results) == 0) {
            // create new participant
            
            $organization = TeamsCollection::getOrganization($team);
            $name = TeamsCollection::getName($team);
            $orgaName = OrganizationsCollection::getShortName($organization);

            $args = [
                'author' => App::instance()->user->get('id'),
                'name' => '['.$orgaName.'] '.$name,
                'type' => 'forge-tournaments-participant'
            ];
            $meta = [];

            $participantID = CollectionItem::create($args, $meta);

            $relation = App::instance()->rd->getRelation('ftt_participant_teams');
            $relation->add($participantID, $team->id);

            return $participantID;
        }
        return $results[0];
        
    }

    public static function registerSubTypes() {
        BaseRegistry::registerTypes('IParticipantType', FORGE_TOURNAMENTS_COLLECTION_SUBTYPES['IParticipantType']);
    }

    /** Stays here because i dont know if this has been used somewhere else...

    public function itemDependentFields($item) {
        $participant = Utils::getSubtype('IParticipantType', $item, 'ft_participant_type');
        if(!is_null($participant)) {
            $new_fields = $participant->fields($item);
            $this->addUniqueFields($new_fields);
            $this->customFields = $participant->modifyFields($this->customFields, $item);
        }
    }

    public function processModifyParticipantType($field, $item, $value) {
        $phase_state = $item->getMeta('ft_phase_state');
        if($phase_state > PhaseState::CONFIG_PHASETYPE) {
            $field['readonly'] = true;
        }
        return $field;
    }
    */
}
