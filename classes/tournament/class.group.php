<?php

namespace Forge\Modules\ForgeTournaments;

use \Forge\Core\App\App;
/**
 * The Encounter determines which participant
 * will be victorius
 */
class Group extends HierarchicalEntity {
    protected $item;

    /**
     * @param mixed $item The Related CollectionItem
     */
    public function __construct($item) {
        $this->item = $item;
    }

    public function addEncounters($encounters) {
        foreach($encounters as $encounter) {
            $encounter->setParent($this);
        }
    }

    public function getEncounters() {
        $children = $this->getChildren();
        foreach($children as $key => $child) {
            $children[$key] = PoolRegistry::instance()->getPool('encounter')->getInstance($child->getID(), $child);
        }
        return $children;
    }

    public function getGroupSize() {
        return $this->getSlotAssignement->numSlots();
    }
}
