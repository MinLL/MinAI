scriptname minai_DialogueDetectionEffect extends ActiveMagicEffect


minai_AIFF aiff
minai_MainQuestController main
actor playerRef
  
Event OnEffectStart(Actor akTarget, Actor akCaster)
  main = Game.GetFormFromFile(0x0802, "MinAI.esp") as minai_MainQuestController
  aiff = Game.GetFormFromFile(0x0802, "MinAI.esp") as minai_AIFF
  playerRef = Game.GetPlayer()
  if (!akTarget || !main || !aiff || !aiff.IsInitialized())
    Debug.Trace("[minai] Skipping OnEffectStart, not ready")
    return
  EndIf
  string targetName = Main.GetActorName(akTarget)
  main.Debug("Dialogue Detection: (" + targetName +") entered dialogue with player")
  ; No way to abort dialogue right now, will add expose a native function for this later.
EndEvent


Event OnEffectFinish(Actor akTarget, Actor akCaster)
  string targetName = Main.GetActorName(akTarget)
  main.Debug("Dialogue Detection: (" + targetName +") left dialogue")
EndEvent


