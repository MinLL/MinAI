<?php
require_once(dirname(__FILE__) . "/info_device_utils.php");
require_once(dirname(__FILE__) . "/info_device_base.php");

class CollarHandler extends DeviceEventHandler {
    public function __construct() {
        parent::__construct("collar");
    }
    
    protected function getFormattedType() {
        return "Collar";
    }
    
    protected $equipDescriptions = [
        "is carefully secured",
        "is firmly locked",
        "is methodically fastened",
        "is deliberately attached"
    ];
    
    protected $removeDescriptions = [
        "is carefully unlocked",
        "is gently removed",
        "is methodically unfastened",
        "is deliberately detached"
    ];
    
    protected function getLowArousalReactions($isEquip) {
        if ($isEquip) {
            return [
                "causing them to shift slightly as they adjust to its presence",
                "drawing a soft intake of breath as the metal settles against their skin",
                "making them straighten instinctively as they feel its weight",
                "eliciting a quiet 'oh' as they test its snug fit"
            ];
        } else {
            return [
                "eliciting a soft sigh of relief as the weight lifts from their neck",
                "drawing a gentle exhale as they feel the freedom of movement",
                "causing them to roll their shoulders experimentally once freed",
                "prompting a quiet 'thank you' as they touch their neck"
            ];
        }
    }
    
    protected function getLowArousalGaggedReactions($isEquip) {
        if ($isEquip) {
            return [
                "causing them to shift slightly as they adjust to its presence, a muffled sound of acknowledgment escaping their gag",
                "drawing a soft, muffled intake of breath as the metal settles against their skin",
                "making them straighten instinctively as they feel its weight around their neck",
                "eliciting a quiet, stifled sound as they test its snug fit"
            ];
        } else {
            return [
                "eliciting a muffled sigh of relief as the weight lifts from their neck",
                "drawing a soft sound through their gag as they feel the freedom of movement",
                "causing them to roll their shoulders experimentally once freed",
                "prompting a quiet, stifled sound of appreciation"
            ];
        }
    }
    
    protected function getMediumArousalReactions($isEquip) {
        if ($isEquip) {
            return [
                "their breath catching slightly as it locks into place",
                "causing them to squirm slightly, their breathing quickening",
                "drawing a soft whimper of anticipation as they feel it secure around their neck",
                "making them shift restlessly, their arousal becoming evident"
            ];
        } else {
            return [
                "their soft sounds mixing relief and a hint of disappointment",
                "causing them to shift restlessly, their expression showing conflicted feelings",
                "drawing a complex series of emotions across their face",
                "making them touch their neck tentatively, as if missing its presence"
            ];
        }
    }
    
    protected function getMediumArousalGaggedReactions($isEquip) {
        if ($isEquip) {
            return [
                "their muffled sounds betraying a mix of nervousness and excitement as it locks into place",
                "causing them to squirm slightly, their gagged breaths coming quicker",
                "drawing a stifled whimper of anticipation as they feel it secure around their neck",
                "making them shift restlessly, muffled sounds suggesting their growing arousal"
            ];
        } else {
            return [
                "their muffled sounds mixing relief and a hint of disappointment",
                "causing them to shift restlessly, stifled sounds suggesting conflicted feelings",
                "drawing a complex series of emotions across their face despite the gag",
                "making them touch their neck tentatively, a questioning sound escaping"
            ];
        }
    }
    
    protected function getHighArousalReactions($isEquip) {
        if ($isEquip) {
            return [
                "their soft moan clearly revealing their arousal as the collar tightens",
                "causing them to squirm needfully, a whimper of pleasure escaping",
                "drawing a shaky gasp as they feel its controlling presence",
                "making them tremble with obvious excitement"
            ];
        } else {
            return [
                "their soft whimper suggesting they'll miss its controlling presence",
                "causing them to squirm needfully, clearly affected by its removal",
                "drawing a shaky breath that betrays their aroused state",
                "making them tremble slightly as the symbol of control is removed"
            ];
        }
    }
    
    protected function getHighArousalGaggedReactions($isEquip) {
        if ($isEquip) {
            return [
                "their muffled moans clearly revealing their arousal as the collar tightens",
                "causing them to squirm needfully, stifled sounds of pleasure escaping their gag",
                "drawing urgent, muffled sounds as they feel its controlling presence",
                "making them tremble with obvious excitement despite their restraints"
            ];
        } else {
            return [
                "their muffled whimper suggesting they'll miss its controlling presence",
                "causing them to squirm needfully, clearly affected by its removal",
                "drawing urgent sounds that betray their aroused state",
                "making them tremble slightly as the symbol of control is removed"
            ];
        }
    }
    
    protected function getVeryHighArousalReactions($isEquip) {
        if ($isEquip) {
            return [
                "their desperate moan betraying their intense arousal as it locks in place",
                "causing them to writhe with need, their breathing becoming ragged",
                "drawing a throaty whimper as they feel its dominance",
                "making them shudder with barely-contained desire"
            ];
        } else {
            return [
                "their desperate whimper suggesting they crave the control it represented",
                "causing them to writhe with unfulfilled need as it's removed",
                "drawing shaky breaths that reveal their desperate arousal",
                "making them shudder with intense, conflicting sensations"
            ];
        }
    }
    
    protected function getVeryHighArousalGaggedReactions($isEquip) {
        if ($isEquip) {
            return [
                "their desperate, muffled moans betraying their intense arousal as it locks in place",
                "causing them to writhe with need, their stifled sounds becoming increasingly urgent",
                "drawing frantic sounds from behind their gag as they feel its dominance",
                "making them shudder with barely-contained desire despite their restraints"
            ];
        } else {
            return [
                "their desperate sounds suggesting they crave the control it represented",
                "causing them to writhe with unfulfilled need as it's removed",
                "drawing frantic muffled sounds that reveal their desperate arousal",
                "making them shudder with intense, conflicting sensations"
            ];
        }
    }
}

// Register the events
$handler = new CollarHandler();
RegisterDeviceEvents("collar", $handler); 