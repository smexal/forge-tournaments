<?php

namespace Forge\Core\Classes;

use \Forge\Core\App\App;

class CollectionItem {
    public $id = null;

    public function __construct($id) {
        $this->id = $id;
    }

    public function getCollection() {}

    public function getType() {}

    public function url() {}

    public function absUrl() {}

    public function slug() {}

    public function getSlug() {}

    public function getName() {}

    public function getAuthor() {}

    public function getCreationDate() {}

    public function getMeta() {}

    public function updateMeta() {}

    public function deleteMeta() {}

    public function setMeta() {}

      public function insertMeta() {}

    public function isPublished() {}

    public function render() {}
}

