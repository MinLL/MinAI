Scriptname minai_ConversationMagicEffect extends ActiveMagicEffect

minai_MainQuestController Property  MainQuestController  Auto

event OnEffectStart(Actor akTarget, Actor akCaster)
  debug.Trace("[minai] OnEffectStart()")
  Actor playerRef = game.GetPlayer()
  bool bPlayerInScene = False
  if akTarget == playerRef || akCaster == playerRef
     bPlayerInScene = True
  EndIf

  if bPlayerInScene
    ; Ugly hack. There is a race condition inbetween this file getting cleared by mantella, and us populating it.
    ; In order to have the NPC aware of our context when we first begin the dialogue, we need this data to be present.
    ; I don't feel like clobbering mantella's files to fix this and having to deal with update conflicts or patching the external python script.
    ; Brute force it for now.
    int i = 0
    while i < 15
      MainQuestController.UpdateEvents(akTarget, akCaster)
      Utility.Wait(0.1)
      i += 1
    EndWhile
  EndIf
EndEvent