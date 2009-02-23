<?php

	/**
	 * New wire post view for the activity stream
	 */
	 
	$owner = $vars['entity']->guid;
	$url_to_wire = $vars['url'] . "mod/thewire/";
	
	//grab the users latest from the wire
	$latest_wire = get_entities("object", "thewire", $owner, "", 1, 0, false, 0, null); 

	if($latest_wire){
		foreach($latest_wire as $lw){
			$content = $lw->description;
			$time = "<span>(" . friendly_time($lw->time_created) . ")</span>";
		}
	}
	
	if($latest_wire){
		echo "<div class=\"profile_status\">";
		echo $content . " " . $time;
		if($owner == $_SESSION['user']->guid)
			echo " <a href=\"{$url_to_wire}\">update</a>";
		echo "</div>";
	}
?>