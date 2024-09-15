scriptname minai_AIFF extends Quest

; Modules
minai_Sex sex
minai_Survival survival
minai_Arousal arousal
minai_DeviousStuff devious

bool bHasAIFF = False

int Property contextUpdateInterval Auto
Actor player

minai_MainQuestController main
Function Maintenance(minai_MainQuestController _main)
  contextUpdateInterval = 30
  main = _main
  player = Game.GetPlayer()
  Debug.Trace("[minai] - Initializing for AIFF.")

  sex = (Self as Quest)as minai_Sex
  survival = (Self as Quest)as minai_Survival
  arousal = (Self as Quest)as minai_Arousal
  devious = (Self as Quest)as minai_DeviousStuff
  bHasAIFF = True
  SetContext(player)
  RegisterForModEvent("AIFF_CommandReceived", "CommandDispatcher") ; Hook into AIFF
EndFunction



Function StoreActorVoice(actor akTarget)
  if akTarget == None 
    Return
  EndIf
  VoiceType voice = akTarget.GetVoiceType()
  if !voice
    Return
  EndIf
  ; Fix broken xtts support in AIFF for VR by exposing the voiceType to the plugin for injection
  SetActorVariable(akTarget, "voiceType", voice)
EndFunction


Function SetContext(actor akTarget)
  Debug.Trace("[minai] AIFF - SetContext()")
  devious.SetContext(akTarget)
  arousal.SetContext(akTarget)
  survival.SetContext(akTarget)
EndFunction



Function SetActorVariable(Actor akActor, string variable, string value)
  string actorName = main.GetActorName(akActor)
  Debug.Trace("[minai] Set actor value for actor " + actorName + " "+ variable + " to " + value)
  AIAgentFunctions.logMessage("_minai_" + actorName + "//" + variable + "@" + value, "setconf")
EndFunction


Function RegisterEvent(string eventLine, string eventType)
  AIAgentFunctions.logMessage(eventLine, eventType)
EndFunction


Event CommandDispatcher(String speakerName,String  command, String parameter)
  Debug.Trace("[minai] - CommandDispatcher(" + speakerName +", " + command +", " + parameter + ")")
  Actor akActor = AIAgentFunctions.getAgentByName(speakerName)
  if !akActor
    return
  EndIf
  SetContext(akActor)
  devious.CommandDispatcher(speakerName, command, parameter)
  sex.CommandDispatcher(speakerName, command, parameter)
  survival.CommandDispatcher(speakerName, command, parameter)
  arousal.CommandDispatcher(speakerName, command, parameter)
EndEvent


Function ChillOut()
  if bHasAIFF
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
  string allFactions = ""
  Faction[] factions = akTarget.GetFactions(-128, 127)
  int i = 0
  while i < factions.Length
    allFactions += factions[i].GetName() + "|"
    i += 1
  EndWhile
  SetActorVariable(akTarget, "AllFactions", allFactions)
EndFunction