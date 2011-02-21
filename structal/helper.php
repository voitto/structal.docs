<?php

class Helper {
	
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

class AuthToken  {
 
  var $token;
  var $secret;

}

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

function redirect_to( $url ) {
	  header('Location: ' . $url );
	  exit;
}
