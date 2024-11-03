scriptname minai_BleedoutDetectionEffect extends ActiveMagicEffect


minai_AIFF aiff
minai_MainQuestController main
minai_CombatManager combat
actor playerRef
  
Event OnEffectStart(Actor akTarget, Actor akCaster)
  main = Game.GetFormFromFile(0x0802, "MinAI.esp") as minai_MainQuestController
  combat = Game.GetFormFromFile(0x0802, "MinAI.esp") as minai_CombatManager
  aiff = Game.GetFormFromFile(0x0802, "MinAI.esp") as minai_AIFF
  playerRef = Game.GetPlayer()
  if (!akTarget || !main || !aiff || !aiff.IsInitialized())
    Debug.Trace("[minai] Skipping OnEffectStart, not ready")
    return
  EndIf
  string targetName = Main.GetActorName(akTarget)
  main.Debug("Bleedout Detection: (" + targetName +") entered bleedout")
  aiff.SetActorVariable(akTarget, "isBleedingOut", true)
  combat.OnBleedoutStart(akTarget)
EndEvent


Event OnEffectFinish(Actor akTarget, Actor akCaster)
  string targetName = Main.GetActorName(akTarget)
  main.Debug("Bleedout Detection: (" + targetName +") left bleedout")
  aiff.SetActorVariable(akTarget, "isBleedingOut", false)
  combat.OnBleedoutEnd(akTarget)
EndEvent

