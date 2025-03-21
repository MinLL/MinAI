scriptname minai_DirtAndBlood extends Quest

bool bHasDirtAndBlood = False
bool bHasMoreSoaps = False

minai_MainQuestController main
minai_AIFF aiff
minai_Util MinaiUtil

actor playerRef

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

function Maintenance(minai_MainQuestController _main)
  main = _main
  MinaiUtil = (self as Quest) as minai_Util
  MinaiUtil.Info("Initializing Dirt and Blood minAI Module.")
  playerRef = Game.GetPlayer()
  aiff = (Self as Quest) as minai_AIFF
  If Game.GetModByName("Dirt and Blood - Dynamic Visuals.esp") != 255
    MinaiUtil.Log("Found Dirt and Blood - Dynamic Visuals","INFO")
    bHasDirtAndBlood = True
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
  if !bHasDirtandBlood
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

; for CHIM, use tags to pass to the PHP where lists of characters are built 
; ie tom dick and harry smell like roses
Function SetContext(actor akActor)
  Main.Debug("SetContext DirtAndBlood(" + main.GetActorName(akActor) + ")")
  if !bHasDirtandBlood
    return
  EndIf
  string msg = GetTagsForActor(akActor)
  aiff.SetActorVariable(akActor, "dirtAndBlood", msg) 
EndFunction

; for mantella, the whole string
string Function GetStringForActor(actor currentActor)
  String actorName = main.GetActorName(currentActor)
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
  
  If (bDirty_Effect_Clean)
    msg = actorName + " appears immaculately clean and well-groomed, with not a speck of dirt or grime to be seen on their person. Their skin glows with health, and their attire has been meticulously maintained. "
  ElseIf (bDirty_Effect_Dirt1)
    msg = actorName + " has a light dusting of dirt on their clothes and skin, the kind that comes from a normal day of travel or work in Skyrim. Nothing unusual or particularly noticeable by local standards. "
  ElseIf (bDirty_Effect_Dirt2 || bDirty_NPCEffect_Dirt2_Bandits_Fix || bDirty_NPCEffect_Dirt2_Professional) 
    msg = actorName + " shows visible signs of dirt and grime accumulated across their clothing and exposed skin. Their appearance suggests it's been several days since they've had a proper bath or cleaned their garments. "
  ElseIf (bDirty_Effect_Dirt3 || bDirty_NPCEffect_Dirt3_Professional || bDirty_NPCEffect_Dirt3_Bandits_Fix)
    msg = actorName + " is heavily soiled with thick layers of dirt and grime covering much of their body and clothing. A noticeable earthy smell emanates from them, and their fingernails are blackened with embedded dirt. "
  ElseIf (bDirty_Effect_Dirt4 || bDirty_NPCEffect_Dirt4_Bandits_Fix)
    msg = actorName + " is absolutely caked in layers of thick, encrusted filth from head to toe. Their features are barely distinguishable beneath the grime, and they emit a powerful stench of rot, sweat, and decay that causes people nearby to wrinkle their noses in disgust. "
  ElseIf (bDirty_Effect_Blood1 || bDirty_NPCEffect_Blood1)
    msg = actorName + " bears scattered bloodstains across their garments and possibly their skin - spatter marks and small patches that suggest recent violence or perhaps tending to wounds. Though noticeable, the blood is minimal and has begun to dry in places. "
  ElseIf (bDirty_Effect_Blood2 || bDirty_NPCEffect_Blood2) 
    msg = actorName + " displays prominent large bloodstains spread across their clothing and armor. Dark crimson patches and streaks suggest they've been in a serious confrontation recently. The metallic scent of blood lingers around them. "
  ElseIf (bDirty_Effect_Blood3 || bDirty_NPCEffect_Blood3)
    msg = actorName + " is quite bloody, with gore liberally splashed across their person. Their hands and forearms are particularly stained with half-dried blood, and numerous spatters mark their face and clothing. They look as though they've just walked away from intense combat. "
  ElseIf (bDirty_Effect_Blood4 || bDirty_NPCEffect_Blood4 || bDirty_NPCEffect_Blood5)
    msg = actorName + " is covered in blood to a horrifying degree. They are drenched in crimson from head to toe, with blood soaking their clothing and armor, pooling in crevices, and still glistening wetly in places. Bloody footprints mark their path, and the overwhelming metallic smell of fresh gore surrounds them like an aura. "
  EndIf
  
  If(bDirty_NPCEffect_Dirt2_Professional || bDirty_NPCEffect_Dirt3_Professional)
    msg += actorName + "'s dirt appears to be related to their profession. The specific pattern of staining and residue suggests the honorable grime of their daily work rather than simple neglect. "
  EndIf
  If(bDirty_NPCEffect_Dirt2_Bandits_Fix || bDirty_NPCEffect_Dirt3_Bandits_Fix || bDirty_NPCEffect_Dirt4_Bandits_Fix)
    msg += "The particular pattern of grime on " + actorName + " is characteristic of bandits and outlaws who live rough in the wilderness, with distinctive weathering and staining from campfire smoke, forest debris, and extended time without proper facilities. "
  EndIf
  If(bHasMoreSoaps)
    if(bSoapLavenderSoapEffect)
      msg = actorName + " appears freshly bathed and impeccably clean. Their skin and clothing emit a soothing, delicate aroma of lavender that wafts pleasantly through the air around them. "  
    EndIf
    if(bSoapBlueMountainSoapEffect)
      msg = actorName + " is exceptionally clean, with a refreshing fragrance of blue mountain flowers emanating from their person. The sweet, slightly crisp scent suggests a recent thorough bathing with fine-quality soap. "
    EndIf
    if(bSoapDragonsTongueSoapEffect)
      msg = actorName + " presents themselves in an immaculately clean state, surrounded by the exotic and slightly spicy aroma of dragon's tongue flowers. The distinctive scent speaks of luxury and attention to personal hygiene. "
    EndIf
    if(bSoapPurpleMountainFlowerSoapEffect)
      msg = actorName + " exhibits pristine cleanliness, carrying with them the rich, enchanting fragrance of purple mountain flowers. The rare and sought-after scent indicates both wealth and fastidious grooming habits. "
    EndIf
    if(bSoapRedMountainFlowerSoapEffect)
      msg = actorName + " shows evidence of recent and thorough bathing, with the warm, comforting scent of red mountain flowers clinging to their skin and clothes. The pleasantly floral aroma is unmistakable to those familiar with fine soaps. "
    EndIf
    if(bSoapSuperiorMountainFlowerSoapEffect)
      msg = actorName + " presents a picture of perfect cleanliness, surrounded by an intoxicating, complex bouquet of various mountain flowers. The layered, sophisticated fragrance suggests use of the finest and most expensive bathing products available in Skyrim. "
    EndIf
  EndIf
  return msg
EndFunction

; CHIM tag list maker
string Function GetTagsForActor(actor currentActor)
  String actorName = main.GetActorName(currentActor)
  
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
  
  If (bDirty_Effect_Clean)
    msg += "Clean,"
  ElseIf (bDirty_Effect_Dirt1)
    msg += "Dirt1,"
  ElseIf (bDirty_Effect_Dirt2 || bDirty_NPCEffect_Dirt2_Bandits_Fix || bDirty_NPCEffect_Dirt2_Professional) 
    msg += "Dirt2,"
  ElseIf (bDirty_Effect_Dirt3 || bDirty_NPCEffect_Dirt3_Professional || bDirty_NPCEffect_Dirt3_Bandits_Fix)
    msg += "Dirt3,"
  ElseIf (bDirty_Effect_Dirt4 || bDirty_NPCEffect_Dirt4_Bandits_Fix)
    msg += "Dirt4,"
  ElseIf (bDirty_Effect_Blood1 || bDirty_NPCEffect_Blood1)
    msg += "Blood1,"
  ElseIf (bDirty_Effect_Blood2 || bDirty_NPCEffect_Blood2) 
    msg += "Blood2,"
  ElseIf (bDirty_Effect_Blood3 || bDirty_NPCEffect_Blood3)
    msg += "Blood3,"
  ElseIf (bDirty_Effect_Blood4 || bDirty_NPCEffect_Blood4 || bDirty_NPCEffect_Blood5)
    msg += "Blood4,"
  EndIf
  
  If(bDirty_NPCEffect_Dirt2_Professional || bDirty_NPCEffect_Dirt3_Professional)
    msg += "Professional,"
  EndIf
  If(bDirty_NPCEffect_Dirt2_Bandits_Fix || bDirty_NPCEffect_Dirt3_Bandits_Fix || bDirty_NPCEffect_Dirt4_Bandits_Fix)
    msg += "Bandit,"
  EndIf
  If(bHasMoreSoaps)
    if(bSoapLavenderSoapEffect)
      msg += "Lavender,"  
    EndIf
    if(bSoapBlueMountainSoapEffect)
      msg += "Blue,"
    EndIf
    if(bSoapDragonsTongueSoapEffect)
      msg += "DragonsTongue,"
    EndIf
    if(bSoapPurpleMountainFlowerSoapEffect)
      msg += "Purple,"
    EndIf
    if(bSoapRedMountainFlowerSoapEffect)
      msg += "Red,"
    EndIf
    if(bSoapSuperiorMountainFlowerSoapEffect)
      msg += "Superior,"
    EndIf
  EndIf
  return msg
EndFunction


