<?php

/**
 * Structal: a Ruby-like language in PHP
 *
 * PHP version 4.3.0+
 *
 * Copyright (c) 2010, Brian Hendrickson
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 * 
 *
 * @package   Structal
 * @author    Brian Hendrickson <brian@megapump.com>
 * @copyright 2003-2010 Brian Hendrickson <brian@megapump.com>
 * @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 * @version   Release: @package_version@
 * @link      http://structal.org
 */

/**
 * Mapper
 *
 * connects the current URI to a Route,
 * establishing the request variable names
 * e.g. my_domain/:resource/:id would map
 * values into $req->resource and $req->id
 *
 * <code>
 *
 * $req = new Mapper();
 *
 * </code>
 *
 * @package   Structal
 * @author    Brian Hendrickson <brian@megapump.com>
 * @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link      http://structal.org/mapper
 */

class Mapper {
  
  /**
   * current URI
   * @var string
   */
  var $uri;

  /**
   * days til SESSION cookie expires
   * @var string
   */

  var $cookiedays;

  /**
   * domain in URI
   * @var string
   */

  var $domain;
  
  /**
   * path after domain in URI
   * @var string
   */
  var $path;
  /**
   * base URI
   * @var string
   */
  var $base;

  /**
   * unmolested regex parts of the URI
   * @var string[]
   */
  var $values;
  
  /**
   * URI parameter names and values
   * @var string[]
   */
  var $params;

  /**
   * matched Route object
   * @var Route
   */
  var $activeroute;

  /**
   * list of connected Route objects
   * @var Route[]
   */
  var $routes;

  /**
   * list of Groups
   * @var string[]
   */
  var $groups;
  
  /**
   * list of public methods
   * @var string[]
   */
  var $allowed_methods;

  /**
   * parameters to (silently) propagate
   * @var string[]
   */
  var $persisted_vars;

  /**
   * path to views
   * @var string
   */
  var $template_path;
  
  /**
   * path to layouts
   * @var string
   */
  var $layout_path;

  /**
   * true if an error has been raised
   * @var boolean
   */
  var $error;

  /**
   * url prefix for sub-sites
   * @var string
   */
  var $prefix;

  /**
   * openid status
   * @var boolean
   */
  var $openid_complete;
  
  /**
   * contents of error message
   * @var string
   */
  var $error_string;
  
  /**
   * database Record object for the current session
   * @var string
   */
  var $DbSession;
  
  var $templates_resource;

  function Mapper() {
    
    $this->params = array('');
    
    $this->prefix = '';
    
    $this->cookiedays = 30;

    $this->uri = $this->composite_uri();
    
    $this->setup();
    
    $this->routes = array();
    $this->persisted_vars = array();
    $this->allowed_methods = array();
    $this->groups = array();
    $this->template_path = '';
    $this->layout_path = '';
    $this->error = false;
    $this->openid_complete = false;
    $this->templates_resource = array();
  }
  
  function setup() {
    
    preg_match( "/^(https?:\/\/)([^\/]+)\/?[^\?]+?[\??]([-%\w\/\.]+)?/i", $this->uri, $this->values );
    
    if (!($this->values))
      preg_match( "/^(https?:\/\/)([^\/]+)\/?(([^\?]+))?/i", $this->uri, $this->values );
    
    $pos = strpos( $this->uri, "?" );
    
    if ( $pos > 0 )
      $this->base = substr( $this->uri, 0, $pos );
    else
      $this->base = $this->uri;
      
    if ( isset( $this->values[3] ) )
      $this->params = explode( '/', $this->values[3] );
    
    $qp = strpos($this->uri,"?");
    
    $end = 0 - (strlen($this->uri) - $qp);
    
    $lenbase = strlen($this->values[1]) + strlen($this->values[2]);
    
    if ($qp === false)
      $this->path = substr($this->uri, $lenbase);
    else
      $this->path = substr($this->uri, $lenbase, $end);

    if (!(strpos($this->params[(count($this->params)-1)],".") === false)) {
      $actionsplit = split("\.", $this->params[(count($this->params)-1)]);
      $this->client_wants = $actionsplit[1];
    }
    
    $expiry = 60*60*24*$this->cookiedays;
    
    if (environment('cookielife'))
      $expiry  = environment('cookielife');
    
    session_set_cookie_params( $expiry, $this->path );
    
    // XXX subdomain upgrade
    if (strpos($this->base,"twitter\/"))
      $this->path = $this->path.$this->prefix;
    
    if (!($this->values[2] == 'localhost'))
      $this->domain = $this->values[2];
    
    $paramstr = substr($this->uri,$qp+1);
    
    $urlsplit = split("http%3A//",$paramstr);
    
    if (count($urlsplit)>1)
      $paramstr = $urlsplit[0];

    if (isset($this->client_wants) && $qp > $lenbase) {
      $ext = $this->client_wants;
      if (!(strpos($paramstr,".".$ext) === false))
        $paramsplit = split("\.".$ext, $paramstr);
      if (isset($paramsplit) && count($paramsplit) == 2)
        $paramstr = $paramsplit[0].".".$ext.str_replace('/','%2F',str_replace(':','%3A',$paramsplit[1]));
    }
    
    if ($qp > $lenbase)
      $this->params = explode( '/', $paramstr);
    else
      $this->params = array('');
    
  }
  
  function handle_error( $errstr ) {
    $this->error = true;
    $this->params['error'] .= $errstr . "\n";
    trigger_before( 'handle_error', $this, $errstr );
  }
  
  function route_exists( $routename ) {
    foreach ( $this->routes as $r ) 
      if ( $routename == $r->name )
        return true;
    return false;
  }
  
  function url_for( $params, $altparams = NULL ) {
    $match = false;
    $route_match = NULL;
    
    if ( is_string( $params ) ) {
      // first var is a route name (or a URL)
      
      if (strstr($params,"http")) {
        return $params;
      }
      
      $routename = $params;
      $params = $altparams;
    }

    foreach ( $this->routes as $r ) {
      
      $vars = array();
      
      foreach ( $r->patterns as $pos => $str ) {
        if ( substr( $str, 0, 1 ) == ':' ) {
          $vars[substr( $str, 1 )] = $pos;
        }
      }

      if ( isset( $routename ) ) {
        if ( $routename == $r->name ) {
          // a named route was found
          if ($altparams == NULL)
            $params = $r->defaults;
          // XXX subdomain upgrade
          return $r->build_url( $params, $this->base, $this->prefix );
        }
//      } elseif ( is_array($params) && count( array_intersect( array_keys($vars), array_keys($params) ) ) == count( $vars ) && count($vars) == count($params) && count($r->patterns) == count($params) ) {
      } elseif ( is_array($params) && count( array_intersect( array_keys($vars), array_keys($params) ) ) == count( $vars ) && count($vars) == count($params)  ) {
        // every pattern in the route exists in the requested params
        // XXX subdomain upgrade
        return $r->build_url( $params, $this->base, $this->prefix );
      } else {
        // eh
      }
      
    }

    foreach ( $this->params as $paramkey=>$paramval ) {
      
      if ( is_integer( $paramkey ) )
        continue;
      
      $params[$paramkey] = $paramval;
      
      foreach ( $this->routes as $r ) {
        
        $vars = array();
        
        foreach ( $r->patterns as $pos => $str ) {
          if ( substr( $str, 0, 1 ) == ':' ) {
            $vars[substr( $str, 1 )] = $pos;
          }
        }
        
        if ( count( array_intersect( array_keys($vars), array_keys($params) ) ) == count( $vars ) && count($vars) == count($params) ) {
          // XXX subdomain upgrade
          return $r->build_url( $params, $this->base, $this->prefix );
        }
      
      } // end foreach routes
    
    } // end foreach params

  }
  
  function link_to( $params, $altparams = NULL ) {
    $url = $this->url_for( $params, $altparams );
    return "<a href=\"$url\">$url</a>";
  }
  
  function redirect_to( $params, $altparams = NULL ) {
    header( "Location: " . $this->url_for($params, $altparams) );
    exit;
  }

  function breadcrumbs() {
    $controller = $this->params['resource'];
    $links = array();
    $html = "";

    $links[] = '<a href="'. $this->base .'">Home</a>';
    
    if ( isset( $this->resource ) && ( $this->resource != 'introspection' ))
      if (pretty_urls())
        $links[] = '<a href="'. $this->base .''.$this->resource.'">'.ucwords($this->resource).'</a>';
      else
        $links[] = '<a href="'. $this->base .'?'.$this->resource.'">'.ucwords($this->resource).'</a>';
    
    if ( ($this->id != 0) && isset( $this->resource ) && ( $this->resource != 'introspection' ))
       $links[] = '<a href="'.$this->entry_url($this->id).'">Entry '.ucwords($this->id).'</a>';
    elseif ( isset( $this->resource )  && $this->new_url())
      $links[] = '<a href="'.$this->new_url().'">New '.classify($this->resource).'</a>';
    
    $html = "<span>";
    foreach ($links as $key=>$val) {
      if ($key > 0) {
        $html .= " | ";
      }
      $html .= $val;
    }
    $html .= "</span>";
    return $html;
  }
  
  function set_persisted_vars($arr) {
    if (is_array($arr))
      $this->persisted_vars = $arr;
  }
  
  function set_filter( $name, $func, $when = 'after' ) {
    aspect_join_functions( $func, $name, $when );
  }
  
  function set_action( $method ) {
    $this->allowed_methods[] = $method;
  }
  
  function set_param( $param, $value ) {
    if (is_array($param)) {
      $this->params[$param[0]][$param[1]] = $value;
    } else {
      $this->params[$param] = $value;
      $this->$param =& $this->params[$param];
    }
  }
  
  function set_layout_path( $path ) {
    $this->layout_path = $path;
  }
    
  function set_template_path( $path ) {
    $this->template_path = $path;
  }
  
  function feed_url() {
    $result = false;
    if (isset($this->action)&&in_array($this->action,array('login','email')))
      return $result;
    if (isset($this->resource)&&in_array($this->resource,array('introspection')))
      return $result;
    if (isset($this->resource))
      $result = is_file( $this->template_path . $this->resource . DIRECTORY_SEPARATOR. '_index.atom' );
    if ($result)
      return $this->url_for( array('resource'=>$this->resource, 'action'=>'index.atom'));
    return $result;
  }
  
  function entry_url( $id = NULL ) {
    $result = false;
    if (isset($this->resource))
      $result = is_file( $this->template_path . $this->resource . DIRECTORY_SEPARATOR. '_entry.js' );
    if (!$result)
      $result = is_file( $this->template_path . $this->resource . DIRECTORY_SEPARATOR. '_entry.html' );
    if (isset($GLOBALS['PATH']['apps']))
      foreach($GLOBALS['PATH']['apps'] as $k=>$v)
        $result = file_exists($v['layout_path'].$this->resource . DIRECTORY_SEPARATOR. '_entry.html');
    if ($result && ($id != NULL))
      return $this->url_for( array('resource'=>$this->resource, 'action'=>'entry', 'id'=>$id));
    if ($result)
      return $this->url_for( array('resource'=>$this->resource, 'action'=>'entry'));
    return $result;
  }
  
  function new_url() {
    $result = false;
    if (isset($this->resource))
      $result = is_file( $this->template_path . $this->resource . DIRECTORY_SEPARATOR. '_new.js' );
    if (!$result)
      $result = is_file( $this->template_path . $this->resource . DIRECTORY_SEPARATOR. '_new.html' );
    if (isset($GLOBALS['PATH']['apps']))
      foreach($GLOBALS['PATH']['apps'] as $k=>$v)
        $result = file_exists($v['layout_path'].$this->resource . DIRECTORY_SEPARATOR. '_new.html');
    if ($result)
      return $this->url_for( array('resource'=>$this->resource, 'action'=>'new'));
    return $result;
  }
    
  function get_template_path( $ext, $template = null ) {
    
    if (isset($this->params['resource']))
      $resource = $this->params['resource'] . DIRECTORY_SEPARATOR;
    else
      $resource = "";
    
    if (isset($this->templates_resource[$this->params['resource']]))
      $resource = $this->templates_resource[$this->params['resource']] . DIRECTORY_SEPARATOR;
    
    if ($template == null) {
      $partial = false;
      $template = $this->params['action'];
    } else {
      $partial = true;
      $template = "_" . $template;
    }
    
    if ($template == 'get')
      $template = 'index';
    
    if (isset($this->client_wants))
      $ext = $this->client_wants;
      
    $resource_path = $this->template_path;
    
    if (!(file_exists($this->template_path . $resource))) {
      if (isset($GLOBALS['PATH']['apps'])) {
        foreach($GLOBALS['PATH']['apps'] as $k=>$v) {
          if (file_exists($v['layout_path'].$resource ))
            $resource_path = $v['layout_path'];
        }
      }
    }
    
    // example: blah.net/?posts/new.html
    
    // searching for a layout to go with the partial _new
    
    // /posts/new.html
    $view = $resource_path . $resource . $template . "." . $ext;
    
    // /new.html
    if (!(is_file($view)))
      $view = $this->template_path . $template . "." . $ext;
    
    // /posts/index.html
    if (!$partial && !(is_file($view)))
      $view = $resource_path . $resource . 'index' . "." . $ext;
    
    // /index.html
    if (!$partial && !(is_file($view)))
      $view = $this->template_path . 'index' . "." . $ext;
    
    if (!$partial) {
      
      if ($this->action == 'get')
        $action = 'index';
      else
        $action = $this->action;
      
      // found a potential layout but is there a partial with the same extension?
      
      // /posts/_new.ext  ??
      if ((!(file_exists($resource_path . $resource . "_" . $action . "." . $ext)))
        // /_new.ext    ??
        && (!(file_exists($this->template_path . "_" . $action . "." . $ext))))
          return false;
      
    }
    
    if  (is_file($view))
      return $view;
    
    return false;
    
  }
  
  function is_allowed( $method ) {
    return in_array( $method, $this->allowed_methods, true );
  }
  
  function connect() {
    // connect a Route to the Mapper
    
    $r = new Route();
    
    $args = func_get_args();
    
    if (count($args) == 1) {
      $args[] = $args[0];
      $args[] = array('action'=>$args[0]); 
    }
    
    foreach ( $args as $idx => $arg ) {
      
      if ( is_string( $arg ) ) {
        
        $r->patterns = explode( '/', $arg );
        
        if ( count( $r->patterns ) == 1 && $idx == 0 )
          $r->name = $r->patterns[0];
          
      } elseif ( is_array( $arg ) ) {
        
        foreach ( $arg as $key => $val ) {
          if ( $key == 'requirements' ) {
            $i = 0;
            foreach ( $r->patterns as $pos => $str ) {
              if ( substr( $str, 0, 1 ) == ':' ) {
                $r->requirements[$pos] = $val[$i];
                $i++;
              }
            }
          } else {
            $r->defaults[$key] = $val;
          }
        }
        
      }
      
    }
    
    $this->routes[] = $r;
    
  }
  
  function generate( $controller='index.php', $action='get' ) {
    // Generate a route from a set of keywords and return the url
  }
  
  function set( $param, $val ) {
    $this->$param = $val;
  }
  
  function routematch( $url = NULL ) {
    // Match a URL against against one of the routes contained.
    
    if ($this->activeroute)
      return;
    
    $return = false;
    trigger_before( 'routematch', $this, $this->activeroute );
    
    if ($url === NULL) $url = $this->uri;
    
    foreach ( $this->routes as $route ) {
      if ($this->match( $url, $route )) {
        break;
        $return = true;
      }
    }
    
    if ( isset( $this->params['method'] )
    && !is_array($this->params['method']) ) $this->action = $this->method;
    
    if ( isset( $this->params['forward_to'] ) ) $this->controller = $this->forward_to;
    
    if ( isset( $this->action )) {
      if (!(strpos($this->action,".") === false)) { // check for period
        $actionsplit = split("\.", $this->action);
        $this->set_param( 'action', $actionsplit[0]);
        $this->set( 'client_wants', $actionsplit[1] );
      }
    }
    if (isset($this->resource)) {
      if (!(strpos($this->resource,".") === false)) { // check for period
        $actionsplit = split("\.", $this->resource);
        $this->set_param( 'resource', $actionsplit[0]);
        $this->set( 'client_wants', $actionsplit[1] );
      }
    }
    
    trigger_after( 'routematch', $this, $this->activeroute );
    
    return $return;
    
  }
  
  function match( $url, $r ) {
    
    foreach ( $r->patterns as $idx => $value ) {
      if ( !( isset( $this->params[$idx] ) ) ) {
        return false;
      }
    }
    
    $i = 0;
    $regx = array();
    foreach ( $r->patterns as $pos => $str ) {
      if ( substr( $str, 0, 1 ) == ':' ) {
        if ( isset( $r->requirements[$pos] ) ) {
          $regx[] = $r->requirements[$pos];
        } else {
          $regx[] = '(.+)';
        }
        $i++;
      } else {
        $regx[] = $str;
      }
    }
    
    $params = $this->params;
    $paramcount = count($params);

    while ( count( $params ) > count( $regx ) ) {
      array_shift( $params );
    }
    
    if ($paramcount == 1) {
      if (!(strpos($params[0], '&') === false)) {
        $paramsplit = split("&",$params[0]);
        $params[0] = $paramsplit[0];
      }
      if (!(strpos($params[0], '?') === false)) {
        $paramsplit = split("?",$params[0]);
        $params[0] = $paramsplit[0];
      }
    }
    
    if ( count( $r->patterns ) == 0 ) {
      $r->match = true;
      $pmatches = array();
//    } elseif ( preg_match( "/\/" . implode( "\/", $regx ) . "/i", "/" .implode( "/", $params ), $pmatches )  ) {
    } elseif ( preg_match( "/\/" . implode( "\/", $regx ) . "/i", "/" .implode( "/", $params ), $pmatches ) && count($r->patterns) == $paramcount ) {
      $r->match = true;
    }
    
    if ($r->match) {
      $this->activeroute =& $r;
      $this->params = array_merge( $_GET, $_POST, $r->defaults, $this->params );
      foreach ( $this->params as $p=>$v ) {
        if ( !( isset( $this->$p ) ) )
          $this->$p =& $this->params[$p];
      }
      foreach ( $r->patterns as $idx => $val ) {
        if ( substr( $val, 0, 1 ) == ':' ) {
          $val = substr( $val, 1);
          if ( isset( $params[$idx] ) ) $this->params[$val] = $params[$idx];
        }
      }
      
    }
    return $r->match;
  }
  
  function composite_uri() {
    // cross platform URI code by Angsuman Chakraborty
    $port = "";
    if ( isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS']=='on' ) {
      $_SERVER['FULL_URL'] = 'https://';
      if ( $_SERVER['SERVER_PORT']!='443' ) {
        $port = ':' . $_SERVER['SERVER_PORT'];
      }
    } else {
      $_SERVER['FULL_URL'] = 'http://';
      if ( $_SERVER['SERVER_PORT']!='80' ) {
        $port = ':' . $_SERVER['SERVER_PORT'];
      }
    }
    if ( isset( $_SERVER['REQUEST_URI'] ) ) {
      $script = $_SERVER['REQUEST_URI'];
    } else {
      $script = $_SERVER['PHP_SELF'];
      if ( $_SERVER['QUERY_STRING']>' ' ) {
        $script .= '?'.$_SERVER['QUERY_STRING'];
      }
    }
    if ( isset( $_SERVER['HTTP_HOST'] ) ) {
      $_SERVER['FULL_URL'] .= $_SERVER['HTTP_HOST'] . $port . $script;
    } else {
      $_SERVER['FULL_URL'] .= $_SERVER['SERVER_NAME'] . $port . $script;
    }
    global $pretty_url_base;
    if (file_exists('.htaccess') && !isset($pretty_url_base)) {
			list($subdomain, $rest) = explode('.', $_SERVER['SERVER_NAME'], 2);
      $pretty_url_base = 'http://'.$subdomain.'.'.$rest;
    }
    if (isset($pretty_url_base) && !empty($pretty_url_base)) {
      if (!empty($_SERVER['QUERY_STRING']))
        return $pretty_url_base.'/?'.$_SERVER['QUERY_STRING'];
      else
        return $pretty_url_base.'/';
    }
    return $_SERVER['FULL_URL'];
  }
  
  function hasErrors() {
    if ( $this->error === true )
      return true;
    return false;
  }
  
  function propagate() {
    $allowed = $this->persisted_vars;
    $_SESSION['params'] = array();
    foreach( $this->params as $param=>$val ) {
      if (in_array($param, $allowed)) {
        $_SESSION['params'][$param] = $val;
      }
    }
  }
  
  function restore() {
    if (!(isset($_SESSION['params']))) return false;
    foreach( $_SESSION['params'] as $param=>$val ) {
      $this->params[$param] = $val;
      $this->$param =& $this->params[$param];
    }
  }
  
  function use_templates_from( $resource, $model ) {
    $this->templates_resource[$model] = $resource;
  }
  
}

/**
 * Convert Type
 *
 * Converts any string into its appropriate native type.
 *
 * Example:
 * - 'two' => 'two'
 * - '2' => 2
 * - '2.0' => 2.0
 * - 'true' => true
 * - 'false' => false
 *
 * @access public
 * @author Gary Court <gcourt@gmail.com>
 * @param string $var String to convert.
 * @return mixed The converted string.
 * @version 1.0
 */

function convert_type($var) 
{
  if (is_string($var)) {
    if (is_numeric($var)) {
      if(strpos($var, '.') !== false)
        return (float)$var;
      else
        return (int)$var;
    }
  
    if( $var == "true" )  return true;
    if( $var == "false" ) return false;
  }
  
  return $var;
}

/**
 * Array Trim
 *
 * Recurses through an array, calling trim() on any strings.
 *
 * @access public
 * @author Gary Court <gcourt@gmail.com>
 * @param array $arr Array to trim.
 * @param string $charlist Parameter to pass to trim().
 * @version 1.0
 */

function array_trim($arr, $charlist = " \t\n\r\0\x0B")
{
  for ($i = 0; $i < count($arr); $i++) {
    if (is_string($arr[$i]))
      $arr[$i] = trim($arr[$i], $charlist);
    elseif (is_array($arr[$i]))
      $arr[$i] = array_trim($arr[$i], $charlist);
  }
  return $arr;
}

/**
 * Merge Sort
 *
 * Uses the merge sort algorithm on $array, and compares array elements 
 * using the function named in $cmp_function.
 *
 * @access public
 * @author Gary Court <gcourt@gmail.com>
 * @param array $array The array to be sorted
 * @param string $cmp_function The name of the function to use to compare two array elements. If null, uses 'strcmp'.
 * @version 1.0
 */

function mergesort(&$array, $cmp_function = 'strcmp') {
  // Arrays of size < 2 require no action.
  if (count($array) < 2) return;
  
  // Split the array in half
  $halfway = count($array) / 2;
  $array1 = array_slice($array, 0, $halfway);
  $array2 = array_slice($array, $halfway);
  
  // Recurse to sort the two halves
  mergesort($array1, $cmp_function);
  mergesort($array2, $cmp_function);
  
  // If all of $array1 is <= all of $array2, just append them.
  if (call_user_func($cmp_function, end($array1), $array2[0]) < 1) {
    $array = array_merge($array1, $array2);
    return;
  }
  
  // Merge the two sorted arrays into a single sorted array
  $array = array();
  $ptr1 = $ptr2 = 0;
  while ($ptr1 < count($array1) && $ptr2 < count($array2)) {
    if (call_user_func($cmp_function, $array1[$ptr1], $array2[$ptr2]) < 1)
      $array[] = $array1[$ptr1++];
    else
      $array[] = $array2[$ptr2++];
  }
  
  // Merge the remainder
  while ($ptr1 < count($array1)) $array[] = $array1[$ptr1++];
  while ($ptr2 < count($array2)) $array[] = $array2[$ptr2++];
}


class HTTP_Negotiate
{
  /**
   * HTTP Content Negotiation
   * 
   * Using the content negotiation algorithm specified in 
   * {@link http://cidr-report.org/ietf/all-ids/draft-ietf-http-v11-spec-00.txt draft-ietf-http-v11-spec-00}, 
   * will return the most appropriate variants (may be more then one that
   * works) based on the provided request headers. This function is based
   * off of {@link http://search.cpan.org/dist/libwww-perl/lib/HTTP/Negotiate.pm libwww-perl, HTTP::Negotiate, choose()}.
   *
   * Usage:
   * <code>
   *  $variants = array(
   *    array(
   *      id => 'var1',
   *      qs => 1.000,
   *      type => 'text/html',
   *      encoding => null,
   *      charset => 'iso-8859-1',
   *      language => 'en',
   *      size => 3000
   *    ),
   *    array(
   *      id => 'var2',
   *      qs => 1.000,
   *      type => 'application/xhtml+xml',
   *      encoding => null,
   *      charset => 'iso-8859-1',
   *      language => 'en',
   *      size => 3000
   *    ),
   *  );
   *  
   *  $request_headers = array(
   *    HTTP_ACCEPT => 'text/xml,application/xml,application/xhtml+xml,text/html;q=0.9,text/plain;q=0.8,image/png,{@*}*;q=0.5',
   *    HTTP_ACCEPT_LANGUAGE => 'en-us,en;q=0.5',
   *    HTTP_ACCEPT_CHARSET => 'ISO-8859-1,utf-8;q=0.7,*;q=0.7', 
   *    HTTP_ACCEPT_ENCODING => 'gzip,deflate'
   *  );
   *  
   *  $results = HTTP_Negotiate::choose($variants, $request_headers);
   *  assertTrue(count($results) == 1 && $results[0]['id'] == 'var2');
   * </code>
   *
   * More information on accept headers can be found at 
   * {@link http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html}
   *
   * @access public
   * @author Gary Court <gcourt@gmail.com>
   * @param array $variants Array of array of strings which contain the supported parameters of each variant (supported keys: id, qs, type, encoding, charset, language, size)
   * @param array $request_headers Array of strings which contain the request header (supported keys: HTTP_ACCEPT, HTTP_ACCEPT_LANGUAGE, HTTP_ACCEPT_CHARSET, HTTP_ACCEPT_ENCODING). If null, $_SERVER is used.
   * @return array The acceptable variants (from $variants) based on the request headers. May return more then one acceptable variant (in original order) or may return null (if no acceptable variant was found). 
   * @static
   * @version 1.0
   * @todo Support for parameters in variant->type.
   */
   
  function choose($variants, $request_headers = null) 
  {


    //check arguments
    if (!is_array($variants))
      return false;
    if ($request_headers === null)
      $request_headers = $_SERVER;
    elseif (!is_array($request_headers))
      return false;

    //parse all accept values
    $request = array();
    $request_header_keys = array_keys($request_headers);
    foreach ($request_header_keys as $request_header_key) {
      $accept_type = null;
      if (strpos($request_header_key, 'HTTP_ACCEPT_') !== false)
        $accept_type = strtolower(substr($request_header_key, strlen('HTTP_ACCEPT_')));
      elseif ($request_header_key == 'HTTP_ACCEPT')
        $accept_type = 'type';
      
      if ($accept_type) {
        $request[$accept_type] = array();
        $accept_variants = array_trim(explode(',', $request_headers[$request_header_key]));
        foreach ($accept_variants as $accept_variant) {
          if ($accept_variant) {
            $accept_variant_parameters = array_trim(explode(';', $accept_variant));
            $request[$accept_type][$accept_variant_parameters[0]] = array();
            for ($i = 1; $i < count($accept_variant_parameters); $i++) {
              if (strpos($accept_variant_parameters[$i], '=') !== false) {
                $accept_variant_parameter_values = array_trim(explode('=', $accept_variant_parameters[$i]));
                $accept_variant_parameter_values[1] = convert_type($accept_variant_parameter_values[1]);
                
                if ($accept_variant_parameter_values[0] == 'q') {
                  if ($accept_variant_parameter_values[1] > 1.0)
                    $accept_variant_parameter_values[1] = 1.0;
                  elseif ($accept_variant_parameter_values[1] < 0.0)
                    $accept_variant_parameter_values[1] = 0.0;
                }
                if ($accept_variant_parameter_values[0] == 'mxb' && $accept_variant_parameter_values[1] < 0)
                  $accept_variant_parameter_values[1] = 0;
                
                $request[$accept_type][$accept_variant_parameters[0]][$accept_variant_parameter_values[0]] = $accept_variant_parameter_values[1];
              }
            }
            if (!isset($request[$accept_type][$accept_variant_parameters[0]]['q']))
              $request[$accept_type][$accept_variant_parameters[0]]['q'] = 1.0;
          }
        }
      }
    }
    
    //determine if at least one variant specifies a language
    $language_variant_specified = false;
    foreach ($variants as $variant)
      if (isset($variant['language'])) {
        $language_variant_specified = true;
        break;
      }
    
    //determine the best variant for the request
    $results = array();
    foreach ($variants as $variant) {
      //calculate qs
      if (!isset($variant['qs']) || !is_numeric($variant['qs'])) 
        $qs = 1.0;
      else
        $qs = (float)convert_type($variant['qs']);
      
      //calculate qe
      if (!isset($request['encoding']))
        $qe = 1.0;
      elseif (!isset($variant['encoding']) || !count($request['encoding']))
        $qe = 1.0;
      elseif (array_key_exists($variant['encoding'], $request['encoding']))
        $qe = (float)$request['encoding'][$variant['encoding']]['q'];
      elseif ($variant['encoding'] == 'identity')
        $qe = 1.0;
      elseif (isset($request['encoding']['*']))
        $qe = (float)$request['encoding']['*']['q'];
      else
        $qe = 0.0;
      
      // ---------

      // hack by Brian
       
      // changed !count(... to !isset($request['charset'])

      // ---------
      
      //calculate qc
      if (!(isset($request['charset'])))
        $qc = 1.0;
      elseif (!isset($variant['charset']) || $variant['charset'] == 'US-ASCII' || !isset($request['charset']))
        $qc = 1.0;
      elseif (array_key_exists($variant['charset'], $request['charset']))
        $qc = (float)$request['charset'][$variant['charset']]['q'];
      elseif (isset($request['charset']['*']))
        $qc = (float)$request['charset']['*']['q'];
      else
        $qc = 0.0;
      
      //calculate ql
      if (!(isset($request['language'])))
        $ql = 1.0;
      elseif (!$language_variant_specified || !count($request['language']))
        $ql = 1.0;
      elseif (!isset($variant['language']))
        $ql = 0.5;
      elseif (array_key_exists($variant['language'], $request['language']))
        $ql = (float)$request['language'][$variant['language']]['q'];
      elseif (array_key_exists(substr($variant['language'], 0, 2), $request['language']))
        $ql = (float)$request['language'][substr($variant['language'], 0, 2)]['q'];
      elseif (isset($request['language']['*']))
        $ql = (float)$request['language']['*']['q'];
      else
        $ql = 0.001;
      
      //calculate q & mxb
      $mxb = null;

      // ---------

      // hack by Brian below added (6) !(isset...
      // to prevent warnings on strict php setups

      // ---------
      
      if (!(isset($request['type'])))
        $q = 0.0;
      elseif (!isset($variant['type']))
        $q = 0.0;
      elseif (!count($request['type']))
        $q = 1.0;
      elseif (array_key_exists($variant['type'], $request['type'])) {
        if (!(isset($request['type'][$variant['type']]['q'])))
          $request['type'][$variant['type']]['q'] = $q;
        $q = (float)$request['type'][$variant['type']]['q'];
        if (!(isset($request['type'][$variant['type']]['mxb'])))
          $request['type'][$variant['type']]['mxb'] = $mxb;
        $mxb = $request['type'][$variant['type']]['mxb'];
      }
      elseif (array_key_exists(strtok($variant['type'], '/').'/*', $request['type'])) {
        if (!(isset($request['type'][strtok($variant['type'], '/').'/*']['q'])))
          $request['type'][strtok($variant['type'], '/').'/*']['q'] = $q;
        $q = (float)$request['type'][strtok($variant['type'], '/').'/*']['q'];
        if (!(isset($request['type'][strtok($variant['type'], '/').'/*']['mxb'])))
          $request['type'][strtok($variant['type'], '/').'/*']['mxb'] = $mxb;
        $mxb = $request['type'][strtok($variant['type'], '/').'/*']['mxb'];
      }
      elseif (array_key_exists('*/*', $request['type'])) {
        if (!(isset($request['type']['*/*']['q'])))
          $request['type']['*/*']['q'] = $q;
        $q = (float)$request['type']['*/*']['q'];
        if (!(isset($request['type']['*/*']['mxb'])))
          $request['type']['*/*']['mxb'] = $mxb;
        $mxb = $request['type']['*/*']['mxb'];
      }
      else
        $q = 0.0;
      
      //calculate bs
      $bs = $variant['size'];
      
      //calculate Q
      if ($mxb === null || $bs === null || $mxb >= $bs)
        $Q = $qs*$qe*$qc*$ql*$q;
      else
        $Q = 0.0;
      
      //keep track of the highest Q values
      $variant['Q'] = $Q;
      if (!count($results) || $variant['Q'] > $results[0]['Q'])
        $results = array($variant);
      elseif ($variant['Q'] == $results[0]['Q'])
        array_push($results, $variant);
    }
    
    //sort results (which all have same Q) by smallest filesize, ascending
    if (!function_exists('compareVariants')) {
      function compareVariants($a, $b) {
        if ($a['Q'] == $b['Q']) {
          if (isset($a['size'])) {
            if (isset($b['size'])) {
              if ($a['size'] == $b['size'])
                return 0;
              return ($a['size'] < $b['size'] ? -1 : 1);
            }
            return -1;
          }
          if (isset($b['size']))
            return 1;
          return 0;
        }
        return ($a['Q'] < $b['Q'] ? 1 : -1);
      }
    }
    mergesort($results, 'compareVariants');
    
    //return variants ordered by best choice
    return $results;
  }
}

