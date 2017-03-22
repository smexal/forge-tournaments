<?php

namespace Forge\Modules\ForgeTournaments;

use \Forge\Core\Abstracts\View;
use \Forge\Core\App\App;
use \Forge\Core\Classes\Fields;
use \Forge\Core\Classes\Media;
use \Forge\Core\Classes\Utils;

class EnrollmentView extends View {
    public $name = 'enrollment';
    public $allowNavigation = true;

    // public function additionalNavigationForm() {
    //     $tournaments = App::instance()->cm->getCollection('forge-tournaments')->items();
    //     $values = array();
    //     foreach($tournaments as $tournament) {
    //         $values[$tournament->slug()] = $tournament->getMeta('title');
    //     }
    //     $formfields = Fields::select(array(
    //         'key' => 'add-to-url',
    //         'label' => i('Select the tournament, that you want to display the enrollment form.'),
    //         'values' => $values
    //     ));
    //     return array("form" => $formfields);
    // }

    public function content($parts = []) {
        $collection = App::instance()->cm->getCollection('forge-tournaments');
        $tournament = $collection->getBySlug($parts[0]);

        $db = App::instance()->db;
        $db->where('tournament_id', $tournament->id);
        $participants = count($db->get('forge_tournaments_tournament_participant'));

        if ($tournament->getMeta('max_participants') >= $participants) {
            $form = $this->form($tournament);
        } else {
            $form = i('Tournament has no free slots left.', 'forge-tournaments');
        }

        $timeNow = new \DateTime();
        $timeDiff = $timeNow->diff(new \DateTime($tournament->getMeta('start_time')));
        if (! $timeDiff->invert) {
            if ($timeDiff->d >= 1) {
                $timeRemaining = sprintf(
                    i('%s days, %s hours and %s minutes', 'forge-tournaments'),
                    $timeDiff->d,
                    $timeDiff->h,
                    $timeDiff->i
                );
            } else {
                $timeRemaining = sprintf(
                    i('%s hours and %s minutes', 'forge-tournaments'),
                    $timeDiff->h,
                    $timeDiff->i
                );
            }
        } else {
            $timeRemaining = i('Sorry, enrollment already closed.', 'forge-tournaments');
            $form = 'Link to tournament details?';
        }

        $thumb = new Media($tournament->getMeta('image_thumbnail'));

        return App::instance()->render(MOD_ROOT.'forge-tournaments/templates/views/',
            'enrollment',
            [
                'title' => $tournament->getMeta('title'),
                'starts_in_label' => i('Startet in', 'forge-tournaments'),
                'remaining_time' => $timeRemaining,
                'status_label' => i('Status', 'forge-tournaments'),
                'enrollment_label' => i('Enrollments', 'forge-tournaments'),
                'current_participants' => $participants,
                'max_participants' => $tournament->getMeta('max_participants'),
                'thumbnail' => $thumb->getUrl(),
                'form' => $form,
                'action' => Utils::getUrl(['api', 'forge-tournaments', 'enroll'])
            ]
        );
    }

    public function form($tournament) {
        $organisations = App::instance()->cm->getCollection('forge-tournaments-organisations')->items();
        $userOrganisations = [];
        foreach($organisations as $organisation) {
            $userOrganisations[$organisation->id] = '[' . $organisation->getMeta('key') . '] ' . $organisation->getMeta('title');
        }

        if (! $tournament->getMeta('team_competition')) {
            $userOrganisations[0] = i('Not bound to an organisation', 'forge-tournaments');
            $form = Fields::hidden([
                'key' => 'user_id',
                'value' => App::instance()->user->id
            ]);
        }
        $form = Fields::select([
            'key' => 'organisation_id',
            'label' => i('Participating organisation', 'forge-tournaments'),
            'values' => $userOrganisations
        ]);
        $form.= Fields::text([
            'key' => 'key',
            'label' => i('Team key', 'forge-tournaments')
        ]);
        $form.= Fields::text([
            'key' => 'name',
            'label' => i('Team name', 'forge-tournaments')
        ]);
        $form.= Fields::hidden([
            'key' => 'tournament_id',
            'value' => $tournament->id
        ]);
        $form.= Fields::hidden([
            'key' => 'team_competition',
            'value' => ($tournament->getMeta('team_competition') ? 1 : 0)
        ]);
        $form.= Fields::button(i('Enroll', 'forge-tournaments'), 'primary');
        return $form;
    }

}
