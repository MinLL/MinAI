Scriptname BakaTrapDeathWormVore extends BakaMovingTrap

import debug
import utility
	
;If Loop is true, it swings until activated again.
;If Loop is false, it swings once when activated.
bool restartLooping = false
bool finishedPlaying = false
bool VoreFail = false
bool LockPosition = false
float property initialDelay = 0.25 auto
bool property selfTrigger = true auto
{If this is true, then the trap will trigger when you enter the baked in trigger volume
	Default = TRUE}
	
bool RemoveHeel
string property startDamage = "startDamage" auto hidden
string property stopDamage = "stopDamage" auto hidden

Keyword Property DeathWormVorePosKeyword Auto
Keyword Property DeathWormVoreExitKeyword Auto

Package Property TNTRDoNothing auto

actor Property lastActivateActorRef auto
actor Property playerref auto

Idle property DeathWormVoreInstant auto
Idle property DeathWormVoreStart auto
Idle property DeathWormVoreLoop auto

Idle property DeathWormVoreFail auto

Idle property DeathWormVoreSuccess auto
Idle property DeathWormVoreSuccessLoop auto
Idle property DeathWormVoreSuccessAfter auto
;Idle property DeathWormVoreSuccessAfterLoop auto
Idle property DeathWormVoreRecover auto

;Sound Property BakaTrapDeathWormVoreSurge auto
;Sound Property BakaTrapDeathWormSurge auto
Sound Property BakaTrapDeathWormRoar auto
;Sound Property BakaTrapDeathWormVoreDevour auto

Sound Property BakaTrapDeathWormVoreDevourIdle auto
;Sound Property BakaTrapDeathWormVoreDevourSlurp auto

Quest Property TNTRController Auto; as TNTRControllerScript

ImpactDataSet Property TNTRDeathwormRiseDustImpactSet Auto
{Impact data set to use for the tailstomp}

;-----------------------------------
;-----------Animation Events--------
;-----------------------------------
;Please note that you have to take steps to play specific animation. That's called sequence animation.
;However unlike sequence animation, global animation can be triggered wherever the sequence level is currently in.


;"TNTRReset" Global Reset Trigger. It makes the trap to reset immediately regardless of sequence level
;"Trigger01" -> NormalAttack(It automatically reverts to the initiate state) / "Trigger02" -> VoreLoop / "Trigger03" -> InstantVore

;"Trigger02" -> VoreLoop level, "Trigger02Gulp" -> Deathworm Swallows the actor(Loop)
;When Deathworm swallow Loop, "Trans02GulpDone" -> Release the victim

;"Trigger02" -> VoreLoop level, "Trigger02Spit" -> Deathworm goes to inert state.

;"Trigger03" -> The trap devours the actor instantly. "Reset" -> the initiate state

;When Deathworm inert level,


;-----------------------------------
;-----------------------------------
;-----------------------------------

Function DesignateTarget(actor akactor)
	lastActivateActorRef = akactor
EndFunction

Function SetCoordinates(Actor akactor)
	(TNTRController as TNTRControllerScript).SetCoordinates(akactor, self, 2)
		MuJointFixUtil.ToggleFixes(akactor, 2, false);MuJointFixToggle
		MuJointFixUtil.ToggleFixes(akactor, 4, false)
		MuJointFixUtil.ToggleFixes(akactor, 10, false)
		ObjectReference PosXmarker = getLinkedRef(DeathWormVorePosKeyword)
		if PosXmarker
			Float AngleZ = akactor.GetAngleZ()
			self.MoveTo(akactor, 0.0 * Math.Sin(AngleZ), 0.0 * Math.Cos(AngleZ))
			PosXmarker.MoveTo(self, 0.0, 0.0, 0.0, true) ; PosRef
			akactor.SetAngle(PosXmarker.GetAngleX(), PosXmarker.GetAngleY(), PosXmarker.GetAngleZ())
			akactor.SplineTranslateToRef(PosXmarker, 1.3, 150.0)
			akactor.SetVehicle(PosXmarker)
			self.MoveTo(akactor)
		endif
		;Game.EnablePlayerControls(true, false, false, false, false, false, false, false) ; To display the hud
EndFunction

Function ResetCoordinates(Actor akactor)
	(TNTRController as TNTRControllerScript).ResetCoordinates(akactor, false)
	MuJointFixUtil.ToggleFixes(akactor, 2, true);MuJointFixToggle
	MuJointFixUtil.ToggleFixes(akactor, 4, true)
	MuJointFixUtil.ToggleFixes(akactor, 10, true)
EndFunction

Function SendTNTREvent(Actor akTarget, string asEventName)
	Debug.Trace("DeathWormVore sending TNTR event: " + asEventName)
	Int handle = ModEvent.Create("minai_tntr")
	ModEvent.PushForm(handle, akTarget)
	ModEvent.PushString(handle, "DeathWormVore")
	ModEvent.PushString(handle, asEventName)
	ModEvent.Send(handle)
EndFunction

Function FireVoreSexTrap()
	isFiring = True
			;play windup sound
	if (TNTRController as TNTRControllerScript).RemoveHeelEffect(lastActivateActorRef)
		RemoveHeel = true
	endif
	wait( initialDelay )		;wait for windup
	;TRACE("Initial Delay complete")
	
	if (fireOnlyOnce == True)	;If this can be fired only once then disarm
		trapDisarmed = True
	endif
	
	SetCoordinates(lastActivateActorRef)
	wait( initialDelay )
	;BakaTrapDeathWormVoreSurge.play(self as ObjectReference)
	PlayAnimation("Trigger02")
	SendTNTREvent(lastActivateActorRef, "Trigger02")
	lastActivateActorRef.playidle(DeathWormVoreStart)
	PlayImpactEffect(TNTRDeathwormRiseDustImpactSet, "WormRootBone", 0, 0, -1, 512)
	WaitForAnimationEvent("Trans02")
	lastActivateActorRef.playidle(DeathWormVoreLoop)
	self.moveto(lastActivateActorRef)
	Wait(1.0)
	;self.moveto(lastActivateActorRef, 0.0, 0.0, 0.0, false)
	(GetNthLinkedRef(1) as BakaTrapTriggerBox).FireQTE(self);QTE
	
EndFunction

Function FailVore()
	PlayAnimation("Trigger02Spit")
	SendTNTREvent(lastActivateActorRef, "Trigger02Spit")
	lastActivateActorRef.playidle(DeathWormVoreFail)
	WaitForAnimationEvent("Trans02Spit")
	if RemoveHeel
		(TNTRController as TNTRControllerScript).ResetHeelEffect(lastActivateActorRef)
	endif
	ResetTrap()
EndFunction

Function SuccessVore()
	PlayAnimation("Trigger02Gulp")
	SendTNTREvent(lastActivateActorRef, "Trigger02Gulp")
	lastActivateActorRef.playidle(DeathWormVoreSuccess)
	WaitForAnimationEvent("Trans02Gulp")
	lastActivateActorRef.playidle(DeathWormVoreSuccessLoop)
	;No need to play next idle motion for it is sequenced
	(TNTRController as TNTRControllerScript).InflationEvent(lastActivateActorRef)
	
	
	BakaTrapDeathWormVoreDevourIdle.play(self as ObjectReference)
	Wait(5.0);QTE Wait
	BakaTrapDeathWormVoreDevourIdle.play(self as ObjectReference)
	Wait(4.0)
	
	
	PlayAnimation("Trans02GulpDone")
	SendTNTREvent(lastActivateActorRef, "Trans02GulpDone")
	Debug.Sendanimationevent(lastActivateActorRef, "DeathWormVoreSuccessAfter")
	;lastActivateActorRef.playidle(DeathWormVoreSuccessAfter)
	WaitForAnimationEvent("Trans02Done");auto trigger
	Wait(5.0);QTE
	lastActivateActorRef.playidle(DeathWormVoreRecover)
	if RemoveHeel
		(TNTRController as TNTRControllerScript).ResetHeelEffect(lastActivateActorRef)
	endif
	ResetTrap()
EndFunction

Function ResetTrap()
	if isLoaded 	
		isFiring = false
		trapDisarmed = false
		(GetNthLinkedRef(1) as BakaTrapTriggerBox).ResetTrigger()
		goToState("Reset")
	endif
EndFunction

Function FireVoreTrap()
	isFiring = True
	if (TNTRController as TNTRControllerScript).RemoveHeelEffect(lastActivateActorRef)
		RemoveHeel = true
	endif
	SetCoordinates(lastActivateActorRef)
	;TRACE("Initial Delay complete")

	if (fireOnlyOnce == True)	;If this can be fired only once then disarm
		trapDisarmed = True
	endif


;	(self).moveto(lastActivateActorRef)
	wait( initialDelay )		;wait for windup
	;Trap Guts
;	while(finishedPlaying == False && isLoaded == TRUE)
		;BakaTrapDeathWormSurge.play(self as ObjectReference)		;play windup sound
		PlayAnimation("Trigger03")
		SendTNTREvent(lastActivateActorRef, "Trigger03")
		lastActivateActorRef.playidle(DeathWormVoreInstant)
		finishedPlaying = True
		WaitForAnimationEvent("Trans03")
		
		if getLinkedRef(DeathWormVoreExitKeyword)
			if RemoveHeel
				(TNTRController as TNTRControllerScript).ResetHeelEffect(lastActivateActorRef)
			endif
			lastActivateActorRef.moveto(getLinkedRef(DeathWormVoreExitKeyword))
			lastActivateActorRef.kill()
		endif
		wait(0.0)
;	endWhile

		ResetTrap()
EndFunction

Function fireTrap()
	;PlayAnimationAndWait( "reset", "off" )
	;Basic wind up and fire once checking
	;TRACE("fireTrap called")
	ResolveLeveledDamage()
	
	isFiring = True
	;WindupSound.play( self as ObjectReference)		;play windup sound

	wait( initialDelay )		;wait for windup
	;TRACE("Initial Delay complete")
	
	if (fireOnlyOnce == True)	;If this can be fired only once then disarm
		trapDisarmed = True
	endif
	
	;TRACE("Looping =")
	;TRACE(Loop)
	
	;Trap Guts
	while(finishedPlaying == False && isLoaded == TRUE)
		;TRACE("playing anim Single")

		PlayAnimation("Trigger01")
		WaitForAnimationEvent(startDamage)
		BakahitBase.goToState("CanHit")
		finishedPlaying = True
		WaitForAnimationEvent(stopDamage)
		BakahitBase.goToState("CannotHit")
		WaitForAnimationEvent("done")
		if (loop == TRUE)			;Reset Limiter
			resetLimiter()
		endif
		wait(0.0)
	endWhile
	
	ResetTrap()
	
endFunction

Function ResetLimiter()
	finishedPlaying = False
EndFunction

Function CameraShake(ObjectReference akRef)
	Game.ShakeCamera(akRef, 0.8, 2.0)
EndFunction

auto state idle

endstate

State Idleessential;essential
	event Onbeginstate()
		;notification("Idle State")
	endevent

	event onActivate (objectReference activateRef)
; 		;debug.TRACE("Idle trigger")
		;lastActivateActorRef = activateRef as actor
		if (trapDisarmed == False)
			if init == False	;determine if we should initialize
				initialize()
				init = True
			endif
			GoToState ( "DoOnce" )
			ResetLimiter()
			FireTrap()
		endif
	endevent
endstate


State IdleMale
	event Onbeginstate()
		notification("Idlemale State")
	endevent
	Event OnActivate( objectReference activateRef )
		;lastActivateActorRef = activateRef as actor
		if (trapDisarmed == False)
			if init == False	;determine if we should initialize
				initialize()
				init = True
			endif
			GoToState ( "DoOnce" )
			ResetLimiter()
			FireVoreTrap()
		endif
	EndEvent
Endstate

State IdleFemale
	event Onbeginstate()
		notification("Idlefemale State")
	endevent
	Event OnActivate( objectReference activateRef )
		;lastActivateActorRef = activateRef as actor
		if (trapDisarmed == False)
			if init == False	;determine if we should initialize
				initialize()
				init = True
			endif
			GoToState ( "DoOnce" )
			ResetLimiter()
			FireVoreSexTrap()
		endif
	EndEvent
Endstate


State DoOnce
	Event OnActivate( objectReference activateRef )
		;Nothing
	EndEvent
endstate

State Reset
	Event OnBeginState()
		ResetCoordinates(lastActivateActorRef)
		;PlayAnimation("Reset")
		PlayAnimation("TNTRReset");Global reset
		;Debug.SendAnimationEvent(self, "TNTRReset")
		overrideLoop = True
		GoToState ( "Idle" )
		BakahitBase = (self as objectReference) as BakaTrapHitBase
		if BakahitBase
			BakahitBase.goToState("CannotHit")
		endif
	endEvent
	
	Event OnActivate( objectReference activateRef )
		;Nothing
	EndEvent
endState

;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;

faction property owningFaction Auto
actorBase property owningActorBase Auto
bool property playerOnlyTrigger = false auto
bool property checkForLightFootPerk = false Auto
{Setting this will give a chance that the trap trigger will not Fire
	this does not work on triggers with the HOLD trigger type
	Set on by default on all pressure plates
	you should only mess with this locally for triggerboxes}
bool property checkForLightHandsPerk = false Auto
{Setting this will give a chance that the trap trigger will not Fire when lockpicked
	this is used specifically for hinge triggers or other things like that
	set to on by default on the hinge trigger}
perk property lightFootPerk Auto
globalVariable property LightFootGlobalVar auto
perk property lightHandsPerk Auto
globalVariable property LightHandsGlobalVar auto

bool function acceptableTrigger(objectReference triggerRef)

; 	debug.Trace(self + " is checking if " + triggerRef + " is an acceptable trigger")
	if playerOnlyTrigger
		if triggerRef == game.getPlayer()
; 			debug.Trace(self + " has found that " + triggerRef + " is an acceptable trigger")
			if checkPerks(triggerRef)
				Return True
			Else
				Return False
			endif
		Else
; 			debug.Trace(self + " has found that " + triggerRef + " is not an acceptable trigger")
			return False
		endif
	Else
		if !(triggerRef as actor)	;if this is not a player only trigger and this is not an actor
			return True
		elseif owningFaction
			if (triggerRef as actor).IsInFaction(owningFaction)
; 				debug.Trace(self + " has found that " + triggerRef + " is not an acceptable trigger")
				return False
			else
; 				debug.Trace(self + " has found that " + triggerRef + " is an acceptable trigger")
				if checkPerks(triggerRef)
					Return True
				Else
					Return False
				endif
			endif
		else
			if owningActorBase
				if (triggerRef as actor).getActorBase() == owningActorBase
; 					debug.Trace(self + " has found that " + triggerRef + " is not an acceptable trigger")
					return False
				Else
; 					debug.Trace(self + " has found that " + triggerRef + " is an acceptable trigger")
					if checkPerks(triggerRef)
						Return True
					Else
						Return False
					endif
				endif
			else
; 				debug.Trace(self + " has found that " + triggerRef + " is an acceptable trigger")
				if checkPerks(triggerRef)
					Return True
				Else
					Return False
				endif
			endif
		endif
	endif
endFunction

Bool function checkPerks(objectReference triggerRef)
	if checkForLightFootPerk
; 		debug.Trace(self + " is checking if " + triggerRef + " has LightFoot Perk")
		if  (triggerRef as actor).hasPerk(lightFootPerk)
; 			debug.Trace(self + " has found that " + triggerRef + " has LightFoot Perk")
			if utility.randomFloat(0.0,100.00) <= LightFootGlobalVar.getValue()
; 				debug.Trace(self + " is returning false due to failed lightfoot roll")
				return False
			else
; 				debug.Trace(self + " is returning true due to successful lightfoot roll")
				return True
			endif
		Else
; 			debug.Trace(self + " has found that " + triggerRef + " doesn't have the LightFoot Perk")
			Return True
		EndIf
	elseif checkForLightHandsPerk
; 		debug.Trace(self + " is checking if " + triggerRef + " has LightHands Perk")
		if  (triggerRef as actor).hasPerk(lightHandsPerk)
; 			debug.Trace(self + " has found that " + triggerRef + " has LightFoot Perk")
			if utility.randomFloat(0.0,100.00) <= LightHandsGlobalVar.getValue()
; 				debug.Trace(self + " is returning false due to failed lightHands roll")
				return False
			else
; 				debug.Trace(self + " is returning true due to successful lightHands roll")
				return True
			endif
		Else
; 			debug.Trace(self + " has found that " + triggerRef + " doesn't have the LightFoot Perk")
			Return True
		EndIf
	Else
; 		debug.Trace(self + " has found that " + triggerRef + " doesn't have applicable perks")
		return True
	EndIf
endFunction