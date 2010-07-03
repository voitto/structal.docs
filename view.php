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
 * View
 *
 * @package   Structal
 * @author    Brian Hendrickson <brian@megapump.com>
 * @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link      http://structal.org/view
 */

class View {
  
  var $negotiator;
  var $controller;
  var $collection;
  var $named_vars;
  var $layout;
  var $extension;
  var $header_sent;
  
  function View() {
    $this->named_vars = array();
    $this->header_sent = false;
    global $db;
    global $request;
    $env =& environment();
    
    if ( isset( $request->resource ))
      $this->collection = new Collection( $request->resource );
    else
      $this->collection = new Collection( null );
    
    $this->named_vars['db'] =& $db;
    $this->named_vars['request'] =& $request;
    $this->named_vars['collection'] =& $this->collection;
    $this->named_vars['response'] =& $this;
    if (get_profile_id())
      $this->named_vars['profile'] =& get_profile();
    else
      $this->named_vars['profile'] = false;
    if ( isset( $request->resource ) && $request->resource != 'introspection' )
      $this->named_vars['resource'] =& $db->get_table( $request->resource );
    else
      $this->named_vars['resource'] = false;
    $this->named_vars['prefix'] = $db->prefix;
    $this->controller = $request->controller;
    
    load_apps();
    
    $controller_path = controller_path();
    // check for a controller file in controllers/[resource].php
    if ( isset( $request->resource )) {
      $cont = $controller_path . $request->resource . ".php";
      if ( file_exists( $cont )) {
        $this->controller = $request->resource . ".php";
      } elseif (isset($request->templates_resource[$request->resource]) && file_exists($controller_path . $request->templates_resource[$request->resource] . ".php")) {
        $this->controller = $request->templates_resource[$request->resource] . ".php";
      } else {
        if (isset($GLOBALS['PATH']['apps'])) {
          foreach($GLOBALS['PATH']['apps'] as $k=>$v) {
            if (file_exists($v['controller_path'].$request->resource . ".php" )) {
              $this->controller =  $request->resource . ".php";
              $controller_path = $v['controller_path'];
            }
          }
        }
      }
    }
    
    if ( is_file( $controller_path . $this->controller ))
      require_once( $controller_path . $this->controller );
    else
      trigger_error( 'Sorry, the controller was not found at ' . $controller_path . $this->controller, E_USER_ERROR );
    
    if (!(isset($env['content_types'])))
      trigger_error( 'Sorry, the content_types array was not found in the configuration file', E_USER_ERROR );
    
    $this->negotiator = HTTP_Negotiate::choose( $env['content_types'] );
    
  }
  
  function render( &$request ) {
    trigger_before('render',$this,$this);
    // boot.php calls $response->render()

    if (!isset($request->activeroute)) {
	    $request->connect('');
	    $request->routematch();
    }

    global $db;
    
    $ext = $this->pick_template_extension( $request );
    
    $view = $request->get_template_path( $ext );
    
    $action = $request->action;
    
    global $api_methods,$api_method_perms;
    
    $api_method = $action;
    
    if (array_key_exists($action,$api_methods)){
	    if (isset($db)) {
	      trigger_before( $api_method, $request, $db );
	      $action = @create_function( '&$vars', $api_methods[$action] );
	      $this->named_vars['resource'] =& $db->get_table($api_method_perms[$api_method]['table']);
			} else {
	      trigger_before( $api_method, $request, $request );
	      $action = @create_function( '&$vars', $api_methods[$action] );
			}
      if (!($this->named_vars['resource']->can($api_method_perms[$api_method]['perm'])))
        trigger_error('not allowed sorry',E_USER_ERROR);
    }
    
    if (!(function_exists($action)))
      $action = 'index';
    
    if (function_exists( $action )) {
	    if (isset($db)) {
	      trigger_before( $request->action, $request, $db );
	      $result = $action( array_merge( $this->named_vars, $db->get_resource() ));
	      trigger_after( $request->action, $request, $db );
	    } else {
	      trigger_before( $request->action, $request, $request );
	      $result = $action( array_merge( $this->named_vars ));
	      trigger_after( $request->action, $request, $request );
	    }
      if ( is_array( $result ))
        extract( $result );
    }
    
    if ( file_exists( $view ) ) {
      
      
      // example response with Accept set to application/rdf+xml
      
      //HTTP/1.1 200 OK
      //Server: Virtuoso/05.00.3028 (Linux) i686-generic-linux-glibc23-32 VDB
      //Connection: Keep-Alive
      //Date: Wed, 07 May 2008 15:23:16 GMT
      //Accept-Ranges: bytes
      //Content-Length: 0
      //ETag: "6689-2008-05-07T11:23:16.000000-0-9851f9cbda1201e253939e204e596f4d"
      //Content-Type: application/rdf+xml
      
      // example default response
      
      //HTTP/1.1 200 OK
      //Server: Virtuoso/05.00.3028 (Linux) i686-generic-linux-glibc23-32 VDB
      //Connection: Keep-Alive
      //Content-Type: text/html; charset=UTF-8
      //Date: Wed, 07 May 2008 15:23:47 GMT
      //Accept-Ranges: bytes
      //X-XRDS-Location: yadis.xrds
      //Content-Length: 0
      
      
      $content_type = 'Content-Type: ' . $this->pick_content_type( $ext );
      if ($this->pick_content_charset( $ext ))
        $content_type .= '; charset=' . $this->pick_content_charset( $ext );
        
      if ( !$this->header_sent && !( in_array( 'partial', $request->activeroute->patterns, true )) ) {
        
        header( $content_type );
        
        $this->header_sent = true;
        
        // ob_start?
        
        include( $view );
        
      } else {
        
        $this->render_partial( $request, $request->action );
        
      }
      
    } elseif ( !( in_array( 'partial', $request->activeroute->patterns, true )) ) {
      
      // layout_template_not_exists (get/post/put/delete action)
      $this->render_partial( $request, $request->action );
      
    }
    
  }
  
  function render_partial( &$request, $template ) {
    trigger_before('render_partial',$this,$this);
    // content_for_layout() passes the $request->action as $template
    
    $ext = $this->pick_template_extension( $request, $template );
    
    $view = $request->get_template_path( $ext, $template );
    
    if ($template == 'get')
      $template = 'index';
    
    if (file_exists($view))
      $action = "_" . $template;
    else
      $action = $template;
    
    global $db;
    
    if ( file_exists( $view ) && function_exists( $action ) ) {
      
      trigger_before( $request->action, $request, $db );
      $result = $action( array_merge( $this->named_vars, $db->get_resource() ));
      trigger_after( $request->action, $request, $db );
      
      if ( is_array( $result ))
        extract( $result );
      
      if (!($this->header_sent)) {
        $content_type = 'Content-Type: ' . $this->pick_content_type( $ext );
        if ($this->pick_content_charset( $ext ))
          $content_type .= '; charset=' . $this->pick_content_charset( $ext );
        header( $content_type );
        $this->header_sent = true;
      }
      
      include( $view );
      
    } else {
      
      // no template, check for blobcall
      
      if ((in_array(type_of( $ext ), mime_types())) && !($this->header_sent)) {
        $model =& $db->get_table($request->resource);
        if (isset($model->blob))
          $template = $model->blob;
        trigger_before( $request->action, $request, $db );
        $Member = $this->collection->MoveFirst();
        render_blob( $Member->$template, $ext );
      } else {
        
        if (strpos($request->uri, 'robots') === false
          || strpos($request->uri, 'crawl') === false)
            admin_alert($request->uri." $view $action ".$_SERVER[REMOTE_HOST]);
        
      }
      
    }
    
  }
  
  function set_var($name,$value) {
    $this->named_vars[$name] = $value;
  }
  
  function negotiate_content( &$request, $template ) {
    trigger_before('render_partial',$this,$this);
    foreach ( $this->negotiator as $client_wants ) {
      if ( template_exists( $request, $client_wants['id'], $template ))
        return $client_wants['id'];
    }
  }
    
  function pick_content_type( $ext ) {
    $variants = content_types();
    foreach($variants as $content_type) {
      if ($content_type['id'] == $ext && $content_type['type'] != null)
        return $content_type['type'];
    }
    return false;
  }
  
  function pick_content_charset( $ext ) {
    $variants = content_types();
    foreach($variants as $content_type) {
      if ($content_type['id'] == $ext)
        return $content_type['charset'];
    }
    return false;
  }
  
  function set_content_encoding( $ext ) {
    $variants = content_types();
    foreach($variants as $content_type) {
      if (($content_type['id'] == $ext) && $content_type['encoding'])
        header( "Content-Encoding: ". $content_type['encoding'] );
    }
  }
  
  function pick_template_extension( &$request, $template = null ) {
    trigger_before('pick_template_extension',$this,$this);
    if (!empty($request->client_wants))
      return $request->client_wants;
      
    $ext = $this->negotiate_content( $request, $template );
    
    $this->extension = $ext;
    
    if (!$ext) {
      
      // if ( content-negotiation fails ) go to html
      
      $variants = array(
        array(
          'id' => 'html',
          'qs' => 1.000,
          'type' => 'text/html',
          'encoding' => null,
          'charset' => 'utf-8',
          'language' => 'en',
          'size' => 3000
        )
      );
      
      $this->negotiator = $variants;
      
      $ext = 'html';
      
    }
    
    return $ext;
    
  }
  
}

