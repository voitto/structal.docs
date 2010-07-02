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
 * RecordSet
 *
 * RecordSets are objects comprised of a join query result resource
 * and a lazy-loading iterator for each table in the result.
 *
 * <code>
 *
 * $rs = $db->get_recordset( $people->get_query );
 *
 * while ( $Person = $rs->MoveNext() )
 *   print $Person->name;
 *
 * </code>
 *
 * @package   Structal
 * @author    Brian Hendrickson <brian@megapump.com>
 * @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link      http://structal.org/recordset
 */

class RecordSet {
  
  var $query;
  var $table;
  var $result;
  var $rowcount;
  var $fieldlist;
  var $tablelist;
  var $rowmap;
  var $iterator;
  var $activerow;
  var $relations;
  
  function RecordSet( $sql ) {

    global $db;

    $this->query = $sql;
    $this->result = $db->get_result($sql, true);
    $this->rowcount = $db->num_rows($this->result);
    $this->fieldlist = array();
    $this->tablelist = array();

    // get table and field names from result column headers
    $num_fields = $db->num_fields( $this->result );
    for ( $i = 0; $i < $num_fields; $i++ ) {
      $col = split( "\.", $db->field_name( $this->result, $i ) );
      if ( count( $col ) == 2 && $col[0] && $col[1] ) {
        $tab = $col[0];
        $fld = $col[1];
        if (substr($tab,2,1) == '_')
          $tab = substr($tab,3);
        $this->fieldlist[$tab][$fld] = $i;
        if ($fld == $db->models[$tab]->primary_key) {
          $this->tablelist[$tab] = $i; // pk offset
        }
        if ($i == 0) $this->table = $tab;
      } else {
        trigger_error( 'Malformed SQORP query "'.$db->field_name( $this->result, $i ).'". Example: select people.id as "people.id".', E_USER_ERROR );
      }
    }

    $this->rowmap = array();
    $this->relations = array();
    
    // read the primary key value(s) in each row and map them to the result row number

    for ( $i = 0; $i < $db->num_rows( $this->result ); $i++ ) {
      foreach ( $this->tablelist as $table => $pkoffset ) {
        $pkvalue = $db->result_value( $this->result, $i, $pkoffset );
        if ( $pkvalue ) {
          $this->rowmap[$table][$pkvalue] = $i;
          if ( !( $table == $this->table ) ) {
            $this->relations[$db->result_value( $this->result, $i, $this->tablelist[$this->table] )][$table][] = $pkvalue;
          }
        }
      }
    }
    
    $this->iterator = array();
    $this->activerow = array();

  }
  
  function MoveFirst( $table ) {
    if ( array_key_exists( $table, $this->fieldlist )) {
      if ( !( isset( $this->iterator[$table] ))) {
        $this->iterator[$table] = new ResultIterator( $this, $table );
      }
      return $this->iterator[$table]->MoveFirst();
    } else {
      return false;
    }
  }
  
  function MoveNext( $table = NULL ) {
    if ($table === NULL) {
      $keys = array_keys( $this->fieldlist );
      $table = $keys[0];
    }
    if ( array_key_exists( $table, $this->fieldlist )) {
      if ( !( isset( $this->iterator[$table] ))) {
        $this->iterator[$table] = new ResultIterator( $this, $table );
      }
      return $this->iterator[$table]->MoveNext();
    } else {
      return false;
    }
  }
  
  function FirstChild( $parent_pkval, $table ) {
    if ( array_key_exists( $table, $this->fieldlist )) {
      if ( !( isset( $this->iterator[$table] ))) {
        $this->iterator[$table] = new ResultIterator( $this, $table );
      }
      return $this->iterator[$table]->FirstChild( $parent_pkval );
    } else {
      return false;
    }
  }

  function NextChild( $parent_pkval, $table ) {
    if ( array_key_exists( $table, $this->fieldlist )) {
      if ( !( isset( $this->iterator[$table] ))) {
        $this->iterator[$table] = new ResultIterator( $this, $table );
      }
      return $this->iterator[$table]->NextChild( $parent_pkval );
    } else {
      return false;
    }
  }
  
  function Load( $table, $row ) {
    global $db;
    trigger_before( 'Load', $db, $this ); 
    if ( !( $row < $this->rowcount )) return false;
    if ( array_key_exists( $table, $this->fieldlist )) {
      $this->activerow[$table] = $db->fetch_array( $this->result, $row );
      foreach ( $this->fieldlist[$table] as $field => $idx ) {
        $this->fieldlist[$table][$field] =& $this->activerow[$table][$db->prefix.$table.".".$field];
      }
      trigger_after( 'Load', $db, $this ); 
      return $db->iterator_load_record( $table, $this->fieldlist[$table], $this );
    } else {
      return false;
    }
  }
  
  function rewind() {
    $table = $this->table;
    $row = 0;
    if ( array_key_exists( $table, $this->fieldlist )) {
      if ( !( isset( $this->iterator[$table] ))) {
        $this->iterator[$table] = new ResultIterator( $this, $table );
      }
      return $this->iterator[$table]->seek( $row );
    } else {
      return false;
    }
  }
  
  function num_rows( $table ) {
    if (isset($this->rowmap[$table]))
      return count($this->rowmap[$table]);
    return 0;
  }

}

