scriptname minai_ContextEffect extends ActiveMagicEffect


minai_Sex sex
minai_Survival survival
minai_Arousal arousal
minai_DeviousStuff devious
minai_AIFF aiff

Function OnEffectStart(Actor akTarget, Actor akCaster)
  aiff = Game.GetFormFromFile(0x0802, "MinAI.esp") as minai_AIFF
  string targetName = akTarget.GetActorBase().GetName()
  ; Store voice types even if they're not a managed actor so that they will immediately have voices when spoken to
  aiff.StoreActorVoice(akTarget)
  if AIAgentFunctions.getAgentByName(targetName)
    Debug.Trace("[minai] - Updating context for managed NPC: " + targetName)
    ; sex = (Self as Quest) as minai_Sex
    aiff.SetContext(akTarget)
  EndIf
EndFunction

Event OnUpdate()
  actor akTarget = GetTargetActor()
  if(!aiff || !akTarget.Is3DLoaded())
    UnregisterForUpdate()
    return
  endif
  RegisterForSingleUpdate(aiff.ContextUpdateInterval)
EndEvent