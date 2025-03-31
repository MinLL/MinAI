Scriptname BakaTrapMimic extends BakaMovingTrap  

import debug
import utility
int Mimichitcount
int Property MimicHealth = 3 Auto
int Property MimicType = 2 Auto
{0 = Attack, 1 = VoreSimple, 2 = VoreSex, 3 = VoreInstant}
Actor Property actorref Auto Hidden
bool property FemaleOnlyTrigger = True Auto
bool property playerOnlyTrigger = True Auto
float property initialDelay = 0.25 auto
bool LockPosition = false
bool btrapDisarmed = false
bool RemoveHeel

string property startDamage = "startDamage" auto hidden
string property stopDamage = "stopDamage" auto hidden

Faction Property MimicVoreDefaultFaction Auto;XX1EBFB1
Keyword Property MimicPosKeyword Auto
Keyword Property MimicDispenseKeyword Auto
ObjectReference PosXmarker
ObjectReference DispenseXmarker

bool property StartOpen = True Auto
;bool property QTE = false Auto
{default == true}
globalVariable property LightFootTriggerPercent auto
actor property playerRef Auto

idle property MimicVoreStart auto
idle property MimicVoreLoop auto
idle property MimicVoreEndFail auto
idle property MimicVoreEndSuccess auto
idle property MimicVoreEndSuccessDefault auto
idle property MimicVoreEndSuccessLoop auto
idle property MimicVoreStage02Preface auto
idle property MimicVoreStage02Start auto
idle property MimicVoreStage02Loop auto
idle property MimicVoreStage02EndPreface auto
idle property MimicVoreStage02Fail auto
idle property MimicVoreStage02Success auto
idle property MimicVoreStage02SuccessLoop auto
idle property MimicVoreStage02Finale auto
idle property MimicVoreIdle auto
idle property MimicVoreSpit auto
idle property MimicVoreGetUpAfterSpit auto
idle property MimicVoreInstant auto

Package Property TNTRDoNothing auto
Quest Property TNTRController Auto; as TNTRControllerScript

int Xaxis
int Yaxis

Float AngleZ
Float rMoveX
Float rMoveY
Float rMoveZ

Form[] wornForms

Function SendTNTREvent(Actor akTarget, string asEventName)
	Debug.Trace("Mimic sending TNTR event: " + asEventName)
	Int handle = ModEvent.Create("minai_tntr")
	ModEvent.PushForm(handle, akTarget)
	ModEvent.PushString(handle, "Mimic")
	ModEvent.PushString(handle, asEventName)
	ModEvent.Send(handle)

EndFunction

;TriggerVoreStart - Mimic Start

Function ResetTrap()
	ResetCoordinates(actorref)
	actorref.removefromfaction(MimicVoreDefaultFaction)
	if isLoaded
		isFiring = false
		;btrapDisarmed = false
		(GetNthLinkedRef(1) as BakaTrapTriggerBox).ResetTrigger()
		goToState("Ready")
	endif
EndFunction

Function MimicTest()

EndFunction


Function Getnaked(Actor target, Bool OnlyBody = False)
wornForms = new Form[30]
int index = wornForms.Length
int slotsChecked
slotsChecked += 0x00000002;Hair
slotsChecked += 0x00000800;LongHair
slotsChecked += 0x00002000;LongHair
slotsChecked += 0x00100000
slotsChecked += 0x00200000 ;ignore reserved slots
slotsChecked += 0x80000000
if OnlyBody
	slotsChecked += 0x00000008
	slotsChecked += 0x00000010
	slotsChecked += 0x00000020
	slotsChecked += 0x00000040
	slotsChecked += 0x00000080
	slotsChecked += 0x00000080
	slotsChecked += 0x00000100
	slotsChecked += 0x00000400
	slotsChecked += 0x00001000
endif

int thisSlot = 0x01
	while (thisSlot < 0x80000000)
	if (Math.LogicalAnd(slotsChecked, thisSlot) != thisSlot) ;only check slots we haven't found anything equipped on already
	Armor thisArmor = target.GetWornForm(thisSlot) as Armor
		if (thisArmor)
			Index -= 1
			wornForms[Index] = thisArmor
			Target.UnequipItem(thisArmor)
		endif
	endif
		thisSlot *= 2 ;double the number to move on to the next slot
	endWhile
EndFunction

Function DispenseArmor()
	;DispenseXmarker.MoveToNode(Self, "4_Mimic_Sucker_Projectile")
	DispenseXmarker.MoveToNode(Self, "4_Mimic_Sucker14")
	int index = wornForms.Length
	;Debug.notification("Mimic Fomr " + index)
	While index
		index -= 1
		actorref.DropObject(wornForms[Index])
	EndWhile
EndFunction

int function acceptableTrigger(objectReference triggerRef)
;0 = Shake
;1 = Attack
;2 = Swallow
;3 = InstantSwallow

; 	debug.Trace(self + " is checking if " + triggerRef + " is an acceptable trigger")
	if playerOnlyTrigger
		if triggerRef == PlayerRef
			actorref = triggerRef as actor
			if FemaleOnlyTrigger && (triggerRef as actor).getactorbase().getsex() == 1
				return 2
			elseif FemaleOnlyTrigger && (triggerRef as actor).getactorbase().getsex() == 0
				return 1
			else
				return 1
			endif
		Else
			return 0
		endif
	Else
		if (triggerRef as actor)
			actorref = triggerRef as actor
			if FemaleOnlyTrigger && (triggerRef as actor).getactorbase().getsex() == 1
				return 2
			elseif FemaleOnlyTrigger && (triggerRef as actor).getactorbase().getsex() == 0
				return 1
			else
				return 1
			endif
		else
			return 0
		endif
	endif
endFunction

Function ResetCoordinates(Actor akactor)
	if LockPosition
		MuJointFixUtil.ToggleFixes(akactor, 2, true);MuJointFixToggle
		MuJointFixUtil.ToggleFixes(akactor, 4, true)
		MuJointFixUtil.ToggleFixes(akactor, 10, true)
		akactor.SetVehicle(None)
		;Game.EnablePlayerControls()
		If (akactor == PlayerRef)
			Game.SetPlayerAIDriven(false)
			;akactor.SetAnimationVariableBool("bHumanoidFootIKEnable", true)
		endif
		akactor.SetRestrained(False)
		akactor.SetDontMove(False)
		akactor.SetHeadTracking(TRUE)
		ActorUtil.RemovePackageOverride(akactor, TNTRDoNothing)
		LockPosition = false
	endif
EndFunction

Function SetCoordinates(Actor akactor)
		MuJointFixUtil.ToggleFixes(akactor, 2, false);MuJointFixToggle
		MuJointFixUtil.ToggleFixes(akactor, 4, false)
		MuJointFixUtil.ToggleFixes(akactor, 10, false)
		If (akactor == PlayerRef)
			Game.SetPlayerAIDriven(true)
			;akactor.SetDontMove(True)
			ActorUtil.AddPackageOverride(akactor, TNTRDoNothing, 100, 1)
			akactor.EvaluatePackage()
		Else
			ActorUtil.AddPackageOverride(akactor, TNTRDoNothing, 100, 1)
			akactor.EvaluatePackage()
			akactor.SetHeadTracking(true)
			akactor.SetRestrained(true)
			akactor.SetDontMove(True)
		Endif
	;akactor.SetVehicle(Self)
		DispenseXmarker = getLinkedRef(MimicDispenseKeyword)
		PosXmarker = getLinkedRef(MimicPosKeyword);It should be placed y axis -60 of the mimic
		Xaxis = 0
		Yaxis = -60
		Utility.Wait(0.5)
		AngleZ = Self.GetAngleZ()
		rMoveX = (Math.sin(AngleZ) * Yaxis) + (Math.cos(AngleZ) * Xaxis)
		rMoveY = (Math.cos(AngleZ) * Yaxis) - (Math.sin(AngleZ) * Xaxis)
		rMoveZ = -10.0;I have no idea but it seems the actor always gets +10.0 z axis.
		
		PosXmarker.MoveTo(self, rMoveX, rMoveY, rMoveZ)
		PosXmarker.setangle(0, 0, AngleZ)
		
		if PosXmarker
			akactor.SetVehicle(PosXmarker)
			akactor.MoveTo(PosXmarker)
		endif
		LockPosition = true
EndFunction

Function FireVoreInstantTrap()
	isFiring = True
			;play windup sound

	wait( initialDelay )		;wait for windup
	;TRACE("Initial Delay complete")
	
	if (fireOnlyOnce == True)	;If this can be fired only once then disarm
		btrapDisarmed = True
	endif
	SetCoordinates(actorref)
	wait( initialDelay )
	PlayAnimation("TriggerVoreInstant")
	SendTNTREvent(actorref, "TriggerVoreInstant")
	actorref.playidle(MimicVoreInstant)
	WaitForAnimationEvent("TransVoreInstant")
	actorref.playidle(MimicVoreIdle)
	;actorref.moveto(PosXmarker)
	Wait(1.0)
	(GetNthLinkedRef(1) as BakaTrapTriggerBox).DesignateTarget(actorref)
	SuccessInstantVore()
EndFunction

Function FireVoreSimpleTrap()
	isFiring = True
			;play windup sound
	if (TNTRController as TNTRControllerScript).RemoveHeelEffect(actorref)
		RemoveHeel = true
	endif
	wait( initialDelay )		;wait for windup
	;TRACE("Initial Delay complete")
	
	if (fireOnlyOnce == True)	;If this can be fired only once then disarm
		btrapDisarmed = True
	endif
	actorref.addtofaction(MimicVoreDefaultFaction);for oar
	SetCoordinates(actorref)
	wait( initialDelay )
	PlayAnimation("TriggerVoreStart")
	SendTNTREvent(actorref, "TriggerVoreStart")
	actorref.playidle(MimicVoreStart)
	WaitForAnimationEvent("TransVoreStart")
	actorref.playidle(MimicVoreLoop)
	;actorref.moveto(PosXmarker)
	Wait(1.0)
	(GetNthLinkedRef(1) as BakaTrapTriggerBox).DesignateTarget(actorref);QTE
	(GetNthLinkedRef(1) as BakaTrapTriggerBox).FireQTE(self);QTE
EndFunction

Function FireVoreSexTrap()
	isFiring = True
			;play windup sound
	if (TNTRController as TNTRControllerScript).RemoveHeelEffect(actorref)
		RemoveHeel = true
	endif
	wait( initialDelay )		;wait for windup
	;TRACE("Initial Delay complete")
	
	if (fireOnlyOnce == True)	;If this can be fired only once then disarm
		btrapDisarmed = True
	endif
	SetCoordinates(actorref)
	wait( initialDelay )
	PlayAnimation("TriggerVoreStart")
	SendTNTREvent(actorref, "TriggerVoreStart")
	actorref.playidle(MimicVoreStart)
	WaitForAnimationEvent("TransVoreStart")
	actorref.playidle(MimicVoreLoop)
	;actorref.moveto(PosXmarker)
	Wait(1.0)
	(GetNthLinkedRef(1) as BakaTrapTriggerBox).DesignateTarget(actorref);QTE
	(GetNthLinkedRef(1) as BakaTrapTriggerBox).FireQTE(self);QTE
EndFunction

Function FailVore()
	PlayAnimation("TransVoreLooptoFail")
	SendTNTREvent(actorref, "TransVoreLooptoFail")
	actorref.playidle(MimicVoreEndFail)
	WaitForAnimationEvent("TransVoreEndFail")
	if RemoveHeel
		(TNTRController as TNTRControllerScript).ResetHeelEffect(actorref)
	endif
	ResetTrap()
EndFunction


Function PrefaceVorePhase02()
	PlayAnimation("TransVoreStage02Loop")
	SendTNTREvent(actorref, "TransVoreStage02Loop")
	actorref.playidle(MimicVoreStage02EndPreface)
	WaitForAnimationEvent("TransVoreStage02EndPreface");This goes to VoreInert state again.
	actorref.playidle(MimicVoreIdle)
	actorref.moveto(PosXmarker)
EndFunction

Function FailVorePhase02()
	PrefaceVorePhase02()
	Wait(5.0)
	PlayAnimation("TriggerVoreStage02Fail")
	SendTNTREvent(actorref, "TriggerVoreStage02Fail")
	actorref.playidle(MimicVoreStage02Fail)
	WaitForAnimationEvent("TransVoreStage02Fail")
	actorref.playidle(MimicVoreIdle)

	Wait(10.0)
	
	PlayAnimation("TriggerVoreSpit")
	SendTNTREvent(actorref, "TriggerVoreSpit")
	actorref.playidle(MimicVoreSpit)
	
	WaitForAnimationEvent("TransPlay")
	
	if RemoveHeel
		(TNTRController as TNTRControllerScript).ResetHeelEffect(actorref)
	endif
	Wait(10.0)
	actorref.playidle(MimicVoreGetUpAfterSpit)
	ResetTrap()
EndFunction

Function SuccessVore()
	actorref.moveto(PosXmarker)
	;actorref.MoveTo(self, rMoveX, rMoveY, rMoveZ)
	if actorref.isinfaction(MimicVoreDefaultFaction)
		PlayAnimation("TransVoreLooptoSuccessDefault")
		SendTNTREvent(actorref, "TransVoreLooptoSuccessDefault")
		actorref.playidle(MimicVoreEndSuccessDefault)
		WaitForAnimationEvent("TransVoreEndSuccessDefault")
		actorref.playidle(MimicVoreIdle)
	else
		PlayAnimation("TransVoreLooptoSuccess")
		SendTNTREvent(actorref, "TransVoreLooptoSuccess")
		actorref.playidle(MimicVoreEndSuccess)
		WaitForAnimationEvent("TransVoreEndSuccess")
		actorref.playidle(MimicVoreEndSuccessLoop)
		; (TNTRController as TNTRControllerScript).InflationEventcustom(actorref, 2, 10.0)
		Wait(20.0)
		PlayAnimation("TransVoreEndSuccessLoop")
		SendTNTREvent(actorref, "TransVoreEndSuccessLoop")
		actorref.playidle(MimicVoreStage02Preface)
		WaitForAnimationEvent("TransVoreStage02Preface");This goes to VoreInert state
		actorref.playidle(MimicVoreIdle)

	endif


;------------------------VoreInert State-------------------------------
;This is where Mimic completely gulped the actor and closed the lid.
;You can put QTE event here along with Mimicshake animation.
;I'll just skip another QTE round this time.
;actorref.MoveTo(self, rMoveX, rMoveY, rMoveZ)
actorref.moveto(PosXmarker)
Wait(7.0)
playAnimationAndWait("TriggerMimicShake","TransMimicShake01")
SendTNTREvent(actorref, "TriggerMimicShake")
Getnaked(actorref, true)
Wait(3.0)
playAnimationAndWait("TriggerMimicShake","TransMimicShake01")
SendTNTREvent(actorref, "TriggerMimicShake")
;unequip event
Wait(7.0)
;actorref.MoveTo(self, rMoveX, rMoveY, rMoveZ)
actorref.moveto(PosXmarker)
;---------------------------------------------------------------

	PlayAnimation("TriggerMimicThrowup")
	SendTNTREvent(actorref, "TriggerMimicThrowup")
	WaitForAnimationEvent("EventMimicThrowup")
	DispenseArmor()
	WaitForAnimationEvent("TransMimicThrowup")
	
	if actorref.isinfaction(MimicVoreDefaultFaction)
		playAnimationAndWait("TriggerMimicBurp","TransMimicBurp")
		SendTNTREvent(actorref, "TriggerMimicBurp")
		PlayAnimation("TriggerVoreSpit")
		SendTNTREvent(actorref, "TriggerVoreSpit")
		actorref.playidle(MimicVoreSpit)
		WaitForAnimationEvent("TransPlay")
		if RemoveHeel
			(TNTRController as TNTRControllerScript).ResetHeelEffect(actorref)
		endif
		ResetTrap()
	else
		PlayAnimation("TriggerVoreStage02Start")
		SendTNTREvent(actorref, "TriggerVoreStage02Start")
		actorref.playidle(MimicVoreStage02Start)
		WaitForAnimationEvent("TransVoreStage02Start")
		actorref.playidle(MimicVoreStage02Loop)
		
		actorref.moveto(PosXmarker)
		;actorref.MoveTo(self, rMoveX, rMoveY, rMoveZ)
		;(GetNthLinkedRef(1) as BakaTrapTriggerBox).DesignateTarget(actorref);QTE
		(GetNthLinkedRef(1) as BakaTrapTriggerBox).FireVorePhase02(self);QTE
	endif
EndFunction

Function SuccessInstantVore()

	(TNTRController as TNTRControllerScript).InflationEventcustom(actorref, 2, 10.0)
	Wait(7.0)
	playAnimationAndWait("TriggerMimicShake","TransMimicShake01")
	SendTNTREvent(actorref, "TriggerMimicShake")
	Wait(3.0)
	playAnimationAndWait("TriggerMimicShake","TransMimicShake01")
	SendTNTREvent(actorref, "TriggerMimicShake")
	;unequip event
	Wait(7.0)

	PlayAnimation("TriggerVoreStage02Start")
	SendTNTREvent(actorref, "TriggerVoreStage02Start")
	actorref.playidle(MimicVoreStage02Start)
	WaitForAnimationEvent("TransVoreStage02Start")
	actorref.playidle(MimicVoreStage02Loop)
	
	actorref.moveto(PosXmarker)
	;actorref.MoveTo(self, rMoveX, rMoveY, rMoveZ)
	;(GetNthLinkedRef(1) as BakaTrapTriggerBox).DesignateTarget(actorref);QTE
	(GetNthLinkedRef(1) as BakaTrapTriggerBox).FireVorePhase02(self);QTE

EndFunction

Function SuccessVorePhase02()
	PrefaceVorePhase02()
	Wait(5.0)
	PlayAnimation("TriggerVoreStage02Success")
	SendTNTREvent(actorref, "TriggerVoreStage02Success")
	actorref.playidle(MimicVoreStage02Success)
	WaitForAnimationEvent("TransVoreStage02Success")
	actorref.playidle(MimicVoreStage02SuccessLoop)
	
	Wait(50.0)
	actorref.moveto(PosXmarker)
	;actorref.MoveTo(self, rMoveX, rMoveY, rMoveZ)
	Wait(10.0)
	
	PlayAnimation("TransVoreStage02SuccessLoop")
	SendTNTREvent(actorref, "TransVoreStage02SuccessLoop")
	actorref.playidle(MimicVoreStage02Finale)
	WaitForAnimationEvent("TransVoreStage02Finale")
	actorref.playidle(MimicVoreIdle)
	(TNTRController as TNTRControllerScript).InflationEventcustom(actorref, 1, 15.0)

	Wait(10.0)
	
	PlayAnimation("TriggerVoreSpit")
	SendTNTREvent(actorref, "TriggerVoreSpit")
	actorref.playidle(MimicVoreSpit)
	WaitForAnimationEvent("TransPlay")
	if RemoveHeel
		(TNTRController as TNTRControllerScript).ResetHeelEffect(actorref)
	endif
	ResetTrap()
EndFunction

Event onReset()
	goToState("EndPhase")
	self.reset()
endEvent

event OnTriggerEnter(objectReference TriggerRef)
endEvent
	
event OnActivate(objectReference TriggerRef)
endEvent

Event OnHit(ObjectReference akAggressor, Form akSource, Projectile akProjectile, bool abPowerAttack, bool abSneakAttack, bool abBashAttack, bool abHitBlocked)
EndEvent

event onLoad()
	BakahitBase = (self as objectReference) as BakaTrapHitBase
	ResolveLeveledDamage()
	if StartOpen
		playAnimationAndWait("TriggerMimicShake","TransMimicShake01")
		SendTNTREvent(actorref, "TriggerMimicShake")
		Wait(1.0)
		playAnimation("Reset")
		goToState("Ready")
	endif
endEvent

state EndPhase
	Event OnBeginState()
	endEvent
	
	event OnTriggerEnter(objectReference TriggerRef)
	endEvent
	
	event OnActivate(objectReference TriggerRef)
		playAnimation("Reset")
	endEvent
endState

State Dead
	Event OnBeginState()
	endEvent
	
	event OnTriggerEnter(objectReference TriggerRef)
	endEvent
	
	event OnActivate(objectReference TriggerRef)
		goToState("Open")
	endEvent
endstate

State Open
	Event OnBeginState()
		isfiring = true
		playAnimationAndWait("TriggerOpen","TransOpen")
		SendTNTREvent(actorref, "TriggerOpen")
		;Open Inventory
		isfiring = false
	endEvent
	
	event OnTriggerEnter(objectReference TriggerRef)
	endEvent
	
	event OnActivate(objectReference TriggerRef)
		if !isfiring
			goToState("Close")
		endif
	endEvent
Endstate

state Close
	Event OnBeginState()
		isfiring = true
		playAnimationAndWait("TransOpenIdle","TransClose")
		SendTNTREvent(actorref, "TransOpenIdle")
		isfiring = false
		playAnimation("Reset")
		goToState("Ready");Go to ready state. Recycle the trap
	endEvent
	
	event OnTriggerEnter(objectReference TriggerRef)
	endEvent
	
	event OnActivate(objectReference TriggerRef)
	endEvent
endState

auto State Ready

	Event OnHit(ObjectReference akAggressor, Form akSource, Projectile akProjectile, bool abPowerAttack, bool abSneakAttack, bool abBashAttack, bool abHitBlocked)
		if !isfiring
			isfiring == true
			Mimichitcount += 1
			
			if MimicHealth >= Mimichitcount
				;DeadSound WIP
				playAnimationAndWait("TriggerDie","TransDie01")
				SendTNTREvent(actorref, "TriggerDie")
				goToState("Dead")
			else
				playAnimationAndWait("TriggerAttack","TransPlay")
				SendTNTREvent(actorref, "TriggerAttack")
				;HitSound WIP
			endif
			isfiring = false
		endif
		
	EndEvent

	Event OnBeginState()
		isfiring = false
		PlayAnimation("Reset")
	endEvent
	
	event OnTriggerEnter(objectReference TriggerRef)
	endEvent

	event OnActivate(objectReference TriggerRef)
	int itrigger
	if !isfiring
		if MimicType == 2
			itrigger = acceptableTrigger(TriggerRef)
		else
			itrigger = MimicType
			actorref = triggerRef as actor
		endif
		if itrigger == 1
			if btrapDisarmed
				goToState("ShakeBusy")
			else
				goToState("VoreStartDefaultState")
			endif
		elseif itrigger == 2
			if btrapDisarmed
				goToState("AttackBusy")
			else
				goToState("VoreStartState")
			endif
		elseif itrigger == 3
			if btrapDisarmed
				goToState("AttackBusy")
			else
				goToState("VoreInstantState")
			endif
		else
			goToState("AttackBusy")
		endif
	endif
	endEvent

	Function MimicTest()
		int itrigger = acceptableTrigger(PlayerRef)
			if itrigger == 1
				goToState("ShakeBusy")
			elseif itrigger == 2
				if btrapDisarmed
					goToState("AttackBusy")
				else
					goToState("VoreStartState")
				endif
			elseif itrigger == 3
				goToState("AttackBusy")
			endif
	EndFunction

EndState

state VoreStartDefaultState
	Event OnBeginState()
		FireVoreSimpleTrap()
	endEvent

	event OnTriggerEnter(objectReference TriggerRef)
	endEvent
	
	event OnActivate(objectReference TriggerRef);WIP
	endEvent
endState


state VoreStartState
	Event OnBeginState()
		FireVoreSexTrap()
	endEvent

	event OnTriggerEnter(objectReference TriggerRef)
	endEvent
	
	event OnActivate(objectReference TriggerRef);WIP
	endEvent
endState

state VoreInstantState
	Event OnBeginState()
		FireVoreInstantTrap()
	endEvent

	event OnTriggerEnter(objectReference TriggerRef)
	endEvent
	
	event OnActivate(objectReference TriggerRef);WIP
	endEvent
endState

State Busy	;Dummy state to prevent interaction while animating
	Event OnBeginState()
	endEvent
	event OnActivate(objectReference TriggerRef)
	endevent
EndState

State AttackBusy
	Event OnBeginState()
		;playAnimationAndWait("TriggerAttack","TransPlay")
		PlayAnimation("TriggerAttack")
		;WaitForAnimationEvent(startDamage)
		BakahitBase.goToState("CanHit")
		;finishedPlaying = True
		WaitForAnimationEvent(stopDamage)
		BakahitBase.goToState("CannotHit")
		WaitForAnimationEvent("TransPlay")
		goToState("Ready")
	endEvent
	event OnActivate(objectReference TriggerRef)
	endevent
EndState

State ShakeBusy
	Event OnBeginState()
		playAnimationAndWait("TriggerMimicShake","TransMimicShake01")
		SendTNTREvent(actorref, "TriggerMimicShake")
		Utility.wait(0.1)
		playAnimation("Reset")
		goToState("Ready")
	endEvent
	event OnActivate(objectReference TriggerRef)
	endevent
EndState

;==========================================================

Function ResolveLeveledDamage()
	int damageLevel
	int damage
	damageLevel = CalculateEncounterLevel(TrapLevel)
	
	damage = LvlDamage1
	
	if (damageLevel > LvlThreshold1 && damageLevel <= LvlThreshold2)
		damage = LvlDamage2
		;Trace("damage threshold =")
		;Trace("2")
	endif
	if (damageLevel > LvlThreshold2 && damageLevel <= LvlThreshold3)
		damage = LvlDamage3
		;Trace("damage threshold =")
		;Trace("3")
	endif
	if (damageLevel > LvlThreshold3 && damageLevel <= LvlThreshold4)
		damage = LvlDamage4
		;Trace("damage threshold =")
		;Trace("4")
	endif
	if (damageLevel > LvlThreshold4 && damageLevel <= LvlThreshold5)
		damage = LvlDamage5
		;Trace("damage threshold =")
		;Trace("5")
	endif
	if (damageLevel > LvlThreshold5)
		damage = LvlDamage6
		;Trace("damage threshold =")
		;Trace("6")
	endif
	
	;Trace("damage =")
	;Trace(damage)
	
	;return damage
	hitBase.damage = damage
EndFunction
