<?php

namespace Forge\Modules\ForgeTournaments\Facade;

use Forge\Core\Classes\CollectionItem as CollectionItem;
use Forge\Modules\ForgeTournaments\PoolRegistry as PoolRegistry;
use Forge\Modules\ForgeTournaments\Calculations\Nodes\CollectionNode\CollectionTree;

abstract class Tournament {
    /**
     * Simple getters
     */
    public static function getTournament($item_ref) {
        return static::getPoolInstance('tournament', $item_ref);
    }

    public static function getPhase($item_ref) {
        return static::getPoolInstance('phase', $item_ref);
    }

    public static function getGroup($item_ref) {
        return static::getPoolInstance('group', $item_ref);
    }

    public static function getEncounter($item_ref) {
        return static::getPoolInstance('encoounter', $item_ref);
    }

    public function getNodesOfParticipant($trounament_id, $participant_id) {
        $tree = new CollectionTree($phase->getItem());
    }

    /**
     * This only works for hierarchicalentities
     */
    public static function getPoolInstance($type, $item_ref) {
        if(is_numeric($item_ref)) {
            $item = new CollectionItem($item_ref);
        } else if(is_object($item_ref)) {
            $item = $item_ref;
        } else {
            throw new \Exception("Can not load item. Provided reference has to be id of collectionitem or the collectionitem itself");
        }
        return PoolRegistry::instance()->getPool($type)->getInstance($item->getID(), $item);
    }

}