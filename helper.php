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

global $api_methods;
global $pretty_url_base;
global $request;
$api_methods = array();

function plugin( $plugin, $plugpath='' ) {
  if ( file_exists( $plugpath . $plugin . '.php' ) ) {
    include $plugpath . $plugin . '.php';
    $init = $plugin . "_init";
    if ( function_exists( $init ) )
      $init();
    return;
  }
}

function template_exists() {
 return 0;
}
function controller_path() {
 global $request;
 if (empty($request->controller))
 return 'index.php';
 else
 return '';
}
function type_of() {
 return 0;
}
function admin_alert() {
 return 0;
}
function mime_types() {
 return array();
}
function load_apps() {
 return 0;
}
function get_profile_id() {
 return 0;
}
function environment() {
 return array('content_types'=>1);
}
function trigger_before() {
 return 0;
}
function trigger_after() {
 return 0;
}
