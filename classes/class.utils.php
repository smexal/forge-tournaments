<?php

namespace Forge\Modules\ForgeTournaments;

use \Forge\Modules\ForgeTournaments\Phases\PhaseRegistry;

abstract class Utils {


    public static function getPhaseStates() {
        return \triggerModifier(FORGE_TOURNAMENT_NS . '/phase_states', [
            State::FRESH     => \i('Fresh', 'forge-tournaments'),
            State::OPEN      => \i('Open', 'forge-tournaments'),
            State::READY     => \i('Ready', 'forge-tournaments'),
            State::RUNNING   => \i('Running', 'forge-tournaments'),
            State::FINISHED  => \i('Finished', 'forge-tournaments'),
            State::COMPLETED => \i('Completed', 'forge-tournaments')
        ]);
    }

    public static function getPhaseTypes() {
        $phases = PhaseRegistry::instance()->getAll();
        $list = [];
        foreach($phases as $key => $phase) {
            $list[$key] = $phase::name();
        }
        return $list;
    }

    public static function collection($args, $value='') {
        static $defaults = [
            'state' => 'all',
            'maxtags' => false,
        ];

        $args = array_merge($defaults, $args);
        $url = API::getAPIURL();
        $url .= '/collections/' . $args['collection'] . '?s=' . $args['state'] .'&q=%%QUERY%';
        
        $c_ids = is_array($value) ? $value : explode(',', $value);
        if($value && count($c_ids)) {
            $c_items = App::instance()->cm->getCollection($args['collection'])->getItems($c_ids); 

            $c_items = array_map(function($item) {
                return $item->getName();
            }, $c_items);
        } else {
            $c_items = [];
        }

        $args['data_attrs'] = [
            'maxtags' => $args['maxtags'],
            'getter' => $url,
            'tag-labels' => htmlspecialchars(json_encode($c_items)),
            'getter-convert' => 'forge_api.collections.onlyItems',
            'getter-value' => 'id',
            'getter-name' => 'name'
        ];

        unset($args['collection']);
        return static::tags($args, $value);
    }
}