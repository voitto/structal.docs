<?php

class Tabs extends Helper {

  function Tabs( $template, $options=false ) {
		$this->template = $template;
		$this->options = $options;
		$this->tabs = array();
  }

  function create( $tab_id, $tab_next, $options=false ) {
	  $this->tabs[] = array(
		  $tab_id,
		  $tab_next,
		  $options
		);
  }

  function render() {

		//content_tag(:div, (render_tabs + render_bodies), {:id => :tabs}.merge(@options))
		
		$this->content_tag( 
			'div',
			$this->render_tabs().$this->render_bodies()
		);

  }

  function render_tabs() {
	
	  $tabs = '';

	  foreach( $this->tabs as $t ) {

		  $title = $t[1];
		  $id = $t[0];
		 
		  $tabs .= $this->content_tag(
			  'li', 
		    $this->content_tag( 
			    'a' , 
			    $title, 
			    array(
			  		'href'=>''.$id,
						'return'=>true
					)
				),
				array(
					'return'=>true
				)
			);

	  }

	  return $this->content_tag(
		  'ul', 
	    $tabs,
	    array(
		    'class'=>'tabs',
		    'return'=>true
			)
		);

		//content_tag :ul do
		  //@tabs.collect do |tab|
		    //content_tag(:li, link_to(content_tag(:span, tab[1]), "##{tab[0]}") )
		  //end
		//end
		
	}

  function render_bodies() {


	  $tabs = '';

    /*   non-ajax tabs
	  foreach( $this->tabs as $t ) {
		  $title = $t[1];
		  $tabs .= $this->content_tag(
			  'div', 
		    $title,
				array(
					'return'=>true
				)
			);

	  }


	  return $this->content_tag(
		  'div', 
	    $tabs,
	    array(
		    'class'=>'panes',
		    'return'=>true
			)
		);
	*/

  // ajax tabs
	return $this->content_tag(
	  'div', 
    $this->content_tag(
		  'div', 
	    $tabs,
	    array(
		    'style'=>'display:block',
		    'return'=>true
			)
		),
    array(
	    'class'=>'panes',
	    'return'=>true
		)
	);
    //@tabs.collect do |tab| 
      //content_tag(:div, capture(&tab[3]), tab[2].merge(:id => tab[0])) 
    //end.to_s

 	}

	function js(
			$animate=true, 
			$animationSpeed=5000, 
			$defaultTab="li#tab-2", 
			$panelActiveClass="active-content-div", 
			$tabActiveClass="selected-tab",
			$container="tab-container"
		) {

			echo <<<EOD
				<script type="text/javascript"> 

				  $(document).ready(function(){
					  $('#$container').easyTabs()
				  }); 
			
					$("#$container").easyTabs({
					  animate: $animate,
					  animationSpeed: $animationSpeed,
					  defaultTab: "$defaultTab",
					  panelActiveClass: "$panelActiveClass",
					  tabActiveClass: "$tabActiveClass"
					});
				
				</script>
EOD;

	}

	function javascript_include_tag(
			$animate='false', 
			$animationSpeed='5000', 
			$defaultTab="li#tab-2", 
			$panelActiveClass="displayed", 
			$tabActiveClass="selected-tab",
			$container="tab-container"
		) {

			echo <<<EOD
			
			
				<script type="text/javascript"> 

		  $(document).ready(function(){
				$("ul.tabs").tabs("div.panes > div", {effect: 'ajax'});
		  });
				</script>
EOD;
return "";

			echo <<<EOD
				<script type="text/javascript"> 

				  $(document).ready(function(){ $('#$container').easyTabs({
				    animate: $animate,
				    tabActiveClass: "$tabActiveClass",
				    panelActiveClass: "$panelActiveClass"
				  }); 
				if (confirm('hi')){
					alert('yes');
				}
				});

				</script>
EOD;

	}

	function tab( $id, $title, $target ) {
	  $this->content_tag(
		  'li', 
	    $this->content_tag( 
		    'a' , 
		    $title, 
		    array(
		  		'href'=>''.$id,
					'return'=>true
				)
			)
		);
	}
	
	function style() {

		echo <<<EOD
		
  <style type='text/css'>

		body {
			padding:50px 80px;
			font-family:"Lucida Grande","bitstream vera sans","trebuchet ms",sans-serif,verdana;
		}

		/* get rid of those system borders being generated for A tags */
		a:active {
		  outline:none;
		}

		:focus {
		  -moz-outline-style:none;
		}

		ul.tabs { 
			list-style:none; 
			margin:0 !important; 
			padding:0;	
			border-bottom:1px solid #666;	
			height:30px;
		}

		ul.tabs li { 
			float:left;	 
			text-indent:0;
			padding:0;
			margin:0 !important;
			list-style-image:none !important; 
		}

		ul.tabs a { 
			background: url(resource/tabs.png) no-repeat -420px 0;
			font-size:11px;
			display:block;
			height: 30px;  
			line-height:30px;
			width: 134px;
			text-align:center;	
			text-decoration:none;
			color:#333;
			padding:0px;
			margin:0px;	
			position:relative;
			top:1px;
		}

		ul.tabs a:active {
			outline:none;		
		}

		ul.tabs a:hover {
			background-position: -420px -31px;	
			color:#fff;	
		}

		ul.tabs a.current, ul.tabs a.current:hover, ul.tabs li.current a {
			background-position: -420px -62px;		
			cursor:default !important; 
			color:#000 !important;
		}

		.panes div {
			display:none;		
			padding:15px 10px;
			border:1px solid #999;
			border-top:0;
			height:100px;
			font-size:14px;
			background-color:#fff;
		}

  </style>

EOD;

	}

	function sidestyle() {

		echo <<<EOD
      <style type='text/css'>
				#tab-container { border: solid 1px; height: 300px; }
				#tab-container ul { height: 300px; list-style: none; margin: 0; padding: 0; background: #ccc; float: left; border-right: solid 1px; }
				#tab-container ul li { width: 100px; margin: 0; padding: 0; text-align: center; }
				#tab-container ul li a { display: block; padding: 15px 0; outline: none; }
				#tab-container ul li a:hover { text-decoration: underline; }
				#tab-container ul li.selected-tab { background: #fff; position: relative; left: 1px; border-style: solid; border-width: 1px 0; }
				#tab-container ul li:first-child.selected-tab { border-top: none; }
				#tab-container ul li a.selected-tab { font-weight: bold; text-decoration: none; }
				#tab-container .panel-container { padding-top: 5px; padding-left: 120px; }
      </style>
EOD;

	}

}



