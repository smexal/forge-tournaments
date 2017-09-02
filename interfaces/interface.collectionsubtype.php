<?php

namespace Forge\Modules\ForgeTournaments\Interfaces;

use \Forge\Core\Classes\CollectionItem;

interface ICollectionExtension {
    public function fields($item=null) : array;
    public function modifyFields(array $fields, $item=null) : array;
    public function render(CollectionItem $item) : string;
}

