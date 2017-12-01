<?php

namespace Forge\Modules\ForgeTournaments\Data;

use Forge\Core\Traits\Singleton;

use Forge\Modules\ForgeTournaments\Data\SchemaProvider;

class SchemaLoader {
    use Singleton;

    private $loaded = false;
    
    public function load() {
        if($this->loaded) {
            return;
        }
        $schema_provider = SchemaProvider::instance();
        $base = FORGE_TOURNAMENTS_SCHEMAS_DIR;
        $files = glob($base . '*.php');
        if (count($files) > 0) {
            foreach ($files as $file) {
                if (substr($file, 0, 1) == '.') {
                    continue;
                }

                list($schema_id, $schema_name, $node_types, $field_definition) = require_once $file;
                $schema = new DataSchema($schema_id, $node_types, $field_definition);
                $schema->setName($schema_name);
                $schema_provider->addSchema($schema);
            }
        }
        $this->loaded = true;
    }

}