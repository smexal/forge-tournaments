<?php

namespace Forge\Modules\ForgeTournaments\Fields;

use Forge\Core\Classes\Fields;

abstract class FieldRenderer {

    public static function renderFields($fields) {
        $html = '';
        foreach($fields as $field) {
            $value = isset($field['value']) ? $field['value'] : '';
            $html .= Fields::build($field, $value);
        }
        return $html;
    }
}