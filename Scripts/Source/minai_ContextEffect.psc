scriptname minai_ContextEffect extends ActiveMagicEffect

minai_Sex sex
minai_Survival survival
minai_Arousal arousal
minai_DeviousStuff devious
minai_AIFF aiff
minai_MainQuestController main
minai_Config config
Spell ContextSpell
minai_FillHerUp fillHerUp
Form gold
Event OnEffectStart(Actor akTarget, Actor akCaster)
  main = Game.GetFormFromFile(0x0802, "MinAI.esp") as minai_MainQuestController
  aiff = Game.GetFormFromFile(0x0802, "MinAI.esp") as minai_AIFF
  ContextSpell = Game.GetFormFromFile(0x090A, "MinAI.esp") as Spell
  config = Game.GetFormFromFile(0x0912, "MinAI.esp") as minai_Config
  fillHerUp = Game.GetFormFromFile(0x0802, "MinAI.esp") as minai_FillHerUp
  gold = Game.GetFormFromFile(0x00000F, "Skyrim.esm") as Form
  if (!akTarget || !main || !aiff || !aiff.IsInitialized())
    Debug.Trace("[minai] Skipping OnEffectStart, not ready")
    return
  EndIf
  if (!config)
    Debug.Trace("[minai] Skipping OnEffectStart, config not found")
    return
  endif
  string targetName = Main.GetActorName(akTarget)
  main.Debug("Context OnEffectStart(" + targetName +")")
  ; Register for Fill Her Up animations if mod is available
  if fillHerUp
    fillHerUp.RegisterForAnimationEvents(akTarget)
  endif
  
  if aiff && aiff.IsInitialized()    
    ; Initialize actor inventory tracking
    EnableInventoryTracking()
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
  if aiff.AIGetAgentByName(targetName)
    Main.Debug("Updating context for managed NPC: " + targetName)
    ; sex = (Self as Quest) as minai_Sex
    aiff.SetContext(akTarget)
    if !aiff.AIGetAgentByName(targetName)
      Main.Debug("Actor " + targetName + " went away: Removing context tracking")
      DisableSelf(akTarget)
    else
      if (!config)
        config = Game.GetFormFromFile(0x0912, "MinAI.esp") as minai_Config
        Main.Warn("Context: Populated missing config.")
      EndIf
      RegisterForSingleUpdate(config.contextUpdateInterval)
    endif
  Else
    Main.Debug("Actor " + targetName + " went away: Removing context tracking")
    DisableSelf(akTarget)
  EndIf
  if aiff.IsInventoryThrottled(akTarget)
    EnableInventoryTracking()
  endif
  Main.Debug("Context OnUpdate(" + targetName +") END")
EndEvent

Event OnItemAdded(Form akBaseItem, int aiItemCount, ObjectReference akItemReference, ObjectReference akSourceContainer)
  actor akTarget = GetTargetActor()
  if !akTarget
    return
  EndIf
  if aiff
    if aiff.OnInventoryChanged(akTarget, akBaseItem, aiItemCount, true)
      ; Throttled
      DisableInventoryTracking()
    endif 
  EndIf
EndEvent

Event OnItemRemoved(Form akBaseItem, int aiItemCount, ObjectReference akItemReference, ObjectReference akDestContainer)
  actor akTarget = GetTargetActor()
  if !akTarget
    return
  EndIf
  if aiff
    if aiff.OnInventoryChanged(akTarget, akBaseItem, aiItemCount, false)
      ; Throttled
      DisableInventoryTracking()
    endif 
  EndIf
EndEvent


event DisableInventoryTracking()
  ; Filter out everything except gold to neuter the item change events
  actor akTarget = GetTargetActor()
  Main.Info("Disabling inventory tracking for " + Main.GetActorName(akTarget))
  if (!gold)
    gold = Game.GetFormFromFile(0x00000F, "Skyrim.esm") as Form
  endif
  AddInventoryEventFilter(gold)
endEvent


event EnableInventoryTracking()
  ; Remove the filter to allow all item changes again
  actor akTarget = GetTargetActor()
  Main.Info("Enabling inventory tracking for " + Main.GetActorName(akTarget))
  if (!gold)
    gold = Game.GetFormFromFile(0x00000F, "Skyrim.esm") as Form
  endif
  RemoveInventoryEventFilter(gold)
  aiff.TrackActorInventory(akTarget)
endEvent


