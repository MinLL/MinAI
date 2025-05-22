<?php

function str_icontains($haystack, $needle) {
    return stripos($haystack, $needle) !== false;
}




function GetWeatherContext() {
    $utilities = new Utilities();
    $playerName = $GLOBALS["PLAYER_NAME"];

    $weatherDictionary = [
        "0010fef8" => [
            "name" => "SovngardeDark", 
            "descriptionPresent" => "The air is filled with mystic energy. A violet aurura shines over head."
        ],
        "0010fe7e" => [
            "name" => "RiftenOvercastFog", 
            "descriptionPresent" => "There is a dense fog and dark cloudy atmosphere."
        ],
        "0010e3d4" => [
            "name" => "EditorCloudPreview", 
            "descriptionPresent" => "The sky is filled with clouds."
        ],
        "0010e1f2" => [
            "name" => "SkyrimClear_A", 
            "descriptionPresent" => "The sky is nearly cloudless, transparent."
        ],
        "0010e1f1" => [
            "name" => "SkyrimCloudy_A", 
            "descriptionPresent" => "It is overcast. Clouds fill the sky.",
            "descriptionFuture" => "Partly cloudy weather begins to move in."
        ],
        "0010e1f0" => [
            "name" => "SkyrimClearSN_A", 
            "descriptionPresent" => "The sky is nearly cloudless, transparent."
        ],
        "0010e1ef" => [
            "name" => "SkyrimCloudySN_A", 
            "descriptionPresent" => "It is overcast. Clouds fill the sky.",
            "descriptionFuture" => "Partly cloudy weather begins to move in."
        ],
        "0010e1ee" => [
            "name" => "SkyrimClearTU_A", 
            "descriptionPresent" => "The sky is nearly cloudless, transparent."
        ],
        "0010e1ed" => [
            "name" => "SkyrimCloudyTU_A", 
            "descriptionPresent" => "It is overcast. Clouds fill the sky.",
            "descriptionFuture" => "Partly cloudy weather begins to move in."
        ],
        "0010e1ec" => [
            "name" => "SkyrimClearFF_A", 
            "descriptionPresent" => "The sky is nearly cloudless, transparent."
        ],
        "0010e1eb" => [
            "name" => "SkyrimCloudyFF_A", 
            "descriptionPresent" => "It is overcast. Clouds fill the sky.",
            "descriptionFuture" => "Partly cloudy weather begins to move in."
        ],
        "0010e1ea" => [
            "name" => "SkyrimClearRE_A", 
            "descriptionPresent" => "Clouds fill the sky.",
            "descriptionFuture" => "Partly cloudy weather begins to move in."
        ],
        "0010e1e9" => [
            "name" => "SkyrimCloudyRE_A", 
            "descriptionPresent" => "It is overcast. Clouds fill the sky.",
            "descriptionFuture" => "Partly cloudy weather begins to move in."
        ],
        "0010e1e8" => [
            "name" => "SkyrimClearCO_A", 
            "descriptionPresent" => "Clouds fill the sky.",
            "descriptionFuture" => "Partly cloudy weather begins to move in."
        ],
        "0010e1e7" => [
            "name" => "SkyrimCloudyCO_A", 
            "descriptionPresent" => "It is overcast. The sky is dark and cloudy sky."
        ],
        "0010e1e6" => [
            "name" => "SkyrimClearMA_A", 
            "descriptionPresent" => "Clouds fill the sky.",
            "descriptionFuture" => "Partly cloudy weather begins to move in."
        ],
        "0010e1e5" => [
            "name" => "SkyrimCloudyMA_A", 
            "descriptionPresent" => "It is overcast. The sky is dark and cloudy sky."
        ],
        "0010e1e4" => [
            "name" => "SkyrimClearVT_A", 
            "descriptionPresent" => "Clouds fill the sky.",
            "descriptionFuture" => "Partly cloudy weather begins to move in."
        ],
        "0010e1e3" => [
            "name" => "SkyrimCloudyVT_A", 
            "descriptionPresent" => "It is overcast. Clouds fill the sky.",
            "descriptionFuture" => "Partly cloudy weather begins to move in."
        ],
        "0010da13" => [
            "name" => "FXWthrInvertWfindowsWinterhold", 
            "descriptionPresent" => "It is impossible to see throught the haze, everything is a silhouette of black contrasted against a blast of white."
        ],
        "0010d9ec" => [
            "name" => "SovngardeClear", 
            "descriptionPresent" => "The air is filled with mystic energy."
        ],
        "0010c32f" => [
            "name" => "FXSkyrimStormBlowingGrass", 
            "descriptionPresent" => "Dark clouds fill the sky, and intense winds blow dust and debris. Grass shakes violently and trees bend under the force." ,
            "descriptionFuture" => "Dark clouds are roling in, and in the distance one can hear intense winds blowing dust and debris. The sound of trees beginning to bend under the force is unnerving."
        ],
        "0010a7a8" => [
            "name" => "SkyrimCloudyVT", 
            "descriptionPresent" => "It is overcast. Clouds fill the sky.",
            "descriptionFuture" => "Partly cloudy weather begins to move in."
        ],
        "0010a7a7" => [
            "name" => "SkyrimFogVT", 
            "descriptionPresent" => "The air is thick with a dense fog."
        ],
        "0010a7a6" => [
            "name" => "SkyrimOvercastRainVT", 
            "descriptionPresent" => "The air is thick with a dense fog. A light rain patters down.",
            "descriptionFuture" => "The atmosphere is being enveloped by a fog extending from the ground to the clouds above. A light rain is coming."
        ],
        "0010a7a5" => [
            "name" => "SkyrimClearVT", 
            "descriptionPresent" => "Clouds fill the sky.",
            "descriptionFuture" => "Partly cloudy weather begins to move in."
        ],
        "0010a245" => [
            "name" => "SkyrimCloudySN", 
            "descriptionPresent" => "It is overcast. Clouds fill the sky.",
            "descriptionFuture" => "Cloudy overcast weather begins to move in."
        ],
        "0010a244" => [
            "name" => "SkyrimClearSN", 
            "descriptionPresent" => "The sky is nearly cloudless, transparent."
        ],
        "0010a243" => [
            "name" => "SkyrimCloudyTU", 
            "descriptionPresent" => "It is overcast. The sky is a thick plate of clouds.",
            "descriptionFuture" => "A thick plate of clouds is beginning to move in."
        ],
        "0010a242" => [
            "name" => "SkyrimOvercastRainTU", 
        "descriptionPresent" => "The air is thick with a dense fog. A light rain patters down.",
            "descriptionFuture" => "The atmosphere is being enveloped by a fog extending from the ground to the clouds above. A light rain is coming."
        ],
        "0010a241" => [
            "name" => "SkyrimStormRainTU", 
            "descriptionPresent" => "The air is thick with a dense fog. Rain patters down.",
            "descriptionFuture" => "The atmosphere is being enveloped by a fog extending from the ground to the clouds above. Rain is coming."
        ],
        "0010A23C" => [
            "name" => "SkyrimStormRainFF", 
            "descriptionPresent" => "The air is thick with a dense fog. Rain patters down.",
            "descriptionFuture" => "The atmosphere is being enveloped by a fog extending from the ground to the clouds above. Rain is coming."
        ],
        "0010a240" => [
            "name" => "SkyrimClearTU", 
            "descriptionPresent" => "The sky is nearly cloudless, transparent."
        ],
        "0010a23f" => [
            "name" => "SkyrimCloudyFF", 
            "descriptionPresent" => "It is overcast. Clouds fill the sky.",
            "descriptionFuture" => "Cloudy weather begins to move in."
        ],
        "0010a23e" => [
            "name" => "SkyrimFogFF", 
            "descriptionPresent" => "The air is thick with a dense fog."
        ],
        "0010a23d" => [
            "name" => "SkyrimOvercastRainFF", 
        "descriptionPresent" => "The air is thick with a dense fog. A light rain patters down.",
            "descriptionFuture" => "The atmosphere is being enveloped by a fog extending from the ground to the clouds above. A light rain is coming."
        ],
        "0010a23b" => [
            "name" => "SkyrimClearFF", 
            "descriptionPresent" => "The sky is nearly cloudless, transparent."
        ],
        "0010a23a" => [
            "name" => "SkyrimCloudyRE", 
            "descriptionPresent" => "It is overcast. The air is thick with a dense fog.",
            "descriptionFuture" => "A thick plate of clouds is moving in."
        ],
        "0010a239" => [
            "name" => "SkyrimFogRE", 
            "descriptionPresent" => "The air is thick with a dense fog."
        ],
        "0010a238" => [
            "name" => "SkyrimOvercastRainRE", 
        "descriptionPresent" => "The sky is Overcast, and thick with a dense fog. A light rain patters down.",
            "descriptionFuture" => "The atmosphere is being enveloped by a fog. A light rain is incoming."
        ],
        "0010a237" => [
            "name" => "SkyrimClearRE", 
            "descriptionPresent" => "It is partly cloudy.",
            "descriptionFuture" => "Partly cloudy weather begins to move in."
        ],
        "0010a236" => [
            "name" => "SkyrimCloudyCO", 
            "descriptionPresent" => "It is overcast. The sky is thick with endless clouds.",
            "descriptionFuture" => "A thick plate of clouds is moving in."
        ],
        "0010a235" => [
            "name" => "SkyrimFogCO", 
            "descriptionPresent" => "The air is thick with a dense fog."
        ],
        "0010a234" => [
            "name" => "SkyrimClearCO", 
            "descriptionPresent" => "The sky is nearly cloudless, transparent."
        ],
        "0010a233" => [
            "name" => "SkyrimCloudyMA", 
            "descriptionPresent" => "It is overcast. Clouds fill the sky.",
            "descriptionFuture" => "Partly cloudy weather begins to move in."
        ],
        "0010a232" => [
            "name" => "SkyrimFogMA", 
            "descriptionPresent" => "The air is thick with a dense fog."
        ],
        "0010a231" => [
            "name" => "SkyrimOvercastRainMA", 
            "descriptionPresent" => "The air is thick with a dense fog. A light rain patters down.",
            "descriptionFuture" => "The atmosphere is being enveloped by a fog extending from the ground to the clouds above. A light rain is coming."
        ],
        "0010a230" => [
            "name" => "SkyrimClearMA", 
            "descriptionPresent" => "The sky is nearly cloudless, transparent."
        ],
        "00106635" => [
            "name" => "KarthspireRedoubtFog", 
            "descriptionPresent" => "The atmosphere is cloudy with a luminescent fog."
        ],
        "00105f40" => [
            "name" => "SkyrimDA02Weather", 
            "descriptionPresent" => "The air is thick with a dense fog."
        ],
        "00105945" => [
            "name" => "SolitudeBluePalaceFog", 
            "descriptionPresent" => "The air is thick with a dense fog."
        ],
        "00105944" => [
            "name" => "SolitudeBluePalaceFogNMARE", 
            "descriptionPresent" => "The fog and clouds above are red."
        ],
        "00105943" => [
            "name" => "SolitudeBluePalaceFogFEAR", 
            "descriptionPresent" => "The fog and clouds above are green."
        ],
        "00105942" => [
            "name" => "SolitudeBluePalaceFogARENA", 
            "descriptionPresent" => "The fog and clouds above are of an ashen pallor."
        ],
        "00105941" => [
            "name" => "BloatedMansGrottoFog", 
            "descriptionPresent" => "The fog and clouds above are dark red."
        ],
        "00104ab4" => [
            "name" => "SkuldafnCloudy", 
            "descriptionPresent" => "The sky is overcast. A light snow is falling.",
            "descriptionFuture" => "A thick layer of clouds is moving in. A light snowfall is on the horizon."
        ],
        "0010199f" => [
            "name" => "SkyrimMQ206weather", 
            "descriptionPresent" => "The fog and clouds above are dark red."
        ],
        "00101910" => [
            "name" => "FXWthrInvertLightMarkarth", 
            "descriptionPresent" => "There is almost no light."
        ],
        "000ecc96" => [
            "name" => "FXWthrInvertWindowsWindhelm2", 
            "descriptionFuture" => "There is almost no light."
        ],
        "000d9329" => [
            "name" => "HelgenAttackWeather", 
            "descriptionPresent" => "The sky is partly cloudy.",
            "descriptionFuture" => "Partly cloudy weather begins to move in."
        ],
        "0005ed7a" => [
            "name" => "FXWthrInvertLightsSolitude", 
            "descriptionPresent" => "There is almost no light."
        ],
        "0008282a" => [
            "name" => "FXWthrInvertLightsWhiterun", 
            "descriptionPresent" => "There is almost no light."
        ],
        "0008277A" => [
            "name" => "FXWthrInvertWindowsWhiterun", 
            "descriptionPresent" => "There is almost no light."
        ],
        "000d4886" => [
            "name" => "FXMagicStormRain", 
            "descriptionPresent" => "The sky is dark and ladden with heavy storm clouds. Heavy rainfall and thunder rule the landscape outside.",
            "descriptionFuture" => "The distance is dark and ladden with heavy storm clouds. Thunder begins to roll in from the enchroaching storm."
        ],
        "000d299e" => [
            "name" => "SkyrimOvercastWar", 
            "descriptionPresent" => "The sky is overcast and gray.",
            "descriptionFuture" => "Thick cloudy weather begins to move in."
        ],
        "000c8221" => [
            "name" => "SkyrimStormSnow", 
            "descriptionPresent" => "The thick white haze of a heavy blizzard consumes the landscape.",
            "descriptionFuture" => "A distant haze of white portend a heavy blizzard and dangerous weather."
        ],
        "000c8220" => [
            "name" => "SkyrimStormRain", 
            "descriptionPresent" => "Thick clouds and rain wet the world below. Thunder sounds intermittenly. Lightning fires in the sky.",
            "descriptionFuture" => "Thick clouds begin rolling in, and will bring with them rain."
        ],
        "000c821f" => [
            "name" => "SkyrimOvercastRain", 
            "descriptionPresent" => "Thick clouds and rain wet the world below.",
            "descriptionFuture" => "Thick clouds begin rolling in, and will bring with them rain."
        ],
        "000c821e" => [
            "name" => "SkyrimFog", 
            "descriptionPresent" => "A thick fog envelops the land."
        ],
        "00075491" => [
            "name" => "FXWthrSunlightWhite", 
            "descriptionPresent" => "" // no sky
        ],
        "0007548f" => [
            "name" => "FXWthrSunlight", 
            "descriptionPresent" => "" // no sky
        ],
        "00048c14" => [
            "name" => "BlackreachWeather", 
            "descriptionPresent" => "The sky is dark, with a midnight blue glow."
        ],
        "000aee84" => [
            "name" => "FXWthrInvertWindowsWindhelm", 
            "descriptionPresent" => "There is almost no light."
        ],
        "000a6858" => [
            "name" => "WorldMapWeather", 
            "descriptionPresent" => "The sky is laden with fluffy clouds.",
            "descriptionFuture" => "The sky is laden with fluffy clouds."
        ],
        "000923fd" => [
            "name" => "SovngardeFog", 
            "descriptionPresent" => "The air is thick with a dense fog."
        ],
        "000777cf" => [
            "name" => "FXWthrInvertDayNighWarm", 
            "descriptionPresent" => "There is almost no light."
        ],
        "00075de5" => [
            "name" => "FXWthrCaveBluePaleLight", 
            "descriptionPresent" => "There is a pale blue glow coming off the walls."
        ],
        "0006ed5b" => [
            "name" => "FXWthrCaveBlueSkylight", 
            "descriptionPresent" => "There is a pale blue glow coming off the walls."
        ],
        "0006ed5a" => [
            "name" => "FXWthrInvertDayNight", 
            "descriptionPresent" => "There is almost no light."
        ],
        "0004d7fb" => [
            "name" => "SkyrimOvercastSnow", 
            "descriptionPresent" => "Thick clouds are unleashing heavy snows.",
            "descriptionFuture" => "Distant thick clouds and a breeze of cold air portend heavy snows are coming."
        ],
        "0002e7ab" => [
            "name" => "TESTCloudyRain", 
            "descriptionPresent" => "Some extraplanetary wave is causing a vast atmospherc visual distortion; there is a light rain.",
            "descriptionFuture" => "Movement in the clouds suggest soon there will be a light rain."
        ],
        "00012f89" => [
            "name" => "SkyrimCloudy", 
            "descriptionPresent" => "It is overcast. Clouds fill the sky.",
            "descriptionFuture" => "Partly cloudy weather begins to move in."
        ],
        "0000081a" => [
            "name" => "SkyrimClear", 
            "descriptionPresent" => "The sky is nearly cloudless, transparent."
        ],
        "0000015e" => [
            "name" => "DefaultWeather", 
            "descriptionPresent" => "The cloudless sky is dark."
        ],
        "02010E0F" => [
            "name" => "DLC1 SkyrimClearFV",
            "descriptiPresenton" => "The sky is nearly cloudless, transparent."
        ],
        "02010E10" => [
            "name" => "DLC1 SkyrimClearFV_A",
            "descriptiPresenton" => "The sky is nearly cloudless, transparent."
        ],
        "02010E0E" => [
            "name" => "DLC1 SkyrimCloudyFV",
            "descriptiPresenton" => "Clouds fill the sky."
        ],
        "02010E0B" => [
            "name" => "DLC1 SkyrimCloudyFV_A	
            ", "descriptPresention" => "Clouds fill the sky."
        ],
        "0200F89C" => [
            "name" => "DLC1AurielsBowClearWeather", 
            "descriptionPresent" => "The sky is nearly cloudless, transparent."
        ],
        "02006AEC" => [
            "name" => "DLC1Eclipse", 
            "descriptionPresent" => "The sun is eclipsed! It reveals a brilliant aurora in the sky.",
            "descriptionFuture" => "Shade is setting on the sun, soon there will be an eclipse."
        ],
        "02019599" => [
            "name" => "DLC1FalmerValley bf",
            "descriptiPresenton" => "Clouds fill the sky."
        ],
        "020195A0" => [
            "name" => "DLC1FalmerValley bfDark",
            "descriptiPresenton" => "Clouds fill the sky."
        ],
        "0200F89D" => [
            "name" => "DLC1MagicAurielBowCloudyWeather", 
            "descriptionPresent" => "The sky is overcast.",
            "descriptionFuture" => "Cloudy weather begins to move in."
        ],
        "02001407" => [
            "name" => "SoulCairnAmb01", 
            "descriptionPresent" => "The fog and clouds above are purple."
        ],
        "02006aec" => [
            "name" => "SoulCairnAurora", 
            "descriptionPresent" => "The fog and clouds above are red."
        ],
        "02018dbb" => [
            "name" => "SoulCairnAmb02", 
            "descriptionPresent" => "The fog and clouds above are purple."
        ],
        "02018dbc" => [
            "name" => "SoulCairnAmb03", 
            "descriptionPresent" => "The fog and clouds above are purple."
        ],
        "02018dbd" => [
            "name" => "SoulCairnAmb04", 
            "descriptionPresent" => "The fog and clouds above are purple."
        ],
        "02014551" => [
            "name" => "SoulCairnAmb01", 
            "descriptionPresent" => "The fog and clouds above are purple. A light rain falls.",
            "descriptionFuture" => "A light rain is coming. The accompanying fog and clouds are starting to tint the world purple."
        ],
        "04018471" => [
            "name" => "DLC02VolcanicAsh01", 
            "descriptionPresent" => "The sky is blocked by clouds and ash particles."
        ],
        "040374B8" => [
            "name" => "DLC02VolcanicAsh01_A", 
            "descriptionPresent" => "The sky is moslty blocked by clouds and ash particles."
        ],
        "04031AC0" => [
            "name" => "DLC02VolcanicAsh02", 
            "descriptionPresent" => "The sky is blocked by clouds and ash particles."
        ],
        "040374B9" => [
            "name" => "DLC02VolcanicAsh02_A", 
            "descriptionPresent" => "A smoke filled ashy haze envelops the cloudless land."
        ],
        "04032336" => [
            "name" => "DLC02VolcanicAshStorm01", 
            "descriptionPresent" => "Ash fills the air outside."
        ],
        "0401D760" => [
            "name" => "DLC02VolcanicAshTundra01", 
            "descriptionPresent" => "The sky is blocked by clouds and ash particles."
        ],
        "040374BA" => [
            "name" => "DLC02VolcanicAshTundra01_A", 
            "descriptionPresent" => "The sky is partly blocked by clouds and ash particles."
        ],
        "0401DFF5" => [
            "name" => "DLC2ApocryphaWeather", 
            "descriptionPresent" => "An eerie light rules the sky casting the world in a disturbing green glow."
        ],
        "04034CFB" => [
            "name" => "DLC2ApocryphaWeatherNew", 
            "descriptionPresent" => "An eerie light rules the sky casting all in a disturbing green glow."
        ],
        // NAT Weather Types - using ?? as wildcard for first two digits
        "??003df4" => [
            "name" => "SClearSkyTU",
            "descriptionPresent" => "The sky is nearly cloudless, transparent."
        ],
        "??0053b6" => [
            "name" => "OvercastPF",
            "descriptionPresent" => "It is overcast. Thick clouds fill the sky.",
            "descriptionFuture" => "Thick cloudy weather begins to move in."
        ],
        "??0053bd" => [
            "name" => "OvercastTU",
            "descriptionPresent" => "It is overcast. The sky is a thick plate of clouds.",
            "descriptionFuture" => "A thick plate of clouds is beginning to move in."
        ],
        "??0053be" => [
            "name" => "OvercastRE",
            "descriptionPresent" => "It is overcast. The air is thick with clouds.",
            "descriptionFuture" => "A thick plate of clouds is moving in."
        ],
        "??0053c2" => [
            "name" => "SClearSkySN",
            "descriptionPresent" => "The sky is nearly cloudless, transparent."
        ],
        "??0053c4" => [
            "name" => "OvercastMA",
            "descriptionPresent" => "It is overcast. Clouds fill the sky.",
            "descriptionFuture" => "Thick cloudy weather begins to move in."
        ],
        "??0053c7" => [
            "name" => "FogRain",
            "descriptionPresent" => "The air is thick with fog and a steady rain falls.",
            "descriptionFuture" => "Fog is rolling in and rain clouds are gathering."
        ],
        "??0053d1" => [
            "name" => "OvercastFF",
            "descriptionPresent" => "It is overcast. Clouds fill the sky.",
            "descriptionFuture" => "Cloudy weather begins to move in."
        ],
        "??0053d2" => [
            "name" => "OvercastCO",
            "descriptionPresent" => "It is overcast. The sky is thick with endless clouds.",
            "descriptionFuture" => "A thick plate of clouds is moving in."
        ],
        "??005943" => [
            "name" => "OvercastVT",
            "descriptionPresent" => "It is overcast. Clouds fill the sky.",
            "descriptionFuture" => "Cloudy weather begins to move in."
        ],
        "??007f00" => [
            "name" => "SClearSkyPF",
            "descriptionPresent" => "The sky is nearly cloudless, transparent."
        ],
        "??008476" => [
            "name" => "SnowClear",
            "descriptionPresent" => "The sky is clear with light snowfall.",
            "descriptionFuture" => "Clear skies with light snow are approaching."
        ],
        "??023667" => [
            "name" => "SClearSkyCOMA",
            "descriptionPresent" => "The sky is nearly cloudless, transparent."
        ],
        "??06b387" => [
            "name" => "SnowOvercast",
            "descriptionPresent" => "The sky is overcast with steady snowfall.",
            "descriptionFuture" => "Overcast skies and snow are approaching."
        ],
        "??0d64b0" => [
            "name" => "SunnyDLC2_ALT",
            "descriptionPresent" => "The sky is clear and sunny."
        ],
        "??0d64b1" => [
            "name" => "RainCloudy_DLC2_ALT",
            "descriptionPresent" => "The sky is cloudy with steady rainfall.",
            "descriptionFuture" => "Rain clouds are gathering."
        ],
        "??0d64b2" => [
            "name" => "CloudyDLC2_ALT",
            "descriptionPresent" => "The sky is filled with clouds."
        ],
        "??0d64b3" => [
            "name" => "CloudyDLC2_TU_A_ALT",
            "descriptionPresent" => "The sky is filled with clouds."
        ],
        "??0d64b4" => [
            "name" => "StormDLC2_ALT",
            "descriptionPresent" => "A powerful storm rages with heavy rain and thunder.",
            "descriptionFuture" => "Storm clouds are gathering on the horizon."
        ],
        "??0d64d3" => [
            "name" => "Snow_Alt",
            "descriptionPresent" => "Snow falls steadily from the cloudy sky.",
            "descriptionFuture" => "Snow clouds are moving in."
        ],
        "??0d64d4" => [
            "name" => "SnowClear_Alt",
            "descriptionPresent" => "The sky is clear with light snowfall.",
            "descriptionFuture" => "Clear skies with light snow are approaching."
        ],
        "??0d64d5" => [
            "name" => "SnowOvercast_Alt",
            "descriptionPresent" => "The sky is overcast with steady snowfall.",
            "descriptionFuture" => "Overcast skies and snow are approaching."
        ],
        "??0d64d6" => [
            "name" => "Snowstorm_Alt",
            "descriptionPresent" => "A fierce snowstorm rages with heavy snowfall.",
            "descriptionFuture" => "A snowstorm is approaching from the distance."
        ]
    ];
    
    $returnText = "";
    
    $weatherClassification = GetActorValue($playerName, "weatherClassification");

    $bIsRainingPresently = $weatherClassification == 2;
    $bIsSnowingPresently = $weatherClassification == 3;
    $bIsOvercast = $weatherClassification == 1;

    $weatherCode = strtolower(GetActorValue($playerName, "weather"));
    
    // Generate a basic weather description based on weatherClassification
    $basicWeatherDesc = "";
    if ($bIsRainingPresently) {
        $basicWeatherDesc = "Rain falls from the cloudy sky above.";
    } elseif ($bIsSnowingPresently) {
        $basicWeatherDesc = "Snow falls gently from the gray sky above.";
    } elseif ($bIsOvercast) {
        $basicWeatherDesc = "The sky is overcast with thick clouds.";
    }
    
    // If no weather code, return the basic description
    if (!$weatherCode) {
        if ($basicWeatherDesc) {
            return $basicWeatherDesc . "\n";
        }
        return "";
    }
    
    $wc = substr($weatherCode, (stripos($weatherCode, "(") + 1), 8);
    
    // Try exact match first
    $weather = $weatherDictionary[$wc] ?? null;
    
    // If no exact match, try matching with wildcard
    if (!isset($weather)) {
        $lastSixDigits = substr($wc, 2);
        $wildcardKey = "??" . $lastSixDigits;
        $weather = $weatherDictionary[$wildcardKey] ?? null;
    }

    if (!isset($weather)) {
        minai_log("debug", "Unknown Weather type: " . $weatherCode);
        // Return basic weather description if available
        if ($basicWeatherDesc) {
            return $basicWeatherDesc . "\n";
        }
        return "";
    }
    
    // currentWeather as opposed to "outgoing weather" which will overlay current weather for a time
    $currentWeather = $weather["descriptionPresent"] ?? "";
    $bCurrentWeatherSnow = str_icontains($currentWeather, "snow");
    $bCurrentWeatherRain = str_icontains($currentWeather, "rain");

    // according to frostfall's lists, these are specifically 'overcast'
    $bCurrentWeatherOvercast = str_icontains($weatherCode, "Cloudy");

    // this is really only important if rain or snow are incoming but yet actually present
    // if is not snowing presently but soon will be...
    $bCurrentWeatherIsHiddenByOutgoingWeather = (!$bIsSnowingPresently && $bCurrentWeatherSnow) 
                                                || (!$bIsRainingPresently && $bCurrentWeatherRain)
                                                || (!$bIsOvercast && $bCurrentWeatherOvercast);

    $futureOrPresent = $bCurrentWeatherIsHiddenByOutgoingWeather ? "descriptionFuture" : "descriptionPresent";
    $weatherSentence = $weather[$futureOrPresent] ?? $weather["descriptionPresent"] ?? "";

    if ($weatherSentence) {
        $returnText .= $weatherSentence;
    }
    
    $currentGameHour = intval(GetActorValue($playerName, "currentGameHour"));

    if ($currentGameHour <= 5 || $currentGameHour > 21 && str_icontains($weather["name"], "_A")) {
        $returnText .= " There is a bright aurora in the night sky.";
    }
   
    if ($returnText) $returnText .= "\n";
    return $returnText;
}

