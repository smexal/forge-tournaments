<?php

namespace Forge\Modules\ForgeTournaments\Data;

use Forge\Modules\ForgeTournaments\Interfaces\IDataSchema;

use Forge\Modules\ForgeTournaments\FieldAccessLevel;

class DataSchema implements IDataSchema {
    private $id;
    private $name;
    private $node_type = [];
    private $field_definitions = [];

    public function __construct($id, $node_types, array $field_definitions) {
        $this->id = $id;
        $this->node_types = $node_types;
        $this->field_definitions = $field_definitions;
    }


    public function getID() {
        return $this->id;
    }

    public function getName() {
        return $this->name;
    }
    
    public function setName($name) {
        $this->name = $name;
    }

    public function getNodeTypes() {
        return $this->node_types;
    }

    public function isNodeTypeSupported($node_type) {
        return in_array($node_type, $this->node_types);
    }

    public function getFieldsForAccessLevel($access) {
        return array_filter($this->field_definitions, function($field) use ($access) {
            return $field['access'] >= $access;
        });
    }

    public function getFields() {
        return $this->field_definitions;
    }

}