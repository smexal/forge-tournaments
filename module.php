<?php

namespace Forge\Modules\ForgeTournaments;

require_once('config.php');

use \Forge\Loader;
use \Forge\Core\Abstracts\Module;
use \Forge\Core\App\API;
use \Forge\Core\App\Auth;
use \Forge\Core\App\App;
use \Forge\Core\Classes\Group;
use \Forge\Core\Classes\Settings;
use \Forge\Core\Classes\Media;
use \Forge\Core\Classes\Localization;
use \Forge\Modules\ForgeTournaments\Phases\PhaseRegistry;

class ForgeTournaments extends Module {
    const FILE_SIZE_LIMIT = 5*1024*1024; // 5MB

    private static $name_field = 'forge_tournament_organisation_registration_name';
    private static $description_field = 'forge_tournament_organisation_registration_description';
    private static $key_field = 'forge_tournament_organisation_registration_key';
    private static $url_field = 'forge_tournament_organisation_registration_url';
    private static $image_field = 'forge_tournament_organisation_registration_image';

    private $permission = 'manage.forge-tournaments';

    public function setup() {
        $this->version = '1.0.0';
        $this->id = 'forge-tournaments';
        $this->name = i('Forge Tournaments', 'forge-tournaments');
        $this->description = i('Tournament Management for Forge.', 'forge-tournaments');
        $this->image = $this->url().'assets/images/module-image.png';
    }

    public function start() {
        Auth::registerPermissions($this->permission);
        $this->install();

        // backend
        Loader::instance()->addStyle('modules/forge-tournaments/assets/css/forge-tournaments.less');
        Loader::instance()->addStyle('modules/forge-tournaments/assets/css/bracket.less');

        Loader::instance()->addScript('modules/forge-tournaments/assets/scripts/forge-tournaments.js');

        // frontend
        App::instance()->tm->theme->addScript($this->url().'assets/scripts/forge-tournaments.js', true);

        App::instance()->tm->theme->addScript(CORE_WWW_ROOT."ressources/scripts/externals/jquery.js", true, 0);
        App::instance()->tm->theme->addScript(CORE_WWW_ROOT.'ressources/scripts/externals/tooltipster.bundle.min.js', true);
        App::instance()->tm->theme->addScript(CORE_WWW_ROOT."ressources/scripts/forms.js", true);

        App::instance()->tm->theme->addStyle(MOD_ROOT.'forge-tournaments/assets/css/forge-tournaments.less');
        App::instance()->tm->theme->addStyle(MOD_ROOT.'forge-tournaments/assets/css/bracket.less');
        App::instance()->tm->theme->addStyle(CORE_WWW_ROOT.'ressources/css/externals/tooltipster.bundle.min.css');

        API::instance()->register('forge-tournaments', [$this, 'apiAdapter']);
        \registerModifier('Forge/Core/RelationDirectory/collectRelations', '\Forge\Modules\ForgeTournaments\PhaseCollection::relations');
        \registerEvent(FORGE_TOURNAMENT_HOOK_NS . '/RegisterPhaseTypes', '\Forge\Modules\ForgeTournaments\PhaseCollection::registerPhaseTypes');

        PhaseRegistry::instance()->prepare();
    }

    public function install() {
        if(Settings::get($this->name . ".installed")) {
            return;
        }
        Auth::registerPermissions($this->permission);
        Auth::registerPermissions('api.collection.' . PhaseCollection::COLLECTION_NAME . '.read');
        
        $admins = Group::getByName('Administratoren');
        $admins->grant(Auth::getPermissionID('api.collection.' . PhaseCollection::COLLECTION_NAME . '.read'));

        Settings::set($this->name . ".installed", 1);
    }

    public function apiAdapter($data) {
        // TODO: Handling auslagern
        if ($data == 'add_organisation') {
            try {
                if (empty($_POST[static::$name_field])) {
                    throw new \Exception('nameMissing');
                }

                if (empty($_POST[static::$key_field])) {
                    throw new \Exception('keyMissing');
                }

                if (strlen($_POST[static::$key_field]) > 4) {
                    throw new \Exception('keyToLong');
                }
                // save image
                if ($_FILES[static::$image_field]['size'] > 0) {
                    $media = new Media();

                    if ($_FILES[static::$image_field]['size'] > static::FILE_SIZE_LIMIT) {
                        throw new \Exception('fileSizeTooBig');
                    }
                    if (! $media->isImage($_FILES[static::$image_field]['type'])) {
                        throw new \Exception('notAnImage');
                    }

                    $media->create($_FILES[static::$image_field]);
                }


                // persist new item
                // create new collectionitem
                $arr = ['name' => $_POST[static::$name_field],
                        'type' => 'forge-tournaments-organisations'];
                $itemId = App::instance()->cm->add($arr);
                $dataCollection = App::instance()->cm->getCollection('forge-tournaments-organisations');

                // update collectionitem
                $collectionItem = $dataCollection->getItem($itemId);
                foreach (Localization::getActiveLanguages() as $lang) {
                    $code = $lang['code'];
                    $collectionItem->insertMeta('title', $_POST[static::$name_field], $code);
                    $collectionItem->insertMeta('description', $_POST[static::$description_field], $code);
                    $collectionItem->insertMeta('key', $_POST[static::$key_field], $code);
                    $collectionItem->insertMeta('url', $_POST[static::$url_field], $code);
                    if (isset($media)) {
                        $collectionItem->insertMeta('image_logo', $media->id, $code);
                    }
                    $collectionItem->insertMeta('admins', json_encode([App::instance()->user->get('id')]), $code);

                    $collectionItem->insertMeta('status', 'published', $code);
                }

                return json_encode([
                    'type' => 'success',
                    'message' => i('Creation successful.', 'forge-tournaments')
                ]);

            } catch (\Exception $e) {
                $message = i('An error occured.', 'forge-tournaments');
                switch ($e->getMessage()) {
                    case 'nameMissing':
                    case 'keyMissing':
                    case 'fileSizeTooBig':
                    case 'notAnImage':
                        break;
                    default:
                }

                return json_encode([
                    'type' => 'error',
                    'message' => $message
                ]);
            }
        } else if ($data == 'enroll') {
            $db = App::instance()->db;

            $collection = App::instance()->cm->getCollection('forge-tournaments');
            $tournament = $collection->getItem($_POST['tournament_id']);

            if ($_POST['team_competition']) {
                $participant_id = $db->insert('forge_tournaments_tournament_participant',
                    [
                        'tournament_id' => $tournament->id,
                        'organisation_id' => $_POST['organisation_id'],
                        'key' => $_POST['key'],
                        'name' => $_POST['name']
                    ]
                );
            } else {
                $participant_id = $db->insert('forge_tournaments_tournament_participant',
                    [
                        'tournament_id' => $tournament->id,
                        'organisation_id' => $_POST['organisation_id'],
                        'user_id' => $_POST['user_id'],
                        'key' => $_POST['key'],
                        'name' => $_POST['name']
                    ]
                );
            }

            $encounterNr = 0;

            $db->where('tournament_id', $tournament->id);
            $encounters = $db->get('forge_tournaments_tournament_encounter');
            $tmpEncounterList = [];
            foreach ($encounters as $encounter) {
                if (! isset($tmpEncounterList[$encounter['encounter']])) {
                    $tmpEncounterList[$encounter['encounter']] = 0;
                }
                $tmpEncounterList[$encounter['encounter']]++;
            }
            for ($i = 0; $i < $tournament->getMeta('max_participants')/2; $i++) {
                if (! isset($tmpEncounterList[$i]) || $tmpEncounterList[$i] != 2) {
                    $encounterNr = $i;
                    break;
                }
            }

            $db->insert('forge_tournaments_tournament_encounter',
                [
                    'tournament_id' => $tournament->id,
                    'participant_id' => $participant_id,
                    'round' => 0,
                    'encounter' => $encounterNr
                ]
            );
            return json_encode([
                'type' => 'success',
                'message' => i('Enrollment completed', 'forge-tournaments')
            ]);
        }
    }

}

?>