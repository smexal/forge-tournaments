<?php

namespace Forge\Modules\ForgeTournaments;

use Forge\Core\App\App;
use Forge\Core\App\Auth;
use Forge\Core\Classes\User;
use Forge\Core\Classes\Fields;
use Forge\Core\Classes\Utils;
use Forge\Core\Classes\CollectionItem;
use Forge\Modules\ForgeTournaments\ParticipantCollection;
use Forge\Modules\ForgeTournaments\TournamentCollection;
use Forge\Modules\TournamentsTeams\MembersCollection;
use Forge\Modules\TournamentsTeams\OrganizationsCollection;
use Forge\Modules\TournamentsTeams\TeamsCollection;

class Signup {
    private $item = null;

    public function __construct($item) {
        $this->item = $item;
    }

    public function allowedForSignup($type, $participant = null) {
        $allowedForSignup = false;
        if($type == 'team') {
            $participantID = ParticipantCollection::createIfNotExists($participant);
            if($this->item->getMeta('ticket_required') === 'on') {
                $eventCollection = App::instance()->cm->getCollection('forge-events');
                $team = ParticipantCollection::getTeam($participantID);
                $teamMembers = TeamsCollection::getMembers($team);
                foreach($teamMembers as $member) {
                    $mObj = new CollectionItem($member);
                    $ticketAvailable = $eventCollection->userTicketAvailable(
                        $this->item->getMeta('event'), 
                        $mObj->getMeta('user')
                    );
                    if($ticketAvailable) {
                        $failedMembers[] = $mObj->getMeta('user');
                    }
                }
                if(count($failedMembers) == 0) {
                    $allowedForSignup = true;
                }
            } else {
                $allowedForSignup = true;
            }
        }
        if($type == 'user') {
            $participantID = ParticipantCollection::createIfNotExists(null, App::instance()->user->get('id'));
            if($this->item->getMeta('ticket_required') === 'on') {
                $eventCollection = App::instance()->cm->getCollection('forge-events');
                $ticketAvailable = $eventCollection->userTicketAvailable($this->item->getMeta('event'), App::instance()->user->get('id'));
                if( ! $ticketAvailable) {
                    $allowedForSignup = true;
                }
            } else {
                $allowedForSignup = true;
            }
        }
        return [
            'allowed' => $allowedForSignup,
            'participant' => $participantID
        ];
    }

    public function render() {
        $message = null;

        if(! Auth::any()) {
            $parts = Utils::getUriComponents();
            array_pop($parts);
            App::instance()->redirect('login', Utils::getUrl($parts), true);
        }

        if(! $this->item->getMeta('allow_signup')) {
            App::instance()->redirect('denied', false, true);
        }

        if(array_key_exists('selected_participant', $_POST) &&
            ($_POST['selected_participant'] != 0 || $_POST['selected_participant'] == 'user')) {
            $allowed = $this->allowedForSignup($_POST['participant_type'], $_POST['selected_participant']);
            $allowedForSignup = $allowed['allowed'];
            $participantID = $allowed['participant'];
            $failedMembers = [];

            $success = false;
            if($allowedForSignup) {
                $success = TournamentCollection::addParticipant($this->item->id, $participantID);
            }
            if($success) {
                $message = [
                    'value'=> i('Thank you for participating in this tournament.', 'forge-tournaments'),
                    'type' => 'success'
                ];
            } else {
                if(! $allowedForSignup) {
                    $addText = '';
                    if(count($failedMembers) > 0) {
                        $fM = [];
                        foreach($failedMembers as $m) {
                            $u = new User($m);
                            $fM[] = $u->get('username');
                        }
                        $addText.= '('.implode(", ", $fM).')';

                    }
                    $message = [
                        'value'=> i('You are not allowed for this tournament. You need to buy a ticket. '.$addText, 'forge-tournaments'),
                        'type' => 'warning'
                    ];
                } else {
                    $message = [
                        'value'=> i('It seems like you have already signed up.', 'forge-tournaments'),
                        'type' => 'warning'
                    ];
                }
            }
        } else {
            if(array_key_exists('selected_participant', $_POST) && $_POST['participant_type'] == 'team') {
                $message = [
                    'value'=> i('You have to select a viable team.', 'forge-tournaments'),
                    'type' => 'warning'
                ];
            }
        }

        $title = sprintf(i('Signup for <i>%s</i>', 'forge-tournaments'), $this->item->getMeta('title'));

        if($this->item->getMeta('team_size') == 1) {
            $description = i('You can signup for this tournament as a member.', 'forge-tournaments');
            $teamLink = false;
            $teamLinkText = false;
        } else {
            $description = sprintf(i('Make sure you are the owner of an organization with a team of at least %s members. Otherwise you cant signup for this tournament.', 'forge-tournaments'), $this->item->getMeta('team_size'));
            $teamLinkText = i('To the organization management site', 'forge-tournaments');
            $teamLink = App::instance()->vm->getViewByName('teams')->buildURL();
        }
        return App::instance()->render(MOD_ROOT.'forge-tournaments/templates/views/',
            'signup', [
                'title' => $title,
                'description' => $description,
                'teamLink' => $teamLink,
                'teamLinkText' => $teamLinkText,
                'form' => $this->item->getMeta('team_size') == 1 
                    ? $this->getSignupFormUser() : $this->getSignupFormTeam(),
                'message' => $message
            ]
        );
    }

    private function getSignupFormTeam() {
        $content = [];
        $content[] = Fields::hidden([
            'key' => 'participant_type',
            'value' => 'team'
        ]);
        $content[] = Fields::select([
            'label' => i('Define members', 'ftt'),
            'key' => 'selected_participant',
            'chosen' => true,
            'values' => $this->getViableTeams()
        ]);
        $content[] = Fields::button(i('Signup', 'ftt'));

        return App::instance()->render(CORE_TEMPLATE_DIR.'assets/', 'form', [
            'action' => Utils::getCurrentUrl(),
            'method' => 'post',
            'ajax' => true,
            'ajax_target' => '#slidein-overlay .content',
            'horizontal' => false,
            'content' => $content
        ]);
    }

    private function getSignupFormUser() {
        $content = [];
        $content[] = Fields::hidden([
            'key' => 'participant_type',
            'value' => 'user'
        ]);
        $content[] = Fields::hidden([
            'key' => 'selected_participant',
            'value' => 'user'
        ]);
        $content[] = Fields::button(i('Signup', 'ftt'));

        return App::instance()->render(CORE_TEMPLATE_DIR.'assets/', 'form', [
            'action' => Utils::getCurrentUrl(),
            'method' => 'post',
            'ajax' => true,
            'ajax_target' => '#slidein-overlay .content',
            'horizontal' => false,
            'content' => $content
        ]);
    }

    private function getViableTeams() {
        // get Member
        $memberID = MembersCollection::getByUser(App::instance()->user);
        $organizations = MembersCollection::getOwnedOrganizations($memberID);
        $viableTeams = [];
        foreach($organizations as $orga) {
            foreach(OrganizationsCollection::getTeams($orga) as $team) {
                if(TeamsCollection::getMemberCount($team) >= $this->item->getMeta('team_size')) {
                    $viableTeams[$team] = OrganizationsCollection::getName($orga).' - '
                        .TeamsCollection::getName($team);
                }
            }
        }
        // get Organizations, where this member is Owner
        // get Teams which have enough members
        
        if(count($viableTeams) == 0) {
            return [
                0 => i('You are not owner of a vialbe team for this tournament.', 'forge-tournaments')
            ];
        }

        return [
            0 => i('Choose your team', 'forge-tournaments'),
        ] + $viableTeams;
    }

}

?>