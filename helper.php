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
 * Helper
 *
 * @package   Structal
 * @author    Brian Hendrickson <brian@megapump.com>
 * @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link      http://structal.org/helper
 */

class Helper {
	
	//include ERB::Util
	//11	      BOOLEAN_ATTRIBUTES = Set.new(%w(disabled readonly multiple))
	//39	      def tag(name, options = nil, open = false, escape = true)
	//66	      def content_tag(name, content_or_options_with_block = nil, options = nil, escape = true, &block)
	//89	      def cdata_section(content)
	//101	      def escape_once(html)
	//106	      def content_tag_string(name, content, options, escape = true)
	//111	      def tag_options(options, escape = true)
	//128	      def block_is_within_action_view?(block)
	
	function content_tag( $name, $content_or_options, $options=false, $escape=true, $block=null ) {
		$content = $content_or_options;
		return $this->content_tag_string( $name, $content, $options, $escape );
	}

	function content_tag_string( $name, $content, $options, $escape ) {
		$tag_options = '';
		if ($options)
		  $tag_options = $this->tag_options( $options, $escape );
		if (!isset($options['return']))
  		echo "<$name$tag_options>$content</$name>";
    else
      return "<$name$tag_options>$content</$name>";
    return "";
	}

	function tag_options( $options, $escape = true ) {
		$attrs = array();
		if ($escape) {
			foreach( $options as $key => $value ){
				if (!$value)
				  continue;
				if ($key == 'return')
				  continue;
				else
				  $attrs[] = "$key=\"$value\"";
			}
		} else {

		}
		if (count($attrs)>0)
		  return ' '.implode( ' ', $attrs );
		else
		  return '';
	}
	
}

/**
 * AuthToken
 *
 * @package   Structal
 * @author    Brian Hendrickson <brian@megapump.com>
 * @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link      http://structal.org/authtoken
 */

class AuthToken  {
 
  var $token;
  var $secret;

}

/**
 * plugin
 * 
 * @package   Structal
 * @author    Brian Hendrickson <brian@megapump.com>
 * @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link      http://structal.org/plugin
 */

function plugin( $plugin, $plugpath='' ) {
  if ( file_exists( $plugpath . $plugin . '.php' ) ) {
    include $plugpath . $plugin . '.php';
    $init = $plugin . "_init";
    if ( function_exists( $init ) )
      $init();
    return;
  }
}

/**
 * template_exists
 * 
 * @package   Structal
 * @author    Brian Hendrickson <brian@megapump.com>
 * @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link      http://structal.org/template_exists
 */

function template_exists() {
 return 0;
}

/**
 * controller_path
 * 
 * @package   Structal
 * @author    Brian Hendrickson <brian@megapump.com>
 * @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link      http://structal.org/controller_path
 */

function controller_path() {
 global $request;
 if (empty($request->controller))
 return 'index.php';
 else
 return '';
}

/**
 * type_of
 * 
 * @package   Structal
 * @author    Brian Hendrickson <brian@megapump.com>
 * @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link      http://structal.org/type_of
 */

function type_of() {
 return 0;
}

/**
 * admin_alert
 * 
 * @package   Structal
 * @author    Brian Hendrickson <brian@megapump.com>
 * @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link      http://structal.org/admin_alert
 */

function admin_alert() {
 return 0;
}

/**
 * mime_types
 * 
 * @package   Structal
 * @author    Brian Hendrickson <brian@megapump.com>
 * @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link      http://structal.org/mime_types
 */

function mime_types() {
 return array();
}

/**
 * load_apps
 * 
 * @package   Structal
 * @author    Brian Hendrickson <brian@megapump.com>
 * @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link      http://structal.org/load_apps
 */

function load_apps() {
 return 0;
}

/**
 * get_profile_id
 * 
 * @package   Structal
 * @author    Brian Hendrickson <brian@megapump.com>
 * @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link      http://structal.org/get_profile_id
 */

function get_profile_id() {
 return 0;
}

/**
 * environment
 * 
 * @package   Structal
 * @author    Brian Hendrickson <brian@megapump.com>
 * @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link      http://structal.org/environment
 */

function environment() {
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
 return array('content_types'=>$variants);
}

  /**
   * Trigger Before
   * 
   * trip before filters for a function
   * 
	 * @package   Structal
	 * @author    Brian Hendrickson <brian@megapump.com>
	 * @license   http://www.opensource.org/licenses/mit-license.php The MIT License
	 * @link      http://structal.org/trigger_before
   * @param string $func
   * @param object $obj_a
   * @param object $obj_b
   */

function trigger_before( $func, &$obj_a, &$obj_b ) {
  if ( isset( $GLOBALS['ASPECTS']['before'][$func] ) ) {
    foreach( $GLOBALS['ASPECTS']['before'][$func] as $callback ) {
      call_user_func_array( $callback, array( $obj_a, $obj_b ) );
    }
  }
}

  /**
   * Trigger After
   * 
   * trip after filters for a function
   * 
	 * @package   Structal
	 * @author    Brian Hendrickson <brian@megapump.com>
	 * @license   http://www.opensource.org/licenses/mit-license.php The MIT License
	 * @link      http://structal.org/trigger_after
   * @param string $func
   * @param object $obj_a
   * @param object $obj_b
   */

function trigger_after( $func, &$obj_a, &$obj_b ) {
  if ( isset( $GLOBALS['ASPECTS']['after'][$func] ) ) {
    foreach( $GLOBALS['ASPECTS']['after'][$func] as $callback ) {
      call_user_func_array( $callback, array( $obj_a, $obj_b ) );
    }
  }
}


  /**
   * aspect_join_functions
   * 
   * add trigger function name pairs to GLOBALS
   * 
	 * @package   Structal
	 * @author    Brian Hendrickson <brian@megapump.com>
	 * @license   http://www.opensource.org/licenses/mit-license.php The MIT License
	 * @link      http://structal.org/aspect_join_functions
   * @param string $func
   * @param string $callback
   * @param string $type
   */
   
function aspect_join_functions( $func, $callback, $type = 'after' ) {
  $GLOBALS['ASPECTS'][$type][$func][] = $callback;
}


  /**
   * Before Filter
   * 
   * set an aspect function to trigger before another function
   * 
	 * @package   Structal
	 * @author    Brian Hendrickson <brian@megapump.com>
	 * @license   http://www.opensource.org/licenses/mit-license.php The MIT License
	 * @link      http://structal.org/before_filter
   * @param string $name
   * @param string $func
   * @param string $when
   */

function before_filter( $name, $func, $when = 'before' ) {
  aspect_join_functions( $func, $name, $when );
}


  /**
   * After Filter
   * 
   * set an aspect function to trigger after another function
   * 
	 * @package   Structal
	 * @author    Brian Hendrickson <brian@megapump.com>
	 * @license   http://www.opensource.org/licenses/mit-license.php The MIT License
	 * @link      http://structal.org/after_filter
   * @param string $name
   * @param string $func
   * @param string $when
   */

function after_filter( $name, $func, $when = 'after' ) {
  aspect_join_functions( $func, $name, $when );
}

/**
 * Never
 * 
 * returns false
 * 
 * @package   Structal
 * @author    Brian Hendrickson <brian@megapump.com>
 * @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link      http://structal.org/never
 * @return boolean false
 */

function never() {
  return false;
}

/**
 * Always
 * 
 * returns true
 * 
 * @package   Structal
 * @author    Brian Hendrickson <brian@megapump.com>
 * @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link      http://structal.org/always
 * @return boolean true
 */

function always() {
  return true;
}

if (!isset($skip_globals)){
	global $api_methods;
	global $pretty_url_base;
	global $request;
	$api_methods = array();
}

if (!isset($hide_debug)){
	ini_set('display_errors','1');
	ini_set('display_startup_errors','1');
	error_reporting (E_ALL & ~E_NOTICE );
}
