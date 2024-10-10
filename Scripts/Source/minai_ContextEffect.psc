scriptname minai_ContextEffect extends ActiveMagicEffect


minai_Sex sex
minai_Survival survival
minai_Arousal arousal
minai_DeviousStuff devious
minai_AIFF aiff
minai_MainQuestController main
Spell ContextSpell

Function OnEffectStart(Actor akTarget, Actor akCaster)
  main = Game.GetFormFromFile(0x0802, "MinAI.esp") as minai_MainQuestController
  aiff = Game.GetFormFromFile(0x0802, "MinAI.esp") as minai_AIFF
  ContextSpell = Game.GetFormFromFile(0x090A, "MinAI.esp") as Spell
  if (!akTarget || !main || !aiff || !aiff.IsInitialized())
    Debug.Trace("[minai] Skipping OnEffectStart, not ready")
    return
  EndIf
  string targetName = Main.GetActorName(akTarget)
  main.Debug("OnEffectStart(" + targetName +")")
  ; Do one update for actors the first time we enter a zone. Introduce a little jitter to distribute load.
  int updateTime = 2 + Utility.RandomInt(0, 5)
  RegisterForSingleUpdate(updateTime)
  
EndFunction

Function DisableSelf(actor akTarget)
  Main.Debug("Context OnUpdate( " + Main.GetActorName(akTarget) + ") Stopping OnUpdate for actor - actor is not loaded.")
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
    DisableSelf(akTarget)
    return
  endif
  actor[] nearbyActors = AIAgentFunctions.findAllNearbyAgents()
  if nearbyActors.Find(akTarget)
    Main.Debug("Updating context for managed NPC: " + targetName)
    ; sex = (Self as Quest) as minai_Sex
    aiff.SetContext(akTarget)
    nearbyActors = AIAgentFunctions.findAllNearbyAgents()
    if(!nearbyActors.Find(akTarget))
      DisableSelf(akTarget)
    else
      RegisterForSingleUpdate(aiff.ContextUpdateInterval)
    endif
  Else
    DisableSelf(akTarget)
  EndIf
  Main.Debug("Context OnUpdate(" + targetName +") END")
EndEvent
