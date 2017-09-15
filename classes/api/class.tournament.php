<?php

namespace Forge\Core\App\Api;

use \Forge\Core\App\App;
use \Forge\Core\App\API;
use \Forge\Core\App\CollectionManager;
use \Forge\Core\Abstracts\APIFacade;
use \Forge\Core\Classes\Utils;

/**
 * This class allows reading out collections in a crud manner via an APIFacade.
 * 
 * In order to allow access to a new collection the permissians have to be registered
 * via the Permissions / Authentication classes of the forge core.
 */
class Tournament extends APIFacade {
  public $trigger = 'forge-tournaments';

  protected static $uri_mapping = [
    'method' => null,
    'p1'   => null,
    'p2'   => null,
    'p3'   => null
  ];

  protected static $request_params = [
    // QUERY
    'q' => null,
    // STATE
    's' => 'published',
    // ORDER
    'o'  => 'name',
    // ORDER Direction
    'od' => 'ASC',
    // LIMIT Start
    'ls' => '0',
    // LIIMIT End
    'll' => '30',
    // INCLUDE extra infos about the items
    'e' => ''
  ];

  protected function __construct() {}

  public function call($request) {
    header("Content-Type: text/html");

    $query = Utils::extractParams(static::$uri_mapping, $request['query']);
    $data = $this->extractData($request['data']);
    $method = $request['method'];
    $call = [$this, 'get' . ucfirst($method)];

    if(!is_callable($call)) {
      API::error(405, i('Undefined collection method', 'forge'));
    }

    $response = \call_user_func_array($call, [$query, $data]);
    die(json_encode($response));
  }

  public function extractData($data) {
    $data = array_merge(static::$request_params, $data);
    $data['e'] = explode(',', $data['e']);
    return $data;
  }

  public function getPhaseParticipants($query, $data) {
    $phase_id = $data['p1'];
  }

}