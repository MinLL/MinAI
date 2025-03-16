<?php
/*
function BuildNSFWReputationContext($targetName) {
    $ret = "";
    if (IsModEnabled("SLSF") && strtolower($targetName) == strtolower($GLOBALS["PLAYER_NAME"])) {
            $SLSF_Reloaded_CurrentSlutFame = GetActorValue($trargetName, "SLSF_Reloaded_CurrentSlutFame");
            $SLSF_Reloaded_CurrentWhoreFame = GetActorValue($trargetName, "SLSF_Reloaded_CurrentWhoreFame");
            $SLSF_Reloaded_CurrentExhibitionistFame = GetActorValue($trargetName, "SLSF_Reloaded_CurrentExhibitionistFame");
            $SLSF_Reloaded_CurrentOralFame = GetActorValue($trargetName, "SLSF_Reloaded_CurrentOralFame");
            $SLSF_Reloaded_CurrentAnalFame = GetActorValue($trargetName, "SLSF_Reloaded_CurrentAnalFame");
            $SLSF_Reloaded_CurrentNastyFame = GetActorValue($trargetName, "SLSF_Reloaded_CurrentNastyFame");
            $SLSF_Reloaded_CurrentPregnantFame = GetActorValue($trargetName, "SLSF_Reloaded_CurrentPregnantFame");
            $SLSF_Reloaded_CurrentDominantFame = GetActorValue($trargetName, "SLSF_Reloaded_CurrentDominantFame");
            $SLSF_Reloaded_CurrentSubmissiveFame = GetActorValue($trargetName, "SLSF_Reloaded_CurrentSubmissiveFame");
            $SLSF_Reloaded_CurrentSadistFame = GetActorValue($trargetName, "SLSF_Reloaded_CurrentSadistFame");
            $SLSF_Reloaded_CurrentMasochistFame = GetActorValue($trargetName, "SLSF_Reloaded_CurrentMasochistFame");
            $SLSF_Reloaded_CurrentGentleFame = GetActorValue($trargetName, "SLSF_Reloaded_CurrentGentleFame");
            $SLSF_Reloaded_CurrentMenFame = GetActorValue($trargetName, "SLSF_Reloaded_CurrentMenFame");
            $SLSF_Reloaded_CurrentWomenFame = GetActorValue($trargetName, "SLSF_Reloaded_CurrentWomenFame");
            $SLSF_Reloaded_CurrentOrcFame = GetActorValue($trargetName, "SLSF_Reloaded_CurrentOrcFame");
            $SLSF_Reloaded_CurrentKhajiitFame = GetActorValue($trargetName, "SLSF_Reloaded_CurrentKhajiitFame");
            $SLSF_Reloaded_CurrentArgonianFame = GetActorValue($trargetName, "SLSF_Reloaded_CurrentArgonianFame");
            $SLSF_Reloaded_CurrentBestialityFame = GetActorValue($trargetName, "SLSF_Reloaded_CurrentBestialityFame");
            $SLSF_Reloaded_CurrentGroupFame = GetActorValue($trargetName, "SLSF_Reloaded_CurrentGroupFame");
            $SLSF_Reloaded_CurrentBoundFame = GetActorValue($trargetName, "SLSF_Reloaded_CurrentBoundFame");
            $SLSF_Reloaded_CurrentTattooFame = GetActorValue($trargetName, "SLSF_Reloaded_CurrentTattooFame");
            $SLSF_Reloaded_CurrentCumDumpFame = GetActorValue($trargetName, "SLSF_Reloaded_CurrentCumDumpFame");
            $SLSF_Reloaded_CurrentCheatFame = GetActorValue($trargetName, "SLSF_Reloaded_CurrentCheatFame");
            $SLSF_Reloaded_CurrentCuckFame = GetActorValue($trargetName, "SLSF_Reloaded_CurrentCuckFame");
            $SLSF_Reloaded_OralCumVisible = GetActorValue($trargetName, "SLSF_Reloaded_OralCumVisible");
            $SLSF_Reloaded_AnalCumVisible = GetActorValue($trargetName, "SLSF_Reloaded_AnalCumVisible");
            $SLSF_Reloaded_VaginalFameVisible = GetActorValue($trargetName, "SLSF_Reloaded_VaginalFameVisible");
            $SLSF_Reloaded_VisiblyBound = GetActorValue($trargetName, "SLSF_Reloaded_VisiblyBound");
            $SLSF_Reloaded_LightlyBound = GetActorValue($trargetName, "SLSF_Reloaded_LightlyBound");
            $SLSF_Reloaded_HeavilyBound = GetActorValue($trargetName, "SLSF_Reloaded_HeavilyBound");
            $SLSF_Reloaded_Skooma = GetActorValue($trargetName, "SLSF_Reloaded_Skooma");
            $SLSF_Reloaded_CurrentAirheadFame = GetActorValue($trargetName, "SLSF_Reloaded_CurrentAirheadFame");
            $SLSF_Reloaded_IsBelted = GetActorValue($trargetName, "SLSF_Reloaded_IsBelted");
            $SLSF_Reloaded_IsCollared = GetActorValue($trargetName, "SLSF_Reloaded_IsCollared");
            $SLSF_GlobalReputation = GetActorValue($trargetName, "SLSF_GlobalReputation");
        }
    return $ret;
}
                
function BuildSFWReputationContext($targetName) {
    $ret = "";
    if (IsModEnabled("Reputation") && strtolower($targetName) == strtolower($GLOBALS["PLAYER_NAME"])) {
            $SR_Global_AedricDaedric = GetActorValue($trargetName, "SR_Global_AedricDaedric");
            $SR_Global_LawCrime = GetActorValue($trargetName, "SR_Global_LawCrime");
            $SR_Global_DependabilityAmbition = GetActorValue($trargetName, "SR_Global_DependabilityAmbition");
            $SR_DaedricAuras_Vampire = GetActorValue($trargetName, "SR_DaedricAuras_Vampire");
            $SR_DaedricAuras_Werewolf = GetActorValue($trargetName, "SR_DaedricAuras_Werewolf");
            $SR_FactionAuras_Forsworn = GetActorValue($trargetName, "SR_FactionAuras_Forsworn");
            $SR_Global_WerewolfSuspicion = GetActorValue($trargetName, "SR_Global_WerewolfSuspicion");
            $SR_Global_VampireSuspicion = GetActorValue($trargetName, "SR_Global_VampireSuspicion");
            $SR_WerewolfStage = GetActorValue($trargetName, "SR_WerewolfStage");
            $SR_Global_ThaneInCurrentLocation = GetActorValue($trargetName, "SR_Global_ThaneInCurrentLocation");
            $SR_Global_ThievesGuildHasInfluence = GetActorValue($trargetName, "SR_Global_ThievesGuildHasInfluence");
            $SR_Global_PlayerIsViolentCriminal = GetActorValue($trargetName, "SR_Global_PlayerIsViolentCriminal");
            $SR_Global_PlayerIsPettyCriminal = GetActorValue($trargetName, "SR_Global_PlayerIsPettyCriminal");
            $SR_Global_PlayerIsMurderer = GetActorValue($trargetName, "SR_Global_PlayerIsMurderer");
            $SR_Global_PlayerIsThalmorEnemy = GetActorValue($trargetName, "SR_Global_PlayerIsThalmorEnemy");
            $SR_Global_PlayerIsVampireRace = GetActorValue($trargetName, "SR_Global_PlayerIsVampireRace");
            $SR_Global_CurrentFameLevel = GetActorValue($trargetName, "SR_Global_CurrentFameLevel");
            $SR_Global_LocalSideQuestFame = GetActorValue($trargetName, "SR_Global_LocalSideQuestFame");
        }
    return $ret;
}
*/