scriptname minai_ContextEffect extends ActiveMagicEffect

minai_Sex sex
minai_Survival survival
minai_Arousal arousal
minai_DeviousStuff devious
minai_AIFF aiff
minai_MainQuestController main
Spell ContextSpell
minai_FillHerUp fillHerUp

Event OnEffectStart(Actor akTarget, Actor akCaster)
  main = Game.GetFormFromFile(0x0802, "MinAI.esp") as minai_MainQuestController
  aiff = Game.GetFormFromFile(0x0802, "MinAI.esp") as minai_AIFF
  ContextSpell = Game.GetFormFromFile(0x090A, "MinAI.esp") as Spell
  fillHerUp = Game.GetFormFromFile(0x0802, "MinAI.esp") as minai_FillHerUp
  if (!akTarget || !main || !aiff || !aiff.IsInitialized())
    Debug.Trace("[minai] Skipping OnEffectStart, not ready")
    return
  EndIf
  string targetName = Main.GetActorName(akTarget)
  main.Debug("Context OnEffectStart(" + targetName +")")
  ; Register for Fill Her Up animations if mod is available
  if fillHerUp
    fillHerUp.RegisterForAnimationEvents(akTarget)
  endif
  ; Do one update for actors the first time we enter a zone. Introduce a little jitter to distribute load.
  int updateTime = 2 + Utility.RandomInt(0, 5)
  RegisterForSingleUpdate(updateTime)
EndEvent


Event OnEffectFinish(Actor akTarget, Actor akCaster)
  Main.Debug("Context OnEffectFinish( " + Main.GetActorName(akTarget) + ")")
  ; Unregister Fill Her Up animations
  if fillHerUp
    fillHerUp.UnregisterForAnimationEvents(akTarget)
  endif
  UnregisterForUpdate()
  DisableSelf(akTarget)
EndEvent


Function DisableSelf(actor akTarget)
  if !akTarget
    Main.Warn("Context: Could not find target actor. Aborting.")
    return
  EndIf
  Main.Debug("Context OnUpdate( " + Main.GetActorName(akTarget) + ") Stopping OnUpdate.")
  akTarget.RemoveSpell(ContextSpell)
EndFunction

Event OnUpdate()
  actor akTarget = GetTargetActor()
  if !akTarget
    Main.Warn("Context: Could not find target actor. Aborting.")
    return
  EndIf
  string targetName = Main.GetActorName(akTarget)
  Main.Debug("Context OnUpdate (" + targetName +")")
  if(!aiff || !akTarget.Is3DLoaded() || !akTarget.HasSpell(contextSpell))
    Main.Debug("Context Safeguard hit - aborting")
    DisableSelf(akTarget)
    return
  endif
  ; clean up follow, since it can be override or npc has entered a scene
  aiff.CheckIfActorShouldStillFollow(akTarget)
  if AIAgentFunctions.getAgentByName(targetName)
    Main.Debug("Updating context for managed NPC: " + targetName)
    ; sex = (Self as Quest) as minai_Sex
    aiff.SetContext(akTarget)
    if !AIAgentFunctions.getAgentByName(targetName)
      Main.Debug("Actor " + targetName + " went away: Removing context tracking")
      DisableSelf(akTarget)
    else
      RegisterForSingleUpdate(aiff.ContextUpdateInterval)
    endif
  Else
    Main.Debug("Actor " + targetName + " went away: Removing context tracking")
    DisableSelf(akTarget)
  EndIf
  Main.Debug("Context OnUpdate(" + targetName +") END")
EndEvent
