scriptname minai_Reputation extends Quest

minai_MainQuestController main
minai_AIFF aiff
minai_Sex  sex
minai_DeviousStuff devious
minai_Config config
minai_Followers followers
DefeatConfig Defeat
actor playerRef

bool bHasSLSF
bool bHasReputation

; Reputation Global Variables
GlobalVariable SR_GLobalReputation;  060213D0
GlobalVariable SR_Global_AedricDaedric;  0602B5D8
GlobalVariable SR_Global_LawCrime;  0602B5D9
GlobalVariable SR_Global_DependabilityAmbition;  0602B5DA
GlobalVariable SR_DaedricAuras_Vampire;  064AF346
GlobalVariable SR_DaedricAuras_Werewolf;  064AF347
GlobalVariable SR_FactionAuras_Forsworn;  065658F3
GlobalVariable SR_Global_WerewolfSuspicion;  06565922
GlobalVariable SR_Global_VampireSuspicion;  06565923
GlobalVariable SR_WerewolfStage;  06583F46
GlobalVariable SR_Global_ThaneInCurrentLocation;  065DF29E
GlobalVariable SR_Global_ThievesGuildHasInfluence;  065E43AA
GlobalVariable SR_Global_PlayerIsViolentCriminal;  065EE625
GlobalVariable SR_Global_PlayerIsPettyCriminal;  065EE62A
GlobalVariable SR_Global_PlayerIsMurderer ; 065F374E
GlobalVariable SR_Global_PlayerIsThalmorEnemy ; 066358B5
GlobalVariable SR_Global_PlayerIsVampireRace ; 0663FBA3
GlobalVariable SR_Global_CurrentFameLevel ; 0677F166
GlobalVariable SR_Global_LocalSideQuestFame;  0683A959

; SLSF Global Variables
GlobalVariable SLSF_Reloaded_CurrentSlutFame; FE00180C
GlobalVariable SLSF_Reloaded_CurrentWhoreFame; FE00180D
GlobalVariable SLSF_Reloaded_CurrentExhibitionistFame; FE00180E
GlobalVariable SLSF_Reloaded_CurrentOralFame; FE00180F
GlobalVariable SLSF_Reloaded_CurrentAnalFame; FE001810
GlobalVariable SLSF_Reloaded_CurrentNastyFame; FE001811
GlobalVariable SLSF_Reloaded_CurrentPregnantFame; FE001812
GlobalVariable SLSF_Reloaded_CurrentDominantFame; FE001813
GlobalVariable SLSF_Reloaded_CurrentSubmissiveFame; FE001814
GlobalVariable SLSF_Reloaded_CurrentSadistFame; FE001815
GlobalVariable SLSF_Reloaded_CurrentMasochistFame; FE001816
GlobalVariable SLSF_Reloaded_CurrentGentleFame; FE001817
GlobalVariable SLSF_Reloaded_CurrentMenFame; FE001818
GlobalVariable SLSF_Reloaded_CurrentWomenFame; FE001819
GlobalVariable SLSF_Reloaded_CurrentOrcFame; FE00181A
GlobalVariable SLSF_Reloaded_CurrentKhajiitFame; FE00181B
GlobalVariable SLSF_Reloaded_CurrentArgonianFame; FE00181C
GlobalVariable SLSF_Reloaded_CurrentBestialityFame; FE00181D
GlobalVariable SLSF_Reloaded_CurrentGroupFame; FE00181E
GlobalVariable SLSF_Reloaded_CurrentBoundFame; FE00181F
GlobalVariable SLSF_Reloaded_CurrentTattooFame; FE001820
GlobalVariable SLSF_Reloaded_CurrentCumDumpFame; FE001821
GlobalVariable SLSF_Reloaded_CurrentCheatFame; FE001822
GlobalVariable SLSF_Reloaded_CurrentCuckFame; FE001823
GlobalVariable SLSF_Reloaded_OralCumVisible; FE001824
GlobalVariable SLSF_Reloaded_AnalCumVisible; FE001825
GlobalVariable SLSF_Reloaded_VaginalFameVisible; FE001826
GlobalVariable SLSF_Reloaded_VisiblyBound; FE00182B
GlobalVariable SLSF_Reloaded_LightlyBound; FE00182C
GlobalVariable SLSF_Reloaded_HeavilyBound; FE00182D
GlobalVariable SLSF_Reloaded_Skooma; FE00182E
GlobalVariable SLSF_Reloaded_CurrentAirheadFame; FE00182F
GlobalVariable SLSF_Reloaded_IsBelted; FE001830
GlobalVariable SLSF_Reloaded_IsCollared; FE001831

function Maintenance(minai_MainQuestController _main)
  playerRef = Game.GetPlayer()
  main = _main
  config = Game.GetFormFromFile(0x0912, "MinAI.esp") as minai_Config
  if !config
    Main.Fatal("Could not load configuration - script version mismatch with esp")
  EndIf
  aiff = Game.GetFormFromFile(0x0802, "MinAI.esp") as minai_AIFF
  sex = Game.GetFormFromFile(0x0802, "MinAI.esp") as minai_Sex
  devious = Game.GetFormFromFile(0x0802, "MinAI.esp") as minai_DeviousStuff
  followers = Game.GetFormFromFile(0x0913, "MinAI.esp") as minai_Followers
  Main.Info("Initializing Reputation Module.")
  bHasSLSF = (Game.GetModByName("SLSF Reloaded.esp") != 255)
  bHasReputation = (Game.GetModByName("SkyrimReputation_SSE.esp") != 255)
  if (bHasSLSF  )
    Main.Info("Found SLSF Reloaded.")
    SLSF_Reloaded_CurrentSlutFame = Game.GetFormFromFile(0x00180C, "SLSF Reloaded.esp") as GlobalVariable
    SLSF_Reloaded_CurrentWhoreFame = Game.GetFormFromFile(0x00180D, "SLSF Reloaded.esp") as GlobalVariable
    SLSF_Reloaded_CurrentExhibitionistFame = Game.GetFormFromFile(0x00180E, "SLSF Reloaded.esp") as GlobalVariable
    SLSF_Reloaded_CurrentOralFame = Game.GetFormFromFile(0x00180F, "SLSF Reloaded.esp") as GlobalVariable
    SLSF_Reloaded_CurrentAnalFame = Game.GetFormFromFile(0x001810, "SLSF Reloaded.esp") as GlobalVariable
    SLSF_Reloaded_CurrentNastyFame = Game.GetFormFromFile(0x001811, "SLSF Reloaded.esp") as GlobalVariable
    SLSF_Reloaded_CurrentPregnantFame = Game.GetFormFromFile(0x001812, "SLSF Reloaded.esp") as GlobalVariable
    SLSF_Reloaded_CurrentDominantFame = Game.GetFormFromFile(0x001813, "SLSF Reloaded.esp") as GlobalVariable
    SLSF_Reloaded_CurrentSubmissiveFame = Game.GetFormFromFile(0x001814, "SLSF Reloaded.esp") as GlobalVariable
    SLSF_Reloaded_CurrentSadistFame = Game.GetFormFromFile(0x001815, "SLSF Reloaded.esp") as GlobalVariable
    SLSF_Reloaded_CurrentMasochistFame = Game.GetFormFromFile(0x001816, "SLSF Reloaded.esp") as GlobalVariable
    SLSF_Reloaded_CurrentGentleFame = Game.GetFormFromFile(0x001817, "SLSF Reloaded.esp") as GlobalVariable
    SLSF_Reloaded_CurrentMenFame = Game.GetFormFromFile(0x001818, "SLSF Reloaded.esp") as GlobalVariable
    SLSF_Reloaded_CurrentWomenFame = Game.GetFormFromFile(0x001819, "SLSF Reloaded.esp") as GlobalVariable
    SLSF_Reloaded_CurrentOrcFame = Game.GetFormFromFile(0x00181A, "SLSF Reloaded.esp") as GlobalVariable
    SLSF_Reloaded_CurrentKhajiitFame = Game.GetFormFromFile(0x00181B, "SLSF Reloaded.esp") as GlobalVariable
    SLSF_Reloaded_CurrentArgonianFame = Game.GetFormFromFile(0x00181C, "SLSF Reloaded.esp") as GlobalVariable
    SLSF_Reloaded_CurrentBestialityFame = Game.GetFormFromFile(0x00181D, "SLSF Reloaded.esp") as GlobalVariable
    SLSF_Reloaded_CurrentGroupFame = Game.GetFormFromFile(0x00181E, "SLSF Reloaded.esp") as GlobalVariable
    SLSF_Reloaded_CurrentBoundFame = Game.GetFormFromFile(0x00181F, "SLSF Reloaded.esp") as GlobalVariable
    SLSF_Reloaded_CurrentTattooFame = Game.GetFormFromFile(0x001820, "SLSF Reloaded.esp") as GlobalVariable
    SLSF_Reloaded_CurrentCumDumpFame = Game.GetFormFromFile(0x001821, "SLSF Reloaded.esp") as GlobalVariable
    SLSF_Reloaded_CurrentCheatFame = Game.GetFormFromFile(0x001822, "SLSF Reloaded.esp") as GlobalVariable
    SLSF_Reloaded_CurrentCuckFame = Game.GetFormFromFile(0x001823, "SLSF Reloaded.esp") as GlobalVariable
    SLSF_Reloaded_OralCumVisible = Game.GetFormFromFile(0x001824, "SLSF Reloaded.esp") as GlobalVariable
    SLSF_Reloaded_AnalCumVisible = Game.GetFormFromFile(0x001825, "SLSF Reloaded.esp") as GlobalVariable
    SLSF_Reloaded_VaginalFameVisible = Game.GetFormFromFile(0x001826, "SLSF Reloaded.esp") as GlobalVariable
    SLSF_Reloaded_VisiblyBound = Game.GetFormFromFile(0x00182B, "SLSF Reloaded.esp") as GlobalVariable
    SLSF_Reloaded_LightlyBound = Game.GetFormFromFile(0x00182C, "SLSF Reloaded.esp") as GlobalVariable
    SLSF_Reloaded_HeavilyBound = Game.GetFormFromFile(0x00182D, "SLSF Reloaded.esp") as GlobalVariable
    SLSF_Reloaded_Skooma = Game.GetFormFromFile(0x00182E, "SLSF Reloaded.esp") as GlobalVariable
    SLSF_Reloaded_CurrentAirheadFame = Game.GetFormFromFile(0x00182F, "SLSF Reloaded.esp") as GlobalVariable
    SLSF_Reloaded_IsBelted = Game.GetFormFromFile(0x001830, "SLSF Reloaded.esp") as GlobalVariable
    SLSF_Reloaded_IsCollared = Game.GetFormFromFile(0x001831, "SLSF Reloaded.esp") as GlobalVariable

  EndIf
  if (bHasReputation)
    Main.Info("Found Skyrim Reputation")
    ; Reputation Global Variables
    SR_GLobalReputation = Game.GetFormFromFile(0x0213D0, "SkyrimReputation_SSE.esp") as GlobalVariable
    SR_Global_AedricDaedric = Game.GetFormFromFile(0x02B5D8, "SkyrimReputation_SSE.esp") as GlobalVariable
    SR_Global_LawCrime = Game.GetFormFromFile(0x02B5D9, "SkyrimReputation_SSE.esp") as GlobalVariable
    SR_Global_DependabilityAmbition = Game.GetFormFromFile(0x02B5DA, "SkyrimReputation_SSE.esp") as GlobalVariable
    SR_DaedricAuras_Vampire = Game.GetFormFromFile(0x4AF346, "SkyrimReputation_SSE.esp") as GlobalVariable
    SR_DaedricAuras_Werewolf = Game.GetFormFromFile(0x4AF347, "SkyrimReputation_SSE.esp") as GlobalVariable
    SR_FactionAuras_Forsworn = Game.GetFormFromFile(0x5658F3, "SkyrimReputation_SSE.esp") as GlobalVariable
    SR_Global_WerewolfSuspicion = Game.GetFormFromFile(0x565922, "SkyrimReputation_SSE.esp") as GlobalVariable
    SR_Global_VampireSuspicion = Game.GetFormFromFile(0x565923, "SkyrimReputation_SSE.esp") as GlobalVariable
    SR_WerewolfStage = Game.GetFormFromFile(0x583F46, "SkyrimReputation_SSE.esp") as GlobalVariable
    SR_Global_ThaneInCurrentLocation = Game.GetFormFromFile(0x5DF29E, "SkyrimReputation_SSE.esp") as GlobalVariable
    SR_Global_ThievesGuildHasInfluence = Game.GetFormFromFile(0x5E43AA, "SkyrimReputation_SSE.esp") as GlobalVariable
    SR_Global_PlayerIsViolentCriminal = Game.GetFormFromFile(0x5EE625, "SkyrimReputation_SSE.esp") as GlobalVariable
    SR_Global_PlayerIsPettyCriminal = Game.GetFormFromFile(0x5EE62A, "SkyrimReputation_SSE.esp") as GlobalVariable
    
    SR_Global_PlayerIsMurderer  = Game.GetFormFromFile(0x5F374E, "SkyrimReputation_SSE.esp") as GlobalVariable
    SR_Global_PlayerIsThalmorEnemy  = Game.GetFormFromFile(0x6358B5, "SkyrimReputation_SSE.esp") as GlobalVariable
    SR_Global_PlayerIsVampireRace  = Game.GetFormFromFile(0x63FBA3, "SkyrimReputation_SSE.esp") as GlobalVariable
    SR_Global_CurrentFameLevel  = Game.GetFormFromFile(0x77F166, "SkyrimReputation_SSE.esp") as GlobalVariable
    
    SR_Global_LocalSideQuestFame = Game.GetFormFromFile(0x83A959, "SkyrimReputation_SSE.esp") as GlobalVariable
  EndIf
  aiff.SetModAvailable("Reputation", bHasReputation)
  aiff.SetModAvailable("SLSF", bHasSLSF)
EndFunction


Event CommandDispatcher(String speakerName,String  command, String parameter)
  
EndEvent


Function SetContext(actor akTarget)
  Main.Debug("SetContext Reputation(" + main.GetActorName(akTarget) + ")")
  if akTarget != PlayerRef
    return
  EndIf
  if (bHasSLSF  )
    aiff.SetActorVariable(playerRef, "SLSF_Reloaded_CurrentSlutFame", SLSF_Reloaded_CurrentSlutFame.GetValue())
    aiff.SetActorVariable(playerRef, "SLSF_Reloaded_CurrentWhoreFame", SLSF_Reloaded_CurrentWhoreFame.GetValue())
    aiff.SetActorVariable(playerRef, "SLSF_Reloaded_CurrentExhibitionistFame", SLSF_Reloaded_CurrentExhibitionistFame.GetValue())
    aiff.SetActorVariable(playerRef, "SLSF_Reloaded_CurrentOralFame", SLSF_Reloaded_CurrentOralFame.GetValue())
    aiff.SetActorVariable(playerRef, "SLSF_Reloaded_CurrentAnalFame", SLSF_Reloaded_CurrentAnalFame.GetValue())
    aiff.SetActorVariable(playerRef, "SLSF_Reloaded_CurrentNastyFame", SLSF_Reloaded_CurrentNastyFame.GetValue())
    aiff.SetActorVariable(playerRef, "SLSF_Reloaded_CurrentPregnantFame", SLSF_Reloaded_CurrentPregnantFame.GetValue())
    aiff.SetActorVariable(playerRef, "SLSF_Reloaded_CurrentDominantFame", SLSF_Reloaded_CurrentDominantFame.GetValue())
    aiff.SetActorVariable(playerRef, "SLSF_Reloaded_CurrentSubmissiveFame", SLSF_Reloaded_CurrentSubmissiveFame.GetValue())
    aiff.SetActorVariable(playerRef, "SLSF_Reloaded_CurrentSadistFame", SLSF_Reloaded_CurrentSadistFame.GetValue())
    aiff.SetActorVariable(playerRef, "SLSF_Reloaded_CurrentMasochistFame", SLSF_Reloaded_CurrentMasochistFame.GetValue())
    aiff.SetActorVariable(playerRef, "SLSF_Reloaded_CurrentGentleFame", SLSF_Reloaded_CurrentGentleFame.GetValue())
    aiff.SetActorVariable(playerRef, "SLSF_Reloaded_CurrentMenFame", SLSF_Reloaded_CurrentMenFame.GetValue())
    aiff.SetActorVariable(playerRef, "SLSF_Reloaded_CurrentWomenFame", SLSF_Reloaded_CurrentWomenFame.GetValue())
    aiff.SetActorVariable(playerRef, "SLSF_Reloaded_CurrentOrcFame", SLSF_Reloaded_CurrentOrcFame.GetValue())
    aiff.SetActorVariable(playerRef, "SLSF_Reloaded_CurrentKhajiitFame", SLSF_Reloaded_CurrentKhajiitFame.GetValue())
    aiff.SetActorVariable(playerRef, "SLSF_Reloaded_CurrentArgonianFame", SLSF_Reloaded_CurrentArgonianFame.GetValue())
    aiff.SetActorVariable(playerRef, "SLSF_Reloaded_CurrentBestialityFame", SLSF_Reloaded_CurrentBestialityFame.GetValue())
    aiff.SetActorVariable(playerRef, "SLSF_Reloaded_CurrentGroupFame", SLSF_Reloaded_CurrentGroupFame.GetValue())
    aiff.SetActorVariable(playerRef, "SLSF_Reloaded_CurrentBoundFame", SLSF_Reloaded_CurrentBoundFame.GetValue())
    aiff.SetActorVariable(playerRef, "SLSF_Reloaded_CurrentTattooFame", SLSF_Reloaded_CurrentTattooFame.GetValue())
    aiff.SetActorVariable(playerRef, "SLSF_Reloaded_CurrentCumDumpFame", SLSF_Reloaded_CurrentCumDumpFame.GetValue())
    aiff.SetActorVariable(playerRef, "SLSF_Reloaded_CurrentCheatFame", SLSF_Reloaded_CurrentCheatFame.GetValue())
    aiff.SetActorVariable(playerRef, "SLSF_Reloaded_CurrentCuckFame", SLSF_Reloaded_CurrentCuckFame.GetValue())
    aiff.SetActorVariable(playerRef, "SLSF_Reloaded_OralCumVisible", SLSF_Reloaded_OralCumVisible.GetValue())
    aiff.SetActorVariable(playerRef, "SLSF_Reloaded_AnalCumVisible", SLSF_Reloaded_AnalCumVisible.GetValue())
    aiff.SetActorVariable(playerRef, "SLSF_Reloaded_VaginalFameVisible", SLSF_Reloaded_VaginalFameVisible.GetValue())
    aiff.SetActorVariable(playerRef, "SLSF_Reloaded_VisiblyBound", SLSF_Reloaded_VisiblyBound.GetValue())
    aiff.SetActorVariable(playerRef, "SLSF_Reloaded_LightlyBound", SLSF_Reloaded_LightlyBound.GetValue())
    aiff.SetActorVariable(playerRef, "SLSF_Reloaded_HeavilyBound", SLSF_Reloaded_HeavilyBound.GetValue())
    aiff.SetActorVariable(playerRef, "SLSF_Reloaded_Skooma", SLSF_Reloaded_Skooma.GetValue())
    aiff.SetActorVariable(playerRef, "SLSF_Reloaded_CurrentAirheadFame", SLSF_Reloaded_CurrentAirheadFame.GetValue())
    aiff.SetActorVariable(playerRef, "SLSF_Reloaded_IsBelted", SLSF_Reloaded_IsBelted.GetValue())
    aiff.SetActorVariable(playerRef, "SLSF_Reloaded_IsCollared", SLSF_Reloaded_IsCollared.GetValue())
  EndIf
  if (bHasReputation)
    aiff.SetActorVariable(playerRef, "SR_GLobalReputation", SR_GLobalReputation.GetValue())
    aiff.SetActorVariable(playerRef, "SR_Global_AedricDaedric", SR_Global_AedricDaedric.GetValue())
    aiff.SetActorVariable(playerRef, "SR_Global_LawCrime", SR_Global_LawCrime.GetValue())
    aiff.SetActorVariable(playerRef, "SR_Global_DependabilityAmbition", SR_Global_DependabilityAmbition.GetValue())
    aiff.SetActorVariable(playerRef, "SR_DaedricAuras_Vampire", SR_DaedricAuras_Vampire.GetValue())
    aiff.SetActorVariable(playerRef, "SR_DaedricAuras_Werewolf", SR_DaedricAuras_Werewolf.GetValue())
    aiff.SetActorVariable(playerRef, "SR_FactionAuras_Forsworn", SR_FactionAuras_Forsworn.GetValue())
    aiff.SetActorVariable(playerRef, "SR_Global_WerewolfSuspicion", SR_Global_WerewolfSuspicion.GetValue())
    aiff.SetActorVariable(playerRef, "SR_Global_VampireSuspicion", SR_Global_VampireSuspicion.GetValue())
    aiff.SetActorVariable(playerRef, "SR_WerewolfStage", SR_WerewolfStage.GetValue())
    aiff.SetActorVariable(playerRef, "SR_Global_ThaneInCurrentLocation", SR_Global_ThaneInCurrentLocation.GetValue())
    aiff.SetActorVariable(playerRef, "SR_Global_ThievesGuildHasInfluence", SR_Global_ThievesGuildHasInfluence.GetValue())
    aiff.SetActorVariable(playerRef, "SR_Global_PlayerIsViolentCriminal", SR_Global_PlayerIsViolentCriminal.GetValue())
    aiff.SetActorVariable(playerRef, "SR_Global_PlayerIsPettyCriminal", SR_Global_PlayerIsPettyCriminal.GetValue())
    aiff.SetActorVariable(playerRef, "SR_Global_PlayerIsMurderer", SR_Global_PlayerIsMurderer.GetValue())
    aiff.SetActorVariable(playerRef, "SR_Global_PlayerIsThalmorEnemy", SR_Global_PlayerIsThalmorEnemy.GetValue())
    aiff.SetActorVariable(playerRef, "SR_Global_PlayerIsVampireRace", SR_Global_PlayerIsVampireRace.GetValue())
    aiff.SetActorVariable(playerRef, "SR_Global_CurrentFameLevel", SR_Global_CurrentFameLevel.GetValue())
    aiff.SetActorVariable(playerRef, "SR_Global_LocalSideQuestFame", SR_Global_LocalSideQuestFame.GetValue())
  EndIf
EndFunction


string Function GetKeywordsForActor(actor akTarget)
  return "";
EndFunction


string Function GetFactionsForActor(actor akTarget)
  return "";
EndFunction


