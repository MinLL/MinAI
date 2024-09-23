scriptname minai_AIFF extends Quest

; Modules
minai_Sex sex
minai_Survival survival
minai_Arousal arousal
minai_DeviousStuff devious

bool bHasAIFF = False

int Property contextUpdateInterval Auto
int Property playerContextUpdateInterval Auto

Actor player
VoiceType NullVoiceType
bool isInitialized
minai_MainQuestController main

Function Maintenance(minai_MainQuestController _main)
  contextUpdateInterval = 30
  ; This is inefficient. We need to more selectively set specific parts of the context rather than repeatedly re-set everything.
  ; Things like arousal need to update this often probably, most things don't.
  ; TODO: Break this out and fix this.
  playerContextUpdateInterval = 5
  main = _main
  player = Game.GetPlayer()
  Main.Info("- Initializing for AIFF.")

  sex = (Self as Quest)as minai_Sex
  survival = (Self as Quest)as minai_Survival
  arousal = (Self as Quest)as minai_Arousal
  devious = (Self as Quest)as minai_DeviousStuff
  bHasAIFF = True
  RegisterForModEvent("AIFF_CommandReceived", "CommandDispatcher") ; Hook into AIFF
  NullVoiceType = Game.GetFormFromFile(0x01D70E, "AIAgent.esp") as VoiceType
  if (!NullVoiceType)
    Main.Error("Could not load null voice type")
  EndIf
  if isInitialized
    SetContext(player)
    RegisterForSingleUpdate(playerContextUpdateInterval)
  EndIf
EndFunction



Function StoreActorVoice(actor akTarget)
  if akTarget == None 
    Return
  EndIf
  VoiceType voice = akTarget.GetVoiceType()
  if !voice || voice == NullVoiceType ; AIFF dynamically replaces NPC's voices with "null voice type". Don't store this.
    Return
  EndIf
  ; Fix broken xtts support in AIFF for VR by exposing the voiceType to the plugin for injection
  SetActorVariable(akTarget, "voiceType", voice)
EndFunction


Function SetContext(actor akTarget)
  if !akTarget
    Main.Warn("AIFF - SetContext() called with none target")
    return
  EndIf
  if (!IsInitialized())
    Main.Warn("SetContext(" + akTarget + ") - Still Initializing.")
    return
  EndIf  
  Main.Debug("AIFF - SetContext(" + akTarget.GetDisplayName() + ") START")
  devious.SetContext(akTarget)
  arousal.SetContext(akTarget)
  survival.SetContext(akTarget)
  StoreActorVoice(akTarget)
  StoreKeywords(akTarget)
  StoreFactions(akTarget)
  Main.Debug("AIFF - SetContext(" + akTarget.GetDisplayName() + ") FINISH")
EndFunction



bool Function HasIllegalCharacters(string theString)
  return (StringUtil.Find(theString, "'") != -1)
EndFunction

Function SetActorVariable(Actor akActor, string variable, string value)
  if (!IsInitialized())
    Main.Info("SetActorVariable() - Still Initializing.")
    return
  EndIf
  string actorName = main.GetActorName(akActor) ; Damned khajit
  if (HasIllegalCharacters(actorName) || HasIllegalCharacters(variable) || HasIllegalCharacters(value))
    Main.Error("SetActorVariable(" + variable + "): Not persisting value for " + actorName + " due to illegal character: " + value)
    return
  EndIf
  Main.Debug("Set actor value for actor " + actorName + " "+ variable + " to " + value)
  AIAgentFunctions.logMessage("_minai_" + actorName + "//" + variable + "@" + value, "setconf")
EndFunction


Function RegisterEvent(string eventLine, string eventType)
  if (!IsInitialized())
    Main.Info("RegisterEvent() - Still Initializing.")
    return
  EndIf
  AIAgentFunctions.logMessage(eventLine, eventType)
EndFunction


Event CommandDispatcher(String speakerName,String  command, String parameter)
  Main.Info("AIFF - CommandDispatcher(" + speakerName +", " + command +", " + parameter + ")")
  Actor akActor = AIAgentFunctions.getAgentByName(speakerName)
  if !akActor
    return
  EndIf
  SetContext(akActor)
EndEvent


Function ChillOut()
  if bHasAIFF
    if (!IsInitialized())
      Main.Info("ChillOut() - Still Initializing.")
      return
    EndIf
    AIAgentFunctions.logMessage("Relax and enjoy","force_current_task")
  EndIf
EndFunction

Function SetAnimationBusy(int busy, string name)
  if bHasAIFF
    AIAgentFunctions.setAnimationBusy(busy,name)
  EndIf
EndFunction


Function SetModAvailable(string mod, bool yesOrNo)
  if bHasAIFF
    SetActorVariable(player, "mod_" + mod, yesOrNo)
  EndIf
EndFunction


Function StoreFactions(actor akTarget)
  ; Not sure how to get the editor ID here (Eg, JobInnKeeper).
  ; GetName returns something like "Bannered Mare Services".
  ; Manually check the ones we're interested in.
  
  string allFactions = devious.GetFactionsForActor(akTarget)  
  allFactions += arousal.GetFactionsForActor(akTarget)
  allFactions += survival.GetFactionsForActor(akTarget)
  allFactions += sex.GetFactionsForActor(akTarget)

  ; Causing illegal characters that break sql too often
  Faction[] factions = akTarget.GetFactions(-128, 127)
  int i = 0
  while i < factions.Length
   allFactions += factions[i].GetName() + ","
   i += 1
  EndWhile
  SetActorVariable(akTarget, "AllFactions", allFactions)
EndFunction


; Helper function for keyword management
String Function GetKeywordIfExists(actor akTarget, string keywordStr, Keyword theKeyword)
  if theKeyword == None
    return ""
  EndIf
  if (akTarget.WornHasKeyword(theKeyword))
    return keywordStr + ","
  EndIf
  return ""
EndFunction

; Helper function for faction management
String Function GetFactionIfExists(actor akTarget, string factionStr, Faction theFaction)
  if (akTarget.IsInFaction(theFaction))
    return factionStr + ","
  EndIf
  return ""
EndFunction


Function StoreKeywords(actor akTarget)
  string keywords = devious.GetKeywordsForActor(akTarget)
  keywords += arousal.GetKeywordsForActor(akTarget)
  keywords += survival.GetKeywordsForActor(akTarget)
  keywords += sex.GetKeywordsForActor(akTarget)
  SetActorVariable(akTarget, "AllKeywords", keywords)
EndFunction


bool Function IsInitialized()
  return isInitialized
EndFunction

Function SetInitialized()
  Main.Info("AIFF initialization complete.")
  isInitialized = True
EndFunction


Event OnUpdate()
  if (!IsInitialized())
    UnregisterForUpdate()
    return;
  EndIf
  SetContext(player)
  RegisterForSingleUpdate(playerContextUpdateInterval)
EndEvent

bool Function HasAIFF()
  return bHasAIFF
EndFunction