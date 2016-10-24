<?php

function call_progress_bar($test_data, $data) {

	global $CFG;
	
	$data['boxwidth'] = $data['boxwidth'] + 40; //just to add some inner margin
	$data['boxheight'] = $data['boxheight'] + 5;

	
	$progress_bar_id = 'progress_bar'.$data['id'];
	$progress_bar_name = 'progress_bar'.$data['id'];

    $response = '';
	$response .= "<script type=\"text/javascript\" id=\"js_{$progress_bar_id}\">";
	$response .= "setupProgressBar({$data['boxwidth']}, {$data['boxheight']}, '{$progress_bar_id}', '{$progress_bar_name}', '{$test_data}')";
	$response .= '</script>';

	$response .= <<<EOF
    <noscript>
	<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000"
		id="{$progress_bar_id}" width="{$data['boxwidth']}" height="{$data['boxheight']}"
		codebase="http://fpdownload.macromedia.com/get/flashplayer/current/swflash.cab">
		<param name="movie" value="{$CFG->wwwroot}/blocks/userquiz_monitor/generators/progress_bar.swf" />
		<param name="quality" value="high" />
		<param name="wmode" value="transparent"/>
		<param name="bgcolor" value="#ffffff" />
		<param name="scale" value="exactfit" />
	    <param name="allowScriptAccess" value="sameDomain" />
	<embed src="{$CFG->wwwroot}/blocks/userquiz_monitor/generators/progress_bar.swf" quality="high" bgcolor="#ffffff"
		width="'{$data['boxwidth']}'" height="{$data['boxheight']}" name="{$progress_bar_name}" align="middle"
		play="true"
		loop="false"
		quality="high"
		scale="exactfit"
		allowScriptAccess="sameDomain"
		type="application/x-shockwave-flash"
		pluginspage="http://www.adobe.com/go/getflashplayer"></embed>
	</object>
	</noscript>
EOF;


	return ($response);
}


function call_progress_bar_html($test_data, $data){
    global $CFG;

	$data['boxwidth'] = $data['boxwidth'] + 40; //just to add some inner margin
	$data['boxheight'] = $data['boxheight'] + 5;
    $str = <<<EOF
	<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000"
		id="progress_bar{$data['id']}" width="{$data['boxwidth']}" height="{$data['boxheight']}"
		codebase="http://fpdownload.macromedia.com/get/flashplayer/current/swflash.cab">
		<param name="movie" value="{$CFG->wwwroot}/blocks/userquiz_monitor/generators/progress_bar.swf" />
		<param name="quality" value="high" />
		<param name="swliveconnect" value="true" />
		<param name="bgcolor" value="#ffffff" />
		<param name="wmode" value="transparent"/>
		<param name="flashvars" value="data={$test_data}" />
	    <param name="allowScriptAccess" value="sameDomain" />
	    <param name="scale" value="noborder" />
        <embed src="{$CFG->wwwroot}/blocks/userquiz_monitor/generators/progress_bar.swf" 
               width="{$data['boxwidth']}" height="{$data['boxheight']}" align="middle" 
			   wmode= "transparent"
               id="progress_bar{$data['id']}" quality="high" bgcolor="#ffffff" name="progress_bar{$data['id']}" 
               flashvars="data={$test_data}" 
               swliveconnect="true"
               allowScriptAccess="sameDomain" 
               pluginspage="http://www.adobe.com/go/getflashplayer" 
               type="application/x-shockwave-flash" >
        </embed>        
   </object>
EOF;

    return $str;
}
