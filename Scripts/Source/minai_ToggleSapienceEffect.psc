scriptname minai_ToggleSapienceEffect extends ActiveMagicEffect


minai_AIFF aiff
minai_MainQuestController main

Event OnEffectStart(Actor akTarget, Actor akCaster)
  main = Game.GetFormFromFile(0x0802, "MinAI.esp") as minai_MainQuestController
  aiff = Game.GetFormFromFile(0x0802, "MinAI.esp") as minai_AIFF
  if (!akTarget || !main || !aiff || !aiff.IsInitialized())
    Debug.Trace("[minai] Skipping OnEffectStart, not ready")
    return
  EndIf
  
  main.Debug("Sapience Toggle - Start")
  aiff.ToggleSapience()
EndEvent

