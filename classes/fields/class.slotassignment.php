<?php

namespace Forge\Modules\ForgeTournaments\Fields;

use Forge\Core\App\App;

class SlotAssignment {

    public static function load($item, $field, $lang) {
        $args['prepare_load'] = isset($args['prepare_load']) ? $args['prepare_load'] : ['\\Forge\\Modules\\ForgeTournaments\\Fields\\SlotAssignment', 'loadGroups'];
        
        $value = $item->getMeta($field['key'], $lang);
        if ($value) {
            $value = [
                'items' => static::valueMapping(json_decode(json_encode($value), true))
            ];
        }

        if (!$value) {
            $value = isset($field['default']) ? $field['default'] : [
                'items' => [],
            ];
        }
        $value['slot_count'] = $field['slot_count'];

        $item_count = count($value['items']);
        // There are too many items in the array, remove those who are too much
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
        $value = json_decode(rawurldecode($value), true);
        if (isset($args['prepare_save']) && is_callable($args['prepare_save'])) {
            $value = call_user_func_array($args['prepare_save'], [$value, $item, $field, $lang]);
        }
        $value = array_map(function($item) {
            return $item['value'];
        }, $value);

        $value = json_encode($value);
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

    public static function valueMapping($values) {
        $pool = \Forge\Modules\ForgeTournaments\PoolRegistry::instance()->getPool('collectionitem');
        foreach($values as $idx => $value) {
            if(is_numeric($value) && ($instance = $pool->getInstance($value, $value))) {
                $label = $instance->getName();
            } else {
                $label = sprintf(\i('Open', 'forge-tournaments'), $idx + 1);
            }

            $values[$idx] = [
                'slotid' => $idx,
                'value' => $value,
                'label' => $label
            ];
        }
        return $values;
    }

    public static function loadGroups($value, $item, $field, $lang) {
        return $value;
    }

    public static function saveGroups($value, $item, $field, $lang) {
        return $value;
    }

    public static function prepareGroup($args, $value) {
        $group_size = isset($args['group_size']) ? $args['group_size'] : count($value['items']);
        $args['groups'] = static::groupSlotsForGroup($value['items'], $group_size);
        return $args;
    }
    
    public static function prepareKO($args, $value) {
        $args['encounters'] = static::groupSlotsForKO($value['items']);
        return $args;
    }

    public static function groupSlotsForGroup($slots, $group_size=-1) { 
        $slot_count = count($slots);
        $group_size = $group_size == -1 ? $slot_count : $group_size;
        $group_count = ceil($slot_count / $group_size);
        
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