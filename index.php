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
require 'lib/Mullet.php';




// Add your models and controllers

class Page extends Model {}


class Pages extends Controller {
  
  static $item;
  
  function init() {
    self::$item = '';
    Page::bind( 'create', 'addChange' );
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
  

}


return new Pages;





