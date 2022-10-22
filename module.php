<?php

namespace Forge\Modules\ForgeTournaments;

use \Forge\Core\Classes\Utils;
use \Forge\Core\Abstracts\Module;
use \Forge\Core\App\API;
use \Forge\Core\App\App;
use \Forge\Core\App\Auth;
use \Forge\Core\App\ModifyHandler;
use Forge\Core\Classes\CollectionItem;
use \Forge\Core\Classes\Group;
use \Forge\Core\Classes\Localization;
use \Forge\Core\Classes\Media;
use \Forge\Core\Classes\Settings;
use \Forge\Loader;
use \Forge\Modules\ForgeTournaments\CollectionSubtypes\Participants\ParticipantRegistry;
use \Forge\Modules\ForgeTournaments\CollectionSubtypes\Phases\PhaseRegistry;
use \Forge\Modules\ForgeTournaments\Data\SchemaLoader;
use \Forge\Modules\ForgeTournaments\EncounterCollection;
use \Forge\Modules\ForgeTournaments\GroupCollection;
use \Forge\Modules\ForgeTournaments\MatchCollection;
use \Forge\Modules\ForgeTournaments\PhaseCollection;
use \Forge\Modules\ForgeTournaments\Scoring\ScoringLoader;

class ForgeTournaments extends Module {
    const FILE_SIZE_LIMIT = 5 * 1024 * 1024; // 5MB

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

        require_once(MOD_ROOT.'forge-tournaments/config.php');
        // Needs to be run before collections are gathered
        SchemaLoader::instance()->load();
        ScoringLoader::instance()->load();
    }

    public function modules_loaded() {
    }

    public function start() {
        Auth::registerPermissions($this->permission);


        $this->install();

        // backend
        Loader::instance()->addStyle('modules/forge-tournaments/assets/css/general.less');
        Loader::instance()->addStyle('modules/forge-tournaments/assets/css/slotassignment.less');
        Loader::instance()->addStyle('modules/forge-tournaments/assets/css/forge-tournaments.less');
        Loader::instance()->addStyle('modules/forge-tournaments/assets/css/bracket.less');

        Loader::instance()->addStyle('modules/forge-tournaments/assets/css/phases.less');

        Loader::instance()->addScript('modules/forge-tournaments/assets/scripts/forge-tournaments.js');
        Loader::instance()->addScript('modules/forge-tournaments/assets/scripts/slotassignment.js');

        // frontend
        App::instance()->tm->theme->addScript($this->url().'assets/scripts/forge-tournaments.js', true);

        App::instance()->tm->theme->addScript(CORE_WWW_ROOT."ressources/scripts/externals/jquery.js", true, 0);
        App::instance()->tm->theme->addScript(CORE_WWW_ROOT.'ressources/scripts/externals/tooltipster.bundle.min.js', true);
        App::instance()->tm->theme->addScript(CORE_WWW_ROOT."ressources/scripts/forms.js", true);
        App::instance()->tm->theme->addScript(WWW_ROOT."modules/forge-tournaments/assets/scripts/jquery.connections.js", true);
        App::instance()->tm->theme->addScript(WWW_ROOT."modules/forge-tournaments/assets/scripts/tournament-helpers.js", true);


        App::instance()->tm->theme->addStyle(MOD_ROOT.'forge-tournaments/assets/css/forge-tournaments.less');
        App::instance()->tm->theme->addStyle(MOD_ROOT.'forge-tournaments/assets/css/bracket.less');
        App::instance()->tm->theme->addStyle(CORE_WWW_ROOT.'ressources/css/externals/tooltipster.bundle.min.css');

        API::instance()->register('forge-tournaments', [$this, 'apiAdapter']);

        // Prevent too many accesses to db by keeping the instances in the Memory
        PoolRegistry::instance()->add('tournament', new HierarchicalEntityPool('\\Forge\\Modules\\ForgeTournaments\\Tournament', 32));
        PoolRegistry::instance()->add('phase', new HierarchicalEntityPool('\\Forge\\Modules\\ForgeTournaments\\Phase', 64));
        PoolRegistry::instance()->add('group', new HierarchicalEntityPool('\\Forge\\Modules\\ForgeTournaments\\Group', 128));
        PoolRegistry::instance()->add('encounter', new HierarchicalEntityPool('\\Forge\\Modules\\ForgeTournaments\\Encounter', 256));
        PoolRegistry::instance()->add('match', new HierarchicalEntityPool('\\Forge\\Modules\\ForgeTournaments\\Match', 512));
        PoolRegistry::instance()->add('collectionitem', new EntityPool('\\Forge\\Core\\Classes\\CollectionItem', 1024));

        \registerModifier('Forge/Core/RelationDirectory/collectRelations', '\Forge\Modules\ForgeTournaments\PhaseCollection::relations');
        \registerModifier('Forge/Core/RelationDirectory/collectRelations', '\Forge\Modules\ForgeTournaments\NodaDataCollection::relations');

        \registerEvent(FORGE_TOURNAMENT_HOOK_NS . '/RegisterIPhaseType', '\Forge\Modules\ForgeTournaments\PhaseCollection::registerSubTypes');
        \registerEvent(FORGE_TOURNAMENT_HOOK_NS . '/RegisterIParticipantType', '\Forge\Modules\ForgeTournaments\ParticipantCollection::registerSubTypes');
        
        PhaseRegistry::instance()->prepare();
        ParticipantRegistry::instance()->prepare();

        PhaseBuilder::instance();

        // if is admin, reorganize the menu
        ModifyHandler::instance()->add(
            'modify_manage_navigation',
            [$this, 'navigationModification']
        );

        ModifyHandler::instance()->add(
            'modify_user_metafields',
            [$this, 'modifyUserFields']
        );

        ModifyHandler::instance()->add(
            'modify_collection_listing_title',
            [$this, 'modifyTournamentListingTitle']
        );
    }

    public function modifyTournamentListingTitle($title, $item) {
        if($item->getType() == 'forge-tournaments') {
            $eventId = $item->getMeta('event');
            if(is_numeric($eventId)) {
                $event = new CollectionItem($eventId);
                return $event->getMeta('title'). ' - '.$title;
            }
        }
    }

    public function modifyUserFields($fields) {
        $fields[] = [
            'key' => 'game_pubg',
            'label' => i('PUBG Username'),
            'type' => 'text',
            'required' => false,
            'position' => 'right'
        ];
        $fields[] = [
            'key' => 'game_steam',
            'label' => i('Steam Profile Name'),
            'type' => 'text',
            'required' => false,
            'position' => 'right'
        ];
        $fields[] = [
            'key' => 'game_battlenet',
            'label' => i('Battle.Net ID'),
            'type' => 'text',
            'required' => false,
            'position' => 'right'
        ];
        $fields[] = [
            'key' => 'game_lol',
            'label' => i('League of Legends Name'),
            'type' => 'text',
            'required' => false,
            'position' => 'right'
        ];
        return $fields;
    }

    public function navigationModification($navigation) {

        $navigation->add(
            'allocate', 
            i('Tournament System', 'forge-tournaments'),
            Utils::getUrl(['manage']),
            'leftPanel',
            'whatshot'
        );

        $navigation->reorder('leftPanel', 'allocate', 1);

        $elementsToMove = [
            'forge-tournaments',
            'forge-tournaments-phase',
            'forge-tournaments-encounter',
            /*'forge-tournaments-participant',*/
            'forge-tournaments-group',
            'forge-tournaments-match'
        ];

        $dontAdd = [
            'forge-tournaments-phase',
            /*'forge-tournaments-encounter',*/
            'forge-tournaments-group',
            'forge-tournaments-match',
            'forge-tournaments-participant'
        ];

        foreach($elementsToMove as $r) {
            $navigation->removeFromCollections($r);
        }
        foreach($elementsToMove as $a) {
            if(in_array($a, $dontAdd)) {
                continue;
            }
            $collection = App::instance()->cm->getCollection($a);
            $navigation->add(
              $collection->getPref('name'),
              $collection->getPref('title'),
              Utils::getUrl(array('manage', 'collections', $collection->getPref('name'))),
              'leftPanel',
              false,
              'allocate'
            );
        }

        return $navigation;
    }

    public function install() {
        if(Settings::get($this->name . ".installed")) {
            return;
        }
        Auth::registerPermissions($this->permission);

        $api_collections = [PhaseCollection::COLLECTION_NAME, ParticipantCollection::COLLECTION_NAME];

        $admins = Group::getByName('Administratoren');
        foreach($api_collections as $name) {
            Auth::registerPermissions('api.collection.' . $name . '.read');
            // it is not sure, that this group exists...
            if(! is_null($admins)) {
                $admins->grant(Auth::getPermissionID('api.collection.' . $name . '.read'));
            }
        }


        Settings::set($this->name . ".installed", 1);
    }

    public function apiAdapter($data) {
    }

}

?>