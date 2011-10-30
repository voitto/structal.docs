<?php


define( 'DATABASE_ENGINE',    'pgsql'); // mysql | pgsql | couchdb | mongodb | sqlite
define( 'DATABASE_USER',      'brian');
define( 'DATABASE_PASSWORD',  '');
define( 'DATABASE_NAME',      'todos');
define( 'DATABASE_HOST',      ''); // 'localhost' | '' | IP | name
define( 'DATABASE_PORT',      5432); // 3306/mysql | 5432/pgsql | 443



require 'lib/Structal.php';
require 'lib/Moor.php';
require 'lib/Mullet.php';




// Add your models and controllers






function index() {
  require 'lib/Mustache.php';
  $m = new Mustache;
  session_start();
  $params = array();
  if (isset($_SESSION['current_user']))
    $params['username'] = $_SESSION['current_user'];
  echo $m->render(file_get_contents('tpl/index.html'),$params);
}



if (!in_array(strtolower($_SERVER['REQUEST_METHOD']),array('put','delete')))
  Moor::route('/@class/@method', '@class(uc)::@method(lc)');
Moor::route('/@class/:id([0-9A-Za-z_-]+)', '@class(uc)::'.strtolower($_SERVER['REQUEST_METHOD']));
Moor::route('/@class', '@class(uc)::'.strtolower($_SERVER['REQUEST_METHOD']));
Moor::route( '/', 'index' );
Moor::run();
