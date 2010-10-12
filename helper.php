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
		if (!isset($options['no-end']))
		  $content = "<$name$tag_options>$content</$name>";
		else
		  $content = "<$name$tag_options>$content";
		if (!isset($options['return']))
  		echo $content;
    else
      return $content;
    return "";
	}
	
	function end_content_tag( $name, $options = array() ) {
		$content = "</$name>";
		if (!isset($options['return']))
  		echo $content;
    else
      return $content;
    return "";
	}
	
	function tag_options( $options=array(), $escape = true ) {
		$attrs = array();
		if ($escape) {
			foreach( $options as $key => $value ){
				if (!$value)
				  continue;
				if ($key == 'return')
				  continue;
				if ($key == 'selected')
				  continue;
				if ($key == 'no-end')
				  continue;
				if ($key == 'effect')
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

	function javascript_tag( $data, $options = array() ) {
		$tag_options = $this->tag_options( $options );
		echo <<<EOD
         <script type="text/javascript"$tag_options>
           $data
         </script>
EOD;
	}

	function javascript_include_tag($data,$options=array()) {
		  if (!isset($options['charset']))
		    $options['charset'] = "utf-8";
      if (!isset($options['type']))
        $options['type'] = "text/javascript";
      $tag_options = $this->tag_options( $options );
			echo <<<EOD
          <script src="$data.js"$tag_options></script>
EOD;
	return "";
 }

  function stylesheet_link_tag($data) {

			echo <<<EOD
				<link href="$data.css" media="screen" rel="stylesheet" type="text/css" />
EOD;

	return "";
 }

  function stylesheet_import_tag($data) {

			echo <<<EOD
		    <style type="text/css" media="screen">@import "$data.css";</style>
EOD;

	return "";
 }

  function javascript_eventsource($source,$callback) {

			echo <<<EOD
        <script type="text/javascript">

					var item_list = new Array();

					function add_item(data,entry){
						alert('add');
						item_list.push(data[entry]['time']);
					}
					
					window.onbeforeunload = closeStreams;

				  function closeStreams() {
					  if (jQuery.isFunction( $.eventsource )) {
						  var streamsObj = $.eventsource('streams');
							$.each(streamsObj, function (i, obj) {
								$.eventsource('close', obj.options.label);
				      });
					  }
				  }
				
				  window.onload = json_eventsource;
					
				  function json_eventsource() {
						setTimeout(function(){
						  $.eventsource({
							 label:    'json-event-source',
						    url:      '$source',
						    dataType: 'text',
						    message:  function (data) {
							    if (typeof(data)=='object'&&(data instanceof Array)) {
								
							    } else if (data.length > 0) {
	                  eval( "data = " + data );
							    } else {
								    return false;
							    }
								  for ( var entry in data ) {
										found = false;
									  for ( var i=0; i<item_list.length; i++ )
									    if ( item_list[i] == data[entry]['time'] )
										    found = true;
									  if (found == false)
									    $callback(data,entry);
								  }
						    }
						  });
				    }, 5000);
					}
					
	      </script>
EOD;

	return "";
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

function environment($arg = false) {
	global $env;
	if (isset($env[$arg]))
	  return $env[$arg];
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
  if (!is_array($env))
    $env = array('content_types'=>$variants);
  if (!$arg)
    return $env;
  return false;
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

/**
 * content_types
 * 
 * @package   Structal
 * @author    Brian Hendrickson <brian@megapump.com>
 * @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link      http://structal.org/content_types
 */

function content_types(){
	$env = environment();
	return $env['content_types'];
}

/**
 * render
 * 
 * @package   Structal
 * @author    Brian Hendrickson <brian@megapump.com>
 * @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link      http://structal.org/render
 */

function render($varios, $scope=false, $prefix='unique', $suffix='value') {
  if ( $scope )
    $vals = $scope;
  else
    $vals = $GLOBALS;
  $i = 0;
  foreach ($varios as $orig) {
    $var =& $varios[$i];
    $old = $var;
    $var = $new = $prefix . rand() . $suffix;
    $vname = FALSE;
    foreach( $vals as $key => $val ) {
      if ( $val === $new ) $vname = $key;
    }
    $var = $old;
    if ($vname) {
      $varios[$vname] = $var;
    }
    $i++;
  }
  return $varios;
}

/**
 * render_partial
 * 
 * @package   Structal
 * @author    Brian Hendrickson <brian@megapump.com>
 * @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link      http://structal.org/render_partial
 */

function render_partial( $template ) {
  
  global $request,$response;
  
  if (!(strpos($template,".") === false)) {
    $spleet = split("\.",$template);
    $template = $spleet[0];
    $request->set( 'client_wants', $spleet[1] );
  }
  
  $response->render_partial( $request, $template );
  
}

/**
 * content_for_layout
 * 
 * @package   Structal
 * @author    Brian Hendrickson <brian@megapump.com>
 * @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link      http://structal.org/content_for_layout
 */

function content_for_layout() {
  
  global $request;
  
  render_partial( $request->action );
  
}

/**
 * redirect_to
 * 
 * @package   Structal
 * @author    Brian Hendrickson <brian@megapump.com>
 * @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link      http://structal.org/redirect_to
 */

function redirect_to( $param, $altparam = NULL ) {
  
  global $request,$db;
  
  if (substr($param,0,4) == 'http') {
	  header('Location: '.$param);
	  exit;
  }
	

  trigger_before( 'redirect_to', $request, $db );
  
  if (is_ajax()){
    echo "OK";
    exit;
  }else{
    $request->redirect_to( $param, $altparam );
  }
  
}

/**
 * url_for
 * 
 * @package   Structal
 * @author    Brian Hendrickson <brian@megapump.com>
 * @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link      http://structal.org/url_for
 */

function url_for( $params, $altparams = NULL ) {

  global $request;
  
  print $request->url_for( $params, $altparams );
  
}

/**
 * is_ajax
 * 
 * @package   Structal
 * @author    Brian Hendrickson <brian@megapump.com>
 * @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link      http://structal.org/is_ajax
 */

function is_ajax() {
  return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH']=="XMLHttpRequest");
}

/**
 * url
 * 
 * @package   Structal
 * @author    Brian Hendrickson <brian@megapump.com>
 * @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link      http://structal.org/url
 */

function url($data){
	global $request;
	return $request->url_for($data);
}

/**
 * add_include_path
 * 
 * @package   Structal
 * @author    Brian Hendrickson <brian@megapump.com>
 * @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link      http://structal.org/add_include_path
 */

function add_include_path($path,$prepend = false) {
  if (!file_exists($path) OR (file_exists($path) && filetype($path) !== 'dir')) {
    trigger_error("Include path '{$path}' not exists", E_USER_WARNING);
    continue;
  }
  $paths = explode(PATH_SEPARATOR, get_include_path());
  if (array_search($path, $paths) === false && $prepend)
      array_unshift($paths, $path);
  if (array_search($path, $paths) === false)
      array_push($paths, $path);
  set_include_path(implode(PATH_SEPARATOR, $paths));
}

if (!isset($skip_globals)){
	global $api_methods;
	global $pretty_url_base;
	global $request;
	global $db;
	global $helper;
	global $response;
	$api_methods = array();
	session_start();
	$helper = new Helper();
}

if (!isset($hide_debug)){
	ini_set('display_errors','1');
	ini_set('display_startup_errors','1');
	error_reporting (E_ALL & ~E_NOTICE );
}

function stylesheet_link_tag($options){
 echo "\n";
  global $helper;
  return $helper->stylesheet_link_tag($options);	
}

function stylesheet_import_tag($options){
 echo "\n";
  global $helper;
  return $helper->stylesheet_import_tag($options);	
}

function javascript_include_tag($options,$options2=array()){
 echo "\n";
  global $helper;
  return $helper->javascript_include_tag($options,$options2);
}

function javascript_eventsource($options,$options2=''){
 echo "\n";
  global $helper;
  return $helper->javascript_eventsource($options,$options2);
}

function is_pad() {
	return (bool) strpos($_SERVER['HTTP_USER_AGENT'],'iPad');
}

function is_mobile() {
	$useragent=$_SERVER['HTTP_USER_AGENT'];
	if(preg_match('/android|avantgo|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i',$useragent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|e\-|e\/|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(di|rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|xda(\-|2|g)|yas\-|your|zeto|zte\-/i',substr($useragent,0,4)))
    return true;
  return false;
}

function add_action() {
	return true;
}

function get_header() {
  include 'header.php';
}

function wp_footer() {
	return true;
}

function have_posts() {
	return false;
}

function _e($e) {
	echo $e;
}

function __() {
	return true;
}

function get_footer() {
	include 'footer.php';
}

function get_option() {
	return true;
}

function is_home() {
	return true;
}

function wp_title() {
	return "";
}

function get_locale() {
	return "";
}

function get_settings() {
	return "";
}

function wp_list_pages() {
	return "";
}

function wp_specialchars() {
	return "";
}

function get_sidebar() {
	include 'sidebar.php';
}

function is_single() {
	return true;
}

function get_posts() {
	return array();
}

function wp_tag_cloud() {
	return true;
}

function wp_list_cats() {
	return true;
}

function wp_list_bookmarks() {
	return true;
}

function wp_get_archives() {
	return true;
}

function wp_register() {
	return true;
}

function wp_loginout() {
	return true;
}

function get_row() {
	return true;
}


function render_theme( $theme, $title, $description ) {
  
  // dbscript
  global $request, $db;
  
  // wordpress
  global $blogdata, $optiondata, $current_user, $user_login, $userdata;
  global $user_level, $user_ID, $user_email, $user_url, $user_pass_md5;
  global $wpdb, $wp_query, $post, $limit_max, $limit_offset, $comments;
  global $req, $wp_rewrite, $wp_version, $openid, $user_identity, $logic;
  global $submenu;
  global $comment_author; 
  global $comment_author_email;
  global $comment_author_url;

  $folder = 'wp-content' . DIRECTORY_SEPARATOR . 'themes';
  $folder .= DIRECTORY_SEPARATOR . $theme . DIRECTORY_SEPARATOR;
  
  add_include_path($folder);
  
  global $wpmode;
  
  $wpmode = "posts";
  $wpdb = new wpdb();
  
	$blogdata = array(
	  'home'=>base_url(true),
	  'name'=>$title,
	  'subtitle'=>environment('site_subtitle'),
	  'description'=>$description,
	  'wpurl'=>base_url(true),
	  'url'=>base_url(true),
	  'atom_url'=>base_url(true).$atom,
	  'rss_url'=>base_url(true).$rss,
	  'rss2_url'=>base_url(true).$rss,
	  'charset'=>'utf-8',
	  'html_type'=>'text/html',
	  'theme_url'=>theme_path(false,$folder),
	  'stylesheet_url'=>theme_path(false,$folder)."style.css",
	  'stylesheet_directory'=>theme_path(false,$folder),
	  'pingback_url'=>base_url(true),
	  'template_url'=>theme_path(true,$folder)
	);

  if ($request->resource != 'posts' || !(in_array($request->action,array('replies','index')))) {
    $wpmode = "other";
    if (is_file($folder . "functions.php" ))
      require_once( $folder . "functions.php" );
    require_once( $folder . "page.php" );
  } else {
    if (is_file($folder . "functions.php" ))
      require_once( $folder . "functions.php" );
    if ( file_exists( $folder . "index.php" ))
      require_once( $folder . "index.php" );
    else
      require_once( $folder . "index.html" );
  }
}


class wpdb {
  var $base_prefix;
  var $prefix;
  var $show_errors;
  var $dbh;
  var $result;
  var $last_result;
  var $rows_affected;
  var $insert_id;
  var $col_info;
  var $posts;
  function wpdb() {
    $this->posts = 'posts';
    $this->col_info = array();
    $this->last_result = array();
    $this->base_prefix = "";
    $this->prefix = "";
    $this->show_errors = false;
  }
  function prepare() {
	  return true;
  }
  function get_row($query = null, $output = OBJECT, $y = 0) {
    return array();
  }
}

function base_url($return = false) {
  global $request;
  $base = $request->base;
  if ( !( substr( $base, -1 ) == '/' ))
    $base = $base . "/";
  if ($return)
    return $base;
  echo $base;
}

function theme_path($noslash = false,$path) {
  global $request,$db;
  $base = "";
  if ($noslash && "/" == substr($path,-1))
    $path = substr($path,0,-1);
  return $path;
}

function bloginfo( $attr ) {
  echo get_bloginfo($attr);
}

function get_bloginfo( $var ) {
  global $blogdata;
  if (in_array($var,array('wpurl')))
    if (isset($blogdata[$var]))
      if ("/" == substr($blogdata[$var],-1))
        return substr($blogdata[$var],0,-1);
  if (isset($blogdata[$var]))
    return $blogdata[$var];
  return "";
}

function javascript_periodical($source,$callback){
		echo <<<EOD
     <script type="text/javascript">

			var item_list = new Array();

			function add_item(data,entry){
				alert('add');
				item_list.push(data[entry]['time']);
			}
			
			$.PeriodicalUpdater('$source', {
			    method: 'get',          // method; get or post
			      data: '',                   // array of values to be passed to the page - e.g. {name: "John", greeting: "hello"}
			      minTimeout: 3000,       // starting value for the timeout in milliseconds
			      maxTimeout: 7000,       // maximum length of time between requests
			      multiplier: 2,          // if set to 2, timerInterval will double each time the response hasn't changed (up to maxTimeout)
			      type: 'text',           // response type - text, xml, json, etc.  See $.ajax config options
			    maxCalls: 0,            // maximum number of calls. 0 = no limit.
			    autoStop: 0             // automatically stop requests after this many returns of the same data. 0 = disabled.
			}, function(data) {
				if (data.length > 0) {
					data = data.substring(5);
		      eval( "data = " + data );
		    } else {
			    return false;
		    }
			  for ( var entry in data ) {
					found = false;
				  for ( var i=0; i<item_list.length; i++ )
				    if ( item_list[i] == data[entry]['time'] )
					    found = true;
				  if (found == false)
				    $callback(data,entry);
			  }
			
			});

			
      </script>
EOD;

return "";
}

