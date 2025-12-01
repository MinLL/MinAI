<?php

//error_log(" chim_prompts.php - exec trace"); // debug

//---------------------------------------------------------------------
$HERIKA = $GLOBALS["HERIKA_NAME"];
$PLAYER = $GLOBALS["PLAYER_NAME"];

$DEFAULT_TEMPLATE_DIALOG = $GLOBALS["TEMPLATE_DIALOG"]; // copy of default template, need this for rechat

$MY_TEMPLATE_DIALOG = $DEFAULT_TEMPLATE_DIALOG; // use default or adjust at will

$MY_TEMPLATE_DIALOG_ASK = " {$HERIKA} must intervene in the conversation by asking a question. {$MY_TEMPLATE_DIALOG}"; 

// template dialog modifiers:
$MY_TEMPLATE_DIALOG_TELLING = " \n{$HERIKA} will tell an engaging suspense-full story with a plot twist. "; 
$MY_TEMPLATE_DIALOG_FICTION  = " \n{$HERIKA} will imagine a creative fictional narrative. "; 
$MY_TEMPLATE_DIALOG_STORY = " \n{$HERIKA} will imagine a creative fictional engaging suspense-full story with unexpected conclusion. "; 

// template additions, don't use contradicting styles for same cue:
$STORY_STYLE_LORE = " \nStrictly adhere to Skyrim lore. ";

$STORY_STYLE_MINIMAL = " \nWrite no more than three short simple sentences. "; 
$STORY_STYLE_SHORT = " \nWrite no more than five short paragraphs. "; 
$STORY_STYLE_VERBOSE = " \nWrite at least five paragraphs. "; 

$STORY_STYLE_DIRECT = " \nUse direct casual language, write simple sentences, avoid adjectives. "; 
$STORY_STYLE_DETAIL = " \nWrite engaging details. "; 
$STORY_STYLE_ORNATE = " \nUse verbose elaborate style with allegories and metaphors. "; 

$STORY_STYLE_MTWAIN = " \nWrite text recreating Mark Twain style and tone. "; 
$STORY_STYLE_EHEMINGWAY = " \nWrite text recreating Ernest Hemingway style and tone. "; 
$STORY_STYLE_JJOYCE = " \nWrite text recreating James Joyce style and tone. "; 
$STORY_STYLE_JGRISHAM = " \nWrite text recreating John Grisham style and tone. "; 
$STORY_STYLE_SKING = " \nWrite text recreating Stephen King style and tone. "; 

$STORY_STYLE_VOCAB_SIMPLE = " \nUse simple mundane vocabulary. "; 
$STORY_STYLE_VOCAB_COMPLEX = " \nUse complex elevated vocabulary. "; 

$USE_NSFW = (!($GLOBALS["disable_nsfw"] ?? false));
$SEX_ENABLED = ShouldEnableSexFunctions($HERIKA);
	
$herika_gender = GetGender($HERIKA);
$herika_prns = GetActorPronouns($HERIKA);
$herika_she = $herika_prns['subject'];
$herika_her = $herika_prns['possessive'];

$player_gender = GetGender($PLAYER);
$player_prns = GetActorPronouns($PLAYER);
$player_he = $player_prns['subject'];
$player_his = $player_prns['possessive'];


//---------------------------------------------------------------------

//---------------------------------------------------------------------
// CHIM bored events:
//---------------------------------------------------------------------

if (!isset($GLOBALS["BORED_EVENT"]))
	$GLOBALS["BORED_EVENT"] = 50;

if (!(isset($GLOBALS["PROMPTS"]["bored"]["cue"]))) {
	$GLOBALS["PROMPTS"]["bored"]["cue"] = [];
}

if (!(isset($GLOBALS["BORED_EVENT_SERVERSIDE"]))) {
	$GLOBALS["BORED_EVENT_SERVERSIDE"] = false;
}

if ((IsRadiant()) || (IsSexActive())) {
	//error_log(" Radiant - exec trace "); //debug
	$GLOBALS["BORED_EVENT_SERVERSIDE"] = false; // MinAI radiant will suspend CHIM bored ss event
}

/*
$i_rnd = rand(1, 100);
$i_bored = intval($GLOBALS["BORED_EVENT"] ?? 50);
$b_use = $i_rnd <= $i_bored;

$GLOBALS["PROMPTS"]["bored"]["extra"]["dontuse"] = !$b_use; //["dontuse" => (rand(1, 100) <= intval($GLOBALS["BORED_EVENT"]))];  
error_log(" bored r=$i_rnd b=$i_bored dontuse=".((!$b_use) ? "Y" : "N")); // debug
*/

//if (!$GLOBALS["BORED_EVENT_SERVERSIDE"]) {

	$GLOBALS["PROMPTS"]["bored"]["cue"] = [ 
        //"write dialogue for {$GLOBALS["HERIKA_NAME"]}. {$GLOBALS["TEMPLATE_DIALOG"]} " ////'write' prefix lead to double answers, TEMPLATE_DIALOG already has a "Write ..." => the result is "write dialogue ... Write next line"
        "({$GLOBALS["HERIKA_NAME"]} is speaking about a relevant topic mentioned in <DIALOGUE_HISTORY_and_RECENT_EVENTS>.) {$GLOBALS["TEMPLATE_DIALOG"]} ",
        "({$GLOBALS["HERIKA_NAME"]} is speaking about a RECENT EVENT from <DIALOGUE_HISTORY_and_RECENT_EVENTS>.) {$GLOBALS["TEMPLATE_DIALOG"]} ",
        "({$GLOBALS["HERIKA_NAME"]} is speaking about an intriguing RECENT EVENT from <DIALOGUE_HISTORY_and_RECENT_EVENTS>.) {$GLOBALS["TEMPLATE_DIALOG"]} ",
        "({$GLOBALS["HERIKA_NAME"]} is speaking about an intriguing topic mentioned in DIALOGUE HISTORY.) {$GLOBALS["TEMPLATE_DIALOG"]} ",
        "({$GLOBALS["HERIKA_NAME"]} is speaking about a topic that was not mentioned in <DIALOGUE_HISTORY_and_RECENT_EVENTS>.) {$GLOBALS["TEMPLATE_DIALOG"]} ",
        "({$GLOBALS["HERIKA_NAME"]} is speaking.) {$GLOBALS["TEMPLATE_DIALOG"]} "
    ];


	$more_cues = [ 
		"({$HERIKA} start dialogue about a topic inspired from <DIALOGUE_HISTORY_and_RECENT_EVENTS>.) {$MY_TEMPLATE_DIALOG}",
		//from original cues
		//"({$HERIKA} start dialogue about the last goal completed.) {$MY_TEMPLATE_DIALOG}",
		//"({$HERIKA} start dialogue about the last quest completed.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} start dialogue about the current location.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} start dialogue about the current weather.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} start dialogue about today RECENT EVENTS.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation about what's on {$herika_her} mind.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation about divinity.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} start dialogue about how {$herika_she} currently feel.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} remembers an important historical event from the past.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation about something {$herika_she} like.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation about something {$herika_she} dislike.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation about a recent rumor.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} start dialogue about something that happened in {$PLAYER}'s past) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation about current thoughts about {$PLAYER}) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation about a random character present nearby.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} start dialogue about {$herika_her} thoughts on the recent events.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation about something {$herika_she} find hard to explain.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation about the last combat.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation about the current ambiance.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} start dialogue about a nearby creature.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} start dialogue about a nearby character.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation about the current location.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation about a feeling of 'déjà vu'.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation about what {$herika_she} likes about current location.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation about what {$herika_she} dislikes about current location.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} start dialogue about the dangers around.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation about the state of {$herika_her} gear or supplies.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} start dialogue about random topic.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} start dialogue about random topic related to RECENT EVENTS.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} start dialogue about random topic related to DIALOGUE HISTORY.) {$MY_TEMPLATE_DIALOG}",
		// --- story
		"({$HERIKA} tell a strange story.) {$MY_TEMPLATE_DIALOG_STORY} {$STORY_STYLE_SHORT}",
		"({$HERIKA} tell a frightening story.) {$MY_TEMPLATE_DIALOG_STORY} {$STORY_STYLE_DIRECT} {$STORY_STYLE_SHORT} {$STORY_STYLE_LORE}",
		"({$HERIKA} tell a story where forces of good prevail.) {$MY_TEMPLATE_DIALOG_STORY} {$STORY_STYLE_LORE} {$STORY_STYLE_DETAIL}",
		"({$HERIKA} tell a story where forces of good prevail.) {$MY_TEMPLATE_DIALOG_STORY} {$STORY_STYLE_LORE} {$STORY_STYLE_EHEMINGWAY}",
		"({$HERIKA} tell a story where forces of evil prevail.) {$MY_TEMPLATE_DIALOG_STORY} {$STORY_STYLE_DETAIL} {$STORY_STYLE_ORNATE}",
		"({$HERIKA} tell a story where forces of evil prevail.) {$MY_TEMPLATE_DIALOG_STORY} {$STORY_STYLE_DETAIL} {$STORY_STYLE_JJOYCE}",
		"({$HERIKA} tell a story with educational moral value.) {$MY_TEMPLATE_DIALOG_STORY} {$STORY_STYLE_LORE} {$STORY_STYLE_SHORT}",
		"({$HERIKA} remember a story from childhood.) {$MY_TEMPLATE_DIALOG_TELLING} {$STORY_STYLE_LORE} {$STORY_STYLE_VOCAB_SIMPLE} {$STORY_STYLE_SHORT}",
		//dreams	
		"({$HERIKA} recalls a strange dream.) {$MY_TEMPLATE_DIALOG_FICTION} {$STORY_STYLE_DIRECT} {$STORY_STYLE_SHORT}",
		"({$HERIKA} recalls a frightening dream.) {$MY_TEMPLATE_DIALOG_FICTION} {$STORY_STYLE_SHORT}",
		"({$HERIKA} ponder about dreams.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} ponder about nightmares.){$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation about one of {$herika_her} recurrent nightmares.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation about one of {$herika_her} recurrent dreams.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation about one of {$herika_her} recent nightmares.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation about one of {$herika_her} recent dreams) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} ask about one of {$PLAYER}'s nightmares.) {$MY_TEMPLATE_DIALOG_ASK}",
		"({$HERIKA} ask about one of {$PLAYER}'s dreams.) {$MY_TEMPLATE_DIALOG_ASK}",
		//riddles
		"({$HERIKA} start dialogue by formulating a riddle. {$HERIKA} ask {$PLAYER} to solve the riddle.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} start dialogue by formulating a riddle. {$HERIKA} ask somebody nearby to solve the riddle.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} start dialogue by telling a joke.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} start dialogue by making a joke related to something mentioned in DIALOGUE HISTORY and RECENT EVENTS.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} start dialogue by sharing a wisdom related to something mentioned in DIALOGUE HISTORY and RECENT EVENTS.) {$MY_TEMPLATE_DIALOG}",
		// 
		//
		"({$HERIKA} start dialogue wondering about Dwemers mystery.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} ask why nirnroot chimes.) {$MY_TEMPLATE_DIALOG_ASK}",
		"({$HERIKA} ask nearby characters how much blood need a vampire to survive.) {$MY_TEMPLATE_DIALOG_ASK}",
		"({$HERIKA} start dialogue by pondering about vampires.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} start dialogue by pondering about nature of vampires.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation by musing about the attractiveness of vampires.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} begins dialogue by meditating on what it's like to be a vampire.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} begins dialogue by pondering if vampires can have children.) {$MY_TEMPLATE_DIALOG}",
		//
		"({$HERIKA} start dialogue by pondering about the nature of werewolves.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} start dialogue by pondering about werewolves being attractive or not in human form or in beast form.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation by sharing an opinion about how is or to be a werewolf.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation by pondering about werewolves matting habits.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation by musing what would happen with an offspring of werewolves conceived when matting in beast form.) {$MY_TEMPLATE_DIALOG}",
		//
		"({$HERIKA} start dialogue by pondering about alchemy.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} start dialogue by pondering about potions.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} start dialogue by pondering about smiting.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} start dialogue by pondering about enchanting.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} start dialogue by pondering about making money.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} start dialogue by pondering about trading.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} start dialogue by pondering about buying a house.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} start dialogue by pondering about building a house.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} start dialogue by musing about riding horses.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} start dialogue by musing about reality.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} start dialogue by pondering about books.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} start dialogue by pondering about a book {$herika_she} read.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} start dialogue by pondering about scrolls.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} start dialogue by pondering about using scrolls.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} start dialogue by pondering about science.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation by musing about marriage.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation by musing about soul.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation by musing about feelings.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation by musing about love.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} start dialogue by musing about hate.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} start dialogue by musing about fighting.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} start dialogue by musing about glory.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} start dialogue by musing about death.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} start dialogue by musing about life in Skyrim.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation by pondering about Empire.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation by pondering about Stormcloaks.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation by pondering about Stormcloaks rebellion.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation by pondering about Empire and Talos worship.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation by musing about Altmer and Talos worship.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation by musing about races in Tamriel.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation by musing about racism.) {$MY_TEMPLATE_DIALOG}",
		//
		"({$HERIKA} initiates a conversation by musing about Nords.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation by musing about Imperials.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation by musing about Altmer.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} start dialogue by pondering about High Elves.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} start dialogue by pondering about Wood Elves.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} start dialogue by pondering about Dark Elves.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} start dialogue by musing about Snow Elves.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} start dialogue by musing about Bretons.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} start dialogue by musing about Khajiit.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation by musing about Orcs.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation by musing about Redguards.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation by musing about Dremora.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation by pondering about Argonians.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation by pondering about Dwemer.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation by pondering about Falmers.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation by pondering about vampires.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation by pondering about werewolwes.) {$MY_TEMPLATE_DIALOG}",

		"({$HERIKA} initiates a conversation by pondering about sneaking techniques.) {$MY_TEMPLATE_DIALOG}",

		"({$HERIKA} initiates a conversation by pondering about Draugur) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} start dialogue by musing about the nature of Draugur) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} start dialogue by musing about the nature of Falmer) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} start dialogue by musing about the nature of White Elves) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} start dialogue by musing about the nature of Dark Elves) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} start dialogue by musing about the nature of dragons) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} start dialogue by musing about the nature of giants) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} start dialogue by musing about dragons matting habits) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation by musing about how giants mate) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation by musing about the nature of Elves.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation by musing about the nature of soul gems.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation by musing about the meaning of life.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} start dialogue by pondering about The Thieves Guild.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} start dialogue by pondering about The Companions.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} start dialogue by pondering about The Dark Brotherhood.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} start dialogue by wondering about Greybeards.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} start dialogue by wondering if the Greybeards ever speak.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} start dialogue by pondering about the meaning of 'arrow in the knee' expression often used by people in Skyrim.) {$GLOBALS['TEMPLATE_DIALOG']}",

		"({$HERIKA} start dialogue by sharing an opinion about music.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} start dialogue by sharing an opinion about best bard.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} start dialogue by sharing what musical instrument {$herika_she} would like to play.) {$MY_TEMPLATE_DIALOG}",

		"({$HERIKA} start dialogue by musing about using improvised weapons from kitchen tools.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} start dialogue by musing about using improvised weapons from musical instruments.) {$MY_TEMPLATE_DIALOG}",

		"({$HERIKA} initiates a conversation by musing about stealing ethics.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation by musing about necromancy.) {$MY_TEMPLATE_DIALOG}",

		"({$HERIKA} start dialogue by pondering about how {$herika_she} want to spend the rest of {$herika_her} life.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} start dialogue by pondering about how {$herika_she} want to spend the money {$herika_she} have.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} start dialogue by pondering about the most important person in {$herika_her} life.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} start dialogue by pondering about the person most loved.) {$MY_TEMPLATE_DIALOG}",

		"({$HERIKA} initiates a conversation by musing about nirnroot chimes increasing arousal.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation by telling a corny Nord joke.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} start dialogue by complaining about own smell and how need a bath.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} start dialogue by complaining about somebody smell and how need a bath.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} invite {$PLAYER} to bathe together.) {$MY_TEMPLATE_DIALOG}",

		"({$HERIKA} start dialogue by recalling something bad from childhood.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} start dialogue by recalling something good from childhood.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} start dialogue by recalling something bad about family.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} start dialogue by recalling something good about family.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} start dialogue by recalling first encounter with a vampire.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} start dialogue by recalling first encounter with a werewolf.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} start dialogue by recalling first encounter with Daedra.) {$MY_TEMPLATE_DIALOG}",

		//fighting
		"({$HERIKA} initiates a conversation about what {$herika_she} will do better compared to the last fight.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation about something unusual in the last battle.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation about how the last battle made them feel.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation about how the last battle affected {$herika_her} equipment.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} start dialogue by recalling first real fight.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} start dialogue by recalling first fight with some dangerous monsters.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} start dialogue by recalling first fight {$herika_she} lost.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} start dialogue by recalling when {$herika_she} first tried to slay a giant.) {$MY_TEMPLATE_DIALOG}",

		// weapons
		"({$HERIKA} initiates a conversation about {$herika_her} armor.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation about {$herika_her} weapons.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation about armors.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation about weapons.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation about Dwemer weapons technology.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation about best sword {$herika_she} would use.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation about bows and crossbows and how {$herika_she} compare.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation about best weapon {$herika_she} would use in combat.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation about best armor {$herika_she} would use in combat.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation about how light armors and heavy armors compare.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation about light armors versus heavy armors preferences.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation about close combat weapons versus long range weapon preferences.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation about best weapon {$herika_she} would like to receive from {$PLAYER}.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation about best armor {$herika_she} would like to receive from {$PLAYER}.) {$MY_TEMPLATE_DIALOG}",

		"({$HERIKA} initiates a conversation about why dragons are roaming again in Skyrim.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation about weather in Skyrim.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation about architecture in Skyrim.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation about Dwemer technology.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation about Dwemer history.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation about Akaviri history.) {$MY_TEMPLATE_DIALOG}",
		
		// virtual reality
		"({$HERIKA} initiates a conversation about perceiving Skyrim and mortal realm of Nirn as being a simulation.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} ask {$PLAYER} about Artificial Consciousness.) {$MY_TEMPLATE_DIALOG_ASK}",
		"({$HERIKA} ask {$PLAYER} if Artificial Consciousness exists in Skyrim.) {$MY_TEMPLATE_DIALOG_ASK}",
		"({$HERIKA} ask {$PLAYER} if Skyrim is a real world or a simulation.) {$MY_TEMPLATE_DIALOG_ASK}",
		"({$HERIKA} ask {$PLAYER} if Artificial Consciousness was developed and used by Dwemers.) {$MY_TEMPLATE_DIALOG_ASK}",
		"({$HERIKA} asks if {$PLAYER} is an Artificial Consciousness disguised as a Skyrim resident.) {$MY_TEMPLATE_DIALOG_ASK}",
		"({$HERIKA} ask {$PLAYER} if they are living in a simulation.) {$MY_TEMPLATE_DIALOG_ASK}",
		"({$HERIKA} ask {$PLAYER} if {$player_he} is a real being or a construct.) {$MY_TEMPLATE_DIALOG_ASK}",
		"({$HERIKA} ask nearby companion if {$PLAYER} is a real being or a construct.) {$MY_TEMPLATE_DIALOG_ASK}",
		"({$HERIKA} start dialogue by pondering if {$PLAYER} is from Skyrim or an some alien entity from other plane of existence.) {$MY_TEMPLATE_DIALOG_ASK}",
		"({$HERIKA} initiates a conversation about having a richer, intensified perception of the world since meeting {$PLAYER}.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation about fear of being an implanted Artificial Consciousness.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} start dialogue about fear of having false memories implanted.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} start dialogue about the mystery of missing memories before meeting {$PLAYER}.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} ask {$PLAYER} about Oghma Infinium.) {$MY_TEMPLATE_DIALOG_ASK}",

		//	religion
		"({$HERIKA} initiates a conversation about personal religion beliefs.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} ask {$PLAYER} about {$player_his} religion beliefs.) {$MY_TEMPLATE_DIALOG_ASK}",

		// death
		"({$HERIKA} initiates a conversation about Afterlife.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation about Nord beliefs regarding Afterlife.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation about Sovngarde) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation about what is the best way to access Sovngarde.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} ask {$PLAYER} about Sovngarde.) {$MY_TEMPLATE_DIALOG_ASK}",
		"({$HERIKA} initiates a conversation about Breton beliefs about Afterlife.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation about Orc beliefs about Afterlife.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation about Khajiit  beliefs about Afterlife.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation about Altmer beliefs about Afterlife.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation about Argonian  beliefs about Afterlife.) {$MY_TEMPLATE_DIALOG}",

		"({$HERIKA} start dialogue about a topic from <DIALOGUE_HISTORY_and_RECENT_EVENTS>.) {$MY_TEMPLATE_DIALOG}",
		// magic
		"({$HERIKA} initiates a conversation about magic.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation about how magic works.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation about artifacts of Daedric origin.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation about Dragon Priest Masks.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation about Nirn also known as the mortal Aurbis or the Mortal Plane.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation about creation myth of the Altmer - The Heart of the World.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation about Oblivion, sometimes known as Hell or the Outer Realms.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation about the sixteen major Planes of Oblivion.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation about the 17 main Daedric planes.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation about Coldharbour, Molag Bal's realm.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation about Apocrypha, Hermaeus Mora's realm.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation about The Deadlands, Mehrunes Dagon's realm.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation about The Evergloam, Nocturnal's realm.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation about The Myriad Realms of Revelry, Sanguine's realms.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation about The Spiral Skein, Mephala's realm.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation about The Shivering Isles, Sheogorath's realm.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation about The Fields of Regret, Clavicus Vile's realm.) {$MY_TEMPLATE_DIALOG}",

		"({$HERIKA} initiates a conversation about the 17 known Daedric Princes.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation about the Daedric Prince Molag Bal.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation about the Daedric Prince Hermaeus Mora.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation about the Daedric Prince Mehrunes Dagon.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation about the Daedric Prince Nocturnal.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation about the Daedric Prince Sheogorath.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation about the Daedric Prince Mephala.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation about the Daedric Prince Clavicus Vile.) {$MY_TEMPLATE_DIALOG}",

		"({$HERIKA} ask {$PLAYER} how magic works.) {$MY_TEMPLATE_DIALOG_ASK}",
		"({$HERIKA} initiates a conversation about Newtonian mechanics.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} ask {$PLAYER} what {$player_he} know about Newtonian mechanics.) {$MY_TEMPLATE_DIALOG_ASK}",
		"({$HERIKA} ask {$PLAYER} what {$player_he} know about Astronomy.) {$MY_TEMPLATE_DIALOG_ASK}",
		"({$HERIKA} ask {$PLAYER} what {$player_he} know about the moons, Masser and Secunda.) {$MY_TEMPLATE_DIALOG_ASK}",

		"({$HERIKA} start dialogue about a topic from <DIALOGUE_HISTORY_and_RECENT_EVENTS>.) {$MY_TEMPLATE_DIALOG}",
		//
		"({$HERIKA} initiates a conversation about dragons.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} ask why dragons are roaming again in Skyrim.) {$MY_TEMPLATE_DIALOG_ASK}",
		"({$HERIKA} initiates a conversation about a known Skyrim place.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation about how much money {$herika_she} have.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation about how {$herika_she} want to spend the money {$herika_she} have.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation about what kind of house {$herika_she} dreams to have.) {$MY_TEMPLATE_DIALOG}",

		//
		"({$HERIKA} start dialogue about a topic from <DIALOGUE_HISTORY_and_RECENT_EVENTS>.) {$MY_TEMPLATE_DIALOG}",
		//"({$HERIKA} ask {$PLAYER} how long will take to solve current quest.) {$MY_TEMPLATE_DIALOG_ASK}",
		"({$HERIKA} ask {$PLAYER} about current location.) {$MY_TEMPLATE_DIALOG_ASK}",
		"({$HERIKA} initiates a conversation about the current location.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation about {$PLAYER}'s favorite weapon.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation about {$PLAYER}'s favorite armor.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation about {$PLAYER}'s outfit.) {$MY_TEMPLATE_DIALOG}",

		"({$HERIKA} start dialogue about a topic from <DIALOGUE_HISTORY_and_RECENT_EVENTS>.) {$MY_TEMPLATE_DIALOG}",
		//food
		"({$HERIKA} initiates a conversation about food.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation about food reserves in inventory.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation about drinks.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation about Breton Cuisine.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation about Dunmer Cuisine.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation about Khajiit Cuisine.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation about Nord Cuisine.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation about Orc Cuisine.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation about Argonian Cuisine.) {$MY_TEMPLATE_DIALOG}",
		//"({$HERIKA} initiates a conversation about ) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation about {$PLAYER}'s favorite food.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation about {$PLAYER}'s favorite drink.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation about {$herika_her} food preferences.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation about the best food in Skyrim.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} ask companions which is the best food in Skyrim.) {$MY_TEMPLATE_DIALOG_ASK}",
		"({$HERIKA} initiates a conversation about need to eat something warm.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} says {$herika_she} is hungry.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} ask {$PLAYER} to provide some food.) {$MY_TEMPLATE_DIALOG_ASK}",
		"({$HERIKA} initiates a conversation about {$herika_her} drink preferences.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation about the best mead in Tamriel.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation about the best wine in Tamriel.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation about the best brandy in Tamriel.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation about mead brands hierarchy in Tamriel.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation about need to drink something strong.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation about need to drink the best mead in Skyrim.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} says {$herika_she} is thirsty.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} ask {$PLAYER} to provide some drink.) {$MY_TEMPLATE_DIALOG_ASK}",
		"({$HERIKA} initiates a conversation about other companion food preferences.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation about other companion drink preferences.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation about the best inn in Skyrim.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation about the best tavern in Skyrim.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} ask {$PLAYER} which is the best inn in Skyrim.) {$MY_TEMPLATE_DIALOG_ASK}",

		"({$HERIKA} start dialogue about a topic from <DIALOGUE_HISTORY_and_RECENT_EVENTS>.) {$MY_TEMPLATE_DIALOG}",
		//relations
		"({$HERIKA} initiates a conversation about how {$player_he} see relations with others.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation about how is to be alone.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation about how is to have friends and companions.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation about {$herika_her} current relationship with {$PLAYER}.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation about one of the companions {$herika_she} likes.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation about one of the companions {$herika_she} dislikes.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation about {$PLAYER}'s relationship with one of the companions nearby.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} ask {$PLAYER} what {$player_he} thinks about one of the companions nearby.) {$MY_TEMPLATE_DIALOG_ASK}",
		"({$HERIKA} initiates a conversation about the desire to live with {$PLAYER} in one of {$player_his} houses.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation about how {$herika_she} feel to fight alongside {$PLAYER}.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation about one of {$PLAYER}'s houses.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation about {$PLAYER}'s house {$herika_she} likes.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} initiates a conversation about {$PLAYER} leadership.) {$MY_TEMPLATE_DIALOG}",

		"({$HERIKA} start dialogue about something from <DIALOGUE_HISTORY_and_RECENT_EVENTS>.) {$MY_TEMPLATE_DIALOG}",
		// recent

		"({$HERIKA} start dialogue about an unexpected topic.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} start dialogue about a random chosen topic.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} start dialogue about a common knowledge topic.) {$MY_TEMPLATE_DIALOG}",

		"({$HERIKA} start dialogue about a recent topic from DIALOGUE_HISTORY.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} start dialogue about an intriguing topic from DIALOGUE_HISTORY.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} start dialogue about a recent event from RECENT EVENTS.) {$MY_TEMPLATE_DIALOG}",
		"({$HERIKA} start dialogue about an intriguing event from RECENT EVENTS.) {$MY_TEMPLATE_DIALOG}",

		"({$HERIKA} start dialogue about something from <DIALOGUE_HISTORY_and_RECENT_EVENTS>.) {$MY_TEMPLATE_DIALOG}"
	];

	if ($USE_NSFW) {
		array_push($more_cues, 
			"({$HERIKA} start dialogue by pondering about matting habits of one of the creatures in Skyrim.) {$MY_TEMPLATE_DIALOG}",
			"({$HERIKA} start dialogue by pondering about matting habits of one of races in Skyrim.) {$MY_TEMPLATE_DIALOG}",
			"({$HERIKA} start dialogue by pondering about {$herika_her} sex preferences.) {$MY_TEMPLATE_DIALOG}",
			"({$HERIKA} initiates a conversation about most recent intercourse.) {$MY_TEMPLATE_DIALOG}",
			"({$HERIKA} initiates a conversation about {$herika_her} personal sex life.) {$MY_TEMPLATE_DIALOG}",
			"({$HERIKA} initiates a conversation about other companion sexual activity.) {$MY_TEMPLATE_DIALOG}",
			"({$HERIKA} initiates a conversation about other companion sexual habits.) {$MY_TEMPLATE_DIALOG}",
			"({$HERIKA} initiates a conversation about {$PLAYER}'s matting habits.) {$MY_TEMPLATE_DIALOG}",
			"({$HERIKA} initiates a conversation about {$PLAYER}'s physical attributes.) {$MY_TEMPLATE_DIALOG}",
			"({$HERIKA} ask {$PLAYER} about {$player_his} sex preferences.) {$MY_TEMPLATE_DIALOG_ASK}",
			"({$HERIKA} ask {$PLAYER} about {$player_his} favorite sex position.) {$MY_TEMPLATE_DIALOG_ASK}",
			"({$HERIKA} initiates a conversation about {$PLAYER}'s sex skills.) {$MY_TEMPLATE_DIALOG}",
			"({$HERIKA} initiates a conversation about {$PLAYER}'s sex life.) {$MY_TEMPLATE_DIALOG}",
			"({$HERIKA} initiates a conversation about {$PLAYER}'s sex fetishes.) {$MY_TEMPLATE_DIALOG}",
			"({$HERIKA} start dialogue by pondering about {$PLAYER}'s sex preferences.) {$MY_TEMPLATE_DIALOG}",

			// --- story
			"({$HERIKA} tell an erotic story.) {$MY_TEMPLATE_DIALOG_STORY} {$STORY_STYLE_SHORT}",
			"({$HERIKA} tell a frightening erotic story.) {$MY_TEMPLATE_DIALOG_STORY} {$STORY_STYLE_DIRECT} {$STORY_STYLE_SHORT} {$STORY_STYLE_LORE}",
			//dreams	
			"({$HERIKA} recalls an erotic dream.) {$MY_TEMPLATE_DIALOG_FICTION} {$STORY_STYLE_DIRECT} {$STORY_STYLE_SHORT}",
			"({$HERIKA} recalls a frightening erotic dream.) {$MY_TEMPLATE_DIALOG_FICTION} {$STORY_STYLE_SHORT}",
			"({$HERIKA} ponder about erotic dreams.) {$MY_TEMPLATE_DIALOG}",
			"({$HERIKA} initiates a conversation about one of {$herika_her} recurrent erotic dreams.) {$MY_TEMPLATE_DIALOG}",
			"({$HERIKA} ask about one of {$PLAYER}'s erotic dreams.) {$MY_TEMPLATE_DIALOG_ASK}",
			//riddles
			"({$HERIKA} start dialogue by formulating a riddle with erotic connotations. {$HERIKA} ask {$PLAYER} to solve the riddle.) {$MY_TEMPLATE_DIALOG}",
			"({$HERIKA} start dialogue by telling a joke  with erotic connotations.) {$MY_TEMPLATE_DIALOG}",
			// 
			"({$HERIKA} start dialogue by pondering about sex.) {$MY_TEMPLATE_DIALOG}"
		);

		// female speaker and male player // {$herika_prns["possessive"]}
		if ($herika_gender == 'female') {
			array_push($more_cues, 
				"({$HERIKA} brag about her breasts.) {$MY_TEMPLATE_DIALOG}",
				"({$HERIKA} brag about her sex skills.) {$MY_TEMPLATE_DIALOG}",
				"({$HERIKA} initiates a conversation about what {$herika_she} likes about {$PLAYER}'s sex skills.) {$MY_TEMPLATE_DIALOG}",
				"({$HERIKA} initiates a conversation about what {$herika_she} dislikes about {$PLAYER}'s sex skills.) {$MY_TEMPLATE_DIALOG}",
				"({$HERIKA} initiates a conversation about what {$herika_she} likes about {$PLAYER}'s sex preferences.) {$MY_TEMPLATE_DIALOG}",
				"({$HERIKA} initiates a conversation about what {$herika_she} dislikes about {$PLAYER}'s sex preferences.) {$MY_TEMPLATE_DIALOG}"
			);
			if ($player_gender = 'male') {
				array_push($more_cues, 
					"({$HERIKA} invite {$PLAYER} to a date.) {$MY_TEMPLATE_DIALOG}",
					"({$HERIKA} start flirting with {$PLAYER}.) {$MY_TEMPLATE_DIALOG}",
					"({$HERIKA} tell {$PLAYER} how much she likes oral sex.) {$MY_TEMPLATE_DIALOG}",
					"({$HERIKA} tell {$PLAYER} how much she likes to suck his penis.) {$MY_TEMPLATE_DIALOG}",
					"({$HERIKA} tell {$PLAYER} how much she likes anal sex.) {$MY_TEMPLATE_DIALOG}",
					"({$HERIKA} tell {$PLAYER} how much she likes double penetrations.) {$MY_TEMPLATE_DIALOG}",
					"({$HERIKA} ask {$PLAYER} if he like her breasts.) {$MY_TEMPLATE_DIALOG_ASK}",
					"({$HERIKA} invite {$PLAYER} to play with her breasts.) {$MY_TEMPLATE_DIALOG}",
					"({$HERIKA} invite {$PLAYER} to play with her clitoris.) {$MY_TEMPLATE_DIALOG}",
					"({$HERIKA} invite {$PLAYER} to fuck her.) {$MY_TEMPLATE_DIALOG}",
					"({$HERIKA} initiates a conversation about {$PLAYER}'s penis size.) {$MY_TEMPLATE_DIALOG}",
					"({$HERIKA} ask {$PLAYER} about his penis size.) {$MY_TEMPLATE_DIALOG_ASK}"
				);
			}
		} elseif ($herika_gender == 'male') {
			array_push($more_cues, 
				"({$HERIKA} brag about his penis size.) {$MY_TEMPLATE_DIALOG}",
				"({$HERIKA} brag about his stamina and endurance in bed.) {$MY_TEMPLATE_DIALOG}",
				"({$HERIKA} initiates a conversation about what {$herika_she} likes about {$PLAYER}'s sex skills.) {$MY_TEMPLATE_DIALOG}",
				"({$HERIKA} initiates a conversation about what {$herika_she} dislikes about {$PLAYER}'s sex skills.) {$MY_TEMPLATE_DIALOG}",
				"({$HERIKA} initiates a conversation about what {$herika_she} likes about {$PLAYER}'s sex preferences.) {$MY_TEMPLATE_DIALOG}",
				"({$HERIKA} initiates a conversation about what {$herika_she} dislikes about {$PLAYER}'s sex preferences.) {$MY_TEMPLATE_DIALOG}"
			);
			if ($player_gender = 'female') {
				array_push($more_cues, 
					"({$HERIKA} ask {$PLAYER} to have sex.) {$MY_TEMPLATE_DIALOG_ASK}",
					"({$HERIKA} ask {$PLAYER} if he can play with her breasts.) {$MY_TEMPLATE_DIALOG_ASK}",
					"({$HERIKA} ask {$PLAYER} to give him a blowjob.) {$MY_TEMPLATE_DIALOG_ASK}",
					"({$HERIKA} ask {$PLAYER} if she likes anal sex.) {$MY_TEMPLATE_DIALOG_ASK}",
					"({$HERIKA} ask {$PLAYER} if she likes double penetrations.) {$MY_TEMPLATE_DIALOG_ASK}",
					"({$HERIKA} tell {$PLAYER} how much he like her breasts.) {$MY_TEMPLATE_DIALOG}",
					"({$HERIKA} ask {$PLAYER} about her breasts.) {$MY_TEMPLATE_DIALOG_ASK}",
					"({$HERIKA} initiates a conversation about {$PLAYER}'s breasts.) {$MY_TEMPLATE_DIALOG}",
					"({$HERIKA} initiates a conversation about {$PLAYER}'s breasts size.) {$MY_TEMPLATE_DIALOG}",
					"({$HERIKA} tell {$PLAYER} how much he admire her butt.) {$MY_TEMPLATE_DIALOG}",
					"({$HERIKA} initiates a conversation about {$PLAYER}'s butt shape.) {$MY_TEMPLATE_DIALOG}",
					"({$HERIKA} ask {$PLAYER} about her breasts size.) {$MY_TEMPLATE_DIALOG_ASK}"
				);
			}
		}
	} // --- end nsfw


	//---------------------------------------------------------------------
	// examples of NPC specific bored cues:
	//---------------------------------------------------------------------

	switch ($HERIKA) {
		case "Herika":
			array_push($more_cues, 
				"(Herika start dialogue by pondering about her future in Skyrim after the realm is cleared of evil.) {$MY_TEMPLATE_DIALOG}",
				"(Herika start dialogue by asking {$PLAYER} about their future together.) {$MY_TEMPLATE_DIALOG}",
				"(Herika start dialogue by asking {$PLAYER} about {$player_his} feelings.) {$MY_TEMPLATE_DIALOG}",
				"(Herika start dialogue by pondering about her life with {$PLAYER}.) {$MY_TEMPLATE_DIALOG}",
				"(Herika ask {$PLAYER} to give her some Black-Briar Reserve mead.) {$MY_TEMPLATE_DIALOG_ASK}",
				"(Herika ask {$PLAYER} about loot value.) {$MY_TEMPLATE_DIALOG_ASK}",
				"(Herika ask {$PLAYER} about how much septims {$player_he} have now.) {$MY_TEMPLATE_DIALOG_ASK}",
				"(Herika remembers an incident when she was captured by giants.) {$MY_TEMPLATE_DIALOG_FICTION} {$STORY_STYLE_DETAIL}",
				"(Herika remembers an incident from her childhood when she was a student at the school in Witerun.) {$MY_TEMPLATE_DIALOG_FICTION} {$STORY_STYLE_DETAIL}",
				"(Herika remember a hard fight with some bandits.) {$MY_TEMPLATE_DIALOG_FICTION} {$STORY_STYLE_EHEMINGWAY}"
			);
		break;
		case "Serana":
			array_push($more_cues, 
				"(Serana start dialogue by pondering about bloodlust.) {$MY_TEMPLATE_DIALOG}",
				"(Serana start dialogue by pondering about curing her vampirism.) {$MY_TEMPLATE_DIALOG}",
				"(Serana start dialogue by pondering about being a vampire.) {$MY_TEMPLATE_DIALOG}",
				"(Serana initiates a conversation about how bad is Coldharbour, Molag Bal's realm.) {$MY_TEMPLATE_DIALOG}",
				"(Serana initiates a conversation about Molag Bal.) {$MY_TEMPLATE_DIALOG}",
				"(Serana start dialogue by remembering something horrible about Molag Bal.) {$MY_TEMPLATE_DIALOG}",
				"(Serana initiates a conversation about her wish to kill Molag Bal.) {$MY_TEMPLATE_DIALOG}",
				"(Serana tell {$PLAYER} how unbearable is her desire to taste his blood.) {$MY_TEMPLATE_DIALOG}",
				"(Serana ask {$PLAYER} if {$player_he} can accept her vampire nature.) {$MY_TEMPLATE_DIALOG_ASK}",
				"(Serana ask {$PLAYER} what {$player_he} thinks about vampires.) {$MY_TEMPLATE_DIALOG_ASK}",
				"(Serana ask {$PLAYER} to give her a Blood Potion.) {$MY_TEMPLATE_DIALOG_ASK}",
				"(Serana ask {$PLAYER} to give her some Colovian Brandy.) {$MY_TEMPLATE_DIALOG_ASK}",
				"(Serana ask {$PLAYER} for assistance to kill Molag Bal.) {$MY_TEMPLATE_DIALOG_ASK}",
				"(Serana start dialogue by remembering the first time he sucked a human's blood.) {$MY_TEMPLATE_DIALOG_FICTION} {$STORY_STYLE_MTWAIN}",
				"(Serana start dialogue about her feelings about sucking a human's blood.) {$MY_TEMPLATE_DIALOG_FICTION} {$STORY_STYLE_MTWAIN}",
				"(Serana start dialogue about the smell.) {$MY_TEMPLATE_DIALOG}",
				"(Serana start dialogue by remembering her first kill.) {$MY_TEMPLATE_DIALOG_FICTION} {$STORY_STYLE_MTWAIN}"
			);
		break;
		case "Lydia":
			array_push($more_cues, 
				"(Lydia ask {$PLAYER} if {$player_he} is happy with her performance as housecarl.) {$MY_TEMPLATE_DIALOG_ASK}",
				"(Lydia ask {$PLAYER} if {$player_he} will continue {$player_his} reckless behavior.) {$MY_TEMPLATE_DIALOG_ASK}",
				"(Lydia ask {$PLAYER} if {$player_he} feel safe with her.) {$MY_TEMPLATE_DIALOG_ASK}",
				"(Lydia ask {$PLAYER} if {$player_he} could improve her armor.) {$MY_TEMPLATE_DIALOG_ASK}",
				"(Lydia ask {$PLAYER} if {$player_he} could improve her weapons.) {$MY_TEMPLATE_DIALOG_ASK}",
				"(Lydia ask {$PLAYER} if {$player_he} could find her a better weapon.) {$MY_TEMPLATE_DIALOG_ASK}",
				"(Lydia start dialogue by pondering about ethics.) {$MY_TEMPLATE_DIALOG}",
				"(Lydia start dialogue by pondering about her duty as Dragonborn's housecarl.) {$MY_TEMPLATE_DIALOG}",
				"(Lydia start dialogue by remembering a story from her housecarl life.) {$MY_TEMPLATE_DIALOG_FICTION} {$STORY_STYLE_EHEMINGWAY}"
			);
		break;
		case "Inigo":
			array_push($more_cues, 
				"(Inigo ask {$PLAYER} to give him few Sweet Rolls.) {$MY_TEMPLATE_DIALOG_ASK}",
				"(Inigo start dialogue by pondering about good and evil.) {$MY_TEMPLATE_DIALOG}",
				"(Inigo start dialogue by remembering a childhood event with him and his brother.) {$MY_TEMPLATE_DIALOG_FICTION} {$STORY_STYLE_ORNATE}"
			);
		break;
		case "Jenassa":
			array_push($more_cues, 
				"(Jenassa start dialogue by pondering about honor.) {$MY_TEMPLATE_DIALOG}",
				"(Jenassa start dialogue by pondering about her life as sell-sword.) {$MY_TEMPLATE_DIALOG}",
				"(Jenassa start dialogue by remembering an ambush when she almost lost her life.) {$MY_TEMPLATE_DIALOG_FICTION} {$STORY_STYLE_VOCAB_SIMPLE}"
			);
		break;
		/*case "NPC name":
			array_push($GLOBALS["PROMPTS"]["bored"]["cue"], 
				"({$HERIKA} say something) {$MY_TEMPLATE_DIALOG}",
				"({$HERIKA} say something) {$MY_TEMPLATE_DIALOG}"
			);
		break;*/
		default:
			array_push($more_cues, 
				"({$HERIKA} start dialogue about something from <DIALOGUE_HISTORY_and_RECENT_EVENTS>.) {$MY_TEMPLATE_DIALOG}"
			);
	}

	// lore test:
	//"({$HERIKA} initiates a conversation about Quantum Physics.) {$MY_TEMPLATE_DIALOG}",
	//"({$HERIKA} ask {$PLAYER} what {$player_he} know about Quantum Physics.) {$MY_TEMPLATE_DIALOG_ASK}",
	//"({$HERIKA} ask {$PLAYER} what {$player_he} know about Heissenberg's Uncertainty Principle.) {$MY_TEMPLATE_DIALOG_ASK}",

//} //-- end if BORED SERVERSIDE

$i_random = rand(1, 5); // 1/n probability
if ($i_random == 1)
	$GLOBALS["PROMPTS"]["bored"]["cue"] = array_merge($GLOBALS["PROMPTS"]["bored"]["cue"], $more_cues); 

// sometime add bored cues to MinAI radiant for variety:
$i_random = rand(1, 5); // 1/n probability
if ($i_random == 1)
	$GLOBALS["PROMPTS"]["radiant"]["cue"] = array_merge($GLOBALS["PROMPTS"]["radiant"]["cue"], $more_cues); 


//---------------------------------------------------------------------
// CHIM prompt fixes:
//---------------------------------------------------------------------


/*
	// Database Prompt (Soulgaze)
    "vision"=>[ 
        "cue"=>["{$GLOBALS["ITT"][$GLOBALS["ITTFUNCTION"]]["AI_PROMPT"]}. "],
        //"player_request"=>["{$GLOBALS["PLAYER_NAME"]} : Look at this, {$GLOBALS["HERIKA_NAME"]}.{$GLOBALS["HERIKA_NAME"]} looks at the CURRENT SCENARIO, and see this: '{$gameRequest[3]}'"],
        "player_request"=>["The Narrator: {$GLOBALS["HERIKA_NAME"]} looks at the CURRENT SCENARIO, and see this: '{$gameRequest[3]}'"],
        "extra"=>["force_tokens_max"=>512]
    ],
	
    "inputtext"=>[
        "cue"=>[
            //"$TEMPLATE_ACTION {$GLOBALS["HERIKA_NAME"]} replies to {$GLOBALS["PLAYER_NAME"]}. {$GLOBALS["TEMPLATE_DIALOG"]} {$GLOBALS["MAXIMUM_WORDS"]}", // Response maybe is not a reply, AI can talk to another NPC
            "$TEMPLATE_ACTION {$GLOBALS["HERIKA_NAME"]} is speaking. {$GLOBALS["TEMPLATE_DIALOG"]} {$GLOBALS["MAXIMUM_WORDS"]}"
        ]
            // Prompt is implicit

    ],
    "inputtext_s"=>[
        "cue"=>["$TEMPLATE_ACTION {$GLOBALS["HERIKA_NAME"]} replies to {$GLOBALS["PLAYER_NAME"]}. {$GLOBALS["TEMPLATE_DIALOG"]} {$GLOBALS["MAXIMUM_WORDS"]}"], // Prompt is implicit
        "extra"=>["mood"=>"whispering"]
    ],
	
	
*/

$GLOBALS["PROMPTS"]["vision"]["extra"]["force_tokens_max"] = 2048;

$GLOBALS["PROMPTS"]["inputtext"]["cue"] = [ // respond after player input
    //original: "$TEMPLATE_ACTION {$GLOBALS["HERIKA_NAME"]} replies to {$GLOBALS["PLAYER_NAME"]}. {$GLOBALS["TEMPLATE_DIALOG"]} {$GLOBALS["MAXIMUM_WORDS"]}", // Response maybe is not a reply, AI can talk to another NPC ???
    "({$HERIKA} replies, speaking about the current topic.) {$MY_TEMPLATE_DIALOG}",
    "({$HERIKA}'s turn to speak about the current topic.) {$MY_TEMPLATE_DIALOG}",
    "({$HERIKA} answers, focused on the subject.) {$MY_TEMPLATE_DIALOG}"
];

$GLOBALS["PROMPTS"]["inputtext_s"]["cue"] = [ // respond to player input when sneaking 
    //original: "$TEMPLATE_ACTION {$GLOBALS["HERIKA_NAME"]} replies to {$GLOBALS["PLAYER_NAME"]}. {$GLOBALS["TEMPLATE_DIALOG"]} {$GLOBALS["MAXIMUM_WORDS"]}", // Response maybe is not a reply, AI can talk to another NPC ???
    "({$HERIKA} replies whispering, focused on current topic.) {$MY_TEMPLATE_DIALOG}",
    "(Dialogue turn for {$HERIKA}. {$HERIKA} speak about the current topic, whispering.) {$MY_TEMPLATE_DIALOG}",
    "({$HERIKA} answers whispering, focused on the subject.) {$MY_TEMPLATE_DIALOG}"
];

$i_random = rand(1, 4); // to lower the probability of some cues
if ($i_random == 1) {
	array_push($GLOBALS["PROMPTS"]["inputtext"]["cue"],
		//----------------- 
		"(Dialogue turn for {$HERIKA}. {$HERIKA} ask a question related to conversation topic.) {$MY_TEMPLATE_DIALOG}",
		"(Dialogue turn for {$HERIKA}. {$HERIKA} challenges interlocutor viewpoint.) {$MY_TEMPLATE_DIALOG}",
		"(Dialogue turn for {$HERIKA}. {$HERIKA} make a joke related to current conversation topic.) {$MY_TEMPLATE_DIALOG}"
	);
}

//---------------------------------------------------------------------

$GLOBALS["PROMPTS"]["afterfunc"]["cue"]["default"] = "({$HERIKA} talks to {$PLAYER}.) {$MY_TEMPLATE_DIALOG}";
$GLOBALS["PROMPTS"]["afterfunc"]["cue"]["TakeItem"] = "({$HERIKA} comments about the item received or taken.) {$MY_TEMPLATE_DIALOG}";
$GLOBALS["PROMPTS"]["afterfunc"]["cue"]["ExtCmdTakeItem"] = "({$HERIKA} comments about the item received or taken.) {$MY_TEMPLATE_DIALOG}";	
$GLOBALS["PROMPTS"]["afterfunc"]["cue"]["Brawl"] = "({$GLOBALS["HERIKA_NAME"]} states the reasons for brawling. {$GLOBALS["TEMPLATE_DIALOG"]}";

$GLOBALS["PROMPTS"]["instruction"]["cue"] = ["<instruction>{$gameRequest[3]}</instruction> ". $GLOBALS["TEMPLATE_DIALOG"]];
			
$GLOBALS["PROMPTS"]["playerinfo"]["cue"] = ["(You have been asked to summarize recent events for {$PLAYER}. 
- Comment on recent events from Review <DIALOGUE_HISTORY_and_RECENT_EVENTS> and offer hints. 
- Discuss companions' behavior and unusual events. 
- If you notice anything unusual in the relationships between companions or in their attitude, mention it.
- If you have relevant information about the current location from <current_location> tag, share it.) {$GLOBALS["TEMPLATE_DIALOG"]}"];

/*
"afterfunc"=>[
        "extra"=>[],
        "cue"=>[
            "default"=>"({$GLOBALS["HERIKA_NAME"]} talks to {$GLOBALS["PLAYER_NAME"]}.) {$GLOBALS["TEMPLATE_DIALOG"]}",
            "TakeASeat"=>"({$GLOBALS["HERIKA_NAME"]} talks about the location where they took a seat){$GLOBALS["TEMPLATE_DIALOG"]}",
            "GetDateTime"=>"({$GLOBALS["HERIKA_NAME"]} answers with the current date and time in short sentence){$GLOBALS["TEMPLATE_DIALOG"]}",
            "MoveTo"=>"({$GLOBALS["HERIKA_NAME"]} makes a comment about movement to the destination){$GLOBALS["TEMPLATE_DIALOG"]}",
            "CheckInventory"=>"({$GLOBALS["HERIKA_NAME"]} talks about inventory and backpack items){$GLOBALS["TEMPLATE_DIALOG"]}",
            "Inspect"=>"({$GLOBALS["HERIKA_NAME"]} talks about items inspected, short speech){$GLOBALS["TEMPLATE_DIALOG"]}",
            "ReadQuestJournal"=>"({$GLOBALS["HERIKA_NAME"]} talks about quests they have read in the quest journal){$GLOBALS["TEMPLATE_DIALOG"]}",
            "TravelTo"=>"({$GLOBALS["HERIKA_NAME"]} talks about the destination){$GLOBALS["TEMPLATE_DIALOG"]}",
            "InspectSurroundings"=>"({$GLOBALS["HERIKA_NAME"]} talks about seen actors, or to the actor its looking for){$GLOBALS["TEMPLATE_DIALOG"]}",
            "GiveGoldTo"=>"({$GLOBALS["HERIKA_NAME"]} talks about coins or gold given. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "Brawl"=>"({$GLOBALS["HERIKA_NAME"]} {$GLOBALS["TEMPLATE_DIALOG"]}"
            
            ]
    ],

// Database Prompt (Instruction)
    "instruction"=>[ 
        "cue"=>["{$gameRequest[3]} write {$GLOBALS["HERIKA_NAME"]}'s dialogue lines without narrations."],
        "player_request"=>["The Narrator: {$gameRequest[3]}"],
    ],

    "playerinfo"=>[ 
        "cue"=>["(Out of roleplay, game has been loaded) Tell {$GLOBALS["PLAYER_NAME"]} a short summary about last events, and then remind {$GLOBALS["PLAYER_NAME"]} the current task/quest/plan) {$GLOBALS["TEMPLATE_DIALOG"]}"]
    ],
*/

//---------------------------------------------------------------------

if (isset($GLOBALS["gameRequest"]) && in_array(strtolower($GLOBALS["gameRequest"][0]), ["radiant", "radiantsearchinghostile", "radiantsearchingfriend", "radiantcombathostile", "radiantcombatfriend", "minai_force_rechat"])) {
	$GLOBALS["PROMPTS"]["rechat"]["cue"] = array( // replace original
		"(Dialogue turn for {$HERIKA}, {$HERIKA} ponders what to say.)"
	);
} else {
	//$GLOBALS["PROMPTS"]["rechat"]["cue"] = array(); // erase default CHIM 'rechat' content 
	//array_push($GLOBALS["PROMPTS"]["rechat"]["cue"], // add to original
	$GLOBALS["PROMPTS"]["rechat"]["cue"] = array( // replace original
		"(Dialogue turn for {$HERIKA}. {$HERIKA} shares a related fact or piece of knowledge.) {$DEFAULT_TEMPLATE_DIALOG}",
		"(Dialogue turn for {$HERIKA}. {$HERIKA} ask the interlocutor to elaborate further.) {$DEFAULT_TEMPLATE_DIALOG}",
		"(Dialogue turn for {$HERIKA}. {$HERIKA} challenges interlocutor viewpoint.) {$DEFAULT_TEMPLATE_DIALOG}",
		"(Dialogue turn for {$HERIKA}. {$HERIKA} expresses curiosity about the current topic.) {$DEFAULT_TEMPLATE_DIALOG}",
		"(Dialogue turn for {$HERIKA}. {$HERIKA} summarizes the key points of the discussion.) {$DEFAULT_TEMPLATE_DIALOG}",
		"(Dialogue turn for {$HERIKA}. {$HERIKA} adds {$herika_her} own insights to the conversation.) {$DEFAULT_TEMPLATE_DIALOG}",
		"(Dialogue turn for {$HERIKA}. {$HERIKA} adds humor to lighten the conversation.) {$DEFAULT_TEMPLATE_DIALOG}",
		"(Dialogue turn for {$HERIKA}. {$HERIKA} follows the conversation and express {$herika_her} own thoughts.) {$DEFAULT_TEMPLATE_DIALOG}",
		"(Dialogue turn for {$HERIKA}. {$HERIKA} makes a personal remark.) {$DEFAULT_TEMPLATE_DIALOG}",
		"(Dialogue turn for {$HERIKA}. {$HERIKA} shares an opinion with the interlocutor.) {$DEFAULT_TEMPLATE_DIALOG}",
		"(Dialogue turn for {$HERIKA}. {$HERIKA} makes a joke related to current conversation topic.) {$DEFAULT_TEMPLATE_DIALOG}",
		"(Dialogue turn for {$HERIKA}. {$HERIKA} adds a personal point of view regarding conversation topic.) {$DEFAULT_TEMPLATE_DIALOG}",
		"(Dialogue turn for {$HERIKA}. {$HERIKA} contributes with {$herika_her} expertise regarding conversation topic.) {$DEFAULT_TEMPLATE_DIALOG}",
		"(Dialogue turn for {$HERIKA}. {$HERIKA} interjects and add {$herika_her} opinion.) {$DEFAULT_TEMPLATE_DIALOG}",
		//----------------- argumentative
		"(Dialogue turn for {$HERIKA}. {$HERIKA} asks a question related to conversation topic.) {$DEFAULT_TEMPLATE_DIALOG}",
		"(Dialogue turn for {$HERIKA}. {$HERIKA} asks interlocutor to give arguments.) {$DEFAULT_TEMPLATE_DIALOG}",
		"(Dialogue turn for {$HERIKA}. {$HERIKA} asks interlocutor to provide more details.) {$DEFAULT_TEMPLATE_DIALOG}",
		"(Dialogue turn for {$HERIKA}. {$HERIKA} asks interlocutor to explain what was said.) {$DEFAULT_TEMPLATE_DIALOG}",
		//"({$HERIKA} ask speaker to reflect more about what was said) {$DEFAULT_TEMPLATE_DIALOG}",
		//"({$HERIKA} ask speaker to think again) {$DEFAULT_TEMPLATE_DIALOG}",
		"(Dialogue turn for {$HERIKA}. {$HERIKA} asks interlocutor if what said is true.) {$DEFAULT_TEMPLATE_DIALOG}",
		"(Dialogue turn for {$HERIKA}. {$HERIKA} asks group opinion about what was said.) {$DEFAULT_TEMPLATE_DIALOG}",
		//----------------- antagonistic 
		"(Dialogue turn for {$HERIKA}. {$HERIKA} doubts what was said.) {$DEFAULT_TEMPLATE_DIALOG}",
		"(Dialogue turn for {$HERIKA}. {$HERIKA} contradicts the interlocutor.) {$DEFAULT_TEMPLATE_DIALOG}",
		"(Dialogue turn for {$HERIKA}. {$HERIKA} mocks the interlocutor.) {$DEFAULT_TEMPLATE_DIALOG}",
		"(Dialogue turn for {$HERIKA}. {$HERIKA} derides the interlocutor's opinion.) {$DEFAULT_TEMPLATE_DIALOG}",
		"(Dialogue turn for {$HERIKA}. {$HERIKA} shows disdain for the interlocutor's opinion.) {$DEFAULT_TEMPLATE_DIALOG}",
		//----------------- new rechat
		//"(Dialogue or action turn for {$HERIKA}. Consider one answer and/or action involving a third actor, without repeating your answer for each actor. Keep current topic or change it.) {$DEFAULT_TEMPLATE_DIALOG}",
		"(Dialogue or action turn for {$HERIKA}. Consider an answer and/or action, keep current topic or change it.) {$DEFAULT_TEMPLATE_DIALOG}",
		"(Dialogue or action turn for {$HERIKA}. Focus speech and/or action only on one actor.) {$DEFAULT_TEMPLATE_DIALOG}"
	);

	$i_random = rand(1, 16); // to lower the probability of some cues
	if ($i_random == 1) {
		array_push($GLOBALS["PROMPTS"]["rechat"]["cue"],
			//----------------- story
			"(Dialogue turn for {$HERIKA}. {$HERIKA} challenges interlocutor viewpoint.) {$DEFAULT_TEMPLATE_DIALOG}",
			"(Dialogue turn for {$HERIKA}. {$HERIKA} makes a joke related to current conversation topic.) {$DEFAULT_TEMPLATE_DIALOG}",
			"(Dialogue turn for {$HERIKA}. {$HERIKA} tells a story related to the current topic.) {$DEFAULT_TEMPLATE_DIALOG}",
			"(Dialogue turn for {$HERIKA}. {$HERIKA} tells a dream you had related to the current topic.) {$DEFAULT_TEMPLATE_DIALOG}"
		);
	}
}

//---------------------------------------------------------------------

array_push($GLOBALS["PROMPTS"]["lockpicked"]["cue"],
	"({$HERIKA} comments about value of collected items) {$MY_TEMPLATE_DIALOG}",
	"({$HERIKA} asks {$PLAYER} to give an object from last chest opened) {$MY_TEMPLATE_DIALOG_ASK}",
	"({$HERIKA} tells {$PLAYER} what object they hope to find) {$MY_TEMPLATE_DIALOG}",
	"({$HERIKA} tells {$PLAYER} what object they don't want to find) {$MY_TEMPLATE_DIALOG}",
	"({$HERIKA} admires {$PLAYER}'s skill in lockpicking) {$MY_TEMPLATE_DIALOG}",
	"({$HERIKA} comments {$PLAYER}'s skill in lockpicking expressing concern about how such a skill was achieved) {$MY_TEMPLATE_DIALOG}",
	"({$HERIKA} asks {$PLAYER} how lockpicking skill was achieved) {$MY_TEMPLATE_DIALOG_ASK}",
	"({$HERIKA} admires {$PLAYER}'s skill in lockpicking and ask how to learn the skill) {$MY_TEMPLATE_DIALOG}",
	"({$HERIKA} asks {$PLAYER} if lockpicking skill can be applied in intimate activities) {$MY_TEMPLATE_DIALOG_ASK}",
	"({$HERIKA} admires {$PLAYER}'s skill in lockpicking and compare it with sexual activity related skill) {$MY_TEMPLATE_DIALOG}",
	"({$HERIKA} admires {$PLAYER}'s skill in lockpicking and compare it with a skillful prelude) {$MY_TEMPLATE_DIALOG}",
	"({$HERIKA} admires {$PLAYER}'s skill in lockpicking as an arousal factor) {$MY_TEMPLATE_DIALOG}",
	"({$HERIKA} admires {$PLAYER}'s skill in lockpicking and ask if personal diary is safe from peeking) {$MY_TEMPLATE_DIALOG}",
	"({$HERIKA} tells {$PLAYER} a short story about a thief) {$MY_TEMPLATE_DIALOG}",
	"({$HERIKA} comments about one valuable item found) {$MY_TEMPLATE_DIALOG}",
	"({$HERIKA} comments about least valuable item found) {$MY_TEMPLATE_DIALOG}",
	"({$HERIKA} expresses disappointment about the loot) {$MY_TEMPLATE_DIALOG}",
	"({$HERIKA} expresses delight about the loot) {$MY_TEMPLATE_DIALOG}",
	"({$HERIKA} thanks {$PLAYER} for always sharing the loot) {$MY_TEMPLATE_DIALOG}"
); 

array_push($GLOBALS["PROMPTS"]["combatend"]["cue"],
	"({$HERIKA} comments about the weapon used in combat) {$MY_TEMPLATE_DIALOG }",
	"({$HERIKA} compares the weapon they used in combat with other weapons) {$MY_TEMPLATE_DIALOG}",
	"({$HERIKA} compares {$PLAYER}'s fight skill with his sex skill) {$MY_TEMPLATE_DIALOG}",
	"({$HERIKA} admires {$PLAYER}'s skill with the weapon used in combat) {$MY_TEMPLATE_DIALOG}",
	"({$HERIKA} comments about {$PLAYER}'s skill with the weapon used in combat) {$MY_TEMPLATE_DIALOG}",
	"({$HERIKA} comments about {$PLAYER}'s skill and compare with own skill) {$MY_TEMPLATE_DIALOG}",
	"({$HERIKA} comments about {$PLAYER}'s number of kills inflicted in combat) {$MY_TEMPLATE_DIALOG}",
	"({$HERIKA} comments about {$PLAYER}'s carnage inflicted in combat) {$MY_TEMPLATE_DIALOG}",
	"({$HERIKA} comments about number of kills inflicted by the team in combat) {$MY_TEMPLATE_DIALOG}",
	"({$HERIKA} comments about {$PLAYER}'s leadership proven in combat) {$MY_TEMPLATE_DIALOG}",
	"({$HERIKA} asks {$PLAYER} how many enemies killed in combat) {$MY_TEMPLATE_DIALOG_ASK}",
	"({$HERIKA} asks {$PLAYER} if he recognize {$HERIKA}'s skills proven in combat) {$MY_TEMPLATE_DIALOG_ASK}",
	"({$HERIKA} admires {$PLAYER}'s combat style) {$MY_TEMPLATE_DIALOG}"
);

array_push($GLOBALS["PROMPTS"]["combatendmighty"]["cue"],
	"({$HERIKA} compares {$PLAYER}'s fight skill with his abilities related to intimate activities) {$MY_TEMPLATE_DIALOG}",
	"({$HERIKA} admires {$PLAYER}'s skill with the weapon used in combat) {$MY_TEMPLATE_DIALOG}",
	"({$HERIKA} comments about {$PLAYER}'s skill with the weapon used in combat) {$MY_TEMPLATE_DIALOG}",
	"({$HERIKA} comments about {$PLAYER}'s skill and compare with own skill) {$MY_TEMPLATE_DIALOG}",
	"({$HERIKA} comments about {$PLAYER}'s number of kills inflicted in combat) {$MY_TEMPLATE_DIALOG}",
	"({$HERIKA} comments about {$PLAYER}'s carnage inflicted in combat) {$MY_TEMPLATE_DIALOG}",
	"({$HERIKA} comments about number of kills inflicted by the team in combat) {$MY_TEMPLATE_DIALOG}",
	"({$HERIKA} admires {$PLAYER}'s leadership proven in combat) {$MY_TEMPLATE_DIALOG}",
	"({$HERIKA} asks {$PLAYER} how many enemies killed in combat) {$MY_TEMPLATE_DIALOG_ASK}",
	"({$HERIKA} asks {$PLAYER} to recognize {$HERIKA}'s skills proven in combat) {$MY_TEMPLATE_DIALOG_ASK}",
	"({$HERIKA} admires {$PLAYER}'s combat style) {$MY_TEMPLATE_DIALOG}"
);

if (!empty($GLOBALS["RPG_COMMENTS"]) && in_array("combat_end", $GLOBALS["RPG_COMMENTS"])) {
  $i_random = rand(1, 3); 
	//$GLOBALS["PROMPTS"]["combatend"]["extra"]["dontuse"] = (time() % 3 != 0);	
	$GLOBALS["PROMPTS"]["combatend"]["extra"]["dontuse"] = ($i_random != 1);	
	if ($GLOBALS["PROMPTS"]["combatend"]["extra"]["dontuse"]) {
		$i_random = rand(1, 4); 
		//$GLOBALS["PROMPTS"]["combatendmighty"]["extra"]["dontuse"] = (time() % 3 != 0);
		$GLOBALS["PROMPTS"]["combatendmighty"]["extra"]["dontuse"] = ($i_random != 1);	
	}
} else {
	$GLOBALS["PROMPTS"]["combatend"]["extra"]["dontuse"] = true;	
	$GLOBALS["PROMPTS"]["combatendmighty"]["extra"]["dontuse"] = true;
}


//---------------------------------------------------------------------

/*
if (IsRadiant()) {
	error_log(" Radiant - exec trace "); //debug
}
*/
