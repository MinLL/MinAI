scriptname minai_DirtAndBlood extends Quest
bool bHasDirtAndBlood = False

minai_MainQuestController main
minai_AIFF aiff

actor playerRef

Spell dirty_SoapEffectSpell
Spell dirty_Spell_Dirt1
Spell dirty_Spell_Dirt2
Spell dirty_Spell_Dirt3
Spell dirty_Spell_Blood1
Spell dirty_Spell_Blood2
Spell dirty_Spell_Blood3
Spell dirty_Spell_Clean
Spell dirty_NPCIsWashingNow
Spell dirty_Spell_DirtForNPCs
Spell dirty_Spell_Swimming
Spell dirty_CleanYoSelf
Spell dirty_Spell_Dirt4
Spell dirty_Spell_Blood4
Spell dirty_Spell_NegativeSideEffects
Spell dirty_Spell_PositiveSideEffects
Spell dirty_Spell_IsRaining
Spell dirty_CleanYoSelfNPC
Spell dirty_BloodySpellForNPCs

bool bDirty_SoapEffectSpell
bool bDirty_Spell_Dirt1
bool bDirty_Spell_Dirt2
bool bDirty_Spell_Dirt3
bool bDirty_Spell_Blood1
bool bDirty_Spell_Blood2
bool bDirty_Spell_Blood3
bool bDirty_Spell_Clean
bool bDirty_NPCIsWashingNow
bool bDirty_Spell_DirtForNPCs
bool bDirty_Spell_Swimming
bool bDirty_CleanYoSelf
bool bDirty_Spell_Dirt4
bool bDirty_Spell_Blood4
bool bDirty_Spell_NegativeSideEffects
bool bDirty_Spell_PositiveSideEffects
bool bDirty_Spell_IsRaining
bool bDirty_CleanYoSelfNPC
bool bDirty_BloodySpellForNPCs


function Maintenance(minai_MainQuestController _main)
  playerRef = Game.GetPlayer()
  main = _main
  aiff = (Self as Quest) as minai_AIFF
  
  Main.Info("Initializing Dirt and Blood minAI Module.")
  if Game.GetModByName("Dirt and Blood - Dynamic Visuals.esp") != 255
    Main.Info("Found Dirt and Blood - Dynamic Visuals")
    bHasDirtAndBlood = True
    SLA_FullSkirt = Game.GetFormFromFile(0x08F40D, "SexlabAroused.esm") as Keyword
    dirty_SoapEffectSpell = Game.GetFormFromFile(0x000800, "Dirt and Blood - Dynamic Visuals.esp") as Spell
    dirty_Spell_Dirt1 = Game.GetFormFromFile(0x000806, "Dirt and Blood - Dynamic Visuals.esp") as Spell
    dirty_Spell_Dirt2 = Game.GetFormFromFile(0x000807, "Dirt and Blood - Dynamic Visuals.esp") as Spell
    dirty_Spell_Dirt3 = Game.GetFormFromFile(0x000808, "Dirt and Blood - Dynamic Visuals.esp") as Spell
    dirty_Spell_Blood1 = Game.GetFormFromFile(0x000809, "Dirt and Blood - Dynamic Visuals.esp") as Spell
    dirty_Spell_Blood2 = Game.GetFormFromFile(0x00080A, "Dirt and Blood - Dynamic Visuals.esp") as Spell
    dirty_Spell_Blood3 = Game.GetFormFromFile(0x00080B, "Dirt and Blood - Dynamic Visuals.esp") as Spell
    dirty_Spell_Clean = Game.GetFormFromFile(0x00080C, "Dirt and Blood - Dynamic Visuals.esp") as Spell
    dirty_NPCIsWashingNow = Game.GetFormFromFile(0x00081C, "Dirt and Blood - Dynamic Visuals.esp") as Spell
    dirty_Spell_DirtForNPCs = Game.GetFormFromFile(0x000822, "Dirt and Blood - Dynamic Visuals.esp") as Spell
    dirty_Spell_Swimming = Game.GetFormFromFile(0x000825, "Dirt and Blood - Dynamic Visuals.esp") as Spell
    dirty_CleanYoSelf = Game.GetFormFromFile(0x00082F, "Dirt and Blood - Dynamic Visuals.esp") as Spell
    dirty_Spell_Dirt4 = Game.GetFormFromFile(0x000838, "Dirt and Blood - Dynamic Visuals.esp") as Spell
    dirty_Spell_Blood4 = Game.GetFormFromFile(0x000839, "Dirt and Blood - Dynamic Visuals.esp") as Spell
    dirty_Spell_NegativeSideEffects = Game.GetFormFromFile(0x000844, "Dirt and Blood - Dynamic Visuals.esp") as Spell
    dirty_Spell_PositiveSideEffects = Game.GetFormFromFile(0x000848, "Dirt and Blood - Dynamic Visuals.esp") as Spell
    dirty_Spell_IsRaining = Game.GetFormFromFile(0x00085D, "Dirt and Blood - Dynamic Visuals.esp") as Spell
    dirty_CleanYoSelfNPC = Game.GetFormFromFile(0x000860, "Dirt and Blood - Dynamic Visuals.esp") as Spell
    dirty_BloodySpellForNPCs = Game.GetFormFromFile(0x000DE6, "Dirt and Blood - Dynamic Visuals.esp") as Spell
  EndIf
  aiff.SetModAvailable("DirtAndBlood", bHasDirtAndBlood)
EndFunction


Function SetContext(actor akTarget)
  if !aiff
    return
  EndIf

  bDirty_SoapEffectSpell = actor.HasSpell(dirty_SoapEffectSpell)
  bDirty_Spell_Dirt1 = actor.HasSpell(dirty_Spell_Dirt1)
  bDirty_Spell_Dirt2 = actor.HasSpell(dirty_Spell_Dirt2)
  bDirty_Spell_Dirt3 = actor.HasSpell(dirty_Spell_Dirt3)
  bDirty_Spell_Dirt4 = actor.HasSpell(dirty_Spell_Dirt4)
  bDirty_Spell_Blood1 = actor.HasSpell(dirty_Spell_Blood1)
  bDirty_Spell_Blood2 = actor.HasSpell(dirty_Spell_Blood2)
  bDirty_Spell_Blood3 = actor.HasSpell(dirty_Spell_Blood3)
  bDirty_Spell_Blood4 = actor.HasSpell(dirty_Spell_Blood4)
  bDirty_Spell_Clean = actor.HasSpell(dirty_Spell_Clean, False)
 
  string dirt_and_blood_tag_list = ""
  If bDirty_SoapEffectSpell 
    dirt_and_blood_tag_list += "SoapEffectSpell,"
  EndIf
  
  If bDirty_Spell_Dirt1
    dirt_and_blood_tag_list += "Dirt1,"
  EndIf
  
  If bDirty_Spell_Dirt2 
    dirt_and_blood_tag_list += "Dirt2,"
  EndIf
  
  If bDirty_Spell_Dirt3 
    dirt_and_blood_tag_list += "Dirt3,"
  EndIf
  
  If bDirty_Spell_Dirt4 
    dirt_and_blood_tag_list += "Dirt4,"
  EndIf
  
  If bDirty_Spell_Blood1 
    dirt_and_blood_tag_list += "Blood1,"
  EndIf
  
  If bDirty_Spell_Blood2 
    dirt_and_blood_tag_list += "Blood2,"
  EndIf
  
  If bDirty_Spell_Blood3 
    dirt_and_blood_tag_list += "Blood3,"
  EndIf
  
  If bDirty_Spell_Blood4 
    dirt_and_blood_tag_list += "Blood4,"
  EndIf
  
  If bDirty_Spell_Clean
    dirt_and_blood_tag_list += "Clean,"
  EndIf  
  aiff.SetActorVariable(akTarget, "dirt_and_blood_tag_list", dirt_and_blood_tag_list)
EndFunction

