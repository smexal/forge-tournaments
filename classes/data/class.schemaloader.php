<?php

namespace Forge\Modules\ForgeTournaments\Data;

use Forge\Core\Traits\Singleton;

use Forge\Modules\ForgeTournaments\Data\SchemaProvider;

class SchemaLoader {
    use Singleton;

    public function load() {
        $schema_provider = SchemaProvider::instance();
        $base = FORGE_TOURNAMENTS_SCHEMAS_DIR;
        $files = glob($base . '.*\.php');
        if (count($files) > 0) {
            foreach ($files as $file) {
                if (substr($file, 0, 1) == '.') {
                    continue;
                }

                list($schema_id, $field_definition) = require_once $base . $file;
                $schema = new DataSchema($field_definition);
                $schema_provider->addSchema($schema_id, $schema);
            }
        }
    }

}