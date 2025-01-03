scriptname minai_SapienceController extends Quest

minai_MainQuestController main
minai_AIFF aiff
minai_Sex  sex
minai_DeviousStuff devious
minai_Config config
AIAgentMCMConfigScript aiffConfig
  
actor playerRef
bool bHasAIFF
GlobalVariable minai_SapienceEnabled

Actor rechatActor1
Actor rechatActor2
bool bRechatActive
Actor lastRechatActor
int numRechatsSoFar
int targetRechatCount

function Maintenance(minai_MainQuestController _main)
  playerRef = Game.GetPlayer()
  main = _main
  config = Game.GetFormFromFile(0x0912, "MinAI.esp") as minai_Config
  if !config
    Main.Fatal("Could not load configuration - script version mismatch with esp")
  EndIf
  bHasAIFF = (Game.GetModByName("AIAgent.esp") != 255)
  if !bHasAIFF
    return
  EndIf
  aiff = Game.GetFormFromFile(0x0802, "MinAI.esp") as minai_AIFF
  aiffConfig = Game.GetFormFromFile(0x9EC2, "AIAgent.esp") as AIAgentMCMConfigScript
  sex = Game.GetFormFromFile(0x0802, "MinAI.esp") as minai_Sex
  devious = Game.GetFormFromFile(0x0802, "MinAI.esp") as minai_DeviousStuff
  Main.Info("Initializing Sapience Module.")
  minai_SapienceEnabled = Game.GetFormFromFile(0x091A, "MinAI.esp") as GlobalVariable
  RegisterForModEvent("CHIM_CommandReceived", "CommandDispatcher") ; Hook into AIFF - This is a separate quest, so we have to do this separately
  RegisterForModEvent("CHIM_TextReceived", "OnTextReceived")
  StartRadiantDialogue()
  if config.toggleCombatDialogue
    EnableCombatDialogue()
  EndIf
  ; RegisterForKey(aiffConfig._myKey2)
  ; RegisterForKey(aiffConfig._myKey)
EndFunction


Function EnableCombatDialogue()
  ; Enable combat dialogue
  AIAgentFunctions.setConf("_combat_dialogue",1,0,"")
EndFunction

Function DisableCombatDialogue()
  ; Disable combat dialogue
  AIAgentFunctions.setConf("_combat_dialogue",0,0,"")
EndFunction


Event CommandDispatcher(String speakerName,String  command, String parameter)
  if !bHasAIFF
    return
  EndIf
  Main.Debug("Sapience - CommandDispatcher(" + speakerName +", " + command +", " + parameter + ")")

EndEvent


Function SetContext(actor akTarget)
  
EndFunction


string Function GetKeywordsForActor(actor akTarget)
  return "";
EndFunction


string Function GetFactionsForActor(actor akTarget)
  return "";
EndFunction

actor[] Function FindActorsForRechat(actor lastSpeaker)
  actor[] validActors = FindActors(returnAll=true, exclude=lastSpeaker)
  return validActors ; LoS checks are not working consistently, so we're not using them for now
  ; Only return actors that have line of sight  to the speaker
  actor[] ret
  int i = 0
  while i < validActors.Length
    if validActors[i].HasLOS(lastSpeaker) || lastSpeaker.HasLOS(validActors[i])
      Main.Debug("SAPIENCE: Found eligible actor for rechat: " + Main.GetActorName(validActors[i]))
      ret = PapyrusUtil.PushActor(ret, validActors[i])
    EndIf
    i += 1
  EndWhile
  Main.Debug("SAPIENCE: Found " + ret.Length + " eligible actors for rechat (of " + validActors.Length + " nearby actors)")
  return ret
EndFunction 

actor[] Function FindActors(bool returnAll = False, Actor exclude = None)
  actor[] allNearbyActors = AIAgentFunctions.findAllNearbyAgents()
  actor[] nearbyActors
  actor[] ret
  int i = 0
  while i < allNearbyActors.Length
    ; Filter out actors that are having sex or are in combat
    if  allNearbyActors[i].IsInDialogueWithPlayer() ; Player is in dialogue with at least one nearby actor. Don't start radiance.
      return ret
    EndIf
    if Sex.CanAnimate(allNearbyActors[i]) && !allNearbyActors[i].GetCurrentScene() && !allNearbyActors[i].GetDialogueTarget() && Main.GetActorName(allNearbyActors[i]) != "The Narrator" && allNearbyActors[i] != exclude
      Main.Debug("SAPIENCE: Found nearby actor for dialogue: " + Main.GetActorName(allNearbyActors[i]))
      nearbyActors = PapyrusUtil.PushActor(nearbyActors,allNearbyActors[i])
    EndIf
    i += 1
  EndWhile

  if returnAll
    return nearbyActors
  EndIf
  
  if main.PlayerInCombat
    ; Let the NPC decide to talk to the player if they're searching for / in combat
    Main.Debug("SAPIENCE: Combat is active, adding player to radiant target list")
    nearbyActors = PapyrusUtil.PushActor(nearbyActors, playerRef)
  EndIf
  
  if (nearbyActors.Length >= 2)
    ret = new actor[2]
    int actor1 = PO3_SKSEFunctions.GenerateRandomInt(0, nearbyActors.Length - 1)
    int actor2 = actor1
    while actor2 == actor1
      actor2 = PO3_SKSEFunctions.GenerateRandomInt(0, nearbyActors.Length - 1)
    endwhile
    ret[0] = nearbyActors[actor1]
    ret[1] = nearbyActors[actor2]
  EndIf
  return ret
EndFunction


bool Function IsSearching(actor actor1, actor actor2)
  return actor1.GetCombatState() == 2 || actor2.GetCombatState() == 2
EndFunction

bool Function IsInCombat(actor actor1, actor actor2)
  return actor1.GetCombatState() == 1 || actor2.GetCombatState() == 1
EndFunction

bool Function IsFighting(actor actor1, actor actor2)
  return actor1.IsHostileToActor(actor2) || actor2.IsHostileToActor(actor1)
EndFunction

Event OnUpdate()
  if minai_SapienceEnabled.GetValueInt() != 1 || !bHasAIFF
    StopRadiantDialogue()
    return
  EndIf
  ; Don't start radiant dialogue while the player is talking already, or in a menu
  if (Utility.IsInMenuMode() || PlayerRef.GetCurrentScene() || UI.IsMenuOpen("Dialogue Menu"))
    Main.Debug("SAPIENCE: Player in menu or in a scene. Not checking radiant dialogue.")
    ; Shorter cooldown if we were in a blocking condition
    StartNextUpdate(5.0)
    return
  EndIf
  actor[] nearbyActors = FindActors()
  if (nearbyActors && nearbyActors.Length >= 2)
    if Utility.RandomFloat(0, 100) <= config.radiantDialogueChance    
      ResetRechat()
      rechatActor1 = nearbyActors[0]
      rechatActor2 = nearbyActors[1]
      targetRechatCount = PO3_SKSEFunctions.GenerateRandomInt(config.minRadianceRechats, config.maxRadianceRechats)
      string actor1name = Main.GetActorName(nearbyActors[0])
      string actor2name = Main.GetActorName(nearbyActors[1])
      bool actorsAreFighting = IsFighting(nearbyActors[0], nearbyActors[1])
      if IsSearching(nearbyActors[0], nearbyActors[1]) && !actorsAreFighting
        Main.Info("SAPIENCE: Triggering Search Dialogue ( " + actor1name + " => " + actor2name + ")")
        AIAgentFunctions.requestMessageForActor(actor2name, "radiantsearchingfriend", actor1name)
        TriggerRechat(actor1name, actor2name)
      elseif IsSearching(nearbyActors[0], nearbyActors[1]) && actorsAreFighting
        Main.Info("SAPIENCE: Triggering Search Dialogue ( " + actor1name + " => " + actor2name + ")")
        AIAgentFunctions.requestMessageForActor(actor2name, "radiantsearchinghostile", actor1name)
        TriggerRechat(actor1name, actor2name)
      elseIf IsInCombat(nearbyActors[0], nearbyActors[1]) && !actorsAreFighting
        Main.Info("SAPIENCE: Triggering Combat Dialogue ( " + actor1name + " => " + actor2name + ")")
        AIAgentFunctions.requestMessageForActor(actor2name, "radiantcombatfriend", actor1name)
        TriggerRechat(actor1name, actor2name)
      elseIf IsInCombat(nearbyActors[0], nearbyActors[1]) && actorsAreFighting
        Main.Info("SAPIENCE: Triggering Combat Dialogue ( " + actor1name + " => " + actor2name + ")")
        AIAgentFunctions.requestMessageForActor(actor2name, "radiantcombathostile", actor1name)
        TriggerRechat(actor1name, actor2name)
      else
        Main.Info("SAPIENCE: Triggering Radiant Dialogue ( " + actor1name + " => " + actor2name + ")")
        AIAgentFunctions.requestMessageForActor(actor2name, "radiant", actor1name)
        TriggerRechat(actor1name, actor2name)
      EndIf
      ; Set a longer delay after triggering a rechat to avoid overwhelming AIFF if the player has the cooldown set too low.
      ; This will be overridden by OnTextReceived with the proper cooldown after the LLM responds.
      StartNextUpdate(60.0)
    else
      StartNextUpdate()
    EndIf
  Else ; Shorter cooldown if there weren't enough actors nearby last time we checked
    Main.Debug("SAPIENCE: Not enough nearby actors for radiant dialogue or player in blocking condition")
    StartNextUpdate(5.0)
  EndIf
EndEvent

Function ResetRechat()
  lastRechatActor = None
  bRechatActive = false
  numRechatsSoFar = 0
  targetRechatCount = 0
  rechatActor1 = None
  rechatActor2 = None
EndFunction

Function TriggerRechat(string actor1name, string actor2name)
  ; string payload = utility.GetCurrentRealTime() + "|" + utility.GetCurrentGameTime()  + "|" + playerRef.GetCurrentLocation() + "|" + speakerName + " has something to say"
  ; AIAgentFunctions.logMessageForActor("rechat", payload)
  if actor2name == Main.GetActorName(playerRef)
    Main.Debug("SAPIENCE: Not rechatting with player")
    return
  EndIf
  Main.Info("SAPIENCE: Rechat triggered (" + actor2name + " => " + actor1name + "): " + numRechatsSoFar + "/" + targetRechatCount)
  AIAgentFunctions.requestMessageForActor(actor1name, "minai_force_rechat", actor2name)
  numRechatsSoFar += 1
EndFunction

Function CheckForRechat(string speakerName)
  Main.Debug("SAPIENCE: Starting to check for rechat")
  if rechatActor1 == None || rechatActor2 == None
    Main.Debug("SAPIENCE: Not rechatting, no actors set")
    return
  EndIf
  if numRechatsSoFar > targetRechatCount
    Main.Info("SAPIENCE: Rechat limit reached, stopping radiant dialogue")
    ResetRechat()
    return
  EndIf
  string actor1name = Main.GetActorName(rechatActor1)
  string actor2name = Main.GetActorName(rechatActor2)
  ; Don't rechat until after actor2 has responded
  if speakerName == actor2name
    bRechatActive = true
  EndIf
  Actor speaker = AIAgentFunctions.GetAgentByName(speakerName)
  Main.Debug("SAPIENCE: Checking for rechat (" + speakerName + ", " + actor1name + ", " + actor2name + ", bRechatActive: " + bRechatActive + ")")
  if speaker && speaker != lastRechatActor && bRechatActive
    Actor[] eligibleActors = FindActorsForRechat(speaker)
    if (eligibleActors && eligibleActors.Length >= 1)
      Actor newSpeaker = eligibleActors[PO3_SKSEFunctions.GenerateRandomInt(0, eligibleActors.Length - 1)]
      TriggerRechat(speakerName, main.GetActorName(newSpeaker))
    else
      Main.Debug("SAPIENCE: Not enough eligible actors for rechat")
    EndIf
    lastRechatActor = speaker
  EndIf
EndFunction

Event OnTextReceived(String speakerName, String sayLine)
  if minai_SapienceEnabled.GetValueInt() == 1
    Main.Debug("SAPIENCE: Received LLM response, Resetting radiant dialogue cooldown.")
    CheckForRechat(speakerName)
    StartNextUpdate()
  EndIf
EndEvent


Function StartRadiantDialogue()
  Main.Info("SAPIENCE: Beginning Radiant Dialogue")
  StartNextUpdate()
EndFunction


Function StartNextUpdate(float nextTime = 0.0)
  if minai_SapienceEnabled.GetValueInt() == 1  && config.radiantDialogueChance > 0
    if nextTime == 0.0
      RegisterForSingleUpdate(config.radiantDialogueFrequency)
    else
      RegisterForSingleUpdate(nextTime)
    endif
  EndIf
EndFunction

Function StopRadiantDialogue()
  if !bHasAIFF
    return
  EndIf
  Main.Info("SAPIENCE: Stopping Radiant Dialogue")
  UnregisterForUpdate()
EndFunction


Event OnKeyDown(int KeyCode)
  ; if KeyCode == aiffConfig._myKey || KeyCode == aiffConfig._myKey2
  ;    Main.Debug("SAPIENCE: Detected AIFF dialogue key, resetting sapience cooldown")
  ;    StartNextUpdate()
  ;  EndIf
EndEvent
