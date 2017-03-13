<?php

namespace Forge\Modules\ForgeTournaments;

use \Forge\Loader;
use \Forge\Core\Abstracts\Module;
use \Forge\Core\App\API;
use \Forge\Core\App\Auth;
use \Forge\Core\App\App;
use \Forge\Core\Classes\Media;
use \Forge\Core\Classes\Localization;

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

        // backend
        Loader::instance()->addStyle('modules/forge-tournaments/assets/css/forge-tournaments.less');

        Loader::instance()->addScript('modules/forge-tournaments/assets/scripts/forge-tournaments.js');

        // frontend
        App::instance()->tm->theme->addScript($this->url().'assets/scripts/forge-tournaments.js', true);
        App::instance()->tm->theme->addScript(CORE_WWW_ROOT.'ressources/scripts/externals/tooltipster.bundle.min.js', true);

        App::instance()->tm->theme->addStyle(MOD_ROOT.'forge-tournaments/assets/css/forge-tournaments.less');
        App::instance()->tm->theme->addStyle(MOD_ROOT.'forge-tournaments/assets/css/bracket.less');
        App::instance()->tm->theme->addStyle(CORE_WWW_ROOT.'ressources/css/externals/tooltipster.bundle.min.css');

        API::instance()->register('forge-tournaments', [$this, 'apiAdapter']);
    }

    public function apiAdapter($data) {
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
                $lang = Localization::getCurrentLanguage();
                $collectionItem->insertMeta('description', $_POST[static::$description_field], $lang);
                $collectionItem->insertMeta('key', $_POST[static::$key_field], $lang);
                $collectionItem->insertMeta('url', $_POST[static::$url_field], $lang);
                if (isset($media)) {
                    $collectionItem->insertMeta('image_logo', $media->id, $lang);
                }
                $collectionItem->insertMeta('admins', json_encode([App::instance()->user->get('id')]), $lang);

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
        }
    }

}

?>
