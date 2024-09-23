<?php

/*
  XTTS is broken in VR. This patch allows one to use the mantella xtts runpod for their voices instead as a work-around.
  // https://runpod.io/console/gpu-cloud?template=x9ddee271u&amp;ref=szjabwfp
*/

require_once("util.php");
function parseVoiceType($voiceTypeRaw) {
    if (str_starts_with($GLOBALS["TTS"]["XTTSFASTAPI"]["voiceid"], "#")) {
        return ltrim($GLOBALS["TTS"]["XTTSFASTAPI"]["voiceid"], "#");
    }
    if ($voiceTypeRaw == "") {
        return "malecommoner"; // Don't know what their gender is here. Hmmm.
    }
    // [VoiceType <MaleGuard (000AA8D3)>
    $voiceType = substr($voiceTypeRaw, 12, -1);
    $voiceType = strtolower(substr($voiceType, 0, strpos($voiceType, " ")));
    return $voiceType;
}

if ($GLOBALS["HERIKA_NAME"] != "The Narrator") { // Users can configure the narrator on their own
    $GLOBALS["TTS"]["FORCED_VOICE_DEV"] = parseVoiceType(GetActorValue($GLOBALS["HERIKA_NAME"], "voiceType"));
}
?>
