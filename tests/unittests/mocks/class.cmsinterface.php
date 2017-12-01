<?php
/*
 * MOCK
 */
namespace Forge\Modules\Quests\CMS;

use Forge\Core\Classes\CollectionItem;

abstract class CMSInterface {

    public static function getCollectionItem($id, $name) {
        return new CollectionItem($id);
    } 

}
