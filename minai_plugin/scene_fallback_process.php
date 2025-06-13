<?php
//---------------------------------------------------------------------------
// Clean up and expand tags for scene descriptions built from animation tags. 
// This way, LLMs comments about the scene could be on point.
// Dictionary is parsed first to last for replacements and acronyms should be 
// ordered from longer first to shortest last. 
// Example: don't put FM before FMM because you will end with 
// 'fm explained M', not 'fmm explained ' as it should. 
//---------------------------------------------------------------------------

$xxx_dictionary = [
'MMMMF' => '4 males screwing a female in a gangbang ',
'FMMMM' => 'female having intercourse with 4 males in a gangbang ',
'MFMMM' => 'female having intercourse with 4 males in a gangbang ',
'MMFMM' => 'female having intercourse with 4 males in a gangbang ',
'MMMFM' => 'female having intercourse with 4 males in a gangbang ',

'MFFFF' => 'male banging 4 females in a gangbang ',
'FFFFM' => '4 females screwed by one male in a gangbang ',
'FMFFF' => '4 females screwed by one male in a gangbang ',
'FFMFF' => '4 females screwed by one male in a gangbang ',
'FFFMF' => '4 females screwed by one male in a gangbang ',

'MMFFF' => '2 males screwing 3 females in a gangbang ',
'FFFMM' => '3 females banged by 2 males in a gangbang ',
'MFMFF' => '2 males banging 3 females in a gangbang ',
'MFFMF' => '2 males screwing 3 females in a gangbang ',
'MFFFM' => '2 males banging 3 females in a gangbang ',

'MMMF' => '3 males screwing a female in a foursome ',
'FMMM' => 'female shagged by 3 males in a foursome ',
'MFMM' => 'female banged by 3 males in a foursome ',
'MMFM' => 'female banged by 3 males in a foursome ',

'MFFF' => 'male banging 3 females in a foursome ',
'FFFM' => '3 females screwed by one male in a foursome ',
'FMFF' => '3 females screwed by one male in a foursome ',
'FFMF' => '3 females screwed by one male in a foursome ',

'MMFF' => '2 males and 2 females having intercourse in a foursome ',
'FFMM' => '2 females and 2 males having sex in a foursome ',
'FMFM' => '2 females and 2 males having intercourse in a foursome ',
'MFMF' => '2 females and 2 males having sex in a foursome ',
'MFFM' => '2 females and 2 males having intercourse in a foursome ',
'FMMF' => '2 females and 2 males having intercourse in a foursome ',
'FFFF' => '4 females having intercourse in a foursome ',
'MMMM' => '4 males having sex in a foursome ',

'FMM' => 'a female banged by 2 males in a threesome ',
'MMF' => '2 males screwing a female in a threesome ',
'MFF' => 'male screwing 2 females in a threesome ',
'FFM' => '2 females screwed by a male in a threesome ',
'FMF' => '2 females banged by a male in a threesome ',
'FFF' => '3 females having sex in a threesome',
'MMM' => '3 males having sex ',

'MF' => 'male shagging a female ',
'FM' => 'female screwed by a male ',
'FF' => '2 lesbian females having sex ',
'MM' => '2 males having sex ',

'BBP' => 'bare back pussy slide ',
'DVP' => 'double-vaginal penetration ',
'DAP' => 'double anal penetration ',
'DV' => 'double-vaginal penetration ',
'DP' => 'double penetration ',

'5P' => 'gangbang ',
'5p' => 'gangbang ',
'4P' => 'foursome ',
'4p' => 'foursome ',
'3P' => 'threesome ',
'3p' => 'threesome ',

', ,' => ',',
', .' => '.',
',.' => '.',
',,' => ','];            

// step 1: branding strings will be deleted
$fallback1 = str_ireplace( 
	['FunnyBizness','testpack','Master Mike','FlufyFox','Ace 5p','Ace 4p','Ace 3P','Ace 2p','$ZazAP_','3jiou','4uDIK',
	 'Arrok','Billyy','ayasato','Mitos','Anubs','Kurg4n','Dogma','Leito','Milky','oa3pp','Babo','Baka','nck30','Kom','MNC','Zyn','zDI','drg','ABC','K4'],
	[' '], $fallback);

// step 2: key strings in dictionary will be replaced
$fallback = strtr($fallback1, $xxx_dictionary);

?>
