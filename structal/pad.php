<?php


  function jqpad_init( $options=array() ) {
	  global $helper;
	  $title = "Structal Touch";
	  if (isset($options['title']))
	    $title = $options['title'];
    $helper->javascript_tag('$.jQPad({
			siteName: "'.$title.'",
			defaultHomeTitle: "Home",
			defaultPage: "content/default/",
			ajax: {
				cache: true,
				defaultNavMethod: true
			},
			splash: "jqpad/misc/jqpad-splash.png",
			icon: "jqpad/misc/jqpad-icon.PNG"
		});', array( 'charset'=>"utf-8" ));
  }

  function jqpad_css( $options=array() ) {
	  echo <<<EOD
			<link rel="stylesheet" type="text/css" href="jqpad/themes/global.css" />
			<link rel="stylesheet" type="text/css" href="jqpad/themes/default/style.css" />
			<script src="jqpad/scripts/jqpad.iscroll.js" type="text/javascript"></script>
			<script src="jqpad/scripts/jqpad.jquery.js" type="text/javascript"></script>
EOD;
  }

	function pad_page( $id, $options = array() ) {
     echo <<<EOD
			<div class="content-left">
EOD;
	}
	function end_pad_page() {
     echo <<<EOD
			</div>
EOD;
	}

	function pad_toolbar( $title, $options = array() ) {
     echo <<<EOD
			<div class="toolbar left"><h1></h1></div>
				<div class="scroll-wrapper">

					<div class="content-main" id="scrollableleft">

						<ul class="nav">

							<div class="panel" id="login"></div>
EOD;
	}
	function end_pad_toolbar() {
    echo <<<EOD
				</ul>

			</div>

		</div>
EOD;
	}

	function pad_button_to( $name, $options, $html_options = array() )   {  
		$link = $options;
		$image = $html_options['icon'];
		$title = $name;
    echo <<<EOD
	     <li><img src="$image"><h2><a href="$link" title="$title">$title</a></h2></li>
EOD;
	}

	function pad_panel( $id, $options = array() ) {
    echo <<<EOD
		<div class="content-right">
			<div class="toolbar"><h1></h1></div>
EOD;
	}
	function end_pad_panel() {
	  end_pad_page();
	}

	function pad_back_button( $name, $html_options = array() ) {
	}

	function build_pad_toolbar( $title, $options = array() ) {
	}
	function end_build_pad_toolbar() {
	}
    
	function build_pad_page( $id, $options = array(), $prebody_html = "" ) {
	}
	function end_build_pad_page() {
	}

	function pad_list_item( $item, $options = array() ) {
	}

	function pad_list( $items, $options = array() ) {
	}
	
	function pad_info_section( $options = array() ) {
	}
    
	function pad_fieldset( $options = array() ) {
	}
  
	function pad_row( $name = false, $options = array() ) {
	}

	function pad_page_with_toolbar( $title, $options = array() ) {
	}




