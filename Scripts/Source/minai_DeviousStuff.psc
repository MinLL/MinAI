scriptname minai_DeviousStuff extends Quest

zadLibs Property libs Auto
zadDeviceLists ddLists
SLAppPCSexQuestScript slapp
_DFtools dftools
_DFDealUberController dfDealController

bool bHasDeviousFollowers = False
bool bHasDD = False
bool bHasSTA = False
bool bHasSLHH = False
bool bHasSLApp = False
bool bHasDeviouslyAccessible = False
bool bHasDDExpansion = False
bool bHasSubmissiveLola = False
bool bHasTNTR = False
bool bHasSlaveTats = False
Keyword SLHHScriptEventKeyword
GlobalVariable Debt
GlobalVariable EnslaveDebt
GlobalVariable ContractRemaining
string targetRule = ""

minai_MainQuestController main
minai_Arousal arousal
minai_Sex sex
minai_AIFF aiff
minai_Config config

GlobalVariable eyefucktrack
GlobalVariable eyepenalty
GlobalVariable eyereward
GlobalVariable eyescore
MagicEffect dwp_watched
GlobalVariable dwp_global_minai
Spell dwp_eldwhoresp
Spell dwp_eldwhoresp_neq  
Spell dwp_
actor playerRef
Perk dwp_eldritchwaifueffect_soldsoul
Perk dwp_eldritchwaifueffect_soldsoul_belted

; TODO: Break this out into a separate script for each mod.

; Deviously Accessible Magic Effects
MagicEffect dwp_descbadgirl
MagicEffect dwp_descbadgirl2 
MagicEffect dwp_descbadgirl2b
MagicEffect dwp_descbadgirl2c
MagicEffect dwp_descbadgirl2d
MagicEffect dwp_descbadgirlb
MagicEffect dwp_descbadgirlBASE
MagicEffect dwp_descbadgirlc
MagicEffect dwp_descbadgirld
MagicEffect dwp_descgoodgirl
MagicEffect dwp_descgoodgirl2
MagicEffect dwp_descgoodgirl2b
MagicEffect dwp_descgoodgirl2c
MagicEffect dwp_descgoodgirl2d
MagicEffect dwp_descgoodgirlb
MagicEffect dwp_descgoodgirlBASE
MagicEffect dwp_descgoodgirlc
MagicEffect dwp_descgoodgirld
MagicEffect dwp_descmindcontrol
MagicEffect dwp_descmindcontrol2
MagicEffect dwp_descmindcontrolpost
MagicEffect dwp_descmindcontrolpunishment
MagicEffect dwp_descverybadgirl
MagicEffect dwp_descverybadgirl2
MagicEffect dwp_descverybadgirl2b
MagicEffect dwp_descverybadgirl2c
MagicEffect dwp_descverybadgirl2d
MagicEffect dwp_descverybadgirlb
MagicEffect dwp_descverybadgirlBASE
MagicEffect dwp_descverybadgirlc
MagicEffect dwp_descverybadgirld

function Maintenance(minai_MainQuestController _main)
  Main.Info("Initializing Devious Module")
  playerRef = game.GetPlayer()
  main = _main
  config = Game.GetFormFromFile(0x0912, "MinAI.esp") as minai_Config
  if !config
    Main.Fatal("Could not load configuration - script version mismatch with esp")
  EndIf
  
  arousal = (Self as Quest) as minai_Arousal
  sex = (Self as Quest) as minai_Sex
  aiff = (Self as Quest) as minai_AIFF
    
  RegisterForModEvent("DeviceActorOrgasm", "OnOrgasm")
  RegisterForModEvent("DeviceEdgedActor", "OnEdged")
  RegisterForModEvent("DeviceVibrateEffectStart", "OnVibrateStart")
  RegisterForModEvent("DeviceVibrateEffectStop", "OnVibrateStop")

  RegisterForModEvent("DDI_DeviceEquipped", "OnDeviceEquipped")
  RegisterForModEvent("DDI_DeviceRemoved", "OnDeviceRemoved")
  RegisterForModEvent("DDI_KeyBreak", "OnKeyBreak")
  RegisterForModEvent("DDI_JamLock", "OnJamLock")
  RegisterForModEvent("DDI_DeviceEscapeAttempt", "OnDeviceEscapeAttempt")
  
  libs = Game.GetFormFromFile(0x00F624, "Devious Devices - Integration.esm") as zadlibs
    if libs
    bHasDD = True
    Main.Info("Found Devious Devices")
    ddLists = (Game.GetFormFromFile(0x00CA01, "Devious Devices - Expansion.esm") as Quest) as zadDeviceLists
    if ddLists
      bHasDDExpansion = True
      Main.Info("Found Devious Devices Expansion")
    EndIf
  EndIf
  
  if Game.GetModByName("DeviousFollowers.esp") != 255
    Main.Info("Found Devious Followers")
    bHasDeviousFollowers = True
    Debt = Game.GetFormFromFile(0xC54F, "DeviousFollowers.esp") as GlobalVariable
    ContractRemaining = Game.GetFormFromFile(0x218C7C, "DeviousFollowers.esp") as GlobalVariable
    dftools = Game.GetFormFromFile(0x01210D, "DeviousFollowers.esp") as _DFtools
    dfDealController = Game.GetFormFromFile(0x01C86D, "DeviousFollowers.esp") as _DFDealUberController
    EnslaveDebt = Game.GetFormFromFile(0x00C548, "DeviousFollowers.esp") as GlobalVariable
    Main.Info("enslaveDebt=" + EnslaveDebt.GetValueInt())
    Main.Info("dftools="+dftools)
    
    if dftools == None || enslavedebt == None || dfDealController == None
      Debug.Notification("Warning: Some devious followers content will be broken, incompatible version!")
    EndIf
  EndIf
  
  if Game.GetModByName("Spank That Ass.esp") != 255
    Main.Info("Found Spank That Ass")
    bHasSTA = True
  EndIf

  if Game.GetModByName("SexlabHorribleHarassment.esp") != 255
    bHasSLHH = True
    Main.Info("Found SLHH")
    SLHHScriptEventKeyword = Game.GetFormFromFile(0x00C510, "SexLabHorribleHarassment.esp") as Keyword
    if !SLHHScriptEventKeyword
      Main.Error("Could not find SLHHScriptEventKeyword")
      Debug.Notification("Incompatible version of SLHH. AI Integrations Disabled.")
      bHasSLHH = False
    EndIf
  EndIf

  if Game.GetModByName("Sexlab Approach.esp") != 255
    bHasSLApp = True
    Main.Info("Found SLApp")
    slapp = Game.GetFormFromFile(0x0083F7, "Sexlab Approach.esp") as SLAppPCSexQuestScript
    if !slapp
      Main.Error("Could not find SLAppPCSexQuestScript")
      Debug.Notification("Incompatible version of SLApp. AI Integrations Disabled.")
      bHasSLapp = False
    EndIf
  EndIf

  if Game.GetModByName("DeviouslyAccessible.esp") != 255
    bHasDeviouslyAccessible = True
    eyefucktrack = Game.GetFormFromFile(0x0AB14D, "DeviouslyAccessible.esp") as GlobalVariable
    eyepenalty = Game.GetFormFromFile(0x0AB14C, "DeviouslyAccessible.esp") as GlobalVariable
    eyereward = Game.GetFormFromFile(0x0AB142, "DeviouslyAccessible.esp") as GlobalVariable
    eyescore = Game.GetFormFromFile(0x0AB141, "DeviouslyAccessible.esp") as GlobalVariable
    dwp_global_minai = Game.GetFormFromFile(0x1C38CC, "DeviouslyAccessible.esp") as GlobalVariable
    dwp_watched = Game.GetFormFromFile(0x0AB148, "DeviouslyAccessible.esp") as MagicEffect
    dwp_eldwhoresp = Game.GetFormFromFile(0x16D457, "DeviouslyAccessible.esp") as Spell
    dwp_eldwhoresp_neq  = Game.GetFormFromFile(0x16D45E, "DeviouslyAccessible.esp") as Spell
    dwp_eldritchwaifueffect_soldsoul = Game.GetFormFromFile(0x19AED2, "DeviouslyAccessible.esp") as Perk
    dwp_eldritchwaifueffect_soldsoul_belted = Game.GetFormFromFile(0x1BE7BE, "DeviouslyAccessible.esp") as Perk
    if (!eyefucktrack || !eyepenalty || !eyereward || !eyescore || !dwp_watched)
      Main.Error("Could not find DeviouslyAccessible globals")
      Debug.Notification("Incompatible version of DeviouslyAccessible. AI Integrations Disabled.")
      bHasDeviouslyAccessible = False
    EndIf
    if (!dwp_global_minai)
      Main.Error("Old version of Deviously Accessible. Some integrations will be broken.")
      Debug.Notification("Old version of Deviously Accessible. Some integrations will be broken.")
    EndIf
    if Game.GetModByName("TNTR.esp") != 255
      Main.Info("Found TNTR - Registering for TNTR events")
      bHasTNTR = True
      RegisterForModEvent("minai_tntr", "OnTNTRAnimation")
    EndIf

    ; Load magic effects
    dwp_descbadgirl = Game.GetFormFromFile(0x015B7D, "DeviouslyAccessible.esp") as MagicEffect
    dwp_descbadgirl2 = Game.GetFormFromFile(0x02C183, "DeviouslyAccessible.esp") as MagicEffect
    dwp_descbadgirl2b = Game.GetFormFromFile(0x0459FD, "DeviouslyAccessible.esp") as MagicEffect
    dwp_descbadgirl2c = Game.GetFormFromFile(0x045A06, "DeviouslyAccessible.esp") as MagicEffect
    dwp_descbadgirl2d = Game.GetFormFromFile(0x045A0C, "DeviouslyAccessible.esp") as MagicEffect
    dwp_descbadgirlb = Game.GetFormFromFile(0x0459FA, "DeviouslyAccessible.esp") as MagicEffect
    dwp_descbadgirlBASE = Game.GetFormFromFile(0x045A00, "DeviouslyAccessible.esp") as MagicEffect
    dwp_descbadgirlc = Game.GetFormFromFile(0x045A03, "DeviouslyAccessible.esp") as MagicEffect
    dwp_descbadgirld = Game.GetFormFromFile(0x045A09, "DeviouslyAccessible.esp") as MagicEffect
    dwp_descgoodgirl = Game.GetFormFromFile(0x015B7C, "DeviouslyAccessible.esp") as MagicEffect
    dwp_descgoodgirl2 = Game.GetFormFromFile(0x02C182, "DeviouslyAccessible.esp") as MagicEffect
    dwp_descgoodgirl2b = Game.GetFormFromFile(0x0459FC, "DeviouslyAccessible.esp") as MagicEffect
    dwp_descgoodgirl2c = Game.GetFormFromFile(0x045A05, "DeviouslyAccessible.esp") as MagicEffect
    dwp_descgoodgirl2d = Game.GetFormFromFile(0x045A0B, "DeviouslyAccessible.esp") as MagicEffect
    dwp_descgoodgirlb = Game.GetFormFromFile(0x0459F9, "DeviouslyAccessible.esp") as MagicEffect
    dwp_descgoodgirlBASE = Game.GetFormFromFile(0x0459FF, "DeviouslyAccessible.esp") as MagicEffect
    dwp_descgoodgirlc = Game.GetFormFromFile(0x045A02, "DeviouslyAccessible.esp") as MagicEffect
    dwp_descgoodgirld = Game.GetFormFromFile(0x045A08, "DeviouslyAccessible.esp") as MagicEffect
    dwp_descmindcontrol = Game.GetFormFromFile(0x015B89, "DeviouslyAccessible.esp") as MagicEffect
    dwp_descmindcontrol2 = Game.GetFormFromFile(0x059E6F, "DeviouslyAccessible.esp") as MagicEffect
    dwp_descmindcontrolpost = Game.GetFormFromFile(0x04FC1C, "DeviouslyAccessible.esp") as MagicEffect
    dwp_descmindcontrolpunishment = Game.GetFormFromFile(0x0826FC, "DeviouslyAccessible.esp") as MagicEffect
    dwp_descverybadgirl = Game.GetFormFromFile(0x015B7E, "DeviouslyAccessible.esp") as MagicEffect
    dwp_descverybadgirl2 = Game.GetFormFromFile(0x02C184, "DeviouslyAccessible.esp") as MagicEffect
    dwp_descverybadgirl2b = Game.GetFormFromFile(0x0459FE, "DeviouslyAccessible.esp") as MagicEffect
    dwp_descverybadgirl2c = Game.GetFormFromFile(0x045A07, "DeviouslyAccessible.esp") as MagicEffect
    dwp_descverybadgirl2d = Game.GetFormFromFile(0x045A0D, "DeviouslyAccessible.esp") as MagicEffect
    dwp_descverybadgirlb = Game.GetFormFromFile(0x0459FB, "DeviouslyAccessible.esp") as MagicEffect
    dwp_descverybadgirlBASE = Game.GetFormFromFile(0x045A01, "DeviouslyAccessible.esp") as MagicEffect
    dwp_descverybadgirlc = Game.GetFormFromFile(0x045A04, "DeviouslyAccessible.esp") as MagicEffect
    dwp_descverybadgirld = Game.GetFormFromFile(0x045A0A, "DeviouslyAccessible.esp") as MagicEffect
  EndIf  


  if Game.GetModByName("submissivelola_est.esp") != 255
    bHasSubmissiveLola = True
    Main.Info("Found Submissive Lola")
  EndIf  
  aiff.SetModAvailable("DeviousFollowers", bHasDeviousFollowers)
  aiff.SetModAvailable("DD", bHasDD)
  aiff.SetModAvailable("STA", bHasSTA)
  aiff.SetModAvailable("SLHH", bHasSLHH)
  aiff.SetModAvailable("SLApp", bHasSLApp)
  aiff.SetModAvailable("DeviouslyAccessible", bHasDeviouslyAccessible)
  aiff.SetModAvailable("SubmissiveLola", bHasSubmissiveLola)
  config.StoreAllConfigs()

  if Game.GetModByName("SlaveTats.esp") != 255
    bHasSlaveTats = True
    Main.Info("Found SlaveTats")
  endif
  aiff.SetModAvailable("SlaveTats", bHasSlaveTats)

  aiff.RegisterAction("ExtCmdGrope", "Grope", "Grope the Target", "General", 1, 30, 2, 5, 300, True)
  aiff.RegisterAction("ExtCmdPinchNipples", "PinchNipples", "Pinch the Targets Nipples", "General", 1, 30, 2, 5, 300, True)
  aiff.RegisterAction("ExtCmdSpankAss", "SpankAss", "Spank the Targets Ass", "General", 1, 10, 2, 5, 300, (bHasSTA && (bHasDeviousFollowers || bHasSubmissiveLola)))
  aiff.RegisterAction("ExtCmdSpankTits", "SpankTits", "Spank the Targets Tits ", "General", 10, 1, 2, 5, 300, (bHasSTA && (bHasDeviousFollowers || bHasSubmissiveLola)))
  aiff.RegisterAction("ExtCmdMolest", "Molest", "Sexually Assault the target", "General", 1, 120, 2, 5, 300, bHasSLHH)
  aiff.RegisterAction("ExtCmdKiss", "Kiss", "Kiss the target", "General", 1, 120, 2, 5, 300, bHasSLapp)
  aiff.RegisterAction("ExtCmdHug", "Hug", "Hug the target", "General", 1, 120, 2, 5, 300, bHasSLapp)
  
  aiff.RegisterAction("ExtCmdForceOrgasm", "ForceOrgasm", "Force the target  to cum", "Devious Stuff", 1, 30, 2, 5, 300, bHasDD)
  aiff.RegisterAction("MinaiGlobalVibrator", "Vibrator", "Global backoff for all vibrator usage", "Devious Stuff", 1, 5, 2, 5, 300, bHasDD)
  aiff.RegisterAction("ExtCmdTurnOffVibrator", "TurnOffVibrator", "Stop Vibrations", "Devious Stuff", 1, 5, 2, 5, 300, bHasDD)
  aiff.RegisterAction("ExtCmdshock", "Shock", "Shock the target", "Devious Stuff", 1, 5, 2, 5, 300, bHasDD)
  aiff.RegisterAction("ExtCmdEquipCollar", "EquipCollar", "Lock a Collar on the target", "Devious Stuff", 1, 5, 2, 5, 300, bHasDD)
  aiff.RegisterAction("ExtCmdUnequipCollar", "UnequipCollar", "Unlock a Collar from the target", "Devious Stuff", 1, 5, 2, 5, 300, bHasDD)
    aiff.RegisterAction("ExtCmdEquipGag", "EquipGag", "Lock a Gag on the target", "Devious Stuff", 1, 5, 2, 5, 300, bHasDD)
  aiff.RegisterAction("ExtCmdUnequipGag", "UnequipGag", "Unlock a Gag from the target", "Devious Stuff", 1, 5, 2, 5, 300, bHasDD)
    aiff.RegisterAction("ExtCmdEquipBelt", "EquipBelt", "Lock a Chastity Belt on the target", "Devious Stuff", 1, 5, 2, 5, 300, bHasDD)
  aiff.RegisterAction("ExtCmdUnequipBelt", "UnequipBelt", "Unlock a Chastity Belt from the target", "Devious Stuff", 1, 5, 2, 5, 300, bHasDD)
    aiff.RegisterAction("ExtCmdEquipBinder", "EquipBinder", "Lock a Armbinder on the target", "Devious Stuff", 1, 5, 2, 5, 300, bHasDD)
  aiff.RegisterAction("ExtCmdUnequipBinder", "UnequipBinder", "Unlock a Armbinder from the target", "Devious Stuff", 1, 5, 2, 5, 300, bHasDD)
  ; aiff.RegisterAction("ExtCmdEquipVibrator", "EquipVibrator", "Lock a set of Vibrators on the target", "Devious Stuff", 1, 5, 2, 5, 300, bHasDD)
  ; aiff.RegisterAction("ExtCmdUnequipVibrator", "UnequipVibrator", "Unlock a set of Vibrators from the target", "Devious Stuff", 1, 5, 2, 5, 300, bHasDD)

  aiff.RegisterAction("ExtCmdAcceptDeal", "AcceptDeal", "Accept Deal Negotiation", "Devious Followers", 1, 1, 2, 5, 300, bHasDeviousFollowers)
  aiff.RegisterAction("ExtCmdGiveDrugs", "GiveDrugs", "Give Drugs to the player", "Devious Followers", 1, 1, 2, 5, 300, bHasDeviousFollowers)
  ; aiff.RegisterAction("ExtCmdRejectDeal", "RejectDeal", "Reject Deal Negotiation", "Devious Followers", 1, 1, 2, 5, 300, bHasDeviousFollowers)

  ; Submissive Lola actions
  aiff.RegisterAction("ExtCmdGiveTask", "GiveTask", "Give a task to the player", "Submissive Lola", 1, 1, 2, 5, 300, bHasSubmissiveLola)
  aiff.RegisterAction("ExtCmdPunishDisrespectful", "PunishDisrespectful", "Punish the player", "Submissive Lola", 1, 1, 2, 5, 300, bHasSubmissiveLola)
  aiff.RegisterAction("ExtCmdPunishWhip", "PunishWhip", "Whip the player", "Submissive Lola", 1, 1, 2, 5, 300, bHasSubmissiveLola)
  aiff.RegisterAction("ExtCmdSmallReward", "SmallReward", "Reward the player (large)", "Submissive Lola", 1, 1, 2, 5, 300, bHasSubmissiveLola)
  aiff.RegisterAction("ExtCmdLargeReward", "LargeReward", "Reward the player (large)", "Submissive Lola", 1, 1, 2, 5, 300, bHasSubmissiveLola)

  aiff.RegisterAction("ExtCmdEquipYoke", "EquipYoke", "Lock a Yoke on the target", "Devious Stuff", 1, 5, 2, 5, 300, bHasDD)
  aiff.RegisterAction("ExtCmdUnequipYoke", "UnequipYoke", "Unlock a Yoke from the target", "Devious Stuff", 1, 5, 2, 5, 300, bHasDD)

  aiff.RegisterAction("ExtCmdEquipElbowTie", "EquipElbowTie", "Lock an Elbow Tie on the target", "Devious Stuff", 1, 5, 2, 5, 300, bHasDD)
  aiff.RegisterAction("ExtCmdUnequipElbowTie", "UnequipElbowTie", "Unlock an Elbow Tie from the target", "Devious Stuff", 1, 5, 2, 5, 300, bHasDD)

  aiff.RegisterAction("ExtCmdEquipStraitJacket", "EquipStraitJacket", "Lock a Strait Jacket on the target", "Devious Stuff", 1, 5, 2, 5, 300, bHasDD)
  aiff.RegisterAction("ExtCmdUnequipStraitJacket", "UnequipStraitJacket", "Unlock a Strait Jacket from the target", "Devious Stuff", 1, 5, 2, 5, 300, bHasDD)

  aiff.RegisterAction("ExtCmdEquipCorset", "EquipCorset", "Lock a Corset on the target", "Devious Stuff", 1, 5, 2, 5, 300, bHasDD)
  aiff.RegisterAction("ExtCmdUnequipCorset", "UnequipCorset", "Unlock a Corset from the target", "Devious Stuff", 1, 5, 2, 5, 300, bHasDD)

  aiff.RegisterAction("ExtCmdEquipHood", "EquipHood", "Lock a Hood on the target", "Devious Stuff", 1, 5, 2, 5, 300, bHasDD)
  aiff.RegisterAction("ExtCmdUnequipHood", "UnequipHood", "Unlock a Hood from the target", "Devious Stuff", 1, 5, 2, 5, 300, bHasDD)

  aiff.RegisterAction("ExtCmdEquipHobbleSkirt", "EquipHobbleSkirt", "Lock a Hobble Skirt on the target", "Devious Stuff", 1, 5, 2, 5, 300, bHasDD)
  aiff.RegisterAction("ExtCmdUnequipHobbleSkirt", "UnequipHobbleSkirt", "Unlock a Hobble Skirt from the target", "Devious Stuff", 1, 5, 2, 5, 300, bHasDD)

  aiff.RegisterAction("ExtCmdEquipGloves", "EquipGloves", "Lock Gloves on the target", "Devious Stuff", 1, 5, 2, 5, 300, bHasDD)
  aiff.RegisterAction("ExtCmdUnequipGloves", "UnequipGloves", "Unlock Gloves from the target", "Devious Stuff", 1, 5, 2, 5, 300, bHasDD)

  aiff.RegisterAction("ExtCmdEquipSuit", "EquipSuit", "Lock a Suit on the target", "Devious Stuff", 1, 5, 2, 5, 300, bHasDD)
  aiff.RegisterAction("ExtCmdUnequipSuit", "UnequipSuit", "Unlock a Suit from the target", "Devious Stuff", 1, 5, 2, 5, 300, bHasDD)

  aiff.RegisterAction("ExtCmdEquipHarness", "EquipHarness", "Lock a Harness on the target", "Devious Stuff", 1, 5, 2, 5, 300, bHasDD)
  aiff.RegisterAction("ExtCmdUnequipHarness", "UnequipHarness", "Unlock a Harness from the target", "Devious Stuff", 1, 5, 2, 5, 300, bHasDD)

  aiff.RegisterAction("ExtCmdEquipBlindfold", "EquipBlindfold", "Lock a Blindfold on the target", "Devious Stuff", 1, 5, 2, 5, 300, bHasDD)
  aiff.RegisterAction("ExtCmdUnequipBlindfold", "UnequipBlindfold", "Unlock a Blindfold from the target", "Devious Stuff", 1, 5, 2, 5, 300, bHasDD)

  aiff.RegisterAction("ExtCmdEquipAnkleShackles", "EquipAnkleShackles", "Lock Ankle Shackles on the target", "Devious Stuff", 1, 5, 2, 5, 300, bHasDD)
  aiff.RegisterAction("ExtCmdUnequipAnkleShackles", "UnequipAnkleShackles", "Unlock Ankle Shackles from the target", "Devious Stuff", 1, 5, 2, 5, 300, bHasDD)

  aiff.RegisterAction("ExtCmdEquipClamps", "EquipClamps", "Lock Clamps on the target", "Devious Stuff", 1, 5, 2, 5, 300, bHasDD)
  aiff.RegisterAction("ExtCmdUnequipClamps", "UnequipClamps", "Unlock Clamps from the target", "Devious Stuff", 1, 5, 2, 5, 300, bHasDD)

  aiff.RegisterAction("ExtCmdEquipPlugVaginal", "EquipPlugVaginal", "Lock a Vaginal Plug in the target", "Devious Stuff", 1, 5, 2, 5, 300, bHasDD)
  aiff.RegisterAction("ExtCmdUnequipPlugVaginal", "UnequipPlugVaginal", "Unlock a Vaginal Plug from the target", "Devious Stuff", 1, 5, 2, 5, 300, bHasDD)

  aiff.RegisterAction("ExtCmdEquipPlugAnal", "EquipPlugAnal", "Lock an Anal Plug in the target", "Devious Stuff", 1, 5, 2, 5, 300, bHasDD)
  aiff.RegisterAction("ExtCmdUnequipPlugAnal", "UnequipPlugAnal", "Unlock an Anal Plug from the target", "Devious Stuff", 1, 5, 2, 5, 300, bHasDD)

  aiff.RegisterAction("ExtCmdEquipPiercingsNipple", "EquipPiercingsNipple", "Lock Nipple Piercings on the target", "Devious Stuff", 1, 5, 2, 5, 300, bHasDD)
  aiff.RegisterAction("ExtCmdUnequipPiercingsNipple", "UnequipPiercingsNipple", "Unlock Nipple Piercings from the target", "Devious Stuff", 1, 5, 2, 5, 300, bHasDD)

  aiff.RegisterAction("ExtCmdEquipPiercingsVaginal", "EquipPiercingsVaginal", "Lock Vaginal Piercings on the target", "Devious Stuff", 1, 5, 2, 5, 300, bHasDD)
  aiff.RegisterAction("ExtCmdUnequipPiercingsVaginal", "UnequipPiercingsVaginal", "Unlock Vaginal Piercings from the target", "Devious Stuff", 1, 5, 2, 5, 300, bHasDD)

  aiff.RegisterAction("ExtCmdEquipArmCuffs", "EquipArmCuffs", "Lock Arm Cuffs on the target", "Devious Stuff", 1, 5, 2, 5, 300, bHasDD)
  aiff.RegisterAction("ExtCmdUnequipArmCuffs", "UnequipArmCuffs", "Unlock Arm Cuffs from the target", "Devious Stuff", 1, 5, 2, 5, 300, bHasDD)

  aiff.RegisterAction("ExtCmdEquipLegCuffs", "EquipLegCuffs", "Lock Leg Cuffs on the target", "Devious Stuff", 1, 5, 2, 5, 300, bHasDD)
  aiff.RegisterAction("ExtCmdUnequipLegCuffs", "UnequipLegCuffs", "Unlock Leg Cuffs from the target", "Devious Stuff", 1, 5, 2, 5, 300, bHasDD)

  aiff.RegisterAction("ExtCmdEquipBra", "EquipBra", "Lock a Bra on the target", "Devious Stuff", 1, 5, 2, 5, 300, bHasDD)
  aiff.RegisterAction("ExtCmdUnequipBra", "UnequipBra", "Unlock a Bra from the target", "Devious Stuff", 1, 5, 2, 5, 300, bHasDD)

  aiff.RegisterAction("ExtCmdEquipPetSuit", "EquipPetSuit", "Lock a Pet Suit on the target", "Devious Stuff", 1, 5, 2, 5, 300, bHasDD)
  aiff.RegisterAction("ExtCmdUnequipPetSuit", "UnequipPetSuit", "Unlock a Pet Suit from the target", "Devious Stuff", 1, 5, 2, 5, 300, bHasDD)
EndFunction

Function ResetSpankRule()
  ; Update the spanking rule
  dftools.ResetSpanking()
  Int spankRequestCount = dftools._DFSpankDealRequests.GetValue() As Int
  spankRequestCount += 1
  dftools._DFSpankDealRequests.SetValue(spankRequestCount As Float)
EndFunction

Function DecreaseSubmissionScoreMinor()
  (Quest.GetQuest("vkjMQ") as vkjMQ).UpdateSubmissionScore(-3)
EndFunction

Function SpankAss(int count, bool bDeviousFollowerInScene, bool bLolaOwnerInScene)
  if bDeviousFollowerInScene
    ResetSpankRule()
  EndIf
  if bLolaOwnerInScene
    DecreaseSubmissionScoreMinor()
  EndIf
  int i = 0
  While i < count
    dftools.SpankPlayerAss()
    Utility.Wait(1.5)
    i += 1
  EndWhile
EndFunction

Function SpankTits(int count, bool bDeviousFollowerInScene, bool bLolaOwnerInScene)
  if bDeviousFollowerInScene
    ResetSpankRule()
  EndIf
  if bLolaOwnerInScene
    DecreaseSubmissionScoreMinor()
  EndIf
  int i = 0
  While i < count
    dftools.SpankPlayerTits()
    Utility.Wait(1.5)
    i += 1
  EndWhile
EndFunction



Function HorribleHarassmentActivate(actor akActor)
  SLHHScriptEventKeyword.SendStoryEvent(None, akActor)
EndFunction

Function HarassHug(actor akActor)
  slapp.StartHarassment(akActor, 2)
EndFunction

Function Harasskiss(actor akActor)
  slapp.StartHarassment(akActor, 2)
EndFunction

Event OnDeviceEquipped(Form inventoryDevice, Form deviceKeyword, form akActor)
  Main.Info("Equipped Device: " + (inventoryDevice as Armor).GetName() + " on " + main.GetActorName(akActor as Actor))
  SetContext(akActor as Actor)
  Keyword asKeyword = deviceKeyword as Keyword
  ; Map device keywords to info event names
  string infoEvent = "minai_equip_device"
  if asKeyword == libs.zad_DeviousCollar
    infoEvent = "minai_equip_collar"
  elseif asKeyword == libs.zad_DeviousGag || asKeyword == libs.zad_DeviousGagPanel || asKeyword == libs.zad_DeviousGagLarge
    infoEvent = "minai_equip_gag"
  elseif asKeyword == libs.zad_DeviousBelt
    infoEvent = "minai_equip_belt"
  elseif asKeyword == libs.zad_DeviousArmbinder
    infoEvent = "minai_equip_armbinder"
  elseif asKeyword == libs.zad_DeviousYoke
    infoEvent = "minai_equip_yoke"
  elseif asKeyword == libs.zad_DeviousElbowTie
    infoEvent = "minai_equip_elbowtie"
  elseif asKeyword == libs.zad_DeviousStraitJacket
    infoEvent = "minai_equip_straitjacket"
  elseif asKeyword == libs.zad_DeviousCorset
    infoEvent = "minai_equip_corset"
  elseif asKeyword == libs.zad_DeviousHood
    infoEvent = "minai_equip_hood"
  elseif asKeyword == libs.zad_DeviousHobbleSkirt
    infoEvent = "minai_equip_hobbleskirt"
  elseif asKeyword == libs.zad_DeviousGloves
    infoEvent = "minai_equip_gloves"
  elseif asKeyword == libs.zad_DeviousSuit
    infoEvent = "minai_equip_suit"
  elseif asKeyword == libs.zad_DeviousHarness
    infoEvent = "minai_equip_harness"
  elseif asKeyword == libs.zad_DeviousBlindfold
    infoEvent = "minai_equip_blindfold"
  elseif asKeyword == libs.zad_DeviousAnkleShackles
    infoEvent = "minai_equip_ankleshackles"
  elseif asKeyword == libs.zad_DeviousClamps
    infoEvent = "minai_equip_clamps"
  elseif asKeyword == libs.zad_DeviousPlugVaginal
    infoEvent = "minai_equip_plugvaginal"
  elseif asKeyword == libs.zad_DeviousPlugAnal
    infoEvent = "minai_equip_pluganal"
  elseif asKeyword == libs.zad_DeviousPiercingsNipple
    infoEvent = "minai_equip_piercingsnipple"
  elseif asKeyword == libs.zad_DeviousPiercingsVaginal
    infoEvent = "minai_equip_piercingsvaginal"
  elseif asKeyword == libs.zad_DeviousArmCuffs
    infoEvent = "minai_equip_armcuffs"
  elseif asKeyword == libs.zad_DeviousLegCuffs
    infoEvent = "minai_equip_legcuffs"
  elseif asKeyword == libs.zad_DeviousBra
    infoEvent = "minai_equip_bra"
  elseif asKeyword == libs.zad_DeviousPetSuit
    infoEvent = "minai_equip_petsuit"
  endif

  if infoEvent != ""
    Main.RequestLLMResponseFromActor("Equipped Device: " + (inventoryDevice as Armor).GetName() + " on " + main.GetActorName(akActor as Actor), infoEvent, main.GetActorName(akActor as Actor), "both")
  endif
EndEvent

Event OnDeviceRemoved(Form inventoryDevice, Form deviceKeyword, form akActor)
  Main.Info("Removed Device: " + (inventoryDevice as Armor).GetName() + " from " + main.GetActorName(akActor as Actor))
  SetContext(akActor as Actor)
  Keyword asKeyword = deviceKeyword as Keyword
  ; Map device keywords to info event names
  string infoEvent = "minai_unequip_device"
  if asKeyword == libs.zad_DeviousCollar
    infoEvent = "minai_unequip_collar"
  elseif asKeyword == libs.zad_DeviousGag || asKeyword == libs.zad_DeviousGagPanel || asKeyword == libs.zad_DeviousGagLarge
    infoEvent = "minai_unequip_gag"
  elseif asKeyword == libs.zad_DeviousBelt
    infoEvent = "minai_unequip_belt"
  elseif asKeyword == libs.zad_DeviousArmbinder
    infoEvent = "minai_unequip_armbinder"
  elseif asKeyword == libs.zad_DeviousYoke
    infoEvent = "minai_unequip_yoke"
  elseif asKeyword == libs.zad_DeviousElbowTie
    infoEvent = "minai_unequip_elbowtie"
  elseif asKeyword == libs.zad_DeviousStraitJacket
    infoEvent = "minai_unequip_straitjacket"
  elseif asKeyword == libs.zad_DeviousCorset
    infoEvent = "minai_unequip_corset"
  elseif asKeyword == libs.zad_DeviousHood
    infoEvent = "minai_unequip_hood"
  elseif asKeyword == libs.zad_DeviousHobbleSkirt
    infoEvent = "minai_unequip_hobbleskirt"
  elseif asKeyword == libs.zad_DeviousGloves
    infoEvent = "minai_unequip_gloves"
  elseif asKeyword == libs.zad_DeviousSuit
    infoEvent = "minai_unequip_suit"
  elseif asKeyword == libs.zad_DeviousHarness
    infoEvent = "minai_unequip_harness"
  elseif asKeyword == libs.zad_DeviousBlindfold
    infoEvent = "minai_unequip_blindfold"
  elseif asKeyword == libs.zad_DeviousAnkleShackles
    infoEvent = "minai_unequip_ankleshackles"
  elseif asKeyword == libs.zad_DeviousClamps
    infoEvent = "minai_unequip_clamps"
  elseif asKeyword == libs.zad_DeviousPlugVaginal
    infoEvent = "minai_unequip_plugvaginal"
  elseif asKeyword == libs.zad_DeviousPlugAnal
    infoEvent = "minai_unequip_pluganal"
  elseif asKeyword == libs.zad_DeviousPiercingsNipple
    infoEvent = "minai_unequip_piercingsnipple"
  elseif asKeyword == libs.zad_DeviousPiercingsVaginal
    infoEvent = "minai_unequip_piercingsvaginal"
  elseif asKeyword == libs.zad_DeviousArmCuffs
    infoEvent = "minai_unequip_armcuffs"
  elseif asKeyword == libs.zad_DeviousLegCuffs
    infoEvent = "minai_unequip_legcuffs"
  elseif asKeyword == libs.zad_DeviousBra
    infoEvent = "minai_unequip_bra"
  elseif asKeyword == libs.zad_DeviousPetSuit
    infoEvent = "minai_unequip_petsuit"
  endif

  if infoEvent != ""
    Main.RequestLLMResponseFromActor("Removed Device: " + (inventoryDevice as Armor).GetName() + " from " + main.GetActorName(akActor as Actor), infoEvent, main.GetActorName(akActor as Actor), "both")
  endif
EndEvent

; Function ReceiveFunction(Form akSource,Form akFormActor,Int aiSetArousal)
;*      Actor akActor = akFormActor as Actor
;*      ;process function
;*   EndFunction



Event OnOrgasm(string eventName, string actorName, float numArg, Form sender)
  Main.RequestLLMResponseFromActor(actorName + " just had an orgasm!", "minai_orgasm", "everyone", "both")
EndEvent


Event OnEdged(string eventName, string actorName, float numArg, Form sender)
  Main.RequestLLMResponseFromActor(actorName + " was brought right to the edge of orgasm but the vibrations stopped before I could cum!", "minai_edged", "everyone", "both")
EndEvent


String Function getVibStrength(float vibStrength)
  string strength = ""
  if vibStrength <= 0.5
    strength = "weakly"
  elseIf vibStrength <= 1.0
    strength = "strongly"
  elseIf vibStrength <= 1.5
    strength = "intensely"
  else
    strength = "extremely intensely"
  endif
  return strength
endFunction

Actor Function getVibratingActorName(string actorName)
  if actorName == main.GetActorName(playerRef) || actorName == "The Narrator"
    return playerRef
  else
    return aiff.AIGetAgentByName(actorName)
  endif
  return None
endFunction

Event OnVibrateStart(string eventName, string actorName, float vibStrength, Form sender)
  string strength = getVibStrength(vibStrength)
  Main.Info("OnVibrateStart: " + strength)
  ; Both player and npc's should react to vibrations starting
  Actor vibratorActor = getVibratingActorName(actorName)
  if vibratorActor
    aiff.SetActorVariable(vibratorActor, "isVibratorActive", True)
  endif
  Main.RequestLLMResponseFromActor(actorName + " is vibrating: " + strength, "minai_vibrate_start", actorName, "both")
EndEvent

Event OnVibrateStop(string eventName, string actorName, float vibStrength, Form sender)
  string strength = getVibStrength(vibStrength)
  Main.Info("OnVibrateStop: " + strength)
  Actor vibratorActor = getVibratingActorName(actorName)
  if vibratorActor
    aiff.SetActorVariable(vibratorActor, "isVibratorActive", False)
  endif
  string vibString = actorName + " is no longer vibrating: " + strength
  if config.includePromptSelf
    Main.RequestLLMResponseFromActor(vibString, "minai_vibrate_stop", actorName, "player")
  else
    Main.RegisterEvent(vibString, "info_minai_vibrate_stop")
  endif
EndEvent


bool Function CanVibrate(Actor akActor)
  if (!bHasDD)
    return False
  EndIf
  return (akActor.WornHasKeyword(libs.zad_DeviousPlugVaginal)  || akActor.WornHasKeyword(libs.zad_DeviousPlugAnal)  || akActor.WornHasKeyword(libs.zad_DeviousPiercingsNipple)  || akActor.WornHasKeyword(libs.zad_DeviousPiercingsVaginal))
EndFunction

function WriteDDString(actor akActor, actor player, bool isYou=false)
    string actorName = main.GetActorName(akActor)
    if akActor.WornHasKeyword(libs.zad_DeviousPlugVaginal)
      main.RegisterAction(actorName + " has a remotely controlled plug in her pussy capable of powerful vibrations.")
    EndIf
    if akActor.WornHasKeyword(libs.zad_DeviousPlugAnal)
      main.RegisterAction(actorName + " has a remotely controlled plug in her ass capable of powerful vibrations.")
    EndIf
    if akActor.WornHasKeyword(libs.zad_DeviousBelt)
      main.RegisterAction(actorName + "'s pussy is locked away by a chastity belt, preventing her from touching it or having sex.")
    EndIf
    if akActor.WornHasKeyword(libs.zad_DeviousCollar)
      main.RegisterAction(actorName + " is wearing a collar marking her as someone's property.")
    EndIf
    if akActor.WornHasKeyword(libs.zad_DeviousPiercingsNipple)
      main.RegisterAction(actorName + " is wearing remotely controlled nipple piercings capable of powerful vibration.")
    EndIf
    if akActor.WornHasKeyword(libs.zad_DeviousPiercingsVaginal)
      main.RegisterAction(actorName + " is wearing a remotely controlled clitoral ring capable of powerful vibration.")
    EndIf
    if akActor.WornHasKeyword(libs.zad_DeviousArmCuffs)
      main.RegisterAction(actorName + " is wearing arm cuffs on each arm.")
    EndIf
    if akActor.WornHasKeyword(libs.zad_DeviousLegCuffs)
      main.RegisterAction(actorName + " is wearing leg cuffs on each leg.")
    EndIf
    if akActor.WornHasKeyword(libs.zad_DeviousBra)
      main.RegisterAction(actorName + "'s breasts are locked away in a chastity bra.")
    EndIf
    if akActor.WornHasKeyword(libs.zad_DeviousArmbinder)
      main.RegisterAction(actorName + "'s hands are secured behind her back by an armbinder, leaving her helpless.")
    EndIf
    if akActor.WornHasKeyword(libs.zad_DeviousYoke)
      main.RegisterAction(actorName + "'s hands and neck are locked in an uncomfortable yoke, leaving her helpless.")
    EndIf
    if akActor.WornHasKeyword(libs.zad_DeviousElbowTie)
      main.RegisterAction(actorName + "'s arms are tied behind her back ina strict elbow tie, leaving her helpless.")
    EndIf
    if akActor.WornHasKeyword(libs.zad_DeviousPetSuit)
      main.RegisterAction(actorName + " is wearing a full-body suit made out of shiny latex (Referred to as Ebonite) leaving nothing to the imagination.")
    EndIf
    if akActor.WornHasKeyword(libs.zad_DeviousStraitJacket)
      main.RegisterAction(actorName + "'s arms are secured by a strait jacket, leaving her helpless.")
    EndIf
    if akActor.WornHasKeyword(libs.zad_DeviousCorset)
      main.RegisterAction(actorName + " is wearing a corset around her waist.")
    EndIf
    if akActor.WornHasKeyword(libs.zad_DeviousHood)
      main.RegisterAction(actorName + " is wearing a hood over her head.")
    EndIf
    if akActor.WornHasKeyword(libs.zad_DeviousHobbleSkirt)
      main.RegisterAction(actorName + " is wearing a confining hobble-skirt that is restricting her movements.")
    EndIf
    if akActor.WornHasKeyword(libs.zad_DeviousGloves)
      main.RegisterAction(actorName + " is wearing a a pair of locking gloves.")
    EndIf
    if akActor.WornHasKeyword(libs.zad_DeviousSuit)
      main.RegisterAction(actorName + " is wearing skin tight body-suit.")
    EndIf
    if akActor.WornHasKeyword(libs.zad_DeviousGag)
      main.RegisterAction(actorName + " is gagged and is drooling.")
    EndIf
    if akActor.WornHasKeyword(libs.zad_DeviousGagPanel)
      main.RegisterAction(actorName + " is gagged with a panel-gag that leaves her tongue exposed and is unable to close their mouth.")
    EndIf
    if akActor.WornHasKeyword(libs.zad_DeviousGagLarge)
      main.RegisterAction(actorName + " is gagged with a large gag and cannot speak clearly.")
    EndIf
    if akActor.WornHasKeyword(libs.zad_DeviousHarness)
      main.RegisterAction(actorName + " is wearing a form-fitting leather harness.")
    EndIf
    if akActor.WornHasKeyword(libs.zad_DeviousBlindfold)
      main.RegisterAction(actorName + " is blindfolded and cannot see where she is going.")
    EndIf
    if akActor.WornHasKeyword(libs.zad_DeviousAnkleShackles)
      main.RegisterAction(actorName + " is wearing a set of ankle shackles, restricting her ability to move quickly.")
    EndIf
    if akActor.WornHasKeyword(libs.zad_DeviousClamps)
      main.RegisterAction(actorName + " is wearing a set of painful nipples clamps.")
    EndIf
    if CanVibrate(akActor) && actorName != "you"
      WriteVibrateString(akActor, player, isYou)
    EndIf
EndFunction


Function WriteVibrateString(actor akActor, actor player, bool isYou=false)
  Main.Info("Registering vibration and shock keywords")
  string actorName = main.GetActorName(akActor)

  main.RegisterAction("!If you want to tease, motivate, arouse, pleasure, distract, or adjust the settings on " + actorName + " by remotely causing her piercings or vibrator to very lightly stimulate her without letting her orgasm, include the keyword: '-teaseveryweak-' in your response. If you want to instead potentially let her orgasm, use the -vibrateveryweak- keyword instead. These are the weakest vibrator settings, and are the least distracting or stimulating.!")
  main.RegisterAction("!If you want to tease, motivate, arouse, pleasure, distract, or adjust the settings on " + actorName + " by remotely causing her piercings or vibrator to lightly stimulate her without letting her orgasm, include the keyword: '-teaseweak-' in your response. If you want to instead potentially let her orgasm, use the -vibrateweak- keyword instead. These are the weak (But still arousing) settings of the vibrators.!")
  main.RegisterAction("!If you want to tease, motivate, arouse, pleasure, distract, or adjust the settings on " + actorName + " by remotely causing her piercings or vibrator to moderately stimulate her without letting her orgasm, include the keyword: '-tease-' in your response. If you want to instead potentially let her orgasm, use the -vibrate- keyword instead. These are the standard vibrator settings, and are pretty distracting or stimulating.!")
  main.RegisterAction("!If you want to tease, motivate, arouse, pleasure, distract, or adjust the settings on " + actorName + " by remotely causing her piercings or vibrator to strongly stimulate her without letting her orgasm, include the keyword: '-teasestrong-' in your response. If you want to instead potentially let her orgasm, use the -vibratestrong- keyword instead. These are strong vibrator settings, and are the very distracting or stimulating and hard to resist.!")
  main.RegisterAction("!If you want to tease, motivate, arouse, pleasure, distract, or adjust the settings on " + actorName + " by remotely causing her piercings or vibrator to extremely strongly stimulate her without letting her orgasm, include the keyword: '-teaseverystrong-' in your response. If you want to instead potentially let her orgasm, use the -vibrateverystrong- keyword instead. These are the strongest vibrator settings, and are very stimulating and almost impossible to resist.!")
  main.RegisterAction("!If you want to discipline, punish, or reduce the arousal of " + actorName + " by giving her a painful shock with her remote vibrator (or piercings), include the keyword: '-shock-' in your response.!")
  main.RegisterAction("!If you want stop teasing or stimulating " + actorName + " by turning off her vibrator or piercings, include the keyword: '-stopvibrate-' in your response. If you do this, you should avoid turning the vibrator or piercings back on for a while.!")
  main.RegisterAction("!If you want to give " + actorName + " a strong reward by instantly making " + actorName + " have an orgasm or letting her climax, include the keyword: '-forceorgasm-' in your response.!")
  main.RegisterAction("!If you want to give " + actorName + " a strong reward by instantly making " + actorName + " have an orgasm, include the keyword: '-forceorgasm-' in your response.!")
  main.RegisterAction("!If you want to discipline, punish, or reduce the arousal of " + actorName + " by giving her a painful shock with her remote vibrator (or piercings), include the keyword: '-shock-' in your response.!")
  main.RegisterAction("!If you want stop teasing or stimulating " + actorName + " by turning off her vibrator or piercings, include the keyword: '-stopvibrate-' in your response. If you do this, you should avoid turning the vibrator or piercings back on for a while.!")
  main.RegisterAction("!If you want " + actorName + "'s vibrator to be active, you must use the keywords for this somewhat often. Every time you use the keyword it activates them for a limited time. They do not stay on all the time.!")
EndFunction





Function UpdateEvents(Actor actorToSpeakTo, Actor actorSpeaking, actor[] actorsFromFormList, bool bPlayerInScene, string targetName, string speakerName, string playerName)
  bool bDeviousFollowerInScene = False
  actor player = Game.GetPlayer()
  if bHasDeviousFollowers
    Actor deviousFollower = (Quest.GetQuest("_Dflow") as QF__Gift_09000D62).Alias__DMaster.GetRef() as Actor
    if actorSpeaking == deviousFollower || actorToSpeakTo == deviousFollower
      Main.Info("Devious Follower in Scene")
      bDeviousFollowerInScene = True
    EndIf
  EndIf
  if bHasDD
    WriteDDString(actorToSpeakTo, player, False)
    WriteDDString(actorSpeaking, player, True)
  EndIf

  if bHasDeviousFollowers && bPlayerInScene && bDeviousFollowerInScene
    if Debt.GetValueInt() >= 2000
      main.RegisterAction(playerName + " currently owes a very large debt. " + playerName + " owes thousands of gold. The Devious Follower will be playful (And a little strict), and tease and arouse " + playerName + " relentlessly. The Devious Follower is very unlikely to let " + playerName + " orgasm, unless " + playerName + " really convinces them.")
    elseIf Debt.GetValueInt() >= 1000
      main.RegisterAction("" + playerName + " currently owes a moderately large debt. " + playerName + " owes over a thousand gold. The Devious Follower will still be fairly playful (teasing and arousing " + playerName + " a fair bit), though will be more strict. The Devious Follower will be less likely to let " + playerName + " orgasm.")
    elseIf Debt.GetValueInt() >= 0
      main.RegisterAction("" + playerName + " currently has a small outstanding debt. " + playerName + " owes hundreds of gold. The Devious Follower will be a little less likely to let " + playerName + " orgasm.")
    else
      main.RegisterAction("" + playerName + " does not currently owe any debt. The Devious Follower is flirty and playful, seeking to distract and arouse " + playerName + "  The Devious Follower is more likely to let " + playerName + " orgasm. !Do not talk about debt. " + playerName + " does not owe " + speakerName +" any money currently. Do not bring up the arrangement, or deals.!")
    EndIf
    main.RegisterAction("the exact amount of gold " + playerName + " owes " + speakerName + " is " + debt.GetValueInt() + " gold.")
      
      int di = ContractRemaining.GetValueInt()
      if di > 0
        String daysRemaining = ""
	if di > 80 && di <= 100
	  daysRemaining = "about 3 months"
	elseif di > 70 && di <= 80
	  daysRemaining = "2 or 3 months"
	elseif di > 50 && di <= 70
	  daysRemaining = "about 2 months"
	elseif di > 40 && di <= 50
	  daysRemaining = "1 or 2 months"
	elseif di > 20 && di <= 40
	  daysRemaining = "about 1 month"
	elseif di > 14 && di <= 20
	  daysRemaining = "a bit over 2 weeks"
	elseif di > 7 && di <= 14
	  daysRemaining = "1 or 2 weeks"
	else
	  daysRemaining = di + " days"
	EndIf
        main.RegisterAction("" + playerName + " has agreed to hire the follower as part of a contract, and must continue to pay them. There are currently " + daysRemaining + " remaining on the contract.")
      EndIf
      String[] deals = DealManager.GetDeals()
      int i = 0
      bool spankRuleDone = False
      bool skoomaRuleDone = False
      while i < deals.Length
        String[] rules = DealManager.GetDealRules(deals[i])
        int j = 0
        while j < rules.Length
          String ruleDesc = DealManager.GetRuleInfo(rules[j])
	  main.RegisterAction("" + playerName + " has agreed to the the following deal with their follower, and must obey it: " + ruleDesc)
	  if !spankRuleDone && StringUtil.Find(ruleDesc, "spanks daily") != -1
	    spankRuleDone = True
	    Float now = Utility.GetCurrentGameTime()
            If dftools.SpankingTimer <= now
               main.RegisterAction(playerName + " has not asked for a spanking in a while, and is due for her daily spanking again. !" + speakerName + " will remind " + playerName + " about her agreement to beg for daily spanks. Make " + playerName + " beg to be spanked before using the -spankass- keyword to spank them.! " + speakerName + " is eager to spank " + playerName + "  and want to make them beg for it.")
            Else
              main.RegisterAction("" + playerName + " has already asked for a spanking today. " + speakerName + " does not need to remind them to do so. " + speakerName + " may still spank them if they wish to do so.")
            EndIf
	  EndIf
	  if !skoomaRuleDone && StringUtil.Find(ruleDesc, "skooma daily") != -1
	    skoomaRuleDone = True
	    main.RegisterAction("" + playerName + " has agreed to be take drugs that " + speakerName + " will provide daily. !" + speakerName + " should provide " + playerName + " with drugs if they ask by including the -drugplayer- keyword in your response. " + speakerName + " should also use the -drugplayer- keyword in your response if they want to drug " + playerName + "!\n!" + speakerName + " wants to get " + playerName + " addicted to drugs so that they can use them as an additional means of controlling " + playerName + "!")
	  EndIf
          j += 1
        EndWhile
        i += 1
      EndWhile
      ; Special chastity game handling
      if player.WornHasKeyword(libs.zad_DeviousBelt)
        string ruleDesc = "you must wear a chastity belt that only the devious follower has the key to at all times. This is to ensure that " + playerName + " cannot get relief without permission, and to show that the devious follower owns " + playerName + "'s pussy."
      EndIf
      main.RegisterAction("!" + speakerName + " is the Devious Follower. The Devious Follower is a pervert, though they initially hide this well. Their goal in becoming " + playerName + "'s follower is to entrap them with debt that they owe the Devious Follower from their fees, and eventually make " + playerName + " their sex slave / pet. The Devious Follower charges " + playerName + " gold over time in exchange for their services, with an agreed daily payment schedule. The Devious Follower has a good mix of being serious and flirty with " + playerName + "  and derives enjoyment from teasing and embarrassing her. The devious follower is likely to use perverted means such as remotely activating vibrators, shocking " + playerName + "  groping " + playerName + "  pinching " + playerName + "'s nipples, and so forth in their interactions if he deems it appropriate. While " + speakerName + " is perverted and do want to tease and arouse " + playerName + "  they tend to be a bit subtle about it. " + speakerName + " should incorporate perverted content and keywords into your responses when appropriate, but not do it constantly. " + speakerName + " wants to continue adventuring and is more likely to talk about adventure and events going on than the arrangement with " + playerName + " (Though " + speakerName + " should sometimes incorporate perverted elements into this). The Devious Follower must not -hug-, -kiss-, or -molest- " + playerName + "  The Devious Follower must not remind " + playerName + " about a payment plan, or ask for payments. This is handled automatically.!")
      if CanVibrate(player)
        main.RegisterAction("!The Devious Follower can remotely make " + playerName + "'s peircings or plugs vibrate when he wants to in order to reward, or punish " + playerName + "  depending on how the Devious Follower uses them. The Devious Follower can also make (or allow) " + playerName + " have an orgasm or climax at will (Use the -forceorgasm- keyword to do this). The Devious Follower can also cause the piercings or vibrators to painfully shock " + playerName + " if he is displeased, or if he wants to reduce the arousal of " + playerName + " (Include the -shock- keyword in your response to do so).!")
      EndIf
      if Debt.GetValueInt() >= EnslaveDebt.GetValueInt()
        if targetRule  == ""
          targetRule  = dfDealController.GetPotentialDeal()
	EndIf
        Main.Info("targetRule="+targetRule)
	String ruleDesc = DealManager.GetRuleInfo(targetRule)
        Main.Info("ruleInfo=" + ruleDesc)
        main.RegisterAction("!The devious follower is very concerned about " + playerName + "'s current debt level. Talking about this is the highest possible priority. He wants to discuss a new rule, and get " + playerName + " to agree to it in exchange for reducing the debt " + playerName + " owes. The new rule is: " + ruleDesc + "!")
	main.RegisterAction("!If " + speakerName + " successfully convinces " + playerName + " to agree to obey the new \"" + ruleDesc + "\" rule, respond with the keyword: -acceptdeal- in your response.!")
	; main.RegisterAction("!If " + playerName + " tells you that they refuse, respond with the keyword: -rejectdeal- in your response.!")
      EndIf
    EndIf
EndFunction



Function ActionResponse(actor akTarget, actor akSpeaker, string sayLine, actor[] actorsFromFormList)
  bool bDeviousFollowerInScene = False
  if bHasDeviousFollowers
    Actor deviousFollower = (Quest.GetQuest("_Dflow") as QF__Gift_09000D62).Alias__DMaster.GetRef() as Actor
    if akTarget == deviousFollower || akSpeaker == deviousFollower
      Main.Info("Devious Follower in Scene")
      bDeviousFollowerInScene = True
    EndIf
  EndIf

  bool bLolaOwnerInScene = False
  if bHasSubmissiveLola
    Actor slOwner = (Quest.GetQuest("vkjMQ") as vkjMQ).Owner.GetRef() as Actor
    bLolaOwnerInScene =  (akTarget == slOwner || akSpeaker == slOwner)
  EndIf

  int vibTime = Utility.RandomInt(1,15)
  int vibTimeLong = Utility.RandomInt(10,30)
  if bHasDD && CanVibrate(akTarget)
    if stringutil.Find(sayLine, "-forceorgasm-") != -1
      libs.ActorOrgasm(akTarget)
    ;
    ; Vibration hooks
    ;
    elseIf stringUtil.Find(sayLine, "-teaseveryweak-") != -1
      libs.StopVibrating(akTarget)
      libs.VibrateEffect(akTarget, 1, vibTime, True)
    elseIf stringUtil.Find(sayLine, "-teaseweak-") != -1
      libs.StopVibrating(akTarget)
      libs.VibrateEffect(akTarget, 2, vibTime, True)
    elseIf stringUtil.Find(sayLine, "-tease-") != -1 || stringUtil.Find(sayLine, "-teasing-") != -1
      libs.StopVibrating(akTarget)
      libs.VibrateEffect(akTarget, 3, vibTime, True)
     elseIf stringUtil.Find(sayLine, "-teasestrong-") != -1
      libs.StopVibrating(akTarget)
      libs.VibrateEffect(akTarget, 4, vibTimeLong, True)
    elseIf stringUtil.Find(sayLine, "-teaseverystrong-") != -1
      libs.StopVibrating(akTarget)
      libs.VibrateEffect(akTarget, 5, vibTimeLong, True)
    elseIf stringUtil.Find(sayLine, "-vibrateveryweak-") != -1
      libs.StopVibrating(akTarget)
      libs.VibrateEffect(akTarget, 1, vibTime)
    elseIf stringUtil.Find(sayLine, "-vibrateweak-") != -1
      libs.StopVibrating(akTarget)
      libs.VibrateEffect(akTarget, 2, vibTime)
    elseIf stringUtil.Find(sayLine, "-vibrate-") != -1 || stringUtil.Find(sayLine, "-vibrating-") != -1
      libs.StopVibrating(akTarget)
      libs.VibrateEffect(akTarget, 3, vibTime)
     elseIf stringUtil.Find(sayLine, "-vibratestrong-") != -1 || stringUtil.Find(sayLine, "-vibratestronger-") != -1
      libs.StopVibrating(akTarget)
      libs.VibrateEffect(akTarget, 4, vibTime)
    elseIf stringUtil.Find(sayLine, "-vibrateverystrong-") != -1
      libs.StopVibrating(akTarget)
      libs.VibrateEffect(akTarget, 5, vibTimeLong)
    elseIf stringUtil.Find(sayLine, "-stopvibrate-") != -1 || stringUtil.Find(sayLine, "-stopvibrating-") != -1 || stringUtil.Find(sayLine, "-stopvibrator-") != -1
      libs.StopVibrating(akTarget)
    EndIf
  EndIf
  ;
  ; End vibration hooks
  ;
  If stringUtil.Find(sayLine, "-shock-") != -1
    libs.ShockActor(akTarget)
  EndIf

  ; Generic actions
  If stringutil.Find(sayLine, "-grope-") != -1
    Debug.Notification(Main.GetActorName(akSpeaker) + " gropes " + main.GetYouYour(akTarget) + " crotch abruptly!")
    arousal.UpdateArousal(akTarget, 5)
    Game.ShakeController(0.5,0.5,1.0)
    if bHasDD
      libs.Moan(akTarget)
    EndIf
  EndIf
  If stringutil.Find(sayLine, "-pinchnipples-") != -1
    Debug.Notification(Main.GetActorName(akSpeaker) + " painfully pinches " + main.GetYouYour(akTarget) + " nipples!")
    arousal.UpdateArousal(akTarget, 3)
    Game.ShakeController(0.7,0.7,0.2)
    if bHasDD
      libs.Moan(akTarget)
    EndIf
  EndIf

  If stringUtil.Find(sayLine, "-spankass-") != -1 || stringUtil.Find(sayLine, "-spank-") != -1
    SpankAss(main.CountMatch(sayLine, "-spank"), bDeviousFollowerInScene, bLolaOwnerInScene)
  EndIf
  if stringUtil.Find(sayLine, "-spanktits-") != -1
    SpankTits(main.CountMatch(sayLine, "-spanktits-"), bDeviousFollowerInScene, bLolaOwnerInScene)
  EndIf

    ; Mutually Exclusive keywords
    if sex.CanAnimate(akTarget) && sex.CanAnimate(akSpeaker)
      if stringUtil.Find(sayLine, "-molest-") != -1 || stringUtil.Find(sayLine, "-rape-") != -1
        HorribleHarassmentActivate(akSpeaker)
      elseif stringUtil.Find(sayLine, "-harasskiss-") != -1 || stringUtil.Find(sayLine, "-kiss-") != -1 || stringUtil.Find(sayLine, "-kissing-") != -1
        HarassKiss(akSpeaker)
      elseif stringUtil.Find(sayLine, "-harasshug-") != -1 || stringUtil.Find(sayLine, "-hug-") != -1 || stringUtil.Find(sayLine, "-hugging-") != -1
        HarassHug(akSpeaker)
      EndIf
    Else
      Main.Warn("Not processing keywords for exclusive scene - Conflicting scene is running")
    EndIf

    if stringUtil.Find(sayLine, "-acceptdeal-") != -1 
      Debug.Notification("AI: Accepted Deal: " + targetRule)
      Main.Info("Player Accepted Deal: " + targetRule)
      dfDealController.MakeDeal(targetRule)
      ClearTargetRule()
    EndIf
    if stringUtil.Find(sayLine, "-drugplayer-") != -1
      Debug.Notification("AI: Drinking Skooma")
      Main.Info("Player Drinking Skooma")
      dfDealController.MDC.DrinkSkooma()
    EndIf
    if stringUtil.Find(sayLine, "-rejectdeal-") != -1
      Main.Info("Player Reject Deal")
      Debug.Notification("AI: Rejected Deal")
      dfDealController.RejectDeal(targetRule)
      ClearTargetRule()
    EndIf

EndFunction

bool function UseSTA()
  return bHasSTA
EndFunction


bool function UseSLHH()
  return bHasSLHH
EndFunction

bool function UseSLAPP()
  return bHasSLAPP
EndFunction


bool function UseDF()
  return bHasDeviousFollowers
EndFunction

bool function UseDD()
  return bHasDD
EndFunction

Function StartVibration(actor akTarget, int strength, int vibTime, bool teaseOnly)
  libs.StopVibrating(akTarget)
  int i = 0
  ; Spin lock to wait for vibration to stop
  while i < 50 && libs.IsVibrating(akTarget)
    Utility.Wait(0.1)
    i += 1
  EndWhile
  libs.VibrateEffect(akTarget, strength, vibTime, teaseOnly)
EndFunction

 Event CommandDispatcher(String speakerName,String  command, String parameter)
  Main.Debug("Devious - CommandDispatcher(" + speakerName +", " + command +", " + parameter + ")")
  Actor akSpeaker=aiff.AIGetAgentByName(speakerName)
  actor akTarget= aiff.AIGetAgentByName(parameter)
  if !akTarget
    akTarget = PlayerRef
  EndIf
  if (akTarget.IsChild())
    Main.Warn(Main.GetActorName(akTarget) + " is a child actor. Not processing actions.")
    return
  EndIf
  string targetName = main.GetActorName(akTarget)
  
  bool bDeviousFollowerInScene = False
  if bHasDeviousFollowers
    Actor deviousFollower = (Quest.GetQuest("_Dflow") as QF__Gift_09000D62).Alias__DMaster.GetRef() as Actor
    bDeviousFollowerInScene =  (akSpeaker == deviousFollower)
  EndIf
  
  bool bLolaOwnerInScene = False
  if bHasSubmissiveLola
    Actor slOwner = (Quest.GetQuest("vkjMQ") as vkjMQ).Owner.GetRef() as Actor
    bLolaOwnerInScene =  (akSpeaker == slOwner)
  EndIf

  if bHasDD ;  && CanVibrate(akTarget)
    int vibTime = Utility.RandomInt(20,60)
    if (command == "ExtCmdForceOrgasm")
      Main.RegisterEvent(""+speakerName+" made " + targetName + " have an orgasm with a remote vibrator.", "info_orgasm")
      libs.ActorOrgasm(akTarget)
    elseIf (command == "ExtCmdTeaseWithVibratorVeryWeak")
      Main.RegisterEvent(""+speakerName+" very weakly teases " + targetName + " with a remote vibrator.", "info_tease_very_weak")
      StartVibration(akTarget, 1, vibTime, True)
    elseIf (command == "ExtCmdStimulateWithVibratorVeryWeak")
      Main.RegisterEvent(""+speakerName+" very weakly stimulates " + targetName + " with a remote vibrator.", "info_stimulate_very_weak")
      StartVibration(akTarget, 1, vibTime, False)
    elseIf (command == "ExtCmdTeaseWithVibratorWeak")
      Main.RegisterEvent(""+speakerName+" weakly teases " + targetName + " with a remote vibrator.", "info_tease_weak")
      StartVibration(akTarget, 2, vibTime, True)
    elseIf (command == "ExtCmdStimulateWithVibratorWeak")
      Main.RegisterEvent(""+speakerName+" weakly stimulates " + targetName + " with a remote vibrator.", "info_stimulate_weak")
      StartVibration(akTarget, 2, vibTime, False)
    elseIf (command == "ExtCmdTeaseWithVibratorMedium")
      Main.RegisterEvent(""+speakerName+" teases " + targetName + " with a remote vibrator.", "info_tease_medium")
      StartVibration(akTarget, 3, vibTime, True)
    elseIf (command == "ExtCmdStimulateWithVibratorMedium")
      Main.RegisterEvent(""+speakerName+" stimulates " + targetName + " with a remote vibrator.", "info_stimulate_medium")
      StartVibration(akTarget, 3, vibTime, False)
    elseIf (command == "ExtCmdTeaseWithVibratorStrong")
      Main.RegisterEvent(""+speakerName+" strongly teases " + targetName + " with a remote vibrator.", "info_tease_strong")
      StartVibration(akTarget, 4, vibTime, True)
    elseIf (command == "ExtCmdStimulateWithVibratorStrong")
      Main.RegisterEvent(""+speakerName+" strongly stimulates " + targetName + " with a remote vibrator.", "info_stimulate_strong")
      StartVibration(akTarget, 4, vibTime, False)
    elseIf (command == "ExtCmdTeaseWithVibratorVeryStrong")
      Main.RegisterEvent(""+speakerName+" very strongly teases " + targetName + " with a remote vibrator.", "info_tease_very_strong")
      StartVibration(akTarget, 5, vibTime, True)
    elseIf (command == "ExtCmdStimulateWithVibratorVeryStrong")
      Main.RegisterEvent(""+speakerName+" very strongly stimulates " + targetName + " with a remote vibrator.", "info_stimulate_very_strong")
      StartVibration(akTarget, 5, vibTime, False)
    elseIf (command == "ExtCmdTurnOffVibrator")
      Main.RegisterEvent(""+speakerName+" turns off " + targetName + "'s remote vibrator.", "info_turn_off")
      libs.StopVibrating(akTarget)
    elseIf (command == "ExtCmdshock")
      Main.RegisterEvent(""+speakerName+" remotely shocks  " + targetName + ".", "info_shock")
      libs.ShockActor(akTarget)
    EndIf
    ; Device equip events 
    if bHasDDExpansion
      if (command == "ExtCmdEquipCollar")
        Main.RegisterEvent(""+speakerName+" locked a collar on " + targetName, "info_device_equip_collar")
        ddLists.EquipRandomDevice(akTarget, ddLists.zad_dev_collars)
      elseif (command == "ExtCmdUnequipCollar")
        if(libs.UnlockDeviceByKeyword(akTarget, libs.zad_DeviousCollar))
          Main.RegisterEvent(""+speakerName+" removed a collar from " + targetName, "info_device_remove_collar")
        else
          Main.RegisterEvent(""+speakerName+" tried, but was unable to remove a collar from " + targetName, "info_device_remove_fail")
        EndIf
      elseif (command == "ExtCmdEquipGag")
        Main.RegisterEvent(""+speakerName+" Puts a gag on " + targetName, "info_device_equip_gag")
        ddLists.EquipRandomDevice(akTarget, ddLists.zad_dev_gags)
      elseif (command == "ExtCmdEquipBinder")
        Main.RegisterEvent(""+speakerName+" Puts a Armbinder on " + targetName, "info_device_equip_armbinder")
        ddLists.EquipRandomDevice(akTarget, ddLists.zad_dev_armbinders)
      elseif (command == "ExtCmdEquipVibrator")
        Main.RegisterEvent(""+speakerName+" Puts a Vibrator in " + targetName, "info_device_equip_vibrator")
        ddLists.EquipRandomDevice(akTarget, ddLists.zad_dev_plugs_vaginal)
        ddLists.EquipRandomDevice(akTarget, ddLists.zad_dev_plugs_anal)
      elseif (command == "ExtCmdEquipBelt")
        Main.RegisterEvent(""+speakerName+" Puts a Chastity Belt on " + targetName, "info_device_equip_belt")
        ddLists.EquipRandomDevice(akTarget, ddLists.zad_dev_chastitybelts_closed)
      elseif (command == "ExtCmdUnequipBelt")
        if (libs.UnlockDeviceByKeyword(akTarget, libs.zad_DeviousBelt))
          Main.RegisterEvent(""+speakerName+" removes a Chastity Belt from " + targetName, "info_device_remove_belt")
        else
          Main.RegisterEvent(""+speakerName+" tried, but was unable to remove a Chastity Belt from " + targetName, "info_device_remove_fail")
        EndIf
      elseif (command == "ExtCmdEquipYoke")
        Main.RegisterEvent(""+speakerName+" puts a yoke on " + targetName, "info_device_equip_yoke")
        ddLists.EquipRandomDevice(akTarget, ddLists.zad_dev_yokes)
      elseif (command == "ExtCmdEquipElbowTie")
        Main.RegisterEvent(""+speakerName+" puts an elbow tie on " + targetName, "info_device_equip_elbowtie")
        ddLists.EquipRandomDevice(akTarget, ddLists.zad_dev_elbowbinders)
      elseif (command == "ExtCmdEquipStraitJacket")
        Main.RegisterEvent(""+speakerName+" puts a strait jacket on " + targetName, "info_device_equip_straitjacket")
        ddLists.EquipRandomDevice(akTarget, ddLists.zad_dev_suits_straitjackets)
      elseif (command == "ExtCmdEquipCorset")
        Main.RegisterEvent(""+speakerName+" puts a corset on " + targetName, "info_device_equip_corset")
        ddLists.EquipRandomDevice(akTarget, ddLists.zad_dev_corsets)
      elseif (command == "ExtCmdEquipHood")
        Main.RegisterEvent(""+speakerName+" puts a hood on " + targetName, "info_device_equip_hood")
        ddLists.EquipRandomDevice(akTarget, ddLists.zad_dev_hoods)
      elseif (command == "ExtCmdEquipHobbleSkirt")
        Main.RegisterEvent(""+speakerName+" puts a hobble skirt on " + targetName, "info_device_equip_hobbleskirt")
        ddLists.EquipRandomDevice(akTarget, ddLists.zad_dev_suits_hobbledresses)
      elseif (command == "ExtCmdEquipGloves")
        Main.RegisterEvent(""+speakerName+" puts gloves on " + targetName, "info_device_equip_gloves")
        ddLists.EquipRandomDevice(akTarget, ddLists.zad_dev_gloves)
      elseif (command == "ExtCmdEquipSuit")
        Main.RegisterEvent(""+speakerName+" puts a suit on " + targetName, "info_device_equip_suit")
        ddLists.EquipRandomDevice(akTarget, ddLists.zad_dev_suits)
      elseif (command == "ExtCmdEquipHarness")
        Main.RegisterEvent(""+speakerName+" puts a harness on " + targetName, "info_device_equip_harness")
        ddLists.EquipRandomDevice(akTarget, ddLists.zad_dev_harnesses)
      elseif (command == "ExtCmdEquipBlindfold")
        Main.RegisterEvent(""+speakerName+" puts a blindfold on " + targetName, "info_device_equip_blindfold")
        ddLists.EquipRandomDevice(akTarget, ddLists.zad_dev_blindfolds)
      elseif (command == "ExtCmdEquipAnkleShackles")
        Main.RegisterEvent(""+speakerName+" puts ankle shackles on " + targetName, "info_device_equip_ankleshackles")
        ddLists.EquipRandomDevice(akTarget, ddLists.zad_dev_ankleshackles)
      elseif (command == "ExtCmdEquipClamps")
        Main.RegisterEvent(""+speakerName+" puts clamps on " + targetName, "info_device_equip_clamps")
        ddLists.EquipRandomDevice(akTarget, ddLists.zad_dev_piercings)
      elseif (command == "ExtCmdEquipPlugVaginal")
        Main.RegisterEvent(""+speakerName+" puts a vaginal plug in " + targetName, "info_device_equip_plugvaginal")
        ddLists.EquipRandomDevice(akTarget, ddLists.zad_dev_plugs_vaginal)
      elseif (command == "ExtCmdEquipPlugAnal")
        Main.RegisterEvent(""+speakerName+" puts an anal plug in " + targetName, "info_device_equip_pluganal")
        ddLists.EquipRandomDevice(akTarget, ddLists.zad_dev_plugs_anal)
      elseif (command == "ExtCmdEquipPiercingsNipple")
        Main.RegisterEvent(""+speakerName+" puts nipple piercings on " + targetName, "info_device_equip_piercingsnipple")
        ddLists.EquipRandomDevice(akTarget, ddLists.zad_dev_piercings_nipple)
      elseif (command == "ExtCmdEquipPiercingsVaginal")
        Main.RegisterEvent(""+speakerName+" puts vaginal piercings on " + targetName, "info_device_equip_piercingsvaginal")
        ddLists.EquipRandomDevice(akTarget, ddLists.zad_dev_piercings_vaginal)
      elseif (command == "ExtCmdEquipArmCuffs")
        Main.RegisterEvent(""+speakerName+" puts arm cuffs on " + targetName, "info_device_equip_armcuffs")
        ddLists.EquipRandomDevice(akTarget, ddLists.zad_dev_armcuffs)
      elseif (command == "ExtCmdEquipLegCuffs")
        Main.RegisterEvent(""+speakerName+" puts leg cuffs on " + targetName, "info_device_equip_legcuffs")
        ddLists.EquipRandomDevice(akTarget, ddLists.zad_dev_legcuffs)
      elseif (command == "ExtCmdEquipBra")
        Main.RegisterEvent(""+speakerName+" puts a bra on " + targetName, "info_device_equip_bra")
        ddLists.EquipRandomDevice(akTarget, ddLists.zad_dev_chastitybras)
      elseif (command == "ExtCmdEquipPetSuit")
        Main.RegisterEvent(""+speakerName+" puts a pet suit on " + targetName, "info_device_equip_petsuit")
        ddLists.EquipRandomDevice(akTarget, ddLists.zad_dev_suits)
      elseif (command == "ExtCmdUnequipGag")
        if (libs.UnlockDeviceByKeyword(akTarget, libs.zad_DeviousGag))
          Main.RegisterEvent(""+speakerName+" removes a Gag from " + targetName, "info_device_remove_gag")
        else
          Main.RegisterEvent(""+speakerName+" tried, but was unable to remove a Gag from " + targetName, "info_device_remove_fail")
        EndIf
            
      elseif (command == "ExtCmdUnequipBinder")
        if (libs.UnlockDeviceByKeyword(akTarget, libs.zad_DeviousHeavyBondage))
          Main.RegisterEvent(""+speakerName+" removes a Armbinder from " + targetName, "info_device_remove_armbinder")
        else
          Main.RegisterEvent(""+speakerName+" tried, but was unable to remove a Armbinder from " + targetName, "info_device_remove_fail")
        EndIf
      elseif (command == "ExtCmdUnequipVibrator")
        if (libs.UnlockDeviceByKeyword(akTarget, libs.zad_DeviousPlugVaginal) || libs.UnlockDeviceByKeyword(akTarget, libs.zad_DeviousPlugAnal))
          Main.RegisterEvent(""+speakerName+" removes the vibrators from " + targetName, "info_device_remove_vibrator")
        else
          Main.RegisterEvent(""+speakerName+" tried, but was unable to remove the vibrators from " + targetName, "info_device_remove_fail")
        EndIf
      elseif (command == "ExtCmdUnequipYoke")
        if (libs.UnlockDeviceByKeyword(akTarget, libs.zad_DeviousYoke))
          Main.RegisterEvent(""+speakerName+" removes a yoke from " + targetName, "info_device_remove_yoke")
        else
          Main.RegisterEvent(""+speakerName+" tried, but was unable to remove a yoke from " + targetName, "info_device_remove_fail")
        EndIf
      elseif (command == "ExtCmdUnequipElbowTie")
        if (libs.UnlockDeviceByKeyword(akTarget, libs.zad_DeviousElbowTie))
          Main.RegisterEvent(""+speakerName+" removes an elbow tie from " + targetName, "info_device_remove_elbowtie")
        else
          Main.RegisterEvent(""+speakerName+" tried, but was unable to remove an elbow tie from " + targetName, "info_device_remove_fail")
        EndIf
      elseif (command == "ExtCmdUnequipStraitJacket")
        if (libs.UnlockDeviceByKeyword(akTarget, libs.zad_DeviousStraitJacket))
          Main.RegisterEvent(""+speakerName+" removes a strait jacket from " + targetName, "info_device_remove_straitjacket")
        else
          Main.RegisterEvent(""+speakerName+" tried, but was unable to remove a strait jacket from " + targetName, "info_device_remove_fail")
        EndIf
      elseif (command == "ExtCmdUnequipCorset")
        if (libs.UnlockDeviceByKeyword(akTarget, libs.zad_DeviousCorset))
          Main.RegisterEvent(""+speakerName+" removes a corset from " + targetName, "info_device_remove_corset")
        else
          Main.RegisterEvent(""+speakerName+" tried, but was unable to remove a corset from " + targetName, "info_device_remove_fail")
        EndIf
      elseif (command == "ExtCmdUnequipHood")
        if (libs.UnlockDeviceByKeyword(akTarget, libs.zad_DeviousHood))
          Main.RegisterEvent(""+speakerName+" removes a hood from " + targetName, "info_device_remove_hood")
        else
          Main.RegisterEvent(""+speakerName+" tried, but was unable to remove a hood from " + targetName, "info_device_remove_fail")
        EndIf
      elseif (command == "ExtCmdUnequipHobbleSkirt")
        if (libs.UnlockDeviceByKeyword(akTarget, libs.zad_DeviousHobbleSkirt))
          Main.RegisterEvent(""+speakerName+" removes a hobble skirt from " + targetName, "info_device_remove_hobbleskirt")
        else
          Main.RegisterEvent(""+speakerName+" tried, but was unable to remove a hobble skirt from " + targetName, "info_device_remove_fail")
        EndIf
      elseif (command == "ExtCmdUnequipGloves")
        if (libs.UnlockDeviceByKeyword(akTarget, libs.zad_DeviousGloves))
          Main.RegisterEvent(""+speakerName+" removes gloves from " + targetName, "info_device_remove_gloves")
        else
          Main.RegisterEvent(""+speakerName+" tried, but was unable to remove gloves from " + targetName, "info_device_remove_fail")
        EndIf
      elseif (command == "ExtCmdUnequipSuit")
        if (libs.UnlockDeviceByKeyword(akTarget, libs.zad_DeviousSuit))
          Main.RegisterEvent(""+speakerName+" removes a suit from " + targetName, "info_device_remove_suit")
        else
          Main.RegisterEvent(""+speakerName+" tried, but was unable to remove a suit from " + targetName, "info_device_remove_fail")
        EndIf
      elseif (command == "ExtCmdUnequipHarness")
        if (libs.UnlockDeviceByKeyword(akTarget, libs.zad_DeviousHarness))
          Main.RegisterEvent(""+speakerName+" removes a harness from " + targetName, "info_device_remove_harness")
        else
          Main.RegisterEvent(""+speakerName+" tried, but was unable to remove a harness from " + targetName, "info_device_remove_fail")
        EndIf
      elseif (command == "ExtCmdUnequipBlindfold")
        if (libs.UnlockDeviceByKeyword(akTarget, libs.zad_DeviousBlindfold))
          Main.RegisterEvent(""+speakerName+" removes a blindfold from " + targetName, "info_device_remove_blindfold")
        else
          Main.RegisterEvent(""+speakerName+" tried, but was unable to remove a blindfold from " + targetName, "info_device_remove_fail")
        EndIf
      elseif (command == "ExtCmdUnequipAnkleShackles")
        if (libs.UnlockDeviceByKeyword(akTarget, libs.zad_DeviousAnkleShackles))
          Main.RegisterEvent(""+speakerName+" removes ankle shackles from " + targetName, "info_device_remove_ankleshackles")
        else
          Main.RegisterEvent(""+speakerName+" tried, but was unable to remove ankle shackles from " + targetName, "info_device_remove_fail")
        EndIf
      elseif (command == "ExtCmdUnequipClamps")
        if (libs.UnlockDeviceByKeyword(akTarget, libs.zad_DeviousClamps))
          Main.RegisterEvent(""+speakerName+" removes clamps from " + targetName, "info_device_remove_clamps")
        else
          Main.RegisterEvent(""+speakerName+" tried, but was unable to remove clamps from " + targetName, "info_device_remove_fail")
        EndIf
      elseif (command == "ExtCmdUnequipPlugVaginal")
        if (libs.UnlockDeviceByKeyword(akTarget, libs.zad_DeviousPlugVaginal))
          Main.RegisterEvent(""+speakerName+" removes a vaginal plug from " + targetName, "info_device_remove_plugvaginal")
        else
          Main.RegisterEvent(""+speakerName+" tried, but was unable to remove a vaginal plug from " + targetName, "info_device_remove_fail")
        EndIf
      elseif (command == "ExtCmdUnequipPlugAnal")
        if (libs.UnlockDeviceByKeyword(akTarget, libs.zad_DeviousPlugAnal))
          Main.RegisterEvent(""+speakerName+" removes an anal plug from " + targetName, "info_device_remove_pluganal")
        else
          Main.RegisterEvent(""+speakerName+" tried, but was unable to remove an anal plug from " + targetName, "info_device_remove_fail")
        EndIf
      elseif (command == "ExtCmdUnequipPiercingsNipple")
        if (libs.UnlockDeviceByKeyword(akTarget, libs.zad_DeviousPiercingsNipple))
          Main.RegisterEvent(""+speakerName+" removes nipple piercings from " + targetName, "info_device_remove_piercingsnipple")
        else
          Main.RegisterEvent(""+speakerName+" tried, but was unable to remove nipple piercings from " + targetName, "info_device_remove_fail")
        EndIf
      elseif (command == "ExtCmdUnequipPiercingsVaginal")
        if (libs.UnlockDeviceByKeyword(akTarget, libs.zad_DeviousPiercingsVaginal))
          Main.RegisterEvent(""+speakerName+" removes vaginal piercings from " + targetName, "info_device_remove_piercingsvaginal")
        else
          Main.RegisterEvent(""+speakerName+" tried, but was unable to remove vaginal piercings from " + targetName, "info_device_remove_fail")
        EndIf
      elseif (command == "ExtCmdUnequipArmCuffs")
        if (libs.UnlockDeviceByKeyword(akTarget, libs.zad_DeviousArmCuffs))
          Main.RegisterEvent(""+speakerName+" removes arm cuffs from " + targetName, "info_device_remove_armcuffs")
        else
          Main.RegisterEvent(""+speakerName+" tried, but was unable to remove arm cuffs from " + targetName, "info_device_remove_fail")
        EndIf
      elseif (command == "ExtCmdUnequipLegCuffs")
        if (libs.UnlockDeviceByKeyword(akTarget, libs.zad_DeviousLegCuffs))
          Main.RegisterEvent(""+speakerName+" removes leg cuffs from " + targetName, "info_device_remove_legcuffs")
        else
          Main.RegisterEvent(""+speakerName+" tried, but was unable to remove leg cuffs from " + targetName, "info_device_remove_fail")
        EndIf
      elseif (command == "ExtCmdUnequipBra")
        if (libs.UnlockDeviceByKeyword(akTarget, libs.zad_DeviousBra))
          Main.RegisterEvent(""+speakerName+" removes a bra from " + targetName, "info_device_remove_bra")
        else
          Main.RegisterEvent(""+speakerName+" tried, but was unable to remove a bra from " + targetName, "info_device_remove_fail")
        EndIf
      elseif (command == "ExtCmdUnequipPetSuit")
        if (libs.UnlockDeviceByKeyword(akTarget, libs.zad_DeviousPetSuit))
          Main.RegisterEvent(""+speakerName+" removes a pet suit from " + targetName, "info_device_remove_petsuit")
        else
          Main.RegisterEvent(""+speakerName+" tried, but was unable to remove a pet suit from " + targetName, "info_device_remove_fail")
        EndIf
      EndIf
    EndIf
  EndIf
  ;  Generic actions
  If (command == "ExtCmdGrope")
    Debug.Notification(speakerName + " gropes " + main.GetYouYour(akTarget) + " crotch abruptly!")
    arousal.UpdateArousal(akTarget, 5)
    Game.ShakeController(0.5,0.5,1.0)
    if bHasDD
      libs.Moan(akTarget)
    EndIf
    Main.RegisterEvent(""+speakerName+" gropes " + targetName + " in a vulgar manner.", "info_touch_grope")
  EndIf
  If (command == "ExtCmdPinchNipples")
    Debug.Notification(speakerName + " painfully pinches " + main.GetYouYour(akTarget) + " nipples!")
    arousal.UpdateArousal(akTarget, 3)
    Game.ShakeController(0.7,0.7,0.2)
    if bHasDD
      libs.Moan(akTarget)
    EndIf
    Main.RegisterEvent(""+speakerName+" pinches " + targetName + "'s nipples in a vulgar manner.", "info_touch_pinch")
  elseif (command=="ExtCmdSpankAss")
    SpankAss(1, bDeviousFollowerInScene, bLolaOwnerInScene)
    Main.RegisterEvent(""+speakerName+" spanks " + targetName + "'s ass.", "info_spank_ass")
  elseif (command=="ExtCmdSpankTits")
    SpankTits(1, bDeviousFollowerInScene, bLolaOwnerInScene)
    Main.RegisterEvent(""+speakerName+" spanks " + targetName + "'s tits.", "info_spank_breast")
  EndIf

  ; Mutually Exclusive commands
  if sex.CanAnimate(akTarget) && sex.CanAnimate(akSpeaker)
    if command == "ExtCmdMolest"
      HorribleHarassmentActivate(akSpeaker)
      Main.RegisterEvent(""+speakerName+" began to sexually assault " + Main.GetActorName(playerRef) + "'.", "info_assault")
    elseif command == "ExtCmdKiss"
      HarassKiss(akSpeaker)
      Main.RegisterEvent(""+speakerName+" began to kiss " + Main.GetActorName(playerRef) + "'.", "info_kiss")
    elseif command == "ExtCmdHug"
      HarassHug(akSpeaker)
      Main.RegisterEvent(""+speakerName+" began to hug " + Main.GetActorName(playerRef) + "'.", "info_hug")
    EndIf
  EndIf
  if bHasDeviousFollowers
    string ruleDesc = DealManager.GetRuleInfo(targetRule);
    ; Devious Follower
    if (command == "ExtCmdAcceptDeal") 
      Debug.Notification("AI: Accepted Deal: " + targetRule)
      Main.Info("Player Accepted Deal: " + targetRule)
      dfDealController.MakeDeal(targetRule)
      Main.RegisterEvent(""+targetName+" agreed to obey a new rule: \"" + ruleDesc + "\".", "info_deal_accepted")
      ClearTargetRule()
    EndIf
    if (command == "ExtCmdGiveDrugs") 
      Debug.Notification("AI: Drinking Skooma")
      Main.Info("Player Drinking Skooma")
      dfDealController.MDC.DrinkSkooma()
      Main.RegisterEvent(""+targetName+" used the drugs that " + speakerName + " provided.", "info_drug_consumed")
    EndIf
    if (command == "ExtCmdRejectDeal") 
      Main.Info("Player Reject Deal")
      Debug.Notification("AI: Rejected Deal")
      dfDealController.RejectDeal(targetRule)
      Main.RegisterEvent(""+targetName+" refused to obey the new rule: \"" + ruleDesc + "\".", "info_deal_rejected")
      ClearTargetRule()
    EndIf
  EndIf
  if (bHasSubmissiveLola)
    if (command == "ExtCmdGiveTask" && (Quest.GetQuest("vkjMQ") as vkjMQ).MayAskForService)
      Main.Info("Player asked for a task")
      Main.RegisterEvent(""+Main.GetActorName(playerRef)+" asked " + speakerName +" for a task.", "info_task_requested")
      (Quest.GetQuest("vkjMQ") as vkjMQ).KneelScene.Start()
    endif
    if (command == "ExtCmdPunishDisrespectful")
      Main.Info(speakerName + " punishes player moderatly")
      Main.RegisterEvent(""+speakerName+" punishes " + Main.GetActorName(playerRef) +" moderatly.", "info_punishment")
      (Quest.GetQuest("vkjMQ") as vkjMQ).Disrespectful(-5)
    endif
    if (command == "ExtCmdPunishWhip")
      Main.Info(speakerName + " punishes player harshly")
      Main.RegisterEvent(""+speakerName+" punishes " + Main.GetActorName(playerRef) +" harshly.", "info_punishment")
      vkjMQ SubLolaMQ = Quest.GetQuest("vkjMQ") as vkjMQ
      SubLolaMQ.UpdateSubmissionScore(-10)
      SubLolaMQ.OwnerWillPunishThisTime()
      SubLolaMQ.WhipPlayer(true)
    endif
    if (command == "ExtCmdSmallReward")
      Main.Info(speakerName + " rewards the player (small)")
      Main.RegisterEvent(""+speakerName+" rewards " + Main.GetActorName(playerRef) +" a little.", "info_reward")
      (Quest.GetQuest("vkjMQ") as vkjMQ).MinimalReward()
    endif
    if (command == "ExtCmdLargeReward")
      Main.Info(speakerName + " rewards the player (large)")
      Main.RegisterEvent(""+speakerName+" rewards " + Main.GetActorName(playerRef) +" a lot.", "info_reward")
      (Quest.GetQuest("vkjMQ") as vkjMQ).MediumReward()
    endif
  Endif
EndEvent

Function SetContext(actor akTarget)
  Main.Debug("SetContext DeviousStuff(" + main.GetActorName(akTarget) + ")")
  if !aiff
    return
  EndIf
  string actorName = main.GetActorName(akTarget)
  if bHasDD
    aiff.SetActorVariable(akTarget, "canVibrate", CanVibrate(akTarget))
    aiff.SetActorVariable(akTarget, "isVibratorActive", libs.isVibrating(akTarget))
  endif
  if bHasDeviousFollowers && akTarget == PlayerRef
    Actor deviousFollower = (Quest.GetQuest("_Dflow") as QF__Gift_09000D62).Alias__DMaster.GetRef() as Actor
    if deviousFollower
      aiff.SetActorVariable(playerRef, "deviousFollowerName", main.GetActorName(deviousFollower))
    else
      aiff.SetActorVariable(playerRef, "deviousFollowerName", "")
    EndIf
    aiff.SetActorVariable(playerRef, "deviousFollowerDebt", Debt.GetValueInt())
    aiff.SetActorVariable(playerRef, "deviousFollowerEnslaveDebt", EnslaveDebt.GetValueInt())
    aiff.SetActorVariable(playerRef, "deviousFollowerContractRemaining", ContractRemaining.GetValueInt())
    aiff.SetActorVariable(playerRef, "deviousFollowerWillpower", dftools._DflowWill.GetValueInt())
    if Debt.GetValueInt() >= EnslaveDebt.GetValueInt()
      if targetRule  == ""
        targetRule  = dfDealController.GetPotentialDeal()
      EndIf
      aiff.SetActorVariable(akTarget, "deviousFollowerTargetRule", targetRule)
    EndIf
    string ruleList = "";
    String[] deals = DealManager.GetDeals()
    int i = 0
    while i < deals.Length
      String[] rules = DealManager.GetDealRules(deals[i])
      int j = 0
      while j < rules.Length
       String ruleDesc = DealManager.GetRuleInfo(rules[j])
       ruleList += ruleDesc + "\n";
       j += 1
      EndWhile
      i += 1
    EndWhile
    aiff.SetActorVariable(playerRef, "deviousFollowerRules", ruleList)
    aiff.SetActorVariable(playerRef, "deviousTimeForSpanks",  dftools.SpankingTimer <= Utility.GetCurrentGameTime())
    if Debt.GetValueInt() >= EnslaveDebt.GetValueInt()
      if targetRule  == ""
        targetRule  = dfDealController.GetPotentialDeal()
      EndIf
      Main.Info("Devious Follower targetRule="+targetRule)
      String ruleDesc = DealManager.GetRuleInfo(targetRule)
      Main.Info("Devious Follower ruleInfo=" + ruleDesc)
      aiff.SetActorVariable(playerRef, "deviousFollowerNewRuleDesc",  ruleDesc)
    Else
      aiff.SetActorVariable(playerRef, "deviousFollowerNewRuleDesc",  "")
    EndIf
  EndIf
  if bHasSubmissiveLola && akTarget == PlayerRef
    vkjMQ SubLolaMQ = Quest.GetQuest("vkjMQ") as vkjMQ

    Actor slOwner = SubLolaMQ.Owner.GetRef() as Actor
    if slOwner
      Main.Info("Submissive Lola owner=" + main.GetActorName(slOwner))
      aiff.SetActorVariable(playerRef, "subLolaOwnerName", main.GetActorName(slOwner))
      aiff.SetActorVariable(playerRef, "subLolaGlobalSubmissionScore", SubLolaMQ.GlobalSubmissionScore.GetValue())
      aiff.SetActorVariable(playerRef, "subLolaTimesSexIsRequired", SubLolaMQ.TimesSexIsRequired)
      aiff.SetActorVariable(playerRef, "subLolaTimesServiceIsRequired", SubLolaMQ.TimesServiceIsRequired)
      aiff.SetActorVariable(playerRef, "subLolaSlaveContract", SubLolaMQ.SlaveContract.GetValue())
      aiff.SetActorVariable(playerRef, "subLolaOwnerAttitude", (Quest.GetQuest("vkj_MCM") as vkjmcm).OwnerAttitude)
      if (SubLolaMQ.MayAskForService)
        aiff.SetActorVariable(playerRef, "subLolaMayAskForService",  1)
      else
        aiff.SetActorVariable(playerRef, "subLolaMayAskForService",  0)
      endif
    else
      aiff.SetActorVariable(playerRef, "subLolaOwnerName", "")
    EndIf
    Actor slPlaymate = SubLolaMQ.Playmate.GetRef() as Actor
    if slPlaymate
      aiff.SetActorVariable(playerRef, "subLolaPlaymateName", main.GetActorName(slPlaymate))
    else
      aiff.SetActorVariable(playerRef, "subLolaPlaymateName", "")
    EndIf

  EndIf
  if bHasDeviouslyAccessible && akTarget == PlayerRef
    aiff.SetActorVariable(playerRef, "deviouslyAccessibleEyeFuckTrack", eyefucktrack.GetValueInt())
    aiff.SetActorVariable(playerRef, "deviouslyAccessibleEyePenalty", eyepenalty.GetValueInt())
    aiff.SetActorVariable(playerRef, "deviouslyAccessibleEyeReward", eyereward.GetValueInt())
    aiff.SetActorVariable(playerRef, "deviouslyAccessibleEyeScore", eyescore.GetValueInt())
    aiff.SetActorVariable(playerRef, "deviouslyAccessibleBeingWatched", playerRef.HasMagicEffect(dwp_watched))
    aiff.SetActorVariable(playerRef, "deviouslyAccessibleGlobal", dwp_global_minai.GetValueInt())
    aiff.SetActorVariable(playerRef, "dwp_eldwhoresp", PlayerRef.HasSpell(dwp_eldwhoresp))
    aiff.SetActorVariable(playerRef, "dwp_eldwhoresp_neq", PlayerRef.HasSpell(dwp_eldwhoresp_neq))
    aiff.SetActorVariable(playerRef, "dwp_eldritchwaifueffect_soldsoul", PlayerRef.HasPerk(dwp_eldritchwaifueffect_soldsoul))
    aiff.SetActorVariable(playerRef, "dwp_eldritchwaifueffect_soldsoul_belted", PlayerRef.HasPerk(dwp_eldritchwaifueffect_soldsoul_belted))    
    ; Determine mind control status
    string mindControlStatus = "normal"
    if PlayerRef.HasMagicEffect(dwp_descgoodgirl) || PlayerRef.HasMagicEffect(dwp_descgoodgirl2) || \
       PlayerRef.HasMagicEffect(dwp_descgoodgirl2b) || PlayerRef.HasMagicEffect(dwp_descgoodgirl2c) || \
       PlayerRef.HasMagicEffect(dwp_descgoodgirl2d) || PlayerRef.HasMagicEffect(dwp_descgoodgirlb) || \
       PlayerRef.HasMagicEffect(dwp_descgoodgirlBASE) || PlayerRef.HasMagicEffect(dwp_descgoodgirlc) || \
       PlayerRef.HasMagicEffect(dwp_descgoodgirld)
        mindControlStatus = "goodgirl"
    elseif PlayerRef.HasMagicEffect(dwp_descbadgirl) || PlayerRef.HasMagicEffect(dwp_descbadgirl2) || \
           PlayerRef.HasMagicEffect(dwp_descbadgirl2b) || PlayerRef.HasMagicEffect(dwp_descbadgirl2c) || \
           PlayerRef.HasMagicEffect(dwp_descbadgirl2d) || PlayerRef.HasMagicEffect(dwp_descbadgirlb) || \
           PlayerRef.HasMagicEffect(dwp_descbadgirlBASE) || PlayerRef.HasMagicEffect(dwp_descbadgirlc) || \
           PlayerRef.HasMagicEffect(dwp_descbadgirld)
        mindControlStatus = "badgirl"
    elseif PlayerRef.HasMagicEffect(dwp_descverybadgirl) || PlayerRef.HasMagicEffect(dwp_descverybadgirl2) || \
           PlayerRef.HasMagicEffect(dwp_descverybadgirl2b) || PlayerRef.HasMagicEffect(dwp_descverybadgirl2c) || \
           PlayerRef.HasMagicEffect(dwp_descverybadgirl2d) || PlayerRef.HasMagicEffect(dwp_descverybadgirlb) || \
           PlayerRef.HasMagicEffect(dwp_descverybadgirlBASE) || PlayerRef.HasMagicEffect(dwp_descverybadgirlc) || \
           PlayerRef.HasMagicEffect(dwp_descverybadgirld)
        mindControlStatus = "verybadgirl"
    elseif PlayerRef.HasMagicEffect(dwp_descmindcontrolpunishment)
        mindControlStatus = "punishment"
    elseif PlayerRef.HasMagicEffect(dwp_descmindcontrolpost)
        mindControlStatus = "post"
    endif
    
    aiff.SetActorVariable(playerRef, "dwp_mindcontrol", mindControlStatus)
  EndIf
  if bHasSlaveTats && akTarget
    SerializeTattooForDB(akTarget) ; This will store the data in the DB
  endif
EndFunction


Function ClearTargetRule()
  targetRule = ""
  aiff.SetActorVariable(playerRef, "deviousFollowerTargetRule", "")
EndFunction


string Function GetKeywordsForActor(actor akTarget)
  string ret = ""
  ; Now handled via the equipment system in minai_Arousal.psc
  return ret
EndFunction

string Function GetFactionsForActor(actor akTarget)
  string ret = ""

  return ret
EndFunction


bool Function HasDD()
  return bHasDD
EndFunction

Function SexStarted(actor[] actors, actor akSpeaker, string tags="")
  if !bHasSubmissiveLola
    return
  endif

  Main.Info("Received sexstarted event")

  ; Player involved ?
  if actors.Find(playerRef) == 0
    return
  endif

  ; Owner involved ?
  Actor slOwner = (Quest.GetQuest("vkjMQ") as vkjMQ).Owner.GetRef() as Actor
  if slOwner != akSpeaker
    return
  endif
  
  int handle = ModEvent.Create("PlayerOfferedSexToMaster")
  if handle
    ModEvent.PushForm(handle, self)
    ModEvent.PushForm(handle, slOwner)
	  ModEvent.PushBool(handle, true)
    ModEvent.Send(handle)
  endif

  Main.Info("Event sent to SLola")
EndFunction

; New handler for TNTR animation events
Event OnTNTRAnimation(Form akTarget, string eventSource, string eventName)
    string eventLine = main.GetActorName(akTarget as Actor) + " caught caught by a " + eventSource + " and is being animated with " + eventName
    Main.Info("tntr: " + eventLine)
    main.RequestLLMResponseFromActor(eventLine, "minai_tntr_" + eventSource + "_" + eventName, main.GetActorName(akTarget as Actor), "player")
EndEvent


; Serializes tattoo information into a length-encoded string format
; Format: <field_count>:<field1_len>:<field1><field2_len>:<field2>... with ~ between tattoos 
string Function SerializeTattooInfo(Actor target)
    string result = ""
    
    if !target
        Main.Error("SerializeTattooInfo called with null target")
        return "0:"
    endif
    
    int applied = JFormDB.getObj(target, ".SlaveTats.applied")
    if applied == 0
        Main.Debug("No tattoos found for " + Main.GetActorName(target))
        return "0:"
    endif
    
    int i = JArray.count(applied)
    while i > 0
        i -= 1
        
        int entry = JArray.getObj(applied, i)
        if SlaveTats.is_tattoo(entry)
            ; Start a new tattoo entry with field count
            string tattooStr = "15:" ; Number of standard fields we're including
            
            ; Add standard fields with length encoding
            string[] fields = new string[15]
            fields[0] = JMap.getStr(entry, "section", "")
            fields[1] = JMap.getStr(entry, "name", "")
            fields[2] = JMap.getStr(entry, "area", "")
            fields[3] = JMap.getInt(entry, "slot", -1)
            fields[4] = JMap.getInt(entry, "color", 0)
            fields[5] = JMap.getInt(entry, "glow", 0)
            fields[6] = JMap.getInt(entry, "gloss", 0)
            fields[7] = (1.0 - JMap.getFlt(entry, "invertedAlpha", 0.0))
            fields[8] = JMap.getStr(entry, "texture", "")
            fields[9] = JMap.getInt(entry, "locked", 0)
            fields[10] = JMap.getStr(entry, "excluded_by", "")
            fields[11] = JMap.getStr(entry, "requires", "")
            fields[12] = JMap.getStr(entry, "requires_plugin", "")
            fields[13] = JMap.getStr(entry, "requires_formid", "")
            fields[14] = JMap.getStr(entry, "domain", "")
            
            int j = 0
            while j < fields.Length
                string fieldStr = fields[j]
                int fieldLen = StringUtil.GetLength(fieldStr)
                tattooStr += fieldLen + ":" + fieldStr
                j += 1
            endwhile
            
            ; Add this tattoo entry to the result
            if result != ""
                result += "~"
            endif
            result += tattooStr
        endif
    endwhile
    
    Main.Info("Serialized " + JArray.count(applied) + " tattoos for " + Main.GetActorName(target))
    return result
endfunction

; Helper function to test parsing the serialized string
bool Function TestParseSerializedTattoos(string serialized)
    Main.Info("Testing parse of serialized tattoo string: " + serialized)
    
    string[] tattoos = StringUtil.Split(serialized, "~")
    int i = 0
    while i < tattoos.Length
        string tattoo = tattoos[i]
        Main.Info("Parsing tattoo: " + tattoo)
        
        ; Split by first colon to get field count
        int colonPos = StringUtil.Find(tattoo, ":")
        int fieldCount = tattoo as int
        string remaining = StringUtil.Substring(tattoo, colonPos + 1)
        
        int j = 0
        while j < fieldCount
            ; Get field length
            colonPos = StringUtil.Find(remaining, ":")
            int fieldLen = StringUtil.Substring(remaining, 0, colonPos) as int
            remaining = StringUtil.Substring(remaining, colonPos + 1)
            
            ; Get field value
            string fieldValue = StringUtil.Substring(remaining, 0, fieldLen)
            remaining = StringUtil.Substring(remaining, fieldLen)
            
            Main.Info("Field " + j + ": " + fieldValue)
            j += 1
        endwhile
        
        i += 1
    endwhile
    
    return true
endfunction

; Serializes tattoo information into a length-encoded string format for storage in the database
string Function SerializeTattooForDB(Actor target)
    if !target
        Main.Error("SerializeTattooForDB called with null target")
        return ""
    endif
    
    int applied = JFormDB.getObj(target, ".SlaveTats.applied")
    if applied == 0
        Main.Debug("No tattoos found for " + Main.GetActorName(target))
        return ""
    endif
    
    string result = ""
    int i = JArray.count(applied)
    while i > 0
        i -= 1
        
        int entry = JArray.getObj(applied, i)
        if SlaveTats.is_tattoo(entry)
            if result != ""
                result += "~"
            endif
            
            ; Format: section&name&area&texture&slot&color&glow&gloss&alpha&locked&excluded_by&requires&requires_plugin&requires_formid&domain
            string tattooEntry = ""
            tattooEntry += JMap.getStr(entry, "section", "") + "&"
            tattooEntry += JMap.getStr(entry, "name", "") + "&"
            tattooEntry += JMap.getStr(entry, "area", "") + "&"
            tattooEntry += JMap.getStr(entry, "texture", "") + "&"
            tattooEntry += JMap.getInt(entry, "slot", -1) + "&"
            tattooEntry += JMap.getInt(entry, "color", 0) + "&"
            tattooEntry += JMap.getInt(entry, "glow", 0) + "&"
            tattooEntry += JMap.getInt(entry, "gloss", 0) + "&"
            tattooEntry += (1.0 - JMap.getFlt(entry, "invertedAlpha", 0.0)) + "&"
            tattooEntry += JMap.getInt(entry, "locked", 0) + "&"
            tattooEntry += JMap.getStr(entry, "excluded_by", "") + "&"
            tattooEntry += JMap.getStr(entry, "requires", "") + "&"
            tattooEntry += JMap.getStr(entry, "requires_plugin", "") + "&"
            tattooEntry += JMap.getStr(entry, "requires_formid", "") + "&"
            tattooEntry += JMap.getStr(entry, "domain", "")
            
            result += tattooEntry
        endif
    endwhile
    
    ; Send the full tattoo data to be stored
    if result != ""
        aiff.AILogMessage(Main.GetActorName(target) + "@" + result, "storetattoodesc")
    endif
    
    return result
endfunction