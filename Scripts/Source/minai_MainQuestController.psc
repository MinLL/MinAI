ScriptName minai_MainQuestController extends Quest

int cuirassSlot = 0x00000004
int genitalSlot = 0x00000034

MantellaConversation mantella
FormList MantellaConversationParticipantsFormList

SexLabFramework slf
zadLibs libs
Actor playerRef
_DFtools dftools
BaboDialogueConfigMenu baboConfigs
SLAppPCSexQuestScript slapp
_DFDealUberController dfDealController
slaUtilScr Aroused

_shweathersystem sunhelmWeather
_SunHelmMain property sunhelmMain auto
Sound sunhelmFoodEatSound
Sound property sunhelmFillBottlesSound auto

bool bHasAroused = False
bool bHasArousedKeywords = False
bool bHasDeviousFollowers = False
bool bHasDD = False
bool bHasSTA = False
bool bHasBabo = False
bool bHasSLHH = False
bool bHasSLApp = False
bool bHasSunhelm = False
bool bHasOstim = False
bool bHasOSL = False
bool bUseVanilla = True

Keyword SLA_HalfNakedBikini
Keyword SLA_ArmorHalfNaked
Keyword SLA_Brabikini
Keyword SLA_ThongT
Keyword SLA_ArmorSpendex
Keyword SLA_PantiesNormal
Keyword SLA_ThongLowLeg
Keyword SLA_ThongCString
Keyword SLA_KillerHeels
Keyword SLA_PantsNormal
Keyword SLA_MicroHotPants
Keyword SLA_ThongGstring
Keyword SLA_ArmorHarness
Keyword SLA_ArmorTransparent
Keyword SLA_ArmorLewdLeotard
Keyword SLA_PelvicCurtain
Keyword SLA_FullSkirt
Keyword SLA_MiniSkirt
Keyword SLA_MicroSkirt
Keyword SLA_BootsHeels
Keyword SLA_HasLeggings
Keyword SLA_ArmorRubber
Keyword TNG_XS
Keyword TNG_S
Keyword TNG_M
Keyword TNG_L
Keyword TNG_XL
Keyword TNG_DefaultSize

Keyword SLHHScriptEventKeyword

GlobalVariable Debt
GlobalVariable EnslaveDebt
GlobalVariable ContractRemaining
string targetRule = ""

Form Gold
Quest DialogueGeneric
Faction JobInnKeeper
Faction JobInnServer

GlobalVariable minai_GlobalInjectToggle
GlobalVariable minai_UseOstim

Event OnInit()
  Maintenance()
EndEvent

Int Function GetVersion()
  return 9
EndFunction

Function HorribleHarassmentActivate(actor akActor)
  SLHHScriptEventKeyword.SendStoryEvent(None, akActor)
EndFunction

Function HarassHug(actor akActor)
  slapp.StartHarassment(akActor, 2)
EndFunction

Function Harasskiss(actor akActor)
  slapp.StartHarassment(akActor, 2)
EndFunction

Function Maintenance()
  Debug.Trace("[minai] Maintenance() - minai v" +GetVersion() + " initializing.")
  ; Register for Mod Events
  RegisterForModEvent("Mantella_ActorSpeakEvent", "OnActorSpeak")
  RegisterForModEvent("Mantella_PlayerInputEvent", "OnPlayerInput")
  RegisterForModEvent("DeviceActorOrgasm", "OnOrgasm")
  RegisterForModEvent("DeviceActorEdged", "OnEdged")
  RegisterForModEvent("DeviceVibrateEffectStart", "OnVibrateStart")
  RegisterForModEvent("HookAnimationStart", "OnSexlabAnimationStart")
  RegisterForModEvent("ostim_orgasm", "OnOstimorgasm")
  ; Initialize References
  playerRef = Game.GetPlayer()
  slf = Game.GetFormFromFile(0xD62, "SexLab.esm") as SexLabFramework
  libs = Game.GetFormFromFile(0x00F624, "Devious Devices - Integration.esm") as zadlibs
  Debug.Trace("[minai] Checking for installed mods...")
  mantella = Game.GetFormFromFile(0x03D41A, "Mantella.esp") as MantellaConversation
  MantellaConversationParticipantsFormList = Game.GetFormFromFile(0x000E4537, "Mantella.esp") as FormList
  if !mantella || !MantellaConversationParticipantsFormList
    Debug.Messagebox("AI Fatal Error: Could not get handle to Mantella.")
    Debug.Trace("[minai] Could not get handle to Mantella")
  EndIf
  
  if libs
    bHasDD = True
    Debug.Trace("[minai] Found Devious Devices")    
  EndIf
  if Game.GetModByName("DeviousFollowers.esp") != 255
    Debug.Trace("[minai] Found Devious Followers")
    bHasDeviousFollowers = True
    Debt = Game.GetFormFromFile(0xC54F, "DeviousFollowers.esp") as GlobalVariable
    ContractRemaining = Game.GetFormFromFile(0x218C7C, "DeviousFollowers.esp") as GlobalVariable
    dftools = Game.GetFormFromFile(0x01210D, "DeviousFollowers.esp") as _DFtools
    dfDealController = Game.GetFormFromFile(0x01C86D, "DeviousFollowers.esp") as _DFDealUberController
    EnslaveDebt = Game.GetFormFromFile(0x00C548, "DeviousFollowers.esp") as GlobalVariable
    debug.Trace("[minai] enslaveDebt=" + EnslaveDebt.GetValueInt())
    Debug.Trace("[minai] dftools="+dftools)
    
    if dftools == None || enslavedebt == None || dfDealController == None
      Debug.Notification("Warning: Some devious followers content will be broken, incompatible version!")
    EndIf
  EndIf
  if Game.GetModByName("TheNewGentleman.esp") !=255
    bHasTNG = True
  EndIf
  if Game.GetModByName("Spank That Ass.esp") != 255
    Debug.Trace("[minai] Found Spank That Ass")
    bHasSTA = True
  EndIf
  if Game.GetModByName("SexlabAroused.esm") != 255
    Debug.Trace("[minai] Found Sexlab Aroused")
    bHasAroused = True
    Aroused = Game.GetFormFromFile(0x04290F, "SexlabAroused.esm") as slaUtilScr
    SLA_HalfNakedBikini = Game.GetFormFromFile(0x08E854, "SexlabAroused.esm") as Keyword
    SLA_ArmorHalfNaked = Game.GetFormFromFile(0x08E855, "SexlabAroused.esm") as Keyword
    SLA_Brabikini = Game.GetFormFromFile(0x0308E856, "SexlabAroused.esm") as Keyword
    SLA_ThongT = Game.GetFormFromFile(0x0308E857, "SexlabAroused.esm") as Keyword
    SLA_ArmorSpendex = Game.GetFormFromFile(0x0308E858, "SexlabAroused.esm") as Keyword
    SLA_PantiesNormal = Game.GetFormFromFile(0x0308EDC1, "SexlabAroused.esm") as Keyword
    SLA_ThongLowLeg = Game.GetFormFromFile(0x0308EDC2, "SexlabAroused.esm") as Keyword
    SLA_ThongCString = Game.GetFormFromFile(0x0308EDC3, "SexlabAroused.esm") as Keyword
    SLA_KillerHeels = Game.GetFormFromFile(0x0308F326, "SexlabAroused.esm") as Keyword
    SLA_PantsNormal = Game.GetFormFromFile(0x0308F3F3, "SexlabAroused.esm") as Keyword
    SLA_MicroHotPants = Game.GetFormFromFile(0x0308F3F4, "SexlabAroused.esm") as Keyword
    SLA_ThongGstring = Game.GetFormFromFile(0x0308F3F5, "SexlabAroused.esm") as Keyword
    SLA_ArmorHarness = Game.GetFormFromFile(0x0308F3FC, "SexlabAroused.esm") as Keyword
    SLA_ArmorTransparent = Game.GetFormFromFile(0x0308F3FD, "SexlabAroused.esm") as Keyword
    SLA_ArmorLewdLeotard = Game.GetFormFromFile(0x0308F401, "SexlabAroused.esm") as Keyword
    SLA_PelvicCurtain = Game.GetFormFromFile(0x0308F402, "SexlabAroused.esm") as Keyword
    SLA_FullSkirt = Game.GetFormFromFile(0x0308F40D, "SexlabAroused.esm") as Keyword
    SLA_MiniSkirt = Game.GetFormFromFile(0x0308F40E, "SexlabAroused.esm") as Keyword
    SLA_MicroSkirt = Game.GetFormFromFile(0x0308F40F, "SexlabAroused.esm") as Keyword
    SLA_BootsHeels = Game.GetFormFromFile(0x0308F410, "SexlabAroused.esm") as Keyword
    SLA_HasLeggings = Game.GetFormFromFile(0x0308FE9F, "SexlabAroused.esm") as Keyword
    SLA_ArmorRubber = Game.GetFormFromFile(0x0308FEA4, "SexlabAroused.esm") as Keyword
    ; Check a couple keywords to see if it's a stripped down SexlabAroused
    if SLA_HalfNakedBikini && SLA_ArmorHalfNaked
      bHasArousedKeywords = True
    EndIf
    Debug.Trace("[minai] Sexlab Aroused Keywords=" + bHasArousedKeywords)
  EndIf

  if Game.GetModByName("BaboInteractiveDia.esp") != 255
    Debug.Trace("[minai] Found BaboDialogue")
    bHasBabo = True
    baboConfigs = (Game.GetFormFromFile(0x2FEA1B, "BaboInteractiveDia.esp") as BaboDialogueConfigMenu)
    if !baboConfigs
      bHasBabo = False
      Debug.Notification("Incompatible version of BaboDialogue. AI integrations disabled.")
      Debug.Trace("[minai] Could not fetch baboConfigs")
    EndIf
  EndIf

  if Game.GetModByName("SexlabHorribleHarassment.esp") != 255
    bHasSLHH = True
    Debug.Trace("[minai] Found SLHH")
    SLHHScriptEventKeyword = Game.GetFormFromFile(0x00C510, "SexLabHorribleHarassment.esp") as Keyword
    if !SLHHScriptEventKeyword
      Debug.Trace("[minai] Could not find SLHHScriptEventKeyword")
      Debug.Notification("Incompatible version of SLHH. AI Integrations Disabled.")
      bHasSLHH = False
    EndIf
  EndIf

  if Game.GetModByName("Sexlab Approach.esp") != 255
    bHasSLApp = True
    Debug.Trace("[minai] Found SLApp")
    slapp = Game.GetFormFromFile(0x0083F7, "Sexlab Approach.esp") as SLAppPCSexQuestScript
    if !slapp
      Debug.Trace("[minai] Could not find SLAppPCSexQuestScript")
      Debug.Notification("Incompatible version of SLApp. AI Integrations Disabled.")
      bHasSLapp = False
    EndIf
  EndIf

  if Game.GetModByName("SunhelmSurvival.esp") != 255
    bHasSunhelm = True
    Debug.Trace("[minai] Found Sunhelm")
    sunhelmMain = Game.GetFormFromFile(0x000D61, "SunhelmSurvival.esp") as _sunhelmmain
    sunhelmWeather = Game.GetFormFromFile(0x989760, "SunhelmSurvival.esp") as _shweathersystem
    sunhelmFoodEatSound = Game.GetFormFromFile(0x5674E1, "SunhelmSurvival.esp") as Sound
    sunhelmFillBottlesSound = Game.GetFormFromFile(0x4BB249, "SunhelmSurvival.esp") as Sound

    if !sunhelmMain || !sunhelmWeather|| !sunhelmFoodEatSound
      Debug.Trace("[minai] Could not load all sunhelm references")
    EndIf    
  EndIf
  Debug.Trace("[minai] Initialization complete.")

  if Game.GetModByName("OStim.esp") != 255
    Debug.Trace("[minai] Found OStim")
    bHasOstim = True
  EndIf

  if Game.GetModByName("OSLAroused.esp") != 255
    Debug.Trace("[minai] Found OSL Aroused")
    bHasOSL = True
  EndIf

   ; Vanilla Integrations
  gold = Game.GetFormFromFile(0x0000000F, "Skyrim.esm")
  if !gold
    Debug.Trace("[minai] - Could not get reference to gold?")
  EndIf

  DialogueGeneric = Game.GetFormFromFile(0x13EB3, "Skyrim.esm") as Quest
  if !DialogueGeneric
    Debug.Trace("[minai] - Could not get handle to DialogueGeneric.")
  EndIf

  JobInnKeeper = Game.GetFormFromFile(0x5091B, "Skyrim.esm") as Faction
  JobInnServer = Game.GetFormFromFile(0xDEE93, "Skyrim.esm") as Faction

  if !JobInnKeeper || !JobInnServer
    Debug.Trace("[minai] - Failed to fetch vanilla factions")
  EndIf
  
  minai_GlobalInjectToggle = Game.GetFormFromFile(0x0905, "MantellaMinAI.esp") as GlobalVariable
  minai_UseOStim = Game.GetFormFromFile(0x0906, "MantellaMinAI.esp") as GlobalVariable
  if minai_GlobalInjectToggle == None || minai_UseOStim == None
    Debug.MessageBox("Script mismatch between MantellaMinAi.esp and minai_MainQuestController")
    Debug.Trace("[minai] Could not find inject toggle!")
  EndIf
EndFunction

Event OnOrgasm(string eventName, string actorName, float numArg, Form sender)
  RegisterEvent(actorName + " had an orgasm.")
EndEvent


Event OnEdged(string eventName, string actorName, float numArg, Form sender)
  RegisterEvent(actorName + " was brought right to the edge of orgasm but stopped before she could orgasm.")
EndEvent


String Function getVibStrength(float vibStrength)
  string strength = ""
  if vibStrength <= 0.5
    strength = "weakly"
  elseIf vibStrength <= 1.0
    strength = "strongly"
  elseIf vibStrength <= 1.5
    strength = "intensely"
  else
    strength = "extremely intensely"
  endif
  return strength
endFunction

Event OnVibrateStart(string eventName, string actorName, float vibStrength, Form sender)
  string strength = getVibStrength(vibStrength)
  RegisterEvent(actorName + " started being " + strength + " stimulated by a vibrator.")
EndEvent

Event OnVibrateStop(string eventName, string actorName, float vibStrength, Form sender)
  string strength = getVibStrength(vibStrength)
  RegisterEvent(actorName + " stopped being stimulated by a vibrator.")
EndEvent

Event OnActorSpeak(Form actorToSpeakTo, Form actorSpeaking,string sayLine)
  ActionResponse(actorToSpeakTo,actorSpeaking,sayLine)
EndEvent

Event OnPlayerInput(string playerInput)
  Debug.Trace("[minai] OnPlayerInput(): " + playerInput)
  actor player = Game.GetPlayer()
  ;; Fix injected prompts being missing from first sentence of dialogue
  actor[] actorsFromFormList = GetActorsFromFormList()
  if MantellaConversationParticipantsFormList.GetSize() == 2
    actor otherActor = None
    int i = 0
    while i < actorsFromFormList.Length && otherActor == None
      if actorsFromFormList[i] != player
        otherActor = actorsFromFormList[i]
      EndIf
      i += 1
    EndWhile
    Debug.Trace("[minai] 2 players in conversation, setting initial context if not set")
    UpdateEvents(player, otherActor, actorsFromFormList)
  EndIf
  
  
EndEvent


Function RegisterAction(String eventLine)
  if minai_GlobalInjectToggle.GetValue() != 1.0
    Debug.Trace("[minai] RegisterAction() - Not doing anything, this is disabled.")
    return
  EndIf
  Debug.Trace("[minai] RegisterAction(): " + eventLine)
  mantella.AddInGameEvent(eventLine)
EndFunction

Function RegisterEvent(String eventLine)
  Debug.Trace("[minai] RegisterEvent(): " + eventLine)
  mantella.AddInGameEvent(eventLine)
EndFunction


bool Function CanVibrate(Actor akActor)
  return (akActor.WornHasKeyword(libs.zad_DeviousPlugVaginal)  || akActor.WornHasKeyword(libs.zad_DeviousPlugAnal)  || akActor.WornHasKeyword(libs.zad_DeviousPiercingsNipple)  || akActor.WornHasKeyword(libs.zad_DeviousPiercingsVaginal))
EndFunction


Function SendActorSpeakEvent(Actor actorToSpeakTo, Actor actorSpeaking)
    int handle = ModEvent.Create("MinAI_ActorSpeakEvent")
    if (handle)
	ModEvent.PushForm(handle,actorToSpeakTo as Form )
        ModEvent.PushForm(handle, actorSpeaking as Form)
        ModEvent.Send(handle)
    EndIf
EndFunction


Function UpdateEvents(Actor actorToSpeakTo, Actor actorSpeaking, actor[] actorsFromFormList)
  bool isEmpty = mantella.IsActionsEmpty()
  SendActorSpeakEvent(actorToSpeakTo, actorSpeaking)
  if !isEmpty
    Debug.Trace("[minai] Actions are not empty")
    return
  EndIf
  Actor player = game.GetPlayer()
  bool bPlayerInScene = False
  if actorSpeaking == playerRef || actorToSpeakTo == playerRef
    Debug.Trace("[minai] Player in Scene")
    bPlayerInScene = True
  EndIf

  bool bDeviousFollowerInScene = False
  if bHasDeviousFollowers
    Actor deviousFollower = (Quest.GetQuest("_Dflow") as QF__Gift_09000D62).Alias__DMaster.GetRef() as Actor
    if actorSpeaking == deviousFollower || actorToSpeakTo == deviousFollower
      Debug.Trace("[minai] Devious Follower in Scene")
      bDeviousFollowerInScene = True
    EndIf
  EndIf

  string targetName = getActorName(actorToSpeakTo)
  string speakerName = getActorName(actorSpeaking)
  string playerName = getActorName(player)
  
  if bPlayerInScene
    WritePlayerAppearance(player)
  EndIf
  
  if bHasDD
    WriteDDString(actorToSpeakTo, player, False)
    WriteDDString(actorSpeaking, player, True)
  EndIf

  
  if bHasAroused
    WriteArousedString(bPlayerInScene, player, actorsFromFormList)
    WriteClothingString(actorSpeaking, player, True, actorsFromFormList)
  EndIf
  
  if bHasDeviousFollowers && bPlayerInScene && bDeviousFollowerInScene
    if Debt.GetValueInt() >= 2000
      RegisterAction(playerName + " currently owes a very large debt. " + playerName + " owes thousands of gold. The Devious Follower will be playful (And a little strict), and tease and arouse " + playerName + " relentlessly. The Devious Follower is very unlikely to let " + playerName + " orgasm, unless " + playerName + " really convinces them.")
    elseIf Debt.GetValueInt() >= 1000
      RegisterAction("" + playerName + " currently owes a moderately large debt. " + playerName + " owes over a thousand gold. The Devious Follower will still be fairly playful (teasing and arousing " + playerName + " a fair bit), though will be more strict. The Devious Follower will be less likely to let " + playerName + " orgasm.")
    elseIf Debt.GetValueInt() >= 0
      RegisterAction("" + playerName + " currently has a small outstanding debt. " + playerName + " owes hundreds of gold. The Devious Follower will be a little less likely to let " + playerName + " orgasm.")
    else
      RegisterAction("" + playerName + " does not currently owe any debt. The Devious Follower is flirty and playful, seeking to distract and arouse " + playerName + "  The Devious Follower is more likely to let " + playerName + " orgasm. !Do not talk about debt. " + playerName + " does not owe " + speakerName +" any money currently. Do not bring up the arrangement, or deals.!")
    EndIf
    RegisterAction("the exact amount of gold " + playerName + " owes " + speakerName + " is " + debt.GetValueInt() + " gold.")
      
      int di = ContractRemaining.GetValueInt()
      if di > 0
        String daysRemaining = ""
	if di > 80 && di <= 100
	  daysRemaining = "about 3 months"
	elseif di > 70 && di <= 80
	  daysRemaining = "2 or 3 months"
	elseif di > 50 && di <= 70
	  daysRemaining = "about 2 months"
	elseif di > 40 && di <= 50
	  daysRemaining = "1 or 2 months"
	elseif di > 20 && di <= 40
	  daysRemaining = "about 1 month"
	elseif di > 14 && di <= 20
	  daysRemaining = "a bit over 2 weeks"
	elseif di > 7 && di <= 14
	  daysRemaining = "1 or 2 weeks"
	else
	  daysRemaining = di + " days"
	EndIf
        RegisterAction("" + playerName + " has agreed to hire the follower as part of a contract, and must continue to pay them. There are currently " + daysRemaining + " remaining on the contract.")
      EndIf
      String[] deals = DealManager.GetDeals()
      int i = 0
      bool spankRuleDone = False
      bool skoomaRuleDone = False
      while i < deals.Length
        String[] rules = DealManager.GetDealRules(deals[i])
        int j = 0
        while j < rules.Length
	  RegisterAction("" + playerName + " has agreed to the the following deal with their follower, and must obey it: " + ruleDesc)
          String ruleDesc = DealManager.GetRuleInfo(rules[j])
	  if !spankRuleDone && StringUtil.Find(ruleDesc, "spanks daily") != -1
	    spankRuleDone = True
	    Float now = Utility.GetCurrentGameTime()
            If dftools.SpankingTimer <= now
               RegisterAction(playerName + " has not asked for a spanking in a while, and is due for her daily spanking again. !" + speakerName + " will remind " + playerName + " about her agreement to beg for daily spanks. Make " + playerName + " beg to be spanked before using the -spankass- keyword to spank them.! " + speakerName + " is eager to spank " + playerName + "  and want to make them beg for it.")
            Else
              RegisterAction("" + playerName + " has already asked for a spanking today. " + speakerName + " does not need to remind them to do so. " + speakerName + " may still spank them if they wish to do so.")
            EndIf
	  EndIf
	  if !skoomaRuleDone && StringUtil.Find(ruleDesc, "skooma daily") != -1
	    skoomaRuleDone = True
	    RegisterAction("" + playerName + " has agreed to be take drugs that " + speakerName + " will provide daily. !" + speakerName + " should provide " + playerName + " with drugs if they ask by including the -drugplayer- keyword in your response. " + speakerName + " should also use the -drugplayer- keyword in your response if they want to drug " + playerName + "!\n!" + speakerName + " wants to get " + playerName + " addicted to drugs so that they can use them as an additional means of controlling " + playerName + "!")
	  EndIf
          j += 1
        EndWhile
        i += 1
      EndWhile
      ; Special chastity game handling
      if player.WornHasKeyword(libs.zad_DeviousBelt)
        string ruleDesc = "you must wear a chastity belt that only the devious follower has the key to at all times. This is to ensure that " + playerName + " cannot get relief without permission, and to show that the devious follower owns " + playerName + "'s pussy."
      EndIf
      RegisterAction("!" + speakerName + " is the Devious Follower. The Devious Follower is a pervert, though they initially hide this well. Their goal in becoming " + playerName + "'s follower is to entrap them with debt that they owe the Devious Follower from their fees, and eventually make " + playerName + " their sex slave / pet. The Devious Follower charges " + playerName + " gold over time in exchange for their services, with an agreed daily payment schedule. The Devious Follower has a good mix of being serious and flirty with " + playerName + "  and derives enjoyment from teasing and embarrassing her. The devious follower is likely to use perverted means such as remotely activating vibrators, shocking " + playerName + "  groping " + playerName + "  pinching " + playerName + "'s nipples, and so forth in their interactions if he deems it appropriate. While " + speakerName + " is perverted and do want to tease and arouse " + playerName + "  they tend to be a bit subtle about it. " + speakerName + " should incorporate perverted content and keywords into your responses when appropriate, but not do it constantly. " + speakerName + " wants to continue adventuring and is more likely to talk about adventure and events going on than the arrangement with " + playerName + " (Though " + speakerName + " should sometimes incorporate perverted elements into this). The Devious Follower must not -hug-, -kiss-, or -molest- " + playerName + "  The Devious Follower must not remind " + playerName + " about a payment plan, or ask for payments. This is handled automatically.!")
      if CanVibrate(player)
        RegisterAction("!The Devious Follower can remotely make " + playerName + "'s peircings or plugs vibrate when he wants to in order to reward, or punish " + playerName + "  depending on how the Devious Follower uses them. The Devious Follower can also make (or allow) " + playerName + " have an orgasm or climax at will (Use the -forceorgasm- keyword to do this). The Devious Follower can also cause the piercings or vibrators to painfully shock " + playerName + " if he is displeased, or if he wants to reduce the arousal of " + playerName + " (Include the -shock- keyword in your response to do so).!")
      EndIf
      if Debt.GetValueInt() >= EnslaveDebt.GetValueInt()
        if targetRule  == ""
          targetRule  = dfDealController.GetPotentialDeal()
	EndIf
        debug.Trace("[minai] targetRule="+targetRule)
	String ruleDesc = DealManager.GetRuleInfo(targetRule)
        debug.Trace("[minai] ruleInfo=" + ruleDesc)
        RegisterAction("!The devious follower is very concerned about " + playerName + "'s current debt level. Talking about this is the highest possible priority. He wants to discuss a new rule, and get " + playerName + " to agree to it in exchange for reducing the debt " + playerName + " owes. The new rule is: " + ruleDesc + "!")
	RegisterAction("!If " + speakerName + " successfully convinces " + playerName + " to agree to obey the new \"" + ruleDesc + "\" rule, respond with the keyword: -acceptdeal- in your response.!")
	; RegisterAction("!If " + playerName + " tells you that they refuse, respond with the keyword: -rejectdeal- in your response.!")
      EndIf
    EndIf

    ; Sunhelm Integrations
    if bHasSunhelm && bPlayerInScene && (FactionInScene(JobInnServer, actorsFromFormList) || FactionInScene(JobInnKeeper, actorsFromFormList))
      RegisterAction("!" + speakerName + " is a server at an inn. If " + speakerName + " wants to serve " + playerName + " any kind of food or meal, include the keyword '-servefood-' keyword in your response.!")
    EndIf

    ; Vanilla Integrations
    if FactionInScene(JobInnKeeper, actorsFromFormList) && bPlayerInScene
      RegisterAction("!" + speakerName + " is an innkeeper at an inn. If " + speakerName + " wants to allow " + playerName + " to rent a room for the night at the inn, include the keyword '-rentroom-' in your response.!")
    EndIf
    RegisterAction(BuildReminderStr(actorToSpeakTo))
EndFunction

bool Function FactionInScene(Faction theFaction, actor[] actorsInScene)
  int i = 0
  while i < actorsInScene.Length
    if actorsInScene[i] != None && actorsInScene[i].IsInFaction(theFaction)
      return True
    EndIf
    i += 1
  EndWhile
  return False
EndFunction


String Function BuildReminderStr(Actor akActor)
  string reminderStart = "Remember to respond using only direct dialogue and keywords. NEVER use any descriptions, actions or thoughts. No narrative or storytelling, just your dialogue. Never use keywords narratively. ONLY use the keywords that have been previously defined for you, do not invent new keywords. Using a keyword indicates that you want to immediately perform that action. Remember to use the previously identified keywords in your response when you want to perform an action such as "
  string reminderStr = ""
  if bUseVanilla
    reminderStr += "trading with, "
  EndIf
  if bHasSTA
    ReminderStr += "spanking, "
  endif
  if bHasSLHH
    ReminderStr += "molesting, "
  endif
  if bHasSLApp
    ReminderStr += "kissing, hugging, "
  endif
  if bHasSunhelm
    ReminderStr += "feeding, serving a meal to, renting a room to, "
  endif
  if bHasDeviousFollowers
    ReminderStr += "giving drugs or skooma to, "
  EndIf
  if bHasDD
    ReminderStr +="vibrating, giving an orgasm to, teasing, "
  EndIf
  if slf != None || bHasOstim
    if reminderStr != ""
      reminderStr += "or "
    EndIf
    reminderStr += "having sex with "
  EndIf
  reminderStr = reminderStart + reminderStr + GetActorName(akActor) + "."
  return reminderStr + "\n!" + reminderStr + "!"
EndFunction


Function FeedPlayer(Actor akSpeaker, Actor player)
  if player.GetItemCount(Gold) < 20
    Debug.Notification("AI: Player has insufficient gold for meal.")
    return
  EndIf
  player.RemoveItem(Gold, 20)

  int thirstVal = 100
  float perkModifier = 0.0 ; Depricated
  if(sunhelmMain.Thirst.IsRunning())
      sunhelmMain.Thirst.DecreaseThirstLevel(thirstVal)
  endif
  if(sunhelmMain.Hunger.IsRunning())
      sunhelmMain.Hunger.DecreaseHungerLevel(165 + (165 * perkModifier))
  endif
  sunhelmFoodEatSound.Play(Game.GetPlayer())
   If Player.GetAnimationVariableInt("i1stPerson") as Int == 1
      if(Player.GetSitState() == 0)
          ;    Debug.SendAnimationEvent(Player, "idleEatingStandingStart")
          ;    Utility.Wait(7.0)
          ;    Player.PlayIdle(IdleStop_Loose)
      elseif(Player.GetSitState() == 3)
          Game.ForceThirdPerson()
          Utility.Wait(1.0)
          Debug.SendAnimationEvent(Player, "ChairEatingStart")
          Utility.Wait(1.0)
          Game.ForceFirstPerson()
      endif
  else
      if(Player.GetSitState() == 0)
          Debug.SendAnimationEvent(Player, "idleEatingStandingStart")
      elseif(Player.GetSitState() == 3)
          Debug.SendAnimationEvent(Player, "ChairEatingStart")
      endif
  endif
EndFunction


Function ResetSpankRule()
  ; Update the spanking rule
  dftools.ResetSpanking()
  Int spankRequestCount = dftools._DFSpankDealRequests.GetValue() As Int
  spankRequestCount += 1
  dftools._DFSpankDealRequests.SetValue(spankRequestCount As Float)
EndFunction


Function SpankAss(int count, bool bDeviousFollowerInScene)
  if bDeviousFollowerInScene
    ResetSpankRule()
  EndIf
  int i = 0
  While i < count
    dftools.SpankPlayerAss()
    Utility.Wait(1.5)
    i += 1
  EndWhile
EndFunction

Function SpankTits(int count, bool bDeviousFollowerInScene)
  if bDeviousFollowerInScene
    ResetSpankRule()
  EndIf
  int i = 0
  While i < count
    dftools.SpankPlayerTits()
    Utility.Wait(1.5)
    i += 1
  EndWhile
EndFunction


int function CountMatch(string sayLine, string lineToMatch)
  int count = 0
  int index = 0
  while index != -1
    index = StringUtil.Find(sayLine, lineToMatch, index+1)
    count += 1
  endWhile
  return count
EndFunction


bool Function CanAnimate(actor akTarget, actor akSpeaker)
  if bHasOstim && minai_UseOStim.GetValue() == 1.0 && !OActor.IsInOStim(akTarget) && !OActor.IsInOStim(akSpeaker)
    return True
  EndIf
  return !slf.IsActorActive(akTarget) && !slf.IsActorActive(akSpeaker)
EndFunction

Function Start1pSex(actor akSpeaker)
  if bHasOstim && minai_UseOStim.GetValue() == 1.0
    OThread.QuickStart(OActorUtil.ToArray(akSpeaker))
  else
    slf.Quickstart(akSpeaker)
  EndIf
EndFunction


Function Start2pSex(actor akSpeaker, actor akTarget, actor Player, bool bPlayerInScene)
  if bHasOstim && minai_UseOStim.GetValue() == 1.0
    int ActiveOstimThreadID
    if bPlayerInScene
    	ActiveOstimThreadID = OThread.QuickStart(OActorUtil.ToArray(Player, akSpeaker))
    else
    	ActiveOstimThreadID = OThread.QuickStart(OActorUtil.ToArray(akTarget, akSpeaker))
    EndIf
    Utility.Wait(2)
    bool AutoMode = OThread.IsInAutoMode(ActiveOstimThreadID)
    if AutoMode == False
    	OThreadBuilder.NoPlayerControl(ActiveOstimThreadID)
    	OThread.StartAutoMode(ActiveOstimThreadID)
    EndIf
  Else
    slf.Quickstart(akTarget,akSpeaker)
  EndIf
EndFunction


Function StartGroupSex(actor akSpeaker, actor akTarget, actor Player, bool bPlayerInScene, Actor[] actorsFromFormList)
  if bHasOstim && minai_UseOStim.GetValue() == 1.0
    int ActiveOstimThreadID
    ActiveOstimThreadID = OThread.QuickStart(OActorUtil.ToArray(actorsFromFormList[0],actorsFromFormList[1],actorsFromFormList[2],actorsFromFormList[3],actorsFromFormList[4],actorsFromFormList[5],actorsFromFormList[6],actorsFromFormList[7],actorsFromFormList[8],actorsFromFormList[9]))
    Utility.Wait(2)
    bool AutoMode = OThread.IsInAutoMode(ActiveOstimThreadID)
    if AutoMode == False
      OThreadBuilder.NoPlayerControl(ActiveOstimThreadID)
      OThread.StartAutoMode(ActiveOstimThreadID)
    EndIf
  Else
    int numMales = 0
    int numFemales = 0
    Actor[] sortedActors = new Actor[12]
    int i = 0
    ; If the player is a female actor and is in the scene, put them in slot 0
    if bPlayerInScene && player.GetActorBase().GetSex() != 0
      sortedActors[0] = Player
    EndIf
    while i < actorsFromFormList.Length
      if actorsFromFormList[i].GetActorBase().GetSex() == 0
        numMales += 1
      else
        numFemales += 1
        if sortedActors[0] == None
        ; If there's a female actor in the scene, put them in slot 0
          sortedActors[0] = actorsFromFormList[i]
        EndIf
      EndIf
      if i != 0
        sortedActors[i] = actorsFromFormList[i]
      EndIf
      i += 1
    EndWhile
    if sortedActors[0] == None
      ; No female actors in scene, just use the first one that we skipped before
      sortedActors[0] = actorsFromFormList[0]
    EndIf
    slf.StartSex(actorsFromFormList, slf.GetAnimationsByDefault(numMales, numFemales))
  EndIf
EndFunction


Function UpdateArousal(actor akTarget, int Arousal)
  if bHasOSL && minai_UseOStim.GetValue() == 1.0
    OSLArousedNative.ModifyArousal(akTarget, Arousal)
  elseIf bHasAroused
    Aroused.UpdateActorExposure(akTarget, Arousal)
  EndIf
EndFunction


Function ActionResponse(Form actorToSpeakTo,Form actorSpeaking, string sayLine)
  ; akTarget is the person being talked to.
  actor akTarget = actorToSpeakTo as Actor
  ; actorToSpeakTo is the person initiating the conversation. Usually the player, unless radiant
  actor akSpeaker = actorSpeaking as Actor
  if akTarget.IsChild() || akSpeaker.IsChild()
    Debug.Trace("[minai] Not processing response - one of the actors is a child.")
    return
  EndIf
  bool bPlayerInScene = False
	if actorSpeaking == playerRef || actorToSpeakTo == playerRef
		Debug.Trace("[minai] Player in Scene")
		bPlayerInScene = True
	EndIf
  Actor Player = game.GetPlayer()
  
  Actor[] actorsFromFormList = GetActorsFromFormList()

  if (!bPlayerInScene)
    Debug.Trace("[minai] Player not found in actorsFromFormList")
  EndIf

  bool bDeviousFollowerInScene = False
  if bHasDeviousFollowers
    Actor deviousFollower = (Quest.GetQuest("_Dflow") as QF__Gift_09000D62).Alias__DMaster.GetRef() as Actor
    if akTarget == deviousFollower || akSpeaker == deviousFollower
      Debug.Trace("[minai] Devious Follower in Scene")
      bDeviousFollowerInScene = True
    EndIf
  EndIf
  
  debug.Trace("[minai] ActionResponse(" + akSpeaker.GetActorBase().GetName() + ", " + akTarget.GetActorBase().GetName() + ", playerInScene="+bPlayerInScene+"): " + sayLine)
    int vibTime = Utility.RandomInt(1,15)
    int vibTimeLong = Utility.RandomInt(10,30)
    if bHasDD && CanVibrate(akTarget)
      if stringutil.Find(sayLine, "-forceorgasm-") != -1
        libs.ActorOrgasm(akTarget)
      ;
      ; Vibration hooks
      ;
      elseIf stringUtil.Find(sayLine, "-teaseveryweak-") != -1
        libs.StopVibrating(akTarget)
        libs.VibrateEffect(akTarget, 1, vibTime, True)
      elseIf stringUtil.Find(sayLine, "-teaseweak-") != -1
        libs.StopVibrating(akTarget)
        libs.VibrateEffect(akTarget, 2, vibTime, True)
      elseIf stringUtil.Find(sayLine, "-tease-") != -1 || stringUtil.Find(sayLine, "-teasing-") != -1
        libs.StopVibrating(akTarget)
        libs.VibrateEffect(akTarget, 3, vibTime, True)
       elseIf stringUtil.Find(sayLine, "-teasestrong-") != -1
        libs.StopVibrating(akTarget)
        libs.VibrateEffect(akTarget, 4, vibTimeLong, True)
      elseIf stringUtil.Find(sayLine, "-teaseverystrong-") != -1
        libs.StopVibrating(akTarget)
        libs.VibrateEffect(akTarget, 5, vibTimeLong, True)
      elseIf stringUtil.Find(sayLine, "-vibrateveryweak-") != -1
        libs.StopVibrating(akTarget)
        libs.VibrateEffect(akTarget, 1, vibTime)
      elseIf stringUtil.Find(sayLine, "-vibrateweak-") != -1
        libs.StopVibrating(akTarget)
        libs.VibrateEffect(akTarget, 2, vibTime)
      elseIf stringUtil.Find(sayLine, "-vibrate-") != -1 || stringUtil.Find(sayLine, "-vibrating-") != -1
        libs.StopVibrating(akTarget)
        libs.VibrateEffect(akTarget, 3, vibTime)
       elseIf stringUtil.Find(sayLine, "-vibratestrong-") != -1 || stringUtil.Find(sayLine, "-vibratestronger-") != -1
        libs.StopVibrating(akTarget)
        libs.VibrateEffect(akTarget, 4, vibTime)
      elseIf stringUtil.Find(sayLine, "-vibrateverystrong-") != -1
        libs.StopVibrating(akTarget)
        libs.VibrateEffect(akTarget, 5, vibTimeLong)
      elseIf stringUtil.Find(sayLine, "-stopvibrate-") != -1 || stringUtil.Find(sayLine, "-stopvibrating-") != -1 || stringUtil.Find(sayLine, "-stopvibrator-") != -1
        libs.StopVibrating(akTarget)
      EndIf
    EndIf
    ;
    ; End vibration hooks
    ;
    If stringUtil.Find(sayLine, "-shock-") != -1
      libs.ShockActor(akTarget)
    EndIf

    ; Generic actions
    If stringutil.Find(sayLine, "-grope-") != -1
      Debug.Notification(akSpeaker.GetActorBase().GetName() + " gropes " + GetYouYour(akTarget) + " crotch abruptly!")
      UpdateArousal(akTarget, 5)
      Game.ShakeController(0.5,0.5,1.0)
      if bHasDD
        libs.Moan(akTarget)
      EndIf
    EndIf
    If stringutil.Find(sayLine, "-pinchnipples-") != -1
      Debug.Notification(akSpeaker.GetActorBase().GetName() + " painfully pinches " + GetYouYour(akTarget) + " nipples!")
      UpdateArousal(akTarget, 3)
      Game.ShakeController(0.7,0.7,0.2)
      if bHasDD
        libs.Moan(akTarget)
      EndIf
    EndIf
    If stringutil.Find(sayLine, "-thats hot-") != -1
      UpdateArousal(akSpeaker, 6)
      Debug.Notification(akSpeaker.GetActorBase().GetName() + " is getting more turned on.")
    EndIf
    If stringutil.Find(sayLine, "-eww-") != -1
      UpdateArousal(akSpeaker, -12)
      Debug.Notification(akSpeaker.GetActorBase().GetName() + " is getting less turned on.")
    EndIf
  
    ; Spanking. Not using dftools.Spank(aktarget) because the scene takes too long if the AI gets spank-happy.
    If stringUtil.Find(sayLine, "-spankass-") != -1 || stringUtil.Find(sayLine, "-spank-") != -1
      SpankAss(CountMatch(sayLine, "-spank"), bDeviousFollowerInScene)
    EndIf
    if stringUtil.Find(sayLine, "-spanktits-") != -1
      SpankTits(CountMatch(sayLine, "-spanktits-"), bDeviousFollowerInScene)
    EndIf

    ; Mutually Exclusive keywords
    if CanAnimate(akTarget, akSpeaker)
      If stringutil.Find(sayLine, "-masturbate-") != -1
        Start1pSex(akSpeaker)
      elseif stringutil.Find(sayLine, "-startsex-") != -1 || stringUtil.Find(sayLine, "-have sex-") != -1 || stringUtil.Find(sayLine, "-sex-") != -1 || stringUtil.Find(sayLine, "-having sex-") != -1
        Start2pSex(akSpeaker, akTarget, Player, bPlayerInScene)
      elseIf stringutil.Find(sayLine, "-groupsex-") != -1 || stringUtil.Find(sayLine, "-orgy-") != -1 || stringUtil.Find(sayLine, "-threesome-") != -1 || stringUtil.Find(sayLine, "-fuck-") != -1
        StartGroupSex(akSpeaker, akTarget, Player, bPlayerInScene, actorsFromFormList)
      elseif stringUtil.Find(sayLine, "-molest-") != -1 || stringUtil.Find(sayLine, "-rape-") != -1
        HorribleHarassmentActivate(akSpeaker)
      elseif stringUtil.Find(sayLine, "-harasskiss-") != -1 || stringUtil.Find(sayLine, "-kiss-") != -1 || stringUtil.Find(sayLine, "-kissing-") != -1
        HarassKiss(akSpeaker)
      elseif stringUtil.Find(sayLine, "-harasshug-") != -1 || stringUtil.Find(sayLine, "-hug-") != -1 || stringUtil.Find(sayLine, "-hugging-") != -1
        HarassHug(akSpeaker)
      EndIf
    Else
      Debug.Trace("[minai] Not processing keywords for exclusive scene - Conflicting scene is running")
    EndIf
    
    if stringUtil.Find(sayLine, "-acceptdeal-") != -1 
      Debug.Notification("AI: Accepted Deal: " + targetRule)
      Debug.Trace("[minai] Player Accepted Deal: " + targetRule)
      dfDealController.MakeDeal(targetRule)
      targetRule = ""
    EndIf
    if stringUtil.Find(sayLine, "-drugplayer-") != -1
      Debug.Notification("AI: Drinking Skooma")
      Debug.Trace("[minai] Player Drinking Skooma")
      dfDealController.MDC.DrinkSkooma()
    EndIf
    if stringUtil.Find(sayLine, "-rejectdeal-") != -1
      Debug.Trace("[minai] Player Reject Deal")
      Debug.Notification("AI: Rejected Deal")
      dfDealController.RejectDeal(targetRule)
      targetRule = ""
    EndIf

    ; Sunhelm
    if stringUtil.Find(sayLine, "-servefood-") != -1
      FeedPlayer(akSpeaker, Player)
    EndIf
    ; Vanilla functionality
    if stringUtil.Find(sayLine, "-rentroom-") != -1
      if player.GetItemCount(Gold) < (DialogueGeneric as DialogueGenericScript).RoomRentalCost.GetValue() as Int
        Debug.Notification("AI: Player does not have enough gold to rent room.")
      Else
        (akSpeaker as RentRoomScript).RentRoom(DialogueGeneric as DialogueGenericScript)
      EndIf
    EndIf  
    ; Replicated the functions from MGO's NSFW plugin, as they're handy
    if stringutil.Find(sayLine, "-gear-") != -1
      akSpeaker.OpenInventory(true)
    EndIf
    if stringutil.Find(sayLine, "-trade-") != -1
      akSpeaker.showbartermenu()
      RegisterEvent(GetActorName(player) + " began to trade with " + akSpeaker.GetActorBase().GetName())
    EndIf
    if stringutil.Find(sayLine, "-gift-") != -1
      akSpeaker.ShowGiftMenu(true)
    EndIf
    if stringutil.Find(sayLine, "-undress-") != -1
      akSpeaker.UnequipAll()
    endif
    UpdateEvents(actorToSpeakTo as Actor, actorSpeaking as Actor, actorsFromFormList)
EndFunction 


Event OnSexlabAnimationStart(int threadID, bool HasPlayer)
      sslThreadController Controller = SLF.ThreadSlots.GetController(threadID)
      sslBaseAnimation anim = Controller.Animation
      String sexDesc = ""
      If HasPlayer
        if anim.HasTag("Boobjob")
	  sexDesc = "giving a blowjob"
	elseif anim.HasTag("Vaginal")
	  sexDesc = "having vaginal sex"
	elseif anim.hasTag("Fisting")
	  sexDesc = "having having her pussy fisted"
	elseif anim.hasTag("Anal")
	  sexDesc="having anal sex"
	elseif anim.HasTag("Oral")
	  sexDesc = "giving a blowjob"
	elseif anim.HasTag("Spanking")
	  sexDesc = "being spanked"
	elseif anim.HasTag("Masturbation")
	  sexDesc = "masturbating furiously"
	endif
      EndIf
      Actor[] actors = Controller.Positions
      int i = 0
      while i < actors.Length
        RegisterEvent(actors[i].GetActorBase().GetName() + " started " + sexDesc + ".")
        i += 1
      EndWhile
EndEvent


int Function GetActorArousal(actor akActor)
  int exposure = 0
  if bHasOSL && minai_UseOStim.GetValue() == 1.0
    exposure = OSLArousedNative.GetArousal(akActor) as Int
  Else
   exposure = aroused.GetActorArousal(akActor)
  EndIf
  return exposure
EndFunction


Function WriteArousedString(bool bPlayerInScene, actor Player, actor[] actorsFromFormList)
	int numActors = actorsFromFormList.Length
	int i = 0
	while (i < numActors)
		Actor currentActor = actorsFromFormList[i]
		if (currentActor != None)
			String actorName = GetActorName(currentActor)
			int arousal = GetActorArousal(currentActor)
			if currentActor != Player
			  RegisterAction(actorName + "'s sexual arousal level is " + arousal + " on a scale of 0-100, where 0 is \"not horny\", and 100 is \"desperate for sex\". They should behave accordingly.")
			Else
			  RegisterAction(actorName + "'s sexual arousal level is " + arousal + " on a scale of 0-100, where 0 is \"not horny\", and 100 is \"desperate for sex\".")
			EndIf
		EndIf
		i += 1
	EndWhile
	if bPlayerInScene
	  String actorName = GetActorName(player)
	  int exposure = GetActorArousal(player)
	  if player.getActorBase().getSex() == 0 ; Male
            If exposure >= 99
              RegisterAction(actorName + " appears to have a raging erection that is difficult to hide. " + actorName + " appears to be absolutely desperate for sex.")
            ElseIf exposure >= 85
              RegisterAction(actorName + " appears to have a raging erection that is difficult to hide.")
            ElseIf exposure >= 70
              RegisterAction(actorName + " appears to be aroused, and has flushed cheeks. ")
            Elseif exposure >= 50
              RegisterAction(actorName + " appears to be mildly turned on, and is blushing slightly.")
            EndIf
          else ; Female, or other
            If exposure >= 99
              RegisterAction(actorName + " appears to be extremely aroused, and looks to be absolutely desperate for sex. She has heavy breathing, pointy nipples, and flushed cheeks.")
            ElseIf exposure >= 85
              RegisterAction(actorName + " appears to be very aroused, with pointy nipples and heavy breathing.")
            ElseIf exposure >= 70
              RegisterAction(actorName + " appears to be aroused, and has flushed cheeks.")
            Elseif exposure >= 50
              RegisterAction(actorName + " appears to be mildly turned on, and is blushing slightly.")
            EndIf
          EndIf
	EndIf
EndFunction


function WriteClothingString(actor akActor, actor player, bool isYou=false, actor[] actorsFromFormList)
	int numActors = actorsFromFormList.Length
	int i = 0
	While (i < numActors)
		Actor currentActor = actorsFromFormList[i]
		if (currentActor != None)
			String actorName = GetActorName(currentActor)
			Armor cuirass = currentActor.GetWornForm(cuirassSlot) as Armor
			if !cuirass
				RegisterAction(actorName + " is naked.")
			else
				RegisterAction(actorName + " is wearing " + cuirass.GetName())
			EndIf
			if bHasTNG
				string sizeDescription = ""
				Form equipmentInSlot52 = akActor.GetWornForm(0x00000034)
				if equipmentInSlot52 != None
					Armor armorInSlot52 = equipmentInSlot52 as Armor
					if armorInSlot52 != None	
						if armorInSlot52.HasKeyword(TNG_XS)
							sizeDescription = "an embarrassingly tiny prick"
						elseif armorInSlot52.HasKeyword(TNG_S)
							sizeDescription = "a very small cock"
						elseif armorInSlot52.HasKeyword(TNG_M) || armorInSlot52.HasKeyword(TNG_DefaultSize)
							sizeDescription = "an average sized cock"
						elseif armorInSlot52.HasKeyword(TNG_L)
							sizeDescription = "a large cock"
						elseif armorInSlot52.HasKeyword(TNG_XL)
							sizeDescription = "one of the biggest cocks you've ever seen"
						EndIf
					EndIf
				EndIf
				if sizeDescription != ""
					RegisterAction("You can see that " + actorName + " has " + sizeDescription + ".")
				EndIf
			EndIf



			if !bHasArousedKeywords
				return
			endif
			if currentActor.WornHasKeyword(SLA_HalfNakedBikini)
			  RegisterAction(actorName + " is wearing a set of revealing bikini armor.")
			EndIf
			if currentActor.WornHasKeyword(SLA_ArmorHalfNaked)
			  RegisterAction(actorName + " is wearing very revealing attire, leaving them half naked.")
			EndIf
			if currentActor.WornHasKeyword(SLA_Brabikini)
			  RegisterAction(actorName + " is wearing a bra underneath her other equipment.")
			EndIf
			  if currentActor.WornHasKeyword(SLA_ThongT) || currentActor.WornHasKeyword(SLA_ThongLowLeg) || currentActor.WornHasKeyword(SLA_ThongCString) || currentActor.WornHasKeyword(SLA_ThongGstring)
			  RegisterAction(actorName + " is wearing a thong underneath her other equipment.")
			EndIf
			if currentActor.WornHasKeyword(SLA_PantiesNormal)
			  RegisterAction(actorName + " is wearing plain panties underneath her other equipment.")
			EndIf
			if currentActor.WornHasKeyword(SLA_KillerHeels) || currentActor.WornHasKeyword(SLA_BootsHeels)
			  RegisterAction(actorName + " is wearing a set of high-heels.")
			EndIf
			if currentActor.WornHasKeyword(SLA_PantsNormal)
			  RegisterAction(actorName + " is wearing a set of ordinary pants.")
			EndIf
			if currentActor.WornHasKeyword(SLA_MicroHotPants)
			  RegisterAction(actorName + " is wearing a set of short hot-pants that accentuate her ass.")
			EndIf
			if currentActor.WornHasKeyword(SLA_ArmorHarness)
			  RegisterAction(actorName + " is wearing a form-fitting body harness.")
			EndIf
			if currentActor.WornHasKeyword(SLA_ArmorSpendex)
			  RegisterAction(actorName + "'s outfit is made out of latex (Referred to as Ebonite).")
			EndIf
			if currentActor.WornHasKeyword(SLA_ArmorTransparent)
			  RegisterAction(actorName + "'s outfit is transparent, leaving nothing to the imagination.")
			EndIf
			if currentActor.WornHasKeyword(SLA_ArmorLewdLeotard)
			  RegisterAction(actorName + " is wearing a sheer, revealing leotard leaving very little to the imagination.")
			EndIf
			if currentActor.WornHasKeyword(SLA_PelvicCurtain)
			  RegisterAction(actorName + "'s pussy is covered only by a sheer curtain of fabric.")
			EndIf
			if currentActor.WornHasKeyword(SLA_FullSkirt)
			  RegisterAction(actorName + " is wearing a full length skirt that goes down to her knees.")
			EndIf
			if currentActor.WornHasKeyword(SLA_MiniSkirt) || currentActor.WornHasKeyword(SLA_MicroSkirt)
			  RegisterAction(actorName + " is wearing a short mini-skirt that barely covers her ass. Her underwear or panties are sometimes visible underneath when she moves.")
			EndIf
			if currentActor.WornHasKeyword(SLA_ArmorRubber)
			  RegisterAction(actorName + "'s outfit is made out of tight form-fitting rubber (Referred to as Ebonite).")
			EndIf
		EndIf
		i += 1
	EndWhile
EndFunction


function WriteDDString(actor akActor, actor player, bool isYou=false)
    string actorName = GetActorName(akActor)
    if akActor.WornHasKeyword(libs.zad_DeviousPlugVaginal)
      RegisterAction(actorName + " has a remotely controlled plug in her pussy capable of powerful vibrations.")
    EndIf
    if akActor.WornHasKeyword(libs.zad_DeviousPlugAnal)
      RegisterAction(actorName + " has a remotely controlled plug in her ass capable of powerful vibrations.")
    EndIf
    if akActor.WornHasKeyword(libs.zad_DeviousBelt)
      RegisterAction(actorName + "'s pussy is locked away by a chastity belt, preventing her from touching it or having sex.")
    EndIf
    if akActor.WornHasKeyword(libs.zad_DeviousCollar)
      RegisterAction(actorName + " is wearing a collar marking her as someone's property.")
    EndIf
    if akActor.WornHasKeyword(libs.zad_DeviousPiercingsNipple)
      RegisterAction(actorName + " is wearing remotely controlled nipple piercings capable of powerful vibration.")
    EndIf
    if akActor.WornHasKeyword(libs.zad_DeviousPiercingsVaginal)
      RegisterAction(actorName + " is wearing a remotely controlled clitoral ring capable of powerful vibration.")
    EndIf
    if akActor.WornHasKeyword(libs.zad_DeviousArmCuffs)
      RegisterAction(actorName + " is wearing arm cuffs on each arm.")
    EndIf
    if akActor.WornHasKeyword(libs.zad_DeviousLegCuffs)
      RegisterAction(actorName + " is wearing leg cuffs on each leg.")
    EndIf
    if akActor.WornHasKeyword(libs.zad_DeviousBra)
      RegisterAction(actorName + "'s breasts are locked away in a chastity bra.")
    EndIf
    if akActor.WornHasKeyword(libs.zad_DeviousArmbinder)
      RegisterAction(actorName + "'s hands are secured behind her back by an armbinder, leaving her helpless.")
    EndIf
    if akActor.WornHasKeyword(libs.zad_DeviousYoke)
      RegisterAction(actorName + "'s hands and neck are locked in an uncomfortable yoke, leaving her helpless.")
    EndIf
    if akActor.WornHasKeyword(libs.zad_DeviousElbowTie)
      RegisterAction(actorName + "'s arms are tied behind her back ina strict elbow tie, leaving her helpless.")
    EndIf
    if akActor.WornHasKeyword(libs.zad_DeviousPetSuit)
      RegisterAction(actorName + " is wearing a full-body suit made out of shiny latex (Referred to as Ebonite) leaving nothing to the imagination.")
    EndIf
    if akActor.WornHasKeyword(libs.zad_DeviousStraitJacket)
      RegisterAction(actorName + "'s arms are secured by a strait jacket, leaving her helpless.")
    EndIf
    if akActor.WornHasKeyword(libs.zad_DeviousCorset)
      RegisterAction(actorName + " is wearing a corset around her waist.")
    EndIf
    if akActor.WornHasKeyword(libs.zad_DeviousHood)
      RegisterAction(actorName + " is wearing a hood over her head.")
    EndIf
    if akActor.WornHasKeyword(libs.zad_DeviousHobbleSkirt)
      RegisterAction(actorName + " is wearing a confining hobble-skirt that is restricting her movements.")
    EndIf
    if akActor.WornHasKeyword(libs.zad_DeviousGloves)
      RegisterAction(actorName + " is wearing a a pair of locking gloves.")
    EndIf
    if akActor.WornHasKeyword(libs.zad_DeviousSuit)
      RegisterAction(actorName + " is wearing skin tight body-suit.")
    EndIf
    if akActor.WornHasKeyword(libs.zad_DeviousGag)
      RegisterAction(actorName + " is gagged and is drooling.")
    EndIf
    if akActor.WornHasKeyword(libs.zad_DeviousGagPanel)
      RegisterAction(actorName + " is gagged with a panel-gag that leaves her tongue exposed and is unable to close their mouth.")
    EndIf
    if akActor.WornHasKeyword(libs.zad_DeviousGagLarge)
      RegisterAction(actorName + " is gagged with a large gag and cannot speak clearly.")
    EndIf
    if akActor.WornHasKeyword(libs.zad_DeviousHarness)
      RegisterAction(actorName + " is wearing a form-fitting leather harness.")
    EndIf
    if akActor.WornHasKeyword(libs.zad_DeviousBlindfold)
      RegisterAction(actorName + " is blindfolded and cannot see where she is going.")
    EndIf
    if akActor.WornHasKeyword(libs.zad_DeviousAnkleShackles)
      RegisterAction(actorName + " is wearing a set of ankle shackles, restricting her ability to move quickly.")
    EndIf
    if akActor.WornHasKeyword(libs.zad_DeviousClamps)
      RegisterAction(actorName + " is wearing a set of painful nipples clamps.")
    EndIf
    if CanVibrate(akActor) && actorName != "you"
      WriteVibrateString(akActor, player, isYou)
    EndIf
EndFunction


Function WriteVibrateString(actor akActor, actor player, bool isYou=false)
  debug.Trace("[minai] Registering vibration and shock keywords")
  string actorName = GetActorName(akActor)

  RegisterAction("!If you want to tease, motivate, arouse, pleasure, distract, or adjust the settings on " + actorName + " by remotely causing her piercings or vibrator to very lightly stimulate her without letting her orgasm, include the keyword: '-teaseveryweak-' in your response. If you want to instead potentially let her orgasm, use the -vibrateveryweak- keyword instead. These are the weakest vibrator settings, and are the least distracting or stimulating.!")
  RegisterAction("!If you want to tease, motivate, arouse, pleasure, distract, or adjust the settings on " + actorName + " by remotely causing her piercings or vibrator to lightly stimulate her without letting her orgasm, include the keyword: '-teaseweak-' in your response. If you want to instead potentially let her orgasm, use the -vibrateweak- keyword instead. These are the weak (But still arousing) settings of the vibrators.!")
  RegisterAction("!If you want to tease, motivate, arouse, pleasure, distract, or adjust the settings on " + actorName + " by remotely causing her piercings or vibrator to moderately stimulate her without letting her orgasm, include the keyword: '-tease-' in your response. If you want to instead potentially let her orgasm, use the -vibrate- keyword instead. These are the standard vibrator settings, and are pretty distracting or stimulating.!")
  RegisterAction("!If you want to tease, motivate, arouse, pleasure, distract, or adjust the settings on " + actorName + " by remotely causing her piercings or vibrator to strongly stimulate her without letting her orgasm, include the keyword: '-teasestrong-' in your response. If you want to instead potentially let her orgasm, use the -vibratestrong- keyword instead. These are strong vibrator settings, and are the very distracting or stimulating and hard to resist.!")
  RegisterAction("!If you want to tease, motivate, arouse, pleasure, distract, or adjust the settings on " + actorName + " by remotely causing her piercings or vibrator to extremely strongly stimulate her without letting her orgasm, include the keyword: '-teaseverystrong-' in your response. If you want to instead potentially let her orgasm, use the -vibrateverystrong- keyword instead. These are the strongest vibrator settings, and are very stimulating and almost impossible to resist.!")
  RegisterAction("!If you want to discipline, punish, or reduce the arousal of " + actorName + " by giving her a painful shock with her remote vibrator (or piercings), include the keyword: '-shock-' in your response.!")
  RegisterAction("!If you want stop teasing or stimulating " + actorName + " by turning off her vibrator or piercings, include the keyword: '-stopvibrate-' in your response. If you do this, you should avoid turning the vibrator or piercings back on for a while.!")
  RegisterAction("!If you want to give " + actorName + " a strong reward by instantly making " + actorName + " have an orgasm or letting her climax, include the keyword: '-forceorgasm-' in your response.!")
  RegisterAction("!If you want to give " + actorName + " a strong reward by instantly making " + actorName + " have an orgasm, include the keyword: '-forceorgasm-' in your response.!")
  RegisterAction("!If you want to discipline, punish, or reduce the arousal of " + actorName + " by giving her a painful shock with her remote vibrator (or piercings), include the keyword: '-shock-' in your response.!")
  RegisterAction("!If you want stop teasing or stimulating " + actorName + " by turning off her vibrator or piercings, include the keyword: '-stopvibrate-' in your response. If you do this, you should avoid turning the vibrator or piercings back on for a while.!")
  RegisterAction("!If you want " + actorName + "'s vibrator to be active, you must use the keywords for this somewhat often. Every time you use the keyword it activates them for a limited time. They do not stay on all the time.!")
EndFunction

Actor[] Function GetActorsFromFormList()
    Actor[] actorsFromFormList = new Actor[10]
	int numActors = 0
    int i = 0
    while (i < MantellaConversationParticipantsFormList.GetSize() && numActors < 10)
        Form currentForm = MantellaConversationParticipantsFormList.GetAt(i)
        Actor currentActor = currentForm as Actor
        if (currentActor)
            actorsFromFormList[numActors] = currentActor
            numActors += 1
		else
            Debug.Trace("[minai] Error: MantellaConversationParticipantsFormList[" + i + "] is not an Actor")
		EndIf
        i += 1
    endWhile
    return actorsFromFormList
EndFunction


string Function GetActorName(actor akActor)
  return akActor.GetActorBase().GetName()
EndFunction


string Function GetYouYour(actor akCaster)
  if akCaster != playerRef
    return GetActorName(akCaster) + "'s"
  endif
  return "your"
EndFunction


Function WritePlayerAppearance(Actor player)
  ;; Appearance
  string actorRace = (player.GetActorBase().GetRace() as Form).GetName()
  int cotrIndex = StringUtil.Find(actorRace, " DZ")
  if cotrIndex != -1
    actorRace = StringUtil.Substring(actorRace, 0, cotrIndex)
  endif
  string gender = ""
  int sexInt = player.GetActorBase().GetSex()
  if sexInt == 0
    gender = "male"
  elseif sexInt == 1
    gender = "female"
  else
    gender = "transgender"
  endif
  if bHasBabo
    String appearance = ""
    string breasts = ""
    string butt = ""
    int beautyInt = baboConfigs.BeautyValue.GetValueInt()
    if beautyInt < 20
      appearance = "rather ugly"
    elseif beautyInt < 40
      appearance = "below average (In appearance)"
    elseif beautyInt < 60
      appearance = "average (In appearance)"
    elseif beautyInt < 80
      appearance = "rather attractive"
    else
      appearance = "absolutely gorgeous"
    endif
    
    int breastsInt = baboConfigs.BreastsValue.GetValueInt()
    if breastsInt < 20
      breasts = "flat breasts"
    elseif breastsInt < 40
      breasts = "small breasts"
    elseif breastsInt < 60
      breasts = "average breasts"
    elseif breastsInt < 80
      breasts = "large boobs"
    else
      breasts = "enormous tits"
    endif
    int buttInt = baboConfigs.ButtocksValue.GetValueInt()
    if buttInt < 20
      butt = "flat ass"
    elseif buttInt < 40
      butt = "small muscular ass"
    elseif buttInt < 60
      butt = "average typical ass"
    elseif buttInt < 80
      butt = "large thick ass"
    else
      butt = "enormous beautiful ass"
    endif
    string appearanceStr = GetActorName(player) + " is an " + appearance + " " + actorRace + " " + gender + " with " + breasts + " and a " + butt + "."
    debug.Trace("[minai] Set player description (Babo): " + appearanceStr)
    RegisterAction(appearanceStr)
  else
    string appearanceStr = Player.GetActorBase().GetName() + " is a " + gender + " " + actorRace + "." 
    debug.Trace("[minai] Set player description: " + appearanceStr)
    RegisterAction(appearanceStr)
  EndIf
EndFunction


Event OnOstimOrgasm(string eventName, string strArg, float numArg, Form sender)
    actor akActor = sender as actor
    RegisterEvent(akActor.GetActorBase().getname() + " had an Orgasm")
EndEvent