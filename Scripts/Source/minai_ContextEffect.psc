scriptname minai_ContextEffect extends ActiveMagicEffect


minai_Sex sex
minai_Survival survival
minai_Arousal arousal
minai_DeviousStuff devious
minai_AIFF aiff
minai_MainQuestController main
Perk minai_AIManaged

Function OnEffectStart(Actor akTarget, Actor akCaster)
  main = Game.GetFormFromFile(0x0802, "MinAI.esp") as minai_MainQuestController
  aiff = Game.GetFormFromFile(0x0802, "MinAI.esp") as minai_AIFF
  minai_AIManaged = Game.GetFormFromFile(0x0915, "MinAI.esp") as Perk
  if (!akTarget || !main || !aiff || !aiff.IsInitialized())
    Debug.Trace("[minai] Skipping OnEffectStart, not ready")
    return
  EndIf
  string targetName = akTarget.GetActorBase().GetName()
  main.Debug("OnEffectStart(" + targetName +")")
  ; Do one update for actors the first time we enter a zone. Introduce a little jitter to distribute load.
  int updateTime = 2 + Utility.RandomInt(0, 5)
  RegisterForSingleUpdate(updateTime)
  
EndFunction


Event OnUpdate()
  actor akTarget = GetTargetActor()
  string targetName = akTarget.GetDisplayName()
  Main.Debug("Context OnUpdate (" + targetName +")")
  if(!aiff || !akTarget.Is3DLoaded())
    Main.Debug("Context OnUpdate( " + targetName + ") Stopping OnUpdate for actor - actor is not loaded.")
    UnregisterForUpdate()
    return
  endif
  if AIAgentFunctions.getAgentByName(targetName)
    Main.Debug("Updating context for managed NPC: " + targetName)
    ; sex = (Self as Quest) as minai_Sex
    aiff.SetContext(akTarget)
    RegisterForSingleUpdate(aiff.ContextUpdateInterval)
  Else
    ; Cleanup perk if the actor is no longer ai managed
    if akTarget.HasPerk(minai_AIManaged)
      akTarget.RemovePerk(minai_AIManaged)
      Main.Info("Cleaned up perk on actor " + akTarget.GetActorBase().GetName())
    EndIf
    ; Store voice types even if they're not a managed actor so that they will immediately have voices when spoken to
    ; aiff.StoreActorVoice(akTarget)
    ; Store factions and keywords for the same reason
    ; aiff.StoreFactions(akTarget)
    ; aiff.StoreKeywords(akTarget)  
  EndIf
  Main.Debug("Context OnUpdate(" + targetName +") END")
EndEvent