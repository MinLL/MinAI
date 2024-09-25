scriptname minai_CombatManager extends Quest

minai_MainQuestController main
minai_AIFF aiff
minai_Sex  sex
minai_DeviousStuff devious
minai_Config config

actor playerRef
bool bHasAIFF
bool bHasNFF

nwsFollowerControllerScript nff

function Maintenance(minai_MainQuestController _main)
  playerRef = Game.GetPlayer()
  main = _main
  config = Game.GetFormFromFile(0x0912, "MinAI.esp") as minai_Config
  if !config
    Main.Fatal("Could not load configuration - script version mismatch with esp")
  EndIf
  aiff = Game.GetFormFromFile(0x0802, "MinAI.esp") as minai_AIFF
  sex = Game.GetFormFromFile(0x0802, "MinAI.esp") as minai_Sex
  devious = Game.GetFormFromFile(0x0802, "MinAI.esp") as minai_DeviousStuff
  Main.Info("Initializing Combat Management Module.")
  bHasAIFF = (Game.GetModByName("AIAgent.esp") != 255)
  RegisterForModEvent("DefeatPostAssault", "OnDefeat")
  RegisterForModEvent("da_StartRecoverSequence", "OnDefeatRecoverSequence")
  RegisterForModEvent("AnimationEnd_Defeat", "OnDefeatAnimationEnd")
EndFunction

event OnDefeatRecoverSequence(Form sender, Form theForm, int theInt, string theString)
  main.Info("OnDefeatRecoverSequence(" + sender + ", " + theInt + ", " + theString + ")")
EndEvent


event OnDefeat(Form sender, Form theForm, int theInt, string theString)
  main.Info("OnDefeat(" + sender + ", " + theInt + ", " + theString + ")")
EndEvent

event OnDefeatAnimationEnd(Form sender, Form theForm, int theInt, string theString)
  main.Info("OnDefeatAnimationEnd(" + sender + ", " + theInt + ", " + theString + ")")
EndEvent


Event CommandDispatcher(String speakerName,String  command, String parameter)
  if !bHasAIFF
    return
  EndIf

EndEvent


Function SetContext(actor akTarget)
  
EndFunction


string Function GetKeywordsForActor(actor akTarget)
  return "";
EndFunction


string Function GetFactionsForActor(actor akTarget)
  return "";
EndFunction

