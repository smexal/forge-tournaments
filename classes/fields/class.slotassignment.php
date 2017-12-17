<?php

namespace Forge\Modules\ForgeTournaments\Fields;

use Forge\Core\App\App;

class SlotAssignment {

    public static function load($item, $field, $lang) {
        $args['prepare_load'] = isset($args['prepare_load']) ? $args['prepare_load'] : ['\\Forge\\Modules\\ForgeTournaments\\Fields\\SlotAssignment', 'loadGroups'];
        
        $value = $item->getMeta($field['key'], $lang);
        if($value) {
            $value = json_decode(rawurldecode($value), true);
            $value = [
                'items' => $value
            ];
        }

        if (!$value) {
            $value = isset($field['default']) ? $field['default'] : [
                'items' => [],
            ];
        }
        $value['slot_count'] = isset($value['slot_count']) && $value['slot_count'] ? $value['slot_count'] : $field['slot_count'];

        // There are too many items in the array, remove those who are too much
        $item_count = count($value['items']);
        if($item_count > $value['slot_count']) {
            $offset = $value['slot_count'];
            array_splice($value['items'], $offset);
        
        // There are not enough items in the array, append missing slots
        } elseif($item_count < $value['slot_count']) {
            $length = $value['slot_count'] - $item_count;
            
            $fill = [];
            $counter = $item_count;
            for($i = 0; $i < $length; $i++) {
                $fill[] = [
                    'slotid' => $counter++,
                    'value' => null,
                    'label' => sprintf(\i('Open', 'forge-tournaments'), $counter)
                ];
            }

            $value['items'] = array_merge($value['items'], $fill);
        }

        if (isset($args['prepare_load']) && is_callable($args['prepare_load'])) {
            $value = call_user_func_array($args['prepare_load'], [$value, $item, $field, $lang]);
        }
        return $value;
    }

    public static function save($item, $field, $value, $lang) {
        $value = $value;
        if (isset($args['prepare_load']) && is_callable($args['prepare_load'])) {
            $value = call_user_func_array($args['prepare_load'], [$value, $item, $field, $lang]);
        }
        $item->updateMeta($field['key'], $value, $lang);

    }

    public static function render($args, $value) {
        $args['name'] = isset($args['name']) ? $args['name'] :  $args['key'];
        $args['sa_tpl'] = isset($args['sa_tpl']) ? $args['sa_tpl'] : FORGE_TOURNAMENTS_DIR . 'templates/slotassignment-groups';
        $args['prepare_template'] = isset($args['prepare_template']) ? $args['prepare_template'] : ['\\Forge\\Modules\\ForgeTournaments\\Fields\\SlotAssignment', 'prepareGroup'];

        $path = dirname($args['sa_tpl']);
        $file = basename($args['sa_tpl']);

        if (isset($args['prepare_template']) && is_callable($args['prepare_template'])) {
            $args = call_user_func_array($args['prepare_template'], [$args, $value]);
        }

        $args['value'] = $value;

        $args['pool_source_selector'] = rawurlencode($args['pool_source_selector']);
        $args['data_label_open'] = \i('Open', 'forge-tournaments');
        $args['slot_prefix'] = \i('Slot ', 'forge-tournaments');
        $args['value_json'] = rawurlencode(json_encode($value));

        $args['slot_assignment'] = App::instance()->render($path, $file, $args);
        return App::instance()->render(
            MOD_ROOT.'forge-tournaments/templates/fields',
            'slotassignment',
            $args
        );
    }

    public static function loadGroups($value, $item, $field, $lang) {
        return $value;
    }

    public static function saveGroups($value, $item, $field, $lang) {
        return $value;
    }

    public static function prepareGroup($args, $value) {
        $group_count = isset($args['group_count']) ? $args['group_count'] : 1;
        $args['groups'] = static::groupSlotsForGroup($value['items'], $group_count);
        return $args;
    }
    
    public static function prepareKO($args, $value) {
        $args['encounters'] = static::groupSlotsForKO($value['items']);
        return $args;
    }

    public static function groupSlotsForGroup($slots, $group_count=1) { 
        $slot_count = count($slots);
        $group_size = ceil($slot_count / $group_count);
        
        $groups = [];
        for($i = 0; $i < $group_count; $i++) {
            $start = $i * $group_size;
            $length = $group_size;
            $groups[$i] = array_slice($slots, $start, $length, true);
        }
        return $groups;
    }
    public static function groupSlotsForKO($slots) { 
        $slot_count = count($slots);
        $encounter_count = ceil($slot_count / 2);
        
        $encounters = [];
        for($i = 0; $i < $encounter_count; $i++) {
            $start = $i * 2;
            $length = 2;
            $encounters[$i] = [
                'first' => $slots[$start],
                'second' => $slots[$start + 1]
            ];
        }
        return $encounters;
    }
}