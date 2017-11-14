<?php

namespace Forge\Modules\ForgeTournaments\Data;

use Forge\Modules\ForgeTournaments\Interfaces\IDataSchema;

use Forge\Modules\ForgeTournaments\FieldAccessLevel;

class DataSchema implements IDataSchema {

    private $field_definitions = [];

    public function __construct(array $field_definitions) {
        $this->field_definitions = $field_definitions;
    }

    public function getFieldsForAccessLevel($access) {
        return array_filter($this->field_definitions, function($field) use ($access) {
            return $field['access'] >= $access;
        });
    }

}