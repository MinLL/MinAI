<?php

Function GetFillHerUpContext($name) {
    $ret = "";
    
    // Get inflation ranks (0-101 percentage values)
    $inflateRank = GetActorValue($name, "inflateRank");
    $inflateOralRank = GetActorValue($name, "inflateOralRank");
    $impregnatedRank = GetActorValue($name, "impregnatedRank");
    $impregnatedAnalRank = GetActorValue($name, "impregnatedAnalRank");

    // Get cum amounts
    $cumVaginal = GetActorValue($name, "cumVaginal");
    $cumAnal = GetActorValue($name, "cumAnal");
    $cumOral = GetActorValue($name, "cumOral");

    // Skip if no relevant states
    if (empty($inflateRank) && empty($inflateOralRank) && 
        empty($impregnatedRank) && empty($impregnatedAnalRank) &&
        empty($cumVaginal) && empty($cumAnal) && empty($cumOral)) {
        return $ret;
    }

    // Describe belly inflation states (percentage based)
    if ($inflateRank > 0 || (!empty($cumVaginal) && $cumVaginal > 0)) {
        if ($inflateRank > 75) {
            $ret .= "{$name}'s belly is massively swollen and heavily distended with semen";
            if (!empty($cumVaginal)) {
                $ret .= " (" . number_format($cumVaginal, 1) . " units)";
            }
            $ret .= ".\n";
        } elseif ($inflateRank > 50) {
            $ret .= "{$name}'s belly is very swollen and visibly distended from being filled with cum";
            if (!empty($cumVaginal)) {
                $ret .= " (" . number_format($cumVaginal, 1) . " units)";
            }
            $ret .= ".\n";
        } elseif ($inflateRank > 25) {
            $ret .= "{$name}'s belly is noticeably swollen with seed";
            if (!empty($cumVaginal)) {
                $ret .= " (" . number_format($cumVaginal, 1) . " units)";
            }
            $ret .= ".\n";
        } else {
            // Only show slight belly swelling if cum amount is 1 or greater
            if (empty($cumVaginal) || $cumVaginal >= 1) {
                $ret .= "{$name}'s belly appears slightly swollen from cum";
                if (!empty($cumVaginal)) {
                    $ret .= " (" . number_format($cumVaginal, 1) . " units)";
                }
                $ret .= ".\n";
            }
        }
    }

    // Describe anal inflation if present
    if (!empty($cumAnal) && $cumAnal > 0) {
        $ret .= "{$name}'s rear contains " . number_format($cumAnal, 1) . " units of cum.\n";
    }

    // Describe oral inflation states (percentage based)
    if ($inflateOralRank > 0 || (!empty($cumOral) && $cumOral > 0)) {
        if ($inflateOralRank > 75) {
            $ret .= "{$name}'s throat and chest are extremely bulging and distended with thick semen";
            if (!empty($cumOral)) {
                $ret .= " (" . number_format($cumOral, 1) . " units)";
            }
            $ret .= ".\n";
        } elseif ($inflateOralRank > 50) {
            $ret .= "{$name}'s throat and chest are visibly bulging with cum";
            if (!empty($cumOral)) {
                $ret .= " (" . number_format($cumOral, 1) . " units)";
            }
            $ret .= ".\n";
        } elseif ($inflateOralRank > 25) {
            $ret .= "{$name}'s throat appears noticeably full of seed";
            if (!empty($cumOral)) {
                $ret .= " (" . number_format($cumOral, 1) . " units)";
            }
            $ret .= ".\n";
        } else {
            // Only show slight oral fullness if cum amount is 1 or greater
            if (empty($cumOral) || $cumOral >= 1) {
                $ret .= "{$name}'s throat seems somewhat full of cum";
                if (!empty($cumOral)) {
                    $ret .= " (" . number_format($cumOral, 1) . " units)";
                }
                $ret .= ".\n";
            }
        }
    }

    // Describe impregnation states (binary states)
    if ($impregnatedRank > 0) {
        $ret .= "{$name} has recently been inseminated vaginally.\n";
    }

    if ($impregnatedAnalRank > 0) {
        $ret .= "{$name} has recently been inseminated anally.\n";
    }

    if ($ret != "") {
        $ret .= "\n";
    }
    return $ret;
} 