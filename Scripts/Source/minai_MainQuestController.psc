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

bool bHasMantella = False;
bool bHasAIFF = False;
float lastRequestTime

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
  
  sex.Maintenance(Self)
  survival.Maintenance(Self)
  arousal.Maintenance(Self)
  devious.Maintenance(Self)
  vr.Maintenance(Self)
  
  bHasMantella = (Game.GetModByName("Mantella.esp") != 255)
  bHasAIFF = (Game.GetModByName("AIAgent.esp") != 255)
  
  if bHasMantella
    minMantella.Maintenance(Self)
  EndIf
  if bHasAIFF
    minAIFF.Maintenance(Self)
    if (!minAIFF.IsInitialized())
      Debug.Notification("MinAI - First time setup complete. Save/reload to enable mod functionality")
      minAIFF.SetInitialized()
    EndIf
  EndIf
  lastRequestTime = 0.0
  Info("Initialization complete.")
EndFunction




Function RegisterAction(String eventLine)
  Debug("RegisterAction(" + eventLine + ")")
  if bHasMantella
    minMantella.RegisterAction(eventLine)
  EndIf
EndFunction

Function RegisterEvent(String eventLine, string eventType = "")
  Debug("RegisterEvent(" + eventLine + ", " + eventType + ")")
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

string Function GetActorName(actor akActor)
  return akActor.GetActorBase().GetName()
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