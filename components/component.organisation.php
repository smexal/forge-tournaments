<?php

namespace Forge\Modules\ForgeTournaments;

use \Forge\Core\Abstracts\Component;
use \Forge\Core\App\App;
use \Forge\Core\App\Auth;
use \Forge\Core\Classes\Fields;
use \Forge\Core\Classes\Media;
use \Forge\Core\Classes\Utils;

class OrganisationComponent extends Component {
    public $settings = [];
    private $prefix = 'forge_tournament_organisation_registration_';

    public function prefs() {
        $this->settings = array(
            array(
                'label' => i('Lead text', 'forge-tournaments'),
                'hint' => '',
                'key' => $this->prefix.'lead',
                'type' => 'text'
            ),
            array(
                'label' => i('Name of organisation', 'forge-tournaments'),
                'hint' => '',
                'key' => $this->prefix.'name',
                'type' => 'text'
            ),
            array(
                'label' => i('Key', 'forge-tournaments'),
                'hint' => '',
                'key' => $this->prefix.'key',
                'type' => 'text'
            ),
            array(
                'label' => i('Image', 'forge-tournaments'),
                'hint' => '',
                'key' => $this->prefix.'image',
                'type' => 'text'
            ),
            array(
                'label' => i('Description', 'forge-tournaments'),
                'hint' => '',
                'key' => $this->prefix.'description',
                'type' => 'text'
            ),
            array(
                'label' => i('URL', 'forge-tournaments'),
                'hint' => '',
                'key' => $this->prefix.'url',
                'type' => 'text'
            ),
            array(
                'label' => i('Signup Button Text', 'forge-tournaments'),
                'hint' => '',
                'key' => $this->prefix.'button',
                'type' => 'text'
            )
            // TODO: make a value "callable"
            // make a static calable method to be called, when "values" is required for performance.
        );
        return [
            'name' => i('Organisation registration form'),
            'description' => i('Add a form for organisations to register', 'forge-tournaments'),
            'id' => 'forge_tournament_organisation_registration',
            'image' => '',
            'level' => 'inner',
            'container' => false
        ];
    }

    public function content() {
        return App::instance()->render(DOC_ROOT.'modules/forge-tournaments/templates/components/',
            'organisation_registration',
            [
                'before' => $this->getField($this->prefix.'lead'),
                'action' => Utils::getUrl(['api', 'forge-tournaments', 'add_organisation']),
                'form' => $this->form(),
                'allowed' => Auth::any(),
                'not_allowed_msg' => i('Please log in to use this function', 'forge-tournaments')
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

    public function customBuilderContent() {
        return App::instance()->render(CORE_TEMPLATE_DIR.'components/builder/',
            'text',
            [
                'text' => i('Tournament Organisation Form', 'forge-tournaments')
            ]
        );
    }
}
