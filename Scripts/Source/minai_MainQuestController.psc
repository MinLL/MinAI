ScriptName minai_MainQuestController extends Quest

GlobalVariable minai_WhichAI
actor playerRef

GlobalVariable property logLevel Auto

; AI
minai_Mantella minMantella
minai_AIFF minAIFF

; Modules
minai_Sex sex
minai_Survival survival
minai_Arousal arousal
minai_DeviousStuff devious
minai_VR vr
minai_Config config
minai_Followers followers
minai_CombatManager combat

bool bHasMantella = False;
bool bHasAIFF = False;
float lastRequestTime
actor[] nearbyAI
Event OnInit()
  Maintenance()
EndEvent

Int Function GetVersion()
  return 14
EndFunction

Function Maintenance()
  playerRef = game.GetPlayer()
  config = Game.GetFormFromFile(0x0912, "MinAI.esp") as minai_Config
  if !config
    Fatal("Could not load configuration - script version mismatch with esp")
  EndIf
  logLevel = Game.GetFormFromFile(0x090B, "MinAI.esp") as GlobalVariable
  if (!logLevel)
    Debug.MessageBox("Mismatched MinAI.esp and minai_MainQuestController version")
  EndIf
  Info("Maintenance() - minai v" +GetVersion() + " initializing.")
  ; Register for Mod Events
  Info("Checking for installed mods...")

  minai_WhichAI = Game.GetFormFromFile(0x0907, "MinAI.esp") as GlobalVariable
  minMantella = (Self as Quest) as minai_Mantella
  minAIFF = (Self as Quest) as minai_AIFF
  sex = (Self as Quest) as minai_Sex
  survival = (Self as Quest) as minai_Survival
  arousal = (Self as Quest) as minai_Arousal
  devious = (Self as Quest) as minai_DeviousStuff
  vr = Game.GetFormFromFile(0x090E, "MinAI.esp") as minai_VR
  followers = Game.GetFormFromFile(0x0913, "MinAI.esp") as minai_Followers
  combat = (Self as Quest) as minai_CombatManager

  if (!followers)
    Fatal("Could not load followers script - Mismatched script and esp versions")
  EndIf
  
  bHasMantella = (Game.GetModByName("Mantella.esp") != 255)
  bHasAIFF = (Game.GetModByName("AIAgent.esp") != 255)
  
  ;; Initialize AIFF first so that the action registry is initialized
  if bHasAIFF
    minAIFF.Maintenance(Self)
  EndIf
  
  sex.Maintenance(Self)
  survival.Maintenance(Self)
  arousal.Maintenance(Self)
  devious.Maintenance(Self)
  vr.Maintenance(Self)
  followers.Maintenance(Self)
  combat.Maintenance(Self)
  
  if bHasMantella
    minMantella.Maintenance(Self)
  EndIf
  if bHasAIFF
    if (!minAIFF.IsInitialized())
      Debug.Notification("MinAI - First time setup complete. Save/reload to enable mod functionality")
      minAIFF.SetInitialized()
    Else
      nearbyAI = minAIFF.GetNearbyAI()
    EndIf
    minAIFF.ResetAllActionBackoffs()
  EndIf
  lastRequestTime = 0.0
  RegisterForModEvent("MinAI_RegisterEvent", "OnRegisterEvent")
  RegisterForModEvent("MinAI_RequestResponse", "OnRequestResponse")
  RegisterForModEvent("MinAI_RequestResponseDialogue", "OnRequestResponseDialogue")
  Info("Initialization complete.")
EndFunction




Function RegisterAction(String eventLine)
  ;Debug("RegisterAction(" + eventLine + ")")
  if bHasMantella
    minMantella.RegisterAction(eventLine)
  EndIf
EndFunction

Function RegisterEvent(String eventLine, string eventType = "")
  ;Debug("RegisterEvent(" + eventLine + ", " + eventType + ")")
  if bHasMantella
    minMantella.RegisterEvent(eventLine)
  EndIf
  if bHasAIFF
    if eventType == ""
      eventType = "info_sexscene"
    EndIf
    minAIFF.RegisterEvent(eventLine, eventType)
  EndIf
  
EndFunction


Function RequestLLMResponse(string eventLine, string eventType, string name)
  if bHasAIFF
    float currentTime = Utility.GetCurrentRealTime()
    if currentTime - lastRequestTime > config.requestResponseCooldown
      lastRequestTime = currentTime
      Info("Requesting response from LLM: " + eventLine)
      AIAgentFunctions.requestMessageForActor(eventLine, eventType, name)
    Else
      RegisterEvent(eventLine, eventType)
    EndIf
  elseif bHasMantella
    RegisterEvent(eventLine, eventType)
   EndIf
EndFunction



Function RequestLLMResponseNPC(string speaker, string eventLine, string target)
  if bHasAIFF
    float currentTime = Utility.GetCurrentRealTime()
    string lineToSend = "#NPCTALK(" + speaker + ", " + target + "): " + eventLine
    if currentTime - lastRequestTime > config.requestResponseCooldown
      lastRequestTime = currentTime
      Info("Requesting response from LLM: " + eventLine)
      AIAgentFunctions.requestMessageForActor(eventLine, "chatnf_npc", target)
    Else
      RegisterEvent(lineToSend, "chat_npc")
    EndIf
  elseif bHasMantella
    RegisterEvent(eventLine, "chat_npc")
   EndIf
EndFunction




string Function GetActorName(actor akActor)
  if akActor == playerRef
    return akActor.GetActorBase().GetName()
  else
    return akActor.GetDisplayName()
  EndIf
EndFunction


string Function GetYouYour(actor akCaster)
  if akCaster != playerRef
    return GetActorName(akCaster) + "'s"
  endif
  return "your"
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

Function Log(String str, string lvl)
  Debug.Trace("[minai (" + lvl + ")]: " + str)
EndFunction

Function Fatal(String str)
  ; Always log fatals
  Log(str, "FATAL")
  Debug.MessageBox(str)
EndFunction


Function Error(String str)
  if logLevel.GetValueInt() >= 1
    Log(str, "ERROR")
  EndIf
EndFunction


Function Warn(String str)
  if logLevel.GetValueInt() >= 2
    Log(str, "WARN")
  EndIf
EndFunction


Function Info(String str)
  if logLevel.GetValueInt() >= 3
    Log(str, "INFO")
  EndIf
EndFunction

Function Debug(String str)
  if LogLevel.GetValueInt() >= 4
    Log(str, "DEBUG")
  EndIf
EndFunction

Function DebugVerbose(String str)
  if LogLevel.GetValueInt() >= 5
    Log(str, "VERBOSE")
  EndIf
EndFunction


; Inform the LLM that something has happened, without requesting the LLM to respond immediately.
; int handle = ModEvent.Create("MinAI_RegisterEvent")
;  if (handle)
;    ModEvent.PushString(handle, eventLine)
;    ModEvent.PushString(handle, eventType)
;    ModEvent.Send(handle)
;  endIf
Event OnRegisterEvent(string eventLine, string eventType)
  Info("OnRegisterEvent(" + eventType + "): " + eventLine)
  RegisterEvent(eventLine, eventType)
EndEvent


; Inform the LLM that something has happened, and request a specific actor to respond.
; Use "everyone" for targetName if you don't want a specific response.
; int handle = ModEvent.Create("MinAI_RequestResponse")
;  if (handle)
;    ModEvent.PushString(handle, eventLine)
;    ModEvent.PushString(handle, eventType)
;    ModEvent.PushString(handle, targetName)
;    ModEvent.Send(handle)
;  endIf
Event OnRequestResponse(string eventLine, string eventType, string targetName)
  Info("OnRequestResponse(" + eventType + " => " + targetName + "): " + eventLine)
  RequestLLMResponse(eventLine, eventType, targetName)
EndEvent



; Inform the LLM that an actor has spoken, and request a specific actor to respond.
; Use "everyone" for targetName if you don't want a specific response.
; int handle = ModEvent.Create("MinAI_RequestResponseDialogue")
;  if (handle)
;    ModEvent.PushString(handle, speakerName)
;    ModEvent.PushString(handle, eventLine)
;    ModEvent.PushString(handle, targetName)
;    ModEvent.Send(handle)
;  endIf
Event OnRequestResponseDialogue(string speakerName, string eventLine, string targetName)
  Info("OnRequestResponse(" + speakerName + " => " + targetName + "): " + eventLine)
  RequestLLMResponseNPC(speakerName, eventLine, targetName)
EndEvent


