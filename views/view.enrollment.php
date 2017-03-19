<?php

namespace Forge\Modules\ForgeTournaments;

use \Forge\Core\Abstracts\View;
use \Forge\Core\App\App;
use \Forge\Core\Classes\Fields;
use \Forge\Core\Classes\Media;



class EnrollmentView extends View {
    public $name = 'enrollment';
    public $allowNavigation = true;

    public function additionalNavigationForm() {
        $tournaments = App::instance()->cm->getCollection('forge-tournaments')->items();
        $values = array();
        foreach($tournaments as $tournament) {
            $values[$tournament->slug()] = $tournament->getMeta('title');
        }
        $formfields = Fields::select(array(
            'key' => 'add-to-url',
            'label' => i('Select the tournament, that you want to display the enrollment form.'),
            'values' => $values
        ));
        return array("form" => $formfields);
    }

    public function content($parts = []) {
        $collection = App::instance()->cm->getCollection('forge-tournaments');
        $tournament = $collection->getBySlug($parts[0]);

        return App::instance()->render(MOD_ROOT.'forge-tournaments/templates/views/',
            'enrollment',
            [
                'title' => $tournament->getMeta('title'),
                'starts_in_label' => i('Startet in', 'forge-tournaments'),
                'remaining_time' => $tournament->getMeta('start_time'),
                'status_label' => i('Status', 'forge-tournaments'),
                'enrollment_label' => i('Enrollments', 'forge-tournaments'),
                'current_participants' => 0,
                'max_participants' => $tournament->getMeta('max_participants'),
                'form' => $this->form()
            ]
        );
    }

    public function form() {
        $form = '';
        $form.= Fields::text(array(
            'key' => $this->prefix.'name',
            'label' => $this->getField($this->prefix.'name'),
            'hint' => ''
        ));
        $form.= Fields::text(array(
            'key' => $this->prefix.'key',
            'label' => $this->getField($this->prefix.'key'),
            'hint' => ''
        ));
        $form.= Fields::textarea(array(
            'key' => $this->prefix.'description',
            'label' => $this->getField($this->prefix.'description'),
            'hint' => ''
        ));
        $form.= Fields::text(array(
            'key' => $this->prefix.'url',
            'label' => $this->getField($this->prefix.'url'),
            'hint' => ''
        ));
        $form.= Fields::fileStandart(array(
            'key' => $this->prefix.'image',
            'label' => $this->getField($this->prefix.'image'),
            'hint' => ''
        ));
        $form.= Fields::button($this->getField($this->prefix."button"), 'primary');
        return $form;
    }

}
