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
 * Route
 *
 * connects the current URI to a Route,
 * establishing the request variable names
 * e.g. my_domain/:resource/:id
 * maps values into $req->resource and $req->id
 *
 * <code>
 *
 * $req->connect( 'virtualdir/:var1/:var2' );
 *
 * </code>
 *
 * @package   Structal
 * @author    Brian Hendrickson <brian@megapump.com>
 * @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link      http://structal.org/route
 */

class Route {

  var $patterns;
  var $defaults;
  var $requirements;
  var $match;
  var $name;
  
  function Route() {

    $this->patterns = array();
    $this->requirements = array();
    $this->match = false;
    
    $this->defaults = array(
      
      'controller'=>'index.php',
      'resource'=>NULL,
      'id'=>0,
      'action'=>'get',
      'child'=>0
      
    );
    
  }
  
  function build_url( $params, $base, $prefix = '' ) {
    $url = array();
    
    foreach ( $this->patterns as $pos => $str ) {
      if ( substr( $str, 0, 1 ) == ':' ) {
        $url[] = $params[substr( $str, 1 )];
      } else {
        $url[] = $str;
      }
    }
    global $pretty_url_base,$request;
    // XXX subdomain upgrade
    if (isset($pretty_url_base) && !empty($pretty_url_base))
      $base = $pretty_url_base."/".$request->prefix;
    if ( !( substr( $base, -1 ) == '/' ))
      $base = $base . "/";
    // XXX subdomain upgrade
    if (!empty($prefix)) $q = "";
      else $q = "?";
    if (isset($pretty_url_base) && !empty($pretty_url_base))
      return $base . implode ( '/', $url );
    else
      return $base .$q.  implode ( '/', $url );
  }

}

