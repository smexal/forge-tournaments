<?php

namespace Forge\Modules\ForgeTournaments\Calculations;

class Sorting {
    const SORT_POINTS_ASC='points_asc';
    const SORT_POINTS_DESC='points_desc';
    const SORT_RESULT_ASC='result_asc';
    const SORT_RESULT_DESC='result_desc';

    private $sortby;

    public function __construct($sortby=Sorting::SORT_RESULT_DESC) {
        $this->sortby = $sortby;
    }

    public function sort($data) {
        $sorted = [];
        $call = [$this, 'sort_' . $this->sortby];
        if(!is_callable($call)) {
            throw new \Exception("Cant sort by $this->sortby");
        }
        uasort($data, $call);

        return $data;
    }

    public function _sort_subvalue($a, $b, $subkey) {
        if ($a[$subkey] == $b[$subkey]) {
            return 0;
        } 
        
        if($a[$subkey] < $b[$subkey]) {
            return -1;
        }

        return 1;
    }

    public function sort_points_asc($a, $b) {
        return $this->_sort_subvalue($a, $b, 'points');
    }

    public function sort_points_desc($a, $b) {
        return -1 * $this->_sort_subvalue($a, $b, 'points');
    }

}