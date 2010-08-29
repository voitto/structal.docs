<?php

  function jqtouch_init( $options=array() ) {
	  global $helper;
	  if (isset($options['status_bar']))
	    $status_bar = $options['status_bar'];
	  else
	    $status_bar = 'black-translucent';
	  if (isset($options['preload']))
	    $preload = $options['preload'];
	  else
	    $preload = <<<EOD
	[
        'jqtouch/themes/jqt/img/back_button.png',
        'jqtouch/themes/jqt/img/back_button_clicked.png',
        'jqtouch/themes/jqt/img/button_clicked.png',
        'jqtouch/themes/jqt/img/grayButton.png',
        'jqtouch/themes/jqt/img/whiteButton.png',
        'jqtouch/themes/jqt/img/loading.gif'
        ]
EOD;
    $helper->javascript_tag("var jQT = new $.jQTouch({
		    icon: 'jqtouch.png',
		    addGlossToIcon: false,
		    startupScreen: 'jqt_startup.png',
		    statusBar: '$status_bar',
		    preloadImages: $preload
		});", array( 'charset'=>"utf-8" ));
  }

  function jqtouch_css( $options=array() ) {
	  echo <<<EOD
  <style type="text/css" media="screen">
      body.fullscreen #home .info {
          display: none;
      }
      #about {
          padding: 100px 10px 40px;
          text-shadow: rgba(255, 255, 255, 0.3) 0px -1px 0;
          font-size: 13px;
          text-align: center;
          background: #161618;
      }
      #about p {
          margin-bottom: 8px;
      }
      #about a {
          color: #fff;
          font-weight: bold;
          text-decoration: none;
      }
  </style>
EOD;
  }

	function mobile_page( $id, $options = array() ) {
		$options['no-end'] = true;
    $options['class']  = 'current';
	  build_page( $id, $options );
	}
	function end_mobile_page() {
	  end_build_page();
	}

	function mobile_toolbar( $title, $options = array() ) {
    $options['no-end'] = true;
	  build_toolbar( $title, $options );
	}
	function end_mobile_toolbar() {
	  end_build_toolbar();
	}

	function mobile_button_to( $name, $options, $html_options = array() )   {  
	  global $helper;
	  if (!isset($html_options["class"]))
	    $html_options["class"] = "button";
	  if (!isset($html_options["href"]))
	    $html_options['href'] = $options;
	  if (isset($html_options['effect']))
		  $html_options["class"] .= ' '.$html_options['effect'];
    $helper->content_tag( 'a', $name, $html_options ); 
	}

	function mobile_panel( $id, $options = array() ) {
	  $options["class"] = "panel";
	  $options["no-end"] = true;
	  mobile_page( $id, $options );
	}
	function end_mobile_panel() {
	  end_mobile_page();
	}

	function mobile_pad( $options = array() ) {
	  global $helper;
	  $options["class"] = "pad";
	  $options['no-end'] = true;
	  $helper->content_tag( "div", '', $options );
	}
	function end_mobile_pad() {
	  global $helper;
		$helper->end_content_tag("div");
	}

	function mobile_back_button( $name, $html_options = array() ) {
		if (isset($html_options['class']))
	    $html_options["class"] = "button back ".$html_options['class'];
	  else 
	    $html_options['class'] = 'button back';
	  mobile_button_to( $name, "#", $html_options );
	}

	function build_toolbar( $title, $options = array() ) {
	  global $helper;
		$options['no-end'] = true;
		$options['class'] = 'toolbar';
		$helper->content_tag("div",
		$helper->content_tag("h1", $title, array('return'=>true)),$options);
	}
	function end_build_toolbar() {
	  global $helper;
		$helper->end_content_tag("div");
	}
    
	function build_page( $id, $options = array(), $prebody_html = "" ) {
	  global $helper;
		$options['id'] = $id;
	  return $helper->content_tag( "div", $prebody_html, $options );
	}
	function end_build_page() {
	  global $helper;
		$helper->end_content_tag("div");
	}

	function mobile_list_item( $item, $options = array() ) {
	  global $helper;
		$options['class'] = 'forward';
		$html_options = array('return'=>true);
		$html_options['href'] = $item['url'];
	  $helper->content_tag("li", $helper->content_tag( 'a', $item['name'], $html_options ),$options);
		echo "\n";
	}

	function mobile_list( $items, $options = array() ) {
	  global $helper;
	  $options['no-end'] = true;
	  $options['class'] = 'rounded';
	  $helper->content_tag("ul", "", $options);
		echo "\n";
	  foreach($items as $i)
	    mobile_list_item($i, array());
    $helper->end_content_tag("ul");
	}
	
	function mobile_info_section( $options = array() ) {
	}
    
	function mobile_fieldset( $options = array() ) {
	}
  
	function mobile_row( $name = false, $options = array() ) {
	}

	function mobile_page_with_toolbar( $title, $options = array() ) {
	}

	function item_count( $value ){
	}



