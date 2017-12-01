<?php

// Fake MySQLi DB
class MockStorageHandler {
    public $data = [];
    public $cond = [];
    public $sort = [];

    public function connect() {}
        
    public function setPrefix($prefix = '') {}
        
    public function rawQuery ($query, $bindParams = null, $sanitize = true) {}
        
    public function query($query, $numRows = null) {}
        
    public function get($tableName, $numRows = null, $columns = '*') {
        $table = $this->data[$tableName];
        $conds = $this->conds;
        $sort = $this->sort;
        $list = array_filter($table, function($item) use($conds) {
            foreach($conds as $key => $value) {
                if($item[$key] != $value) {
                    return false;
                }
            }
            return true;
        });
        if(!count($this->sort)) {
            return $list;
        }
        $sort_key = array_keys($sort)[0];
        $sort_val = $sort[$sort_key];
        usort($list, function($a, $b) use ($sort) {
            foreach($sort as $key => $dir) {
                if($a[$key] < $b[$key]) {
                    return $dir == 'ASC' ? -1 : 1;
                } else if($a[$key] > $b[$key]) {
                    return $dir == 'ASC' ? 1 : -1;
                }
            }
            return 0;
        });
        return $list;
    }
        
    public function getOne($tableName, $columns = '*') {}
        
    public function getValue($tableName, $column) {}
        
    public function insert($tableName, $insertData) {
        if(!isset($this->data[$tableName])) {
            $this->data[$tableName] = [];
        }
        if(!isset($insertData['changed'])) {
            $insertData['changed'] = time();
        }
        $this->data[$tableName][] = $insertData;
    }

    public function insertMultiple($tableName, $insertData) {
        if(!isset($this->data[$tableName])) {
            $this->data[$tableName] = [];
        }
        $idx = 0;
        foreach($insertData as &$data) {
            $data['changed'] = time() + $idx;
            $idx++;
        }
        $this->data[$tableName] = array_merge($this->data[$tableName], $insertData);
    }
        
    public function has($tableName) {}
        
    public function update($tableName, $tableData) {}
        
    public function delete($tableName, $numRows = null) {}
        
    public function where($whereProp, $whereValue = null, $operator = null) {
        $this->conds[$whereProp] = $whereValue;
    }
        
    public function orWhere($whereProp, $whereValue = null, $operator = null) {}
        
    public function join($joinTable, $joinCondition, $joinType = '') {}
         
    public function orderBy($orderByField, $orderbyDirection = "DESC", $customFields = null) {
        $this->sort[$orderByField] = strtoupper($orderbyDirection);
    }
        
    public function groupBy($groupByField) {}
        
    public function getInsertId() {}
        
    public function escape($str) {}
        
    public function ping() {}
        
    public function __destruct() {}
        
    public function getLastQuery () {}
        
    public function getLastError () {}
        
    public function getSubQuery () {}
        
    public function interval ($diff, $func = "NOW()") {}
        
    public function now ($diff = null, $func = "NOW()") {}
        
    public function inc($num = 1) {}
        
    public function dec ($num = 1) {}
        
    public function not ($col = null) {}
        
    public function func ($expr, $bindParams = null) {}
        
    public function copy () {}
        
    public function startTransaction () {}
        
    public function commit () {}
        
    public function rollback () {}
        
    public function _transaction_status_check () {}
    
}