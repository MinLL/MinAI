Scriptname minai_PlayerScript extends ReferenceAlias

minai_MainQuestController Property  MainQuestController  Auto
minai_AIFF aiff
bool bHasAIFF

Event OnPlayerLoadGame()
  MainQuestController.Maintenance()
  bHasAIFF = (Game.GetModByName("AIAgent.esp") != 255)
  if (bHasAIFF)
    aiff = Game.GetFormFromFile(0x0802, "MinAI.esp") as minai_AIFF
  endif
EndEvent

Event OnLocationChange(Location akOldLoc, Location akNewLoc)
  if (bHasAIFF)
    aiff.CleanupSapientActors()
  EndIf
endEvent
