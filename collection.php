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
 * Collection
 *
 * @package   Structal
 * @author    Brian Hendrickson <brian@megapump.com>
 * @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link      http://structal.org/collection
 */

class Collection extends GenericIterator {
  
  var $member_entry_iri;
  
  var $media_iri;
  
  var $resource;
  
  var $accept;
  
  var $members;
  
  var $fields;
  
  var $updated;
  
  var $per_page;
  
  // member_entry_iri will be a media link or member entry
  
  // a media link entry is a member entry that
  // contains metadata about a media resource
  
  function Collection( $resource, $find_by = NULL, $accept = "text/html" ) {
    
    $this->_currentRow = 0;
    
    $this->EOF = false;
    
    $this->members = array();
    
    $this->fields = array();
        
    if ($resource == NULL)
      return;
    
    global $request;
    
    global $db;
    
    $this->resource = $resource;
    
    $this->accept = $accept;
    
    if ( $resource == 'introspection' ) {
      $this->members = introspect_tables();
      return;
    }
    
    if ($resource != classify($resource))
      $table =& $db->get_table( $this->resource );
    else
      return;
    
    // $member->member_entry_iri // Entry object of type 'member' or 'media link'
    
    // $member->media_iri = ; // (optional) Entry object of type 'media link'
    
    if ( isset( $table->params )) {
      foreach ( $table->params as $key=>$val ) {
        if (!(isset($this->$key)))
          $this->$key = $val;
      }
    }
    
    if (isset($request->params['offset']))
      $table->set_offset($request->params['offset']);
    
    if (isset($request->params['orderby']))
      $table->set_orderby($request->params['orderby']);
    
    if (isset($request->params['order']))
      $table->set_order($request->params['order']);
    
    if (isset($table->limit))
      $this->per_page = $table->limit;
    else
      $this->per_page = 20;

    if (isset($request->params['page']) && !is_array($request->params['page']))
      $table->set_offset( ($this->per_page * $request->params['page']) - $this->per_page );

    if ( !( $find_by == NULL )){
      $table->set_param('find_by',$find_by);
//print_r($find_by); 
//echo $table->get_query(); exit; 
     $table->find();
  }  elseif ( !$request->id ){
      $table->find();
}    else {
      $table->find( $request->id );
}    
    if (isset($table->uri_key))
      $uri_key = $table->uri_key;
    else
      $uri_key = $table->primary_key;
    
    if ($table->rowcount() > 0) {
      $first = true;
      $this->updated = timestamp();
      while ( $Member = $table->MoveNext() ) {
        if ( isset( $db->models['entries'] )) {
          $Entry = $Member->FirstChild( 'entries' );
          if ($Entry) {
            $Member->last_modified = $Entry->last_modified;
            $Member->etag = $Entry->etag;
          }
        }
        $this->members[$Member->$uri_key] = $Entry->last_modified;
        if ($first) {
          if (isset($Member->created) && !empty($Member->created))
            $this->updated = $Member->created;
          elseif (isset($Member->modified) && !empty($Member->modified))
            $this->updated = $Member->modified;
          elseif (!empty($Entry->last_modified))
            $this->updated = $Entry->last_modified;
        }
        $first = false;
      }
      $table->rewind();
    }
    
  }
  
  function rewind() {
    global $db;
    global $request;
    $model =& $db->models[$this->resource];
    $model->rewind();
    $this->_currentRow = 0;
    $this->EOF = false;
  }
  
  function MoveNext() {
    global $db;
    global $request;
    $model =& $db->models[$this->resource];
    $this->_currentRow++;
    if ($this->_currentRow <= $this->per_page) {
      if ($model)
        return $model->MoveNext();
    }
    
    return false;
    
  }
  
  function MoveFirst() {
    global $db;
    $model =& $db->models[$this->resource];
    if ($model)
      return $model->MoveFirst();
  }
  
}

?>