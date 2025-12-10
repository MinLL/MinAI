<?php

if (isset($GLOBALS["DEBUG_DATA"]["RAW"])) {
	$s_json = trim($GLOBALS["DEBUG_DATA"]["RAW"]); 
	$b_ok = (str_starts_with($s_json, "{") && str_ends_with($s_json, "}"));
	if (!$b_ok) {
		$s_json1 = strstr($s_json, '{', false) ?? "";
		if (strlen($s_json1) > 5) {
		$s_json = (strstr($s_json1, '}', true) ?? "").'}';
			if (strlen($s_json) > 5) {
				$b_ok = (str_starts_with($s_json, "{") && str_ends_with($s_json, "}"));
			}
		}
	}
	if ($b_ok)
		setConfOption('debug_data_raw', $s_json);
	else 
		setConfOption('debug_data_raw','');
}

/*
$semaphore_main = $GLOBALS["SEMAPHORES"]["MAIN"] ?? null;
$semaphore_addnpc = $GLOBALS["SEMAPHORES"]["ADDNPC"] ?? null;
$semaphore_vsx = $GLOBALS["SEMAPHORES"]["VSX"] ?? null;

if (isset($semaphore_main) && $semaphore_main) {
	@sem_release($semaphore_main);
	$sx = "released";
} else {
	$sx = "undefined";	
}
Logger::error("[postrequest] semaphore_main $sx - exec trace " .__FILE__ . " " . __LINE__);

if (isset($semaphore_addnpc) && $semaphore_addnpc) {
	@sem_release($semaphore_addnpc);
	$sx = "released";
} else {
	$sx = "undefined";	
}
Logger::error("[postrequest] semaphore_addnpc $sx - exec trace " .__FILE__ . " " . __LINE__);

if (isset($semaphore_vsx) && $semaphore_vsx) {
	@sem_release($semaphore_vsx);
	$sx = "released";
} else {
	$sx = "undefined";	
}
Logger::error("[postrequest] semaphore_vsx $sx - exec trace " .__FILE__ . " " . __LINE__);
*/

/*

$GLOBALS["LAST_LLM_ RESPONSE"]

*/    


// minai_stop_timer('CHIM');