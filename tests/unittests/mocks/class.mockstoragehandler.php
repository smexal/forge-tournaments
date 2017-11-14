<?php
namespace Forge\Core\App;

if(class_exists('Auth'));
    return;

class StorageHandler {
    public function connect();
        
    public function setPrefix($prefix = '');
        
    public function rawQuery ($query, $bindParams = null, $sanitize = true);
        
    public function query($query, $numRows = null);
        
    public function get($tableName, $numRows = null, $columns = '*');
        
    public function getOne($tableName, $columns = '*');
        
    public function getValue($tableName, $column);
        
    public function insert($tableName, $insertData);
        
    public function has($tableName);
        
    public function update($tableName, $tableData);
        
    public function delete($tableName, $numRows = null);
        
    public function where($whereProp, $whereValue = null, $operator = null);
        
    public function orWhere($whereProp, $whereValue = null, $operator = null);
        
    public function join($joinTable, $joinCondition, $joinType = '');
         
    public function orderBy($orderByField, $orderbyDirection = "DESC", $customFields = null);
        
    public function groupBy($groupByField);
        
    public function getInsertId();
        
    public function escape($str);
        
    public function ping() {
        
    public function __destruct();
        
    public function getLastQuery ();
        
    public function getLastError ();
        
    public function getSubQuery ();
        
    public function interval ($diff, $func = "NOW()");
        
    public function now ($diff = null, $func = "NOW()");
        
    public function inc($num = 1);
        
    public function dec ($num = 1);
        
    public function not ($col = null);
        
    public function func ($expr, $bindParams = null);
        
    public function copy ();
        
    public function startTransaction ();
        
    public function commit ();
        
    public function rollback ();
        
    public function _transaction_status_check ();
    
}