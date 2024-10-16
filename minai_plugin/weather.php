<?php


function GetWeatherContext() {
    $weathers = [
        "0010fef8" => ["name" => "SovngardeDark", "description" => "Weather as seen in Sovngarde with clouds."],
        "0010fe7e" => ["name" => "RiftenOvercastFog", "description" => "Dense fog and dark cloudy atmosphere as seen in Riften."],
        "0010e3d4" => ["name" => "EditorCloudPreview", "description" => "Cloudy sky."],
        "0010e1f2" => ["name" => "SkyrimClear_A", "description" => "Near cloudless, blue sky."],
        "0010e1f1" => ["name" => "SkyrimCloudy_A", "description" => "Cloudy sky."],
        "0010e1f0" => ["name" => "SkyrimClearSN_A", "description" => "Near cloudless, blue sky."],
        "0010e1ef" => ["name" => "SkyrimCloudySN_A", "description" => "Cloudy sky."],
        "0010e1ee" => ["name" => "SkyrimClearTU_A", "description" => "Near cloudless, blue sky."],
        "0010e1ed" => ["name" => "SkyrimCloudyTU_A", "description" => "Cloudy sky."],
        "0010e1ec" => ["name" => "SkyrimClearFF_A", "description" => "Near cloudless, blue sky."],
        "0010e1eb" => ["name" => "SkyrimCloudyFF_A", "description" => "Cloudy sky."],
        "0010e1ea" => ["name" => "SkyrimClearRE_A", "description" => "Cloudy sky."],
        "0010e1e9" => ["name" => "SkyrimCloudyRE_A", "description" => "Cloudy sky."],
        "0010e1e8" => ["name" => "SkyrimClearCO_A", "description" => "Cloudy sky."],
        "0010e1e7" => ["name" => "SkyrimCloudyCO_A", "description" => "Dark cloudy sky."],
        "0010e1e6" => ["name" => "SkyrimClearMA_A", "description" => "Cloudy sky."],
        "0010e1e5" => ["name" => "SkyrimCloudyMA_A", "description" => "Dark cloudy sky."],
        "0010e1e4" => ["name" => "SkyrimClearVT_A", "description" => "Cloudy sky."],
        "0010e1e3" => ["name" => "SkyrimCloudyVT_A", "description" => "Cloudy sky."],
        "0010da13" => ["name" => "FXWthrInvertWfindowsWinterhold", "description" => "Black and white contrast."],
        "0010d9ec" => ["name" => "SovngardeClear", "description" => "Weather as seen in Sovngarde with clear sky."],
        "0010c32f" => ["name" => "FXSkyrimStormBlowingGrass", "description" => "Dark cloudy sky with flying grass particles and moving grass and trees."],
        "0010a7a8" => ["name" => "SkyrimCloudyVT", "description" => "Cloudy sky."],
        "0010a7a7" => ["name" => "SkyrimFogVT", "description" => "Cloudy sky with fog."],
        "0010a7a6" => ["name" => "SkyrimOvercastRainVT", "description" => "Cloudy sky with fog and light rain."],
        "0010a7a5" => ["name" => "SkyrimClearVT", "description" => "Cloudy sky."],
        "0010a245" => ["name" => "SkyrimCloudySN", "description" => "Cloudy sky."],
        "0010a244" => ["name" => "SkyrimClearSN", "description" => "Near cloudless, blue sky."],
        "0010a243" => ["name" => "SkyrimCloudyTU", "description" => "Very cloudy sky."],
        "0010a242" => ["name" => "SkyrimOvercastRainTU", "description" => "Cloudy sky with fog and light rain."],
        "0010a241" => ["name" => "SkyrimStormRainTU", "description" => "Cloudy sky with fog and medium rain."],
        "0010A23C" => ["name" => "SkyrimStormRainFF", "description" => "Cloudy sky with fog and medium rain."],
        "0010a240" => ["name" => "SkyrimClearTU", "description" => "Near cloudless, blue sky."],
        "0010a23f" => ["name" => "SkyrimCloudyFF", "description" => "Cloudy sky."],
        "0010a23e" => ["name" => "SkyrimFogFF", "description" => "Cloudy sky with fog."],
        "0010a23d" => ["name" => "SkyrimOvercastRainFF", "description" => "Cloudy sky with fog and light rain."],
        "0010a23b" => ["name" => "SkyrimClearFF", "description" => "Near cloudless, blue sky."],
        "0010a23a" => ["name" => "SkyrimCloudyRE", "description" => "Cloudy sky with fog."],
        "0010a239" => ["name" => "SkyrimFogRE", "description" => "Cloudy sky with fog."],
        "0010a238" => ["name" => "SkyrimOvercastRainRE", "description" => "Cloudy sky with fog and light rain."],
        "0010a237" => ["name" => "SkyrimClearRE", "description" => "Cloudy sky."],
        "0010a236" => ["name" => "SkyrimCloudyCO", "description" => "Very cloudy sky."],
        "0010a235" => ["name" => "SkyrimFogCO", "description" => "Cloudy sky with fog."],
        "0010a234" => ["name" => "SkyrimClearCO", "description" => "Near cloudless, blue sky."],
        "0010a233" => ["name" => "SkyrimCloudyMA", "description" => "Cloudy sky."],
        "0010a232" => ["name" => "SkyrimFogMA", "description" => "Cloudy sky with fog."],
        "0010a231" => ["name" => "SkyrimOvercastRainMA", "description" => "Cloudy sky with fog and light rain."],
        "0010a230" => ["name" => "SkyrimClearMA", "description" => "Near cloudless, blue sky."],
        "00106635" => ["name" => "KarthspireRedoubtFog", "description" => "Cloudy sky with fog as seen in Karthspire."],
        "00105f40" => ["name" => "SkyrimDA02Weather", "description" => "Cloudy sky with fog."],
        "00105945" => ["name" => "SolitudeBluePalaceFog", "description" => "Cloudy sky with fog."],
        "00105944" => ["name" => "SolitudeBluePalaceFogNMARE", "description" => "Cloudy sky with fog, reddish hue."],
        "00105943" => ["name" => "SolitudeBluePalaceFogFEAR", "description" => "Cloudy sky with fog, green hue."],
        "00105942" => ["name" => "SolitudeBluePalaceFogARENA", "description" => "Cloudy sky with fog, grey hue."],
        "00105941" => ["name" => "BloatedMansGrottoFog", "description" => "Cloudy sky with fog, dark red hue."],
        "00104ab4" => ["name" => "SkuldafnCloudy", "description" => "Cloudy sky with light snow."],
        "0010199f" => ["name" => "SkyrimMQ206weather", "description" => "Cloudy sky with fog, dark red hue."],
        "00101910" => ["name" => "FXWthrInvertLightMarkarth", "description" => "Almost no light."],
        "000ecc96" => ["name" => "FXWthrInvertWindowsWindhelm2", "description" => "Almost no light."],
        "000d9329" => ["name" => "HelgenAttackWeather", "description" => "Cloudy sky."],
        "0005ed7a" => ["name" => "FXWthrInvertLightsSolitude", "description" => "Almost no light."],
        "0008282a" => ["name" => "FXWthrInvertLightsWhiterun", "description" => "Almost no light."],
        "0008277A" => ["name" => "FXWthrInvertWindowsWhiterun", "description" => "Almost no light."],
        "000d4886" => ["name" => "FXMagicStormRain", "description" => "Dark cloudy sky with heavy rainfall and thunder."],
        "000d299e" => ["name" => "SkyrimOvercastWar", "description" => "Cloudy sky."],
        "000c8221" => ["name" => "SkyrimStormSnow", "description" => "Cloudy sky and heavy blizzard."],
        "000c8220" => ["name" => "SkyrimStormRain", "description" => "Cloudy sky and medium rainfall."],
        "000c821f" => ["name" => "SkyrimOvercastRain", "description" => "Cloudy sky and medium rainfall."],
        "000c821e" => ["name" => "SkyrimFog", "description" => "Cloudy sky and fog."],
        "00075491" => ["name" => "FXWthrSunlightWhite", "description" => "No sky."],
        "0007548f" => ["name" => "FXWthrSunlight", "description" => "No sky."],
        "00048c14" => ["name" => "BlackreachWeather", "description" => "Dark sky with blueish hue as seen in Blackreach."],
        "000aee84" => ["name" => "FXWthrInvertWindowsWindhelm", "description" => "Almost no light."],
        "000a6858" => ["name" => "WorldMapWeather", "description" => "Weather as in worldmap."],
        "000923fd" => ["name" => "SovngardeFog", "description" => "Cloudy sky with fog."],
        "000777cf" => ["name" => "FXWthrInvertDayNighWarm", "description" => "Almost no light."],
        "00075de5" => ["name" => "FXWthrCaveBluePaleLight", "description" => "No sky, less light."],
        "0006ed5b" => ["name" => "FXWthrCaveBlueSkylight", "description" => "No sky, less light, blue hue."],
        "0006ed5a" => ["name" => "FXWthrInvertDayNight", "description" => "Almost no light."],
        "0004d7fb" => ["name" => "SkyrimOvercastSnow", "description" => "Cloudy sky with heavy snowfall."],
        "0002e7ab" => ["name" => "TESTCloudyRain", "description" => "Distorted sky with light rain."],
        "00012f89" => ["name" => "SkyrimCloudy", "description" => "Cloudy sky."],
        "0000081a" => ["name" => "SkyrimClear", "description" => "Near cloudless, blue sky."],
        "0000015e" => ["name" => "DefaultWeather", "description" => "Cloudless, dark sky."],
        "02010E0F" => ["name" => "DLC1 SkyrimClearFV", "description" => "Near cloudless, blue sky."],
        "02010E10" => ["name" => "DLC1 SkyrimClearFV_A", "description" => "Near cloudless, blue sky."],
        "02010E0E" => ["name" => "DLC1 SkyrimCloudyFV", "description" => "Cloudy sky."],
        "02010E0B" => ["name" => "DLC1 SkyrimCloudyFV_A	", "description" => "Cloudy sky."],
        "0200F89C" => ["name" => "DLC1AurielsBowClearWeather", "description" => "Near cloudless, blue sky."],
        "02006AEC" => ["name" => "DLC1Eclipse", "description" => ""],
        "02019599" => ["name" => "DLC1FalmerValley bf", "description" => "Cloudy sky."],
        "020195A0" => ["name" => "DLC1FalmerValley bfDark", "description" => "Cloudy sky."],
        "0200F89D" => ["name" => "DLC1MagicAurielBowCloudyWeather", "description" => "Cloudy sky."],
        "02001407" => ["name" => "SoulCairnAmb01", "description" => "Cloudy sky with fog, purple hue."],
        "02006aec" => ["name" => "SoulCairnAurora", "description" => "Cloudy sky with fog, red hue."],
        "02018dbb" => ["name" => "SoulCairnAmb02", "description" => "Cloudy sky with fog, purple hue."],
        "02018dbc" => ["name" => "SoulCairnAmb03", "description" => "Cloudy sky with fog, purple hue."],
        "02018dbd" => ["name" => "SoulCairnAmb04", "description" => "Cloudy sky with fog, purple hue."],
        "02014551" => ["name" => "SoulCairnAmb01", "description" => "Rain Cloudy sky with fog, purple hue and light rain."],
        "04018471" => ["name" => "DLC02VolcanicAsh01", "description" => "Cloudy sky with ash particles."],
        "040374B8" => ["name" => "DLC02VolcanicAsh01_A", "description" => "Cloudy sky with ash particles."],
        "04031AC0" => ["name" => "DLC02VolcanicAsh02", "description" => "Cloudy sky with ash particles."],
        "040374B9" => ["name" => "DLC02VolcanicAsh02_A", "description" => "Near cloudless sky with ash particles."],
        "04032336" => ["name" => "DLC02VolcanicAshStorm01", "description" => "Near cloudless sky with ash particles."],
        "0401D760" => ["name" => "DLC02VolcanicAshTundra01", "description" => "Cloudy sky with ash particles."],
        "040374BA" => ["name" => "DLC02VolcanicAshTundra01_A", "description" => "Cloudy sky with ash particles."],
        "0401DFF5" => ["name" => "DLC2ApocryphaWeather", "description" => "Light green sky as seen in Apocrypha."],
        "04034CFB" => ["name" => "DLC2ApocryphaWeatherNew", "description" => "Light green sky as seen in Apocrypha."]
    ];

        
    $ret = "";
    $skyMode = intval(GetActorValue($GLOBALS["PLAYER_NAME"], "skyMode"));
    if ($skyMode == 3) { // Outdoors, full skybox
        $weather = strtolower(GetActorValue($GLOBALS["PLAYER_NAME"], "weather"));;
        // [VoiceType <MaleGuard (000AA8D3)>
        // [Weather < (0010A231)>]
        if (!$weather)
            return $ret;
        $weather = $weathers[substr($weather, strpos($weather, "(")+1, 8)];
        if (!$weather) {
            error_log("minai: Unknown Weather type");
            return $ret;
        }
        $ret .= "The weather is currently: {$weather["description"]} ";
        $currentGameHour = intval(GetActorValue($GLOBALS["PLAYER_NAME"], "currentGameHour"));
        if ($currentGameHour <= 5 || $currentGameHour > 21 && str_contains($weather["name"], "_A")) {
            $ret .= "The sky contains a bright aurora tonight.";
        }
    }
    if ($ret)
        $ret .= "\n";
    return $ret;
}
    
