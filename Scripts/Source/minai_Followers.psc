scriptname minai_Followers extends Quest

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
  Main.Info("Initializing Followers Module.")
  RegisterForModEvent("AIFF_CommandReceived", "CommandDispatcher") ; Hook into AIFF - This is a separate quest, so we have to do this separately
  bHasAIFF = (Game.GetModByName("AIAgent.esp") != 255)
  bHasNFF = (Game.GetModByName("nwsFollowerFramework.esp") != 255)
  if bHasNFF
    Main.Info("Found NFF")
    nff = Game.GetFormFromFile(0x00434F, "nwsFollowerFramework.esp") as nwsFollowerControllerScript
    if !nff
      Main.Error("Could not load main NFF quest. Disabling NFF support")
      bHasNFF = False
    EndIf
  EndIf
  aiff.RegisterAction("ExtCmdStartLooting", "StartLooting", "Start Looting the Area", "Followers", 1, 0, 2, 5, 60, True)
  aiff.RegisterAction("ExtCmdStopLooting", "StopLooting", "Stop Looting the Area", "Followers", 1, 0, 2, 5, 60, True)
EndFunction


Function StartLooting()
  nff.CallLooting(true, false, false)
EndFunction


Function StopLooting()
  nff.CallLooting(false, True, false)
EndFunction


Event CommandDispatcher(String speakerName,String  command, String parameter)
  if !bHasAIFF
    return
  EndIf
  Main.Debug("Followers - CommandDispatcher(" + speakerName +", " + command +", " + parameter + ")")
  if (command == "ExtCmdStartLooting")
    StartLooting()
    Main.RegisterEvent(speakerName + " started looting the area")
  elseif (command == "ExtCmdStopLooting")
    StartLooting()
    Main.RegisterEvent(speakerName + " stopped looting the area")
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

bool Function IsFollower(actor akActor)
  
EndFunction