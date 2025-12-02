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
		set_conf_opts_value('debug_data_raw', $s_json);
	else 
		set_conf_opts_value('debug_data_raw', '');
}

/*

$GLOBALS["LAST_LLM_RESPONSE"]

*/    


// minai_stop_timer('CHIM');