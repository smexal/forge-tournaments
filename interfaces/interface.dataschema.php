<?php

namespace Forge\Modules\ForgeTournaments\Interfaces;

interface IDataSchema {
    
    public function getNodeTypes();
    
    public function getFields();

    public function getFieldsForAccessLevel($access);
    
    public function isNodeTypeSupported($node_type);
    
    

}