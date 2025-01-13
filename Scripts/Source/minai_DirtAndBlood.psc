scriptname minai_DirtAndBlood extends Quest

bool bHasDirtAndBlood = False
bool bHasMoreSoaps = False

minai_MainQuestController main
minai_AIFF aiff
minai_Util MinaiUtil

actor playerRef

; Dirt and Blood Spells
; Spell Dirty_SoapEffectSpell
; Spell Dirty_Spell_Dirt1
; Spell Dirty_Spell_Dirt2
; Spell Dirty_Spell_Dirt3
; Spell Dirty_Spell_Dirt4
; Spell Dirty_Spell_Blood1
; Spell Dirty_Spell_Blood2
; Spell Dirty_Spell_Blood3
; Spell Dirty_Spell_Blood4
; Spell Dirty_Spell_Clean
; Spell Dirty_NPCIsWashingNow
; Spell Dirty_Spell_DirtForNPCs
; Spell Dirty_Spell_Swimming
; Spell Dirty_CleanYoSelf
; Spell Dirty_Spell_IsRaining
; Spell Dirty_CleanYoSelfNPC
; Spell Dirty_BloodySpellForNPCs

bool bDirty_SoapEffectSpell
bool bDirty_Spell_Dirt1
bool bDirty_Spell_Dirt2
bool bDirty_Spell_Dirt3
bool bDirty_Spell_Dirt4
bool bDirty_Spell_Blood1
bool bDirty_Spell_Blood2
bool bDirty_Spell_Blood3
bool bDirty_Spell_Blood4
bool bDirty_Spell_Clean

bool bDirty_NPCIsWashingNow
bool bDirty_Spell_DirtForNPCs
bool bDirty_CleanYoSelf
bool bDirty_CleanYoSelfNPC
bool bDirty_BloodySpellForNPCs


MagicEffect Dirty_Effect_Dirt1
MagicEffect Dirty_Effect_Dirt2
MagicEffect Dirty_Effect_Dirt3
MagicEffect Dirty_Effect_Blood1
MagicEffect Dirty_Effect_Blood2
MagicEffect Dirty_Effect_Blood3
MagicEffect Dirty_Effect_Clean
MagicEffect Dirty_NPCEffect_Blood3
MagicEffect Dirty_NPCEffect_Blood4
MagicEffect Dirty_NPCEffect_Blood5
MagicEffect Dirty_Effect_Swimming1
MagicEffect Dirty_NPCEffect_FollowerPotion
MagicEffect Dirty_CleaningEffect
MagicEffect Dirty_Effect_Blood4
MagicEffect Dirty_Effect_Dirt4
MagicEffect Dirty_NPCEffect_Blood2
MagicEffect Dirty_NPCEffect_Blood1
MagicEffect Dirty_Effect_Raining
MagicEffect Dirty_NPCEffect_Dirt2_Bandits_Fix
MagicEffect Dirty_NPCEffect_Dirt2_Professional
MagicEffect Dirty_NPCEffect_Dirt3_Professional
MagicEffect Dirty_NPCEffect_Dirt3_Bandits_Fix
MagicEffect Dirty_NPCEffect_Dirt4_Bandits_Fix
MagicEffect Dirty_Effect_SwimmingNPC



bool bDirty_Effect_Dirt1 = False
bool bDirty_Effect_Dirt2 = False
bool bDirty_Effect_Dirt3 = False
bool bDirty_Effect_Blood1 = False
bool bDirty_Effect_Blood2 = False
bool bDirty_Effect_Blood3 = False
bool bDirty_Effect_Clean = False
bool bDirty_NPCEffect_Blood3 = False
bool bDirty_NPCEffect_Blood4 = False
bool bDirty_NPCEffect_Blood5 = False
bool bDirty_Effect_Swimming1 = False
bool bDirty_NPCEffect_FollowerPotion = False
bool bDirty_CleaningEffect = False
bool bDirty_Effect_Blood4 = False
bool bDirty_Effect_Dirt4 = False
bool bDirty_NPCEffect_Blood2 = False
bool bDirty_NPCEffect_Blood1 = False
bool bDirty_Effect_Raining = False
bool bDirty_NPCEffect_Dirt2_Bandits_Fix = False
bool bDirty_NPCEffect_Dirt2_Professional = False
bool bDirty_NPCEffect_Dirt3_Professional = False
bool bDirty_NPCEffect_Dirt3_Bandits_Fix = False
bool bDirty_NPCEffect_Dirt4_Bandits_Fix = False
bool bDirty_Effect_SwimmingNPC = False


; More Soaps extension for nice smells!

Spell SoapBlueMountainSoapEffect 
Spell SoapDragonsTongueSoapEffect
Spell SoapLavenderSoapEffect
Spell SoapPurpleMountainFlowerSoapEffect
Spell SoapRedMountainFlowerSoapEffect
Spell SoapSuperiorMountainFlowerSoapEffect

bool bSoapBlueMountainSoapEffect 
bool bSoapDragonsTongueSoapEffect
bool bSoapLavenderSoapEffect
bool bSoapPurpleMountainFlowerSoapEffect
bool bSoapRedMountainFlowerSoapEffect
bool bSoapSuperiorMountainFlowerSoapEffect


; set minai_loglevel to 5

function Maintenance(minai_MainQuestController _main)
  main = _main
  MinaiUtil = (self as Quest) as minai_Util
  MinaiUtil.Info("Initializing Dirt and Blood minAI Module.")
  playerRef = Game.GetPlayer()
  aiff = (Self as Quest) as minai_AIFF
  If Game.GetModByName("Dirt and Blood - Dynamic Visuals.esp") != 255
    MinaiUtil.Log("Found Dirt and Blood - Dynamic Visuals","INFO")
    bHasDirtAndBlood = True
    ; Dirty_SoapEffectSpell = Game.GetFormFromFile(0x000800, "Dirt and Blood - Dynamic Visuals.esp") as Spell
    ; Dirty_Spell_Dirt1 = Game.GetFormFromFile(0x000806, "Dirt and Blood - Dynamic Visuals.esp") as Spell
    ; Dirty_Spell_Dirt2 = Game.GetFormFromFile(0x000807, "Dirt and Blood - Dynamic Visuals.esp") as Spell
    ; Dirty_Spell_Dirt3 = Game.GetFormFromFile(0x000808, "Dirt and Blood - Dynamic Visuals.esp") as Spell
    ; Dirty_Spell_Dirt4 = Game.GetFormFromFile(0x000838, "Dirt and Blood - Dynamic Visuals.esp") as Spell
    ; Dirty_Spell_Blood1 = Game.GetFormFromFile(0x000809, "Dirt and Blood - Dynamic Visuals.esp") as Spell
    ; Dirty_Spell_Blood2 = Game.GetFormFromFile(0x00080A, "Dirt and Blood - Dynamic Visuals.esp") as Spell
    ; Dirty_Spell_Blood3 = Game.GetFormFromFile(0x00080B, "Dirt and Blood - Dynamic Visuals.esp") as Spell
    ; Dirty_Spell_Blood4 = Game.GetFormFromFile(0x000839, "Dirt and Blood - Dynamic Visuals.esp") as Spell
    ; Dirty_Spell_Clean = Game.GetFormFromFile(0x00080C, "Dirt and Blood - Dynamic Visuals.esp") as Spell
    ; Dirty_Spell_DirtForNPCs = Game.GetFormFromFile(0x000822, "Dirt and Blood - Dynamic Visuals.esp") as Spell
    ; Dirty_BloodySpellForNPCs = Game.GetFormFromFile(0x000DE6, "Dirt and Blood - Dynamic Visuals.esp") as Spell
    ; Dirty_CleanYoSelf = Game.GetFormFromFile(0x00082F, "Dirt and Blood - Dynamic Visuals.esp") as Spell 
    ; Dirty_NPCIsWashingNow = Game.GetFormFromFile(0x00081C, "Dirt and Blood - Dynamic Visuals.esp") as Spell
    ; Dirty_CleanYoSelfNPC = Game.GetFormFromFile(0x000860, "Dirt and Blood - Dynamic Visuals.esp") as Spell
    Dirty_Effect_Dirt1 = Game.GetFormFromFile(0x00080D, "Dirt and Blood - Dynamic Visuals.esp") as MagicEffect
    Dirty_Effect_Dirt2 = Game.GetFormFromFile(0x00080E, "Dirt and Blood - Dynamic Visuals.esp") as MagicEffect
    Dirty_Effect_Dirt3 = Game.GetFormFromFile(0x00080F, "Dirt and Blood - Dynamic Visuals.esp") as MagicEffect
    Dirty_Effect_Blood1 = Game.GetFormFromFile(0x000810, "Dirt and Blood - Dynamic Visuals.esp") as MagicEffect
    Dirty_Effect_Blood2 = Game.GetFormFromFile(0x000811, "Dirt and Blood - Dynamic Visuals.esp") as MagicEffect
    Dirty_Effect_Blood3 = Game.GetFormFromFile(0x000812, "Dirt and Blood - Dynamic Visuals.esp") as MagicEffect
    Dirty_Effect_Clean = Game.GetFormFromFile(0x000813, "Dirt and Blood - Dynamic Visuals.esp") as MagicEffect
    Dirty_NPCEffect_Blood3 = Game.GetFormFromFile(0x00081D, "Dirt and Blood - Dynamic Visuals.esp") as MagicEffect
    Dirty_NPCEffect_Blood4 = Game.GetFormFromFile(0x00081E, "Dirt and Blood - Dynamic Visuals.esp") as MagicEffect
    Dirty_NPCEffect_Blood5 = Game.GetFormFromFile(0x00081F, "Dirt and Blood - Dynamic Visuals.esp") as MagicEffect
    Dirty_Effect_Swimming1 = Game.GetFormFromFile(0x000826, "Dirt and Blood - Dynamic Visuals.esp") as MagicEffect
    Dirty_CleaningEffect = Game.GetFormFromFile(0x000830, "Dirt and Blood - Dynamic Visuals.esp") as MagicEffect
    Dirty_Effect_Blood4 = Game.GetFormFromFile(0x00083A, "Dirt and Blood - Dynamic Visuals.esp") as MagicEffect
    Dirty_Effect_Dirt4 = Game.GetFormFromFile(0x00083B, "Dirt and Blood - Dynamic Visuals.esp") as MagicEffect
    Dirty_NPCEffect_Blood2 = Game.GetFormFromFile(0x00083F, "Dirt and Blood - Dynamic Visuals.esp") as MagicEffect
    Dirty_NPCEffect_Blood1 = Game.GetFormFromFile(0x000843, "Dirt and Blood - Dynamic Visuals.esp") as MagicEffect
    Dirty_Effect_Raining = Game.GetFormFromFile(0x00085F, "Dirt and Blood - Dynamic Visuals.esp") as MagicEffect
    Dirty_NPCEffect_Dirt2_Bandits_Fix = Game.GetFormFromFile(0x000DBE, "Dirt and Blood - Dynamic Visuals.esp") as MagicEffect
    Dirty_NPCEffect_Dirt2_Professional = Game.GetFormFromFile(0x000DBF, "Dirt and Blood - Dynamic Visuals.esp") as MagicEffect
    Dirty_NPCEffect_Dirt3_Professional = Game.GetFormFromFile(0x000DC0  , "Dirt and Blood - Dynamic Visuals.esp") as MagicEffect
    Dirty_NPCEffect_Dirt3_Bandits_Fix = Game.GetFormFromFile(0x000DC1, "Dirt and Blood - Dynamic Visuals.esp") as MagicEffect
    Dirty_NPCEffect_Dirt4_Bandits_Fix = Game.GetFormFromFile(0x000DC2, "Dirt and Blood - Dynamic Visuals.esp") as MagicEffect
    Dirty_Effect_SwimmingNPC = Game.GetFormFromFile(0x000DE7, "Dirt and Blood - Dynamic Visuals.esp") as MagicEffect
    ; Dirty_Spell_NegativeSideEffects = Game.GetFormFromFile(0x000844, "Dirt and Blood - Dynamic Visuals.esp") as Spell
    ; Dirty_Spell_PositiveSideEffects = Game.GetFormFromFile(0x000848, "Dirt and Blood - Dynamic Visuals.esp") as Spell
    ; Dirty_Spell_Swimming = Game.GetFormFromFile(0x000825, "Dirt and Blood - Dynamic Visuals.esp") as Spell
    ; Dirty_Spell_IsRaining = Game.GetFormFromFile(0x00085D, "Dirt and Blood - Dynamic Visuals.esp") as Spell
    aiff.SetModAvailable("DirtAndBlood", bHasDirtAndBlood)
    If Game.GetModByName("More Soaps.esp") != 255
      SoapBlueMountainSoapEffect = Game.GetFormFromFile(0x001806, "More Soaps.esp") as Spell
      SoapDragonsTongueSoapEffect = Game.GetFormFromFile(0x001814, "More Soaps.esp") as Spell
      SoapLavenderSoapEffect = Game.GetFormFromFile(0x001815, "More Soaps.esp") as Spell
      SoapPurpleMountainFlowerSoapEffect = Game.GetFormFromFile(0x001816, "More Soaps.esp") as Spell
      SoapRedMountainFlowerSoapEffect = Game.GetFormFromFile(0x001817, "More Soaps.esp") as Spell
      SoapSuperiorMountainFlowerSoapEffect = Game.GetFormFromFile(0x001818, "More Soaps.esp") as Spell
      bHasMoreSoaps = True
    EndIf
  EndIf
EndFunction

Function UpdateEventsForMantella(Actor actorToSpeakTo, Actor actorSpeaking, actor[] actorsFromFormList)
  MinaiUtil.Log("UpdateEventsForMantella -  Dirt and Blood","INFO")
  if !aiff
    MinaiUtil.Log("No AIFF in minai_Dirt And Blood","INFO")
    return
  EndIf
	int numActors = actorsFromFormList.Length
	int i = 0
	While (i < numActors)
		Actor currentActor = actorsFromFormList[i]
		if (currentActor != None)
			string msg = GetStringForActor(currentActor)
      main.RegisterEvent(msg, "info_context")
    Endif
		i += 1
	EndWhile
EndFunction

Function SetContext(actor akActor)
  string msg = GetStringForActor(akActor)
  aiff.SetActorVariable(akActor, "dirtAndBlood", msg) 
EndFunction

string Function GetStringForActor(actor currentActor)
  String actorName = main.GetActorName(currentActor)
  ; bDirty_SoapEffectSpell = currentActor.HasSpell(Dirty_SoapEffectSpell) ; player in cleaning animation
  ; bDirty_Spell_Dirt1 = currentActor.HasSpell(Dirty_Spell_Dirt1)
  ; bDirty_Spell_Dirt2 = currentActor.HasSpell(Dirty_Spell_Dirt2)
  ; bDirty_Spell_Dirt3 = currentActor.HasSpell(Dirty_Spell_Dirt3)
  ; bDirty_Spell_Dirt4 = currentActor.HasSpell(Dirty_Spell_Dirt4)
  ; bDirty_Spell_Blood1 = currentActor.HasSpell(Dirty_Spell_Blood1)
  ; bDirty_Spell_Blood2 = currentActor.HasSpell(Dirty_Spell_Blood2)
  ; bDirty_Spell_Blood3 = currentActor.HasSpell(Dirty_Spell_Blood3)
  ; bDirty_Spell_Blood4 = currentActor.HasSpell(Dirty_Spell_Blood4)
  ; bDirty_Spell_Clean = currentActor.HasSpell(Dirty_Spell_Clean)
  ; bDirty_Spell_DirtForNPCs = currentActor.HasSpell(Dirty_Spell_DirtForNPCs)
  ; bDirty_BloodySpellForNPCs = currentActor.HasSpell(Dirty_BloodySpellForNPCs)
  ; bDirty_CleanYoSelfNPC = currentActor.HasSpell(Dirty_CleanYoSelfNPC) ; orders an npc to go through cleaning animation
  ; bDirty_NPCIsWashingNow = currentActor.HasSpell(Dirty_NPCisWashingNow) ; npc does washing animation
  ; bDirty_CleanYoSelf = currentActor.HasSpell(Dirty_CleanYoSelf) ; pc to start cleaning animation

  bDirty_SoapEffectSpell = False
  bDirty_Spell_Dirt1 = False
  bDirty_Spell_Dirt2 = False
  bDirty_Spell_Dirt3 = False
  bDirty_Spell_Dirt4 = False
  bDirty_Spell_Blood1 = False
  bDirty_Spell_Blood2 = False
  bDirty_Spell_Blood3 = False
  bDirty_Spell_Blood4 = False
  bDirty_CleanYoSelfNPC = False
  bDirty_NPCIsWashingNow = False
  bDirty_CleanYoSelf = False
  bDirty_Spell_Clean = False
  bDirty_BloodySpellForNPCs = False
  bDirty_Spell_DirtForNPCs = False
  
  bDirty_Effect_Dirt1 = currentActor.HasMagicEffect(Dirty_Effect_Dirt1)
  bDirty_Effect_Dirt2 = currentActor.HasMagicEffect(Dirty_Effect_Dirt2)
  bDirty_Effect_Dirt3 = currentActor.HasMagicEffect(Dirty_Effect_Dirt3)
  bDirty_Effect_Blood1 = currentActor.HasMagicEffect(Dirty_Effect_Blood1)
  bDirty_Effect_Blood2 = currentActor.HasMagicEffect(Dirty_Effect_Blood2)
  bDirty_Effect_Blood3 = currentActor.HasMagicEffect(Dirty_Effect_Blood3)
  bDirty_Effect_Clean = currentActor.HasMagicEffect(Dirty_Effect_Clean)
  bDirty_NPCEffect_Blood3 = currentActor.HasMagicEffect(Dirty_NPCEffect_Blood3)
  bDirty_NPCEffect_Blood4 = currentActor.HasMagicEffect(Dirty_NPCEffect_Blood4)
  bDirty_NPCEffect_Blood5 = currentActor.HasMagicEffect(Dirty_NPCEffect_Blood5)
  bDirty_Effect_Swimming1 = currentActor.HasMagicEffect(Dirty_Effect_Swimming1)
  bDirty_Effect_Blood4 = currentActor.HasMagicEffect(Dirty_Effect_Blood4)
  bDirty_Effect_Dirt4 = currentActor.HasMagicEffect(Dirty_Effect_Dirt4)
  bDirty_NPCEffect_Blood2 = currentActor.HasMagicEffect(Dirty_NPCEffect_Blood2)
  bDirty_NPCEffect_Blood1 = currentActor.HasMagicEffect(Dirty_NPCEffect_Blood1)
  bDirty_Effect_Raining = currentActor.HasMagicEffect(Dirty_Effect_Raining)
  bDirty_NPCEffect_Dirt2_Bandits_Fix = currentActor.HasMagicEffect(Dirty_NPCEffect_Dirt2_Bandits_Fix)
  bDirty_NPCEffect_Dirt2_Professional = currentActor.HasMagicEffect(Dirty_NPCEffect_Dirt2_Professional)
  bDirty_NPCEffect_Dirt3_Professional = currentActor.HasMagicEffect(Dirty_NPCEffect_Dirt3_Professional)
  bDirty_NPCEffect_Dirt3_Bandits_Fix = currentActor.HasMagicEffect(Dirty_NPCEffect_Dirt3_Bandits_Fix)
  bDirty_NPCEffect_Dirt4_Bandits_Fix = currentActor.HasMagicEffect(Dirty_NPCEffect_Dirt4_Bandits_Fix)
 

  If(bHasMoreSoaps)
    bSoapBlueMountainSoapEffect = currentActor.HasSpell(SoapBlueMountainSoapEffect) 
    bSoapDragonsTongueSoapEffect = currentActor.HasSpell(SoapDragonsTongueSoapEffect)
    bSoapLavenderSoapEffect = currentActor.HasSpell(SoapLavenderSoapEffect)
    bSoapPurpleMountainFlowerSoapEffect = currentActor.HasSpell(SoapPurpleMountainFlowerSoapEffect)
    bSoapRedMountainFlowerSoapEffect = currentActor.HasSpell(SoapRedMountainFlowerSoapEffect)
    bSoapSuperiorMountainFlowerSoapEffect = currentActor.HasSpell(SoapSuperiorMountainFlowerSoapEffect)
  EndIf  
  string msg = ""
  bool isProfessional = False;
  If (bDirty_Spell_Clean || bDirty_Effect_Clean)
    msg = actorName + " is clean and well groomed. "
  ElseIf (bDirty_Spell_Dirt1 || bDirty_Effect_Dirt1)
    msg = actorName + " is barely dirty and fits right in with the people of Skyrim. "
  ElseIf (bDirty_Spell_Dirt2 || bDirty_Effect_Dirt2 || bDirty_NPCEffect_Dirt2_Bandits_Fix || bDirty_NPCEffect_Dirt2_Professional) 
    msg = actorName + " is looking a bit dirty and could use a bath. "
  ElseIf (bDirty_Spell_Dirt3 || bDirty_Effect_Dirt3 || bDirty_NPCEffect_Dirt3_Professional || bDirty_NPCEffect_Dirt3_Bandits_Fix)
  ElseIf (bDirty_Spell_Dirt4 || bDirty_Effect_Dirt4 || bDirty_NPCEffect_Dirt4_Bandits_Fix)
    msg = actorName + " is disgustingly filthy.  " + actorName + " reeks like rotting garbage. "
  ElseIf (bDirty_Spell_Blood1 || bDirty_Effect_Blood1 || bDirty_NPCEffect_Blood1)
    msg = actorName + " has light blotches of blood on themselves. "
  ElseIf (bDirty_Spell_Blood2 || bDirty_Effect_Blood2 || bDirty_NPCEffect_Blood2) 
    msg = actorName + " has blotches of blood on themselves. "
  ElseIf (bDirty_Spell_Blood3 || bDirty_Effect_Blood3 || bDirty_NPCEffect_Blood3)
    msg = actorName + " is covered in blood from battle. "
  ElseIf (bDirty_Spell_Blood4 || bDirty_Effect_Blood4 || bDirty_NPCEffect_Blood4 || bDirty_NPCEffect_Blood5)
    msg = actorName + " is seeping with blood from battle, it drips off every bit of them, and pools in crevices of their armor. "
  ElseIf (bDirty_SoapEffectSpell || bDirty_NPCIsWashingNow)
    msg = actorName + " is bathing. "
  EndIf
  If(bDirty_NPCEffect_Dirt2_Professional || bDirty_NPCEffect_Dirt3_Professional)
    msg += actorName + " wears the dirt of their profession. "
  EndIf
  If(bHasMoreSoaps)
    if(bSoapLavenderSoapEffect)
      msg = actorName + " looks clean and well groomed. " + actorName + " smells pleasantly of Lavender."  
    EndIf
    if(bSoapBlueMountainSoapEffect)
      msg = actorName + " looks clean and well groomed. " + actorName + " smells pleasantly of Blue Mountain Flowers."
    EndIf
    if(bSoapDragonsTongueSoapEffect)
      msg = actorName + " looks clean and well groomed. " + actorName + " smells pleasantly of Dragons Tongue Flowers."
    EndIf
    if(bSoapPurpleMountainFlowerSoapEffect)
      msg = actorName + " looks clean and well groomed. " + actorName + " smells pleasantly of Purple Mountain Flowers."
    EndIf
    if(bSoapRedMountainFlowerSoapEffect)
      msg = actorName + " looks clean and well groomed. " + actorName + " smells pleasantly of Red Mountain Flowers."
    EndIf
    if(bSoapSuperiorMountainFlowerSoapEffect)
      msg = actorName + " looks clean and well groomed. " + actorName + " smells pleasantly of a complex Mountain Flowers bouquet."
    EndIf
  EndIf
  return msg
EndFunction


