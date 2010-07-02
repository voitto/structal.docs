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
 * ResultIterator
 *
 * Attached to a RecordSet to lazy-load its result resource.
 *
 * @package   Structal
 * @author    Brian Hendrickson <brian@megapump.com>
 * @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link      http://structal.org/resultiterator
 */
   
class ResultIterator extends GenericIterator {

  var $rs;
  var $result;
  var $rowcount;
  var $table_name;
  var $tablemapper;
  var $pkvals;

  function ResultIterator( &$rs, $table ) {

    $this->rs =& $rs;
    $this->result =& $rs->result;
    $this->table_name = $table;
    $this->_currentRow = 0;
    $this->EOF = false;
    $this->tablemapper = array();
    $this->pkvals = array();
    $this->rowcount = 0;
    foreach ( $rs->rowmap as $table => $pkvals ) {
      if ($table == $this->table_name) {
        foreach ( $pkvals as $pk => $result_row ) {
          if ($pk != 0) {
            $this->tablemapper[] = $result_row;
            $this->pkvals[] = $pk;
            $this->rowcount++;
          }
        }
      }
    }
  }

  function seek( $row ) {
    $return = false;
    if ( !( $this->rowcount > 0 )) {
      $this->EOF = true;
    }
    if ( !( $row < $this->rowcount )) {
      $this->EOF = true;
    }
    if ($this->valid()) {
      $this->_currentRow = $row;
      $return = true;
    }
    return $return;
  }
  
  function MoveFirst() {
    $this->EOF = false;
    $this->_currentRow = 0;
    if ($this->seek( 0 )) return $this->Load();
    return false;
  }
  
  function MoveNext() {
    global $db;
    if ($this->seek( $this->_currentRow )) {
      $rec = $this->Load();
      $this->_currentRow++;
      foreach ($db->models[$this->table_name]->relations as $table=>$vals) {
        if (!(isset($rec->$table))) 
          $rec->$table = $rec->FirstChild( $table );
      }
      return $rec;
    }
    return false;
  }
  
  function FirstChild( $parent_pkval ) {
    $this->_currentRow = array_search( $this->rs->relations[$parent_pkval][$this->table_name][0], $this->pkvals, false );
    if ( $this->seek( $this->_currentRow ) && in_array( $this->pkvals[$this->_currentRow], $this->rs->relations[$parent_pkval][$this->table_name] ) ) {
      return $this->Load();
    }
    return false;
  }
  
  function NextChild( $parent_pkval ) {
    if ( $this->seek( $this->_currentRow ) && in_array( $this->pkvals[$this->_currentRow], $this->rs->relations[$parent_pkval][$this->table_name] ) ) {
      $rec = $this->Load();
      $this->_currentRow++;
      return $rec;
    }
    return false;
  }
  
  function Load() {
    if ( !( $this->valid() ) ) return NULL;
    return $this->rs->Load( $this->table_name, $this->tablemapper[$this->_currentRow] );
  }

}

?>