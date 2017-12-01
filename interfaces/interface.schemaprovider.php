<?php

namespace Forge\Modules\ForgeTournaments\Interfaces;

interface ISchemaProvider {

    public function addSchema(IDataSchema $schema);
        
    public function getAllSchemas();
        
    public function getSchemasForType($type);
        
    public function getSchema($id);
        

}
