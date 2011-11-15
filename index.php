<?php


$config = array(
  '',       // host name ('localhost' | '' | IP | name)
  'brian',  // db user name
  '',       // db user password
  'docs',   // db name
  5432,     // port number (3306/mysql | 5432/pgsql | 443/ssl)
  'pgsql'   // db type (mysql | pgsql | couchdb | mongodb | sqlite | remote)
);



require 'lib/Structal.php';
require 'lib/Moor.php';
require 'lib/Mullet.php';




// Add your models and controllers

class Page extends Model {
  
   static $id = array( 'type'=>'Integer', 'key'=>true );
   static $name = array( 'type'=>'String' );
   static $body = array( 'type'=>'String' );

}


class Pages extends Controller {
  
  static $item;
  
  function init() {
    self::$item = '';
    if ('my.ip.addr' != $_SERVER['REMOTE_ADDR'] && !('get' == strtolower($_SERVER['REQUEST_METHOD']))) exit;
  //  Task::bind( 'update', 'render' );
//    Task::bind( 'update', 'addChange' );
//    Task::bind( 'delete', 'addChange' );
  //  Task::bind( 'create', 'addChange' );
  //  Task::bind( 'read', 'render', 'before' );
  }
  
  function html($data) {
    echo $data;
  }
  
  function render() {
    header('HTTP/1.1 200 OK');
    header('Content-Type: application/json');
    self::html(self::$item);
    return self;
  }
  
  function addOne( $task ) {
    
  }
  
  function addAll() {
    
  }
  
  function renderCount() {
    
  }
  
  function beforeAction() {
    trigger_before( strtolower($_SERVER['REQUEST_METHOD']) );
  }
  
  function afterAction() {
    trigger_after( strtolower($_SERVER['REQUEST_METHOD']) );
  }

}



function index() {
  require 'lib/Mustache.php';
  $m = new Mustache;
  session_start();
  $params = array();
  if (isset($_SESSION['current_user']))
    $params['username'] = $_SESSION['current_user'];
  echo $m->render(file_get_contents('tpl/index.html'),$params);
}


/*
if (!in_array(strtolower($_SERVER['REQUEST_METHOD']),array('put','delete')))
  Moor::route('/@class/@method', '@class(uc)::@method(lc)');
Moor::route('/@class/:id([0-9A-Za-z_-]+)', '@class(uc)::'.strtolower($_SERVER['REQUEST_METHOD']));
Moor::route('/@class', '@class(uc)::'.strtolower($_SERVER['REQUEST_METHOD']));
Moor::route( '*', 'index' );
Moor::run();
*/