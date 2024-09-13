ScriptName minai_MainQuestController extends Quest

GlobalVariable minai_WhichAI
actor playerRef

; AI
minai_Mantella minMantella
minai_AIFF minAIFF

; Modules
minai_Sex sex
minai_Survival survival
minai_Arousal arousal
minai_DeviousStuff devious


Event OnInit()
  Maintenance()
EndEvent

Int Function GetVersion()
  return 14
EndFunction


Function Maintenance()
  playerRef = game.GetPlayer()
  Debug.Trace("[minai] Maintenance() - minai v" +GetVersion() + " initializing.")
  ; Register for Mod Events
  Debug.Trace("[minai] Checking for installed mods...")

  minai_WhichAI = Game.GetFormFromFile(0x0907, "MinAI.esp") as GlobalVariable
  minMantella = (Self as Quest) as minai_Mantella
  minAIFF = (Self as Quest) as minai_AIFF
  sex = (Self as Quest) as minai_Sex
  survival = (Self as Quest) as minai_Survival
  arousal = (Self as Quest) as minai_Arousal
  devious = (Self as Quest) as minai_DeviousStuff
  sex = (Self as Quest) as minai_Sex
  
  sex.Maintenance(Self)
  survival.Maintenance(Self)
  arousal.Maintenance(Self)
  devious.Maintenance(Self)

  if minMantella
    minMantella.Maintenance(Self)
  EndIf
  if minAIFF
    minAIFF.Maintenance(Self)
  EndIf
  Debug.Trace("[minai] Initialization complete.")
EndFunction




Function RegisterAction(String eventLine)
  minMantella.RegisterAction(eventLine)
EndFunction

Function RegisterEvent(String eventLine)
  minMantella.RegisterEvent(eventLine)
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
