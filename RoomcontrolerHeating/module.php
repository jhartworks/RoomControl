<?
// Klassendefinition
class RoomcontrolerHeating extends IPSModule {
    // Überschreibt die interne IPS_Create($id) Funktion
    public function Create() {
        // Diese Zeile nicht löschen.
        parent::Create();
        $this->createClockprofile();
        $this->createRoomstateprofile();
        $this->createRoommodeprofile();
     
        $this->RegisterPropertyInteger("PropertyComforttemp",12345);
        $this->RegisterPropertyInteger("PropertyReducetemp",12345);
        $this->RegisterPropertyInteger("PropertyCalctemp",12345);

        SetValue($this->RegisterVariableInteger("Mode", "Modus","Raummodus",0),1);
        $this->EnableAction("Mode");

        SetValue($this->RegisterVariableInteger("State", "Status","Raumstatus",10),1);



        $this->RegisterVariableBoolean("Clock", "Schaltuhrenkanal","Schaltuhr",50);
        $this->EnableAction("Clock");
        SetValue($this->RegisterVariableFloat("Comforttemp", "Komforttemperatur","~Temperature.Room",60),22);
        $this->EnableAction("Comforttemp");
        SetValue($this->RegisterVariableFloat("Reducetemp", "Absenktemperatur","~Temperature.Room",70),17);
        $this->EnableAction("Reducetemp");

        $this->createSchedule();
        $this->RegisterTimer("Update", 0, 'RCH_controlRoom('.$this->InstanceID.');');
    }

    // Überschreibt die intere IPS_ApplyChanges($id) Funktion
    public function ApplyChanges() {
        // Diese Zeile nicht löschen
        parent::ApplyChanges();

        $this->SetTimerInterval("Update", 10 * 1000);

    }
    /**
    * Die folgenden Funktionen stehen automatisch zur Verfügung, wenn das Modul über die "Module Control" eingefügt wurden.
    * Die Funktionen werden, mit dem selbst eingerichteten Prefix, in PHP und JSON-RPC wiefolgt zur Verfügung gestellt:
    *
    *
    */
    public function createClockprofile(){
        if(!IPS_VariableProfileExists ("Schaltuhr") ){

            IPS_CreateVariableProfile("Schaltuhr", 0);
            IPS_SetVariableProfileAssociation("Schaltuhr", true, "Schaltuhr EIN", "", 0x00FF00);
            IPS_SetVariableProfileAssociation("Schaltuhr", false, "Schaltuhr AUS", "", 0xFF0000);

        }
    }

    public function createRoomstateprofile(){
        if(!IPS_VariableProfileExists ("Raumstatus") ){

            IPS_CreateVariableProfile("Raumstatus", 1);
            IPS_SetVariableProfileDigits ("Raumstatus", 0);
            IPS_SetVariableProfileValues("Raumstatus", 1, 30, 0);
            IPS_SetVariableProfileAssociation("Raumstatus", 5, "Frostschutz", "", 0xFF0000);
            IPS_SetVariableProfileAssociation("Raumstatus", 10, "Ferien", "", 0xFFFF00);
            IPS_SetVariableProfileAssociation("Raumstatus", 20, "Absenkbetrieb", "", -1);
            IPS_SetVariableProfileAssociation("Raumstatus", 30, "Komfortbetrieb", "", -1);

        }
    }
    public function createRoommodeprofile(){
        if(!IPS_VariableProfileExists ("Raummodus") ){

            IPS_CreateVariableProfile("Raummodus", 1);
            IPS_SetVariableProfileDigits ("Raummodus", 0);
            IPS_SetVariableProfileValues("Raummodus", 1, 30, 0);
            IPS_SetVariableProfileAssociation("Raummodus", 1, "Automatik", "", -1);
            IPS_SetVariableProfileAssociation("Raummodus", 5, "Frostschutz", "", 0xFF0000);
            IPS_SetVariableProfileAssociation("Raummodus", 10, "Ferien", "", 0xFFFF00);
            IPS_SetVariableProfileAssociation("Raummodus", 20, "DAUER Absenkbetrieb", "", 0xFFFF00);
            IPS_SetVariableProfileAssociation("Raummodus", 30, "DAUER Komfortbetrieb", "", 0xFFFF00);

        }
    }
    public function RequestAction($Ident, $Value) {
                $varid = $this->GetIDForIdent($Ident);
                SetValue($varid, $Value);

                
                $this->controlRoom();
    }
    public function createSchedule(){
        if (!@IPS_GetEventIDByName("Schaltuhr",$this->InstanceID)){
            $EventID= IPS_CreateEvent(2);
            IPS_SetName($EventID,"Schaltuhr");
            IPS_SetParent($EventID,$this->InstanceID);
            IPS_SetPosition($EventID,40);
            IPS_SetEventActive($EventID, true);  // Activates the event
            $clockid = $this->GetIDForIdent("Clock");
            IPS_SetEventScheduleAction($EventID, 2, "Uhr AUS", 16731202, "RequestAction(".$clockid.",false);");
            IPS_SetEventScheduleAction($EventID, 1, "Uhr EIN", 10020520, "RequestAction(".$clockid.",true);");
            IPS_SetEventScheduleGroup($EventID, 0, 1);
            IPS_SetEventScheduleGroup($EventID, 1, 2);
            IPS_SetEventScheduleGroup($EventID, 2, 4);
            IPS_SetEventScheduleGroup($EventID, 3, 8);
            IPS_SetEventScheduleGroup($EventID, 4, 16);
            IPS_SetEventScheduleGroup($EventID, 5, 32);
            IPS_SetEventScheduleGroup($EventID, 6, 64);
            
            IPS_SetEventScheduleGroupPoint($EventID, 0, 0, 0, 0, 0, 2);
            IPS_SetEventScheduleGroupPoint($EventID, 0, 1, 8, 0, 0, 1);
            IPS_SetEventScheduleGroupPoint($EventID, 0, 2, 20, 0, 0, 2);
            
            IPS_SetEventScheduleGroupPoint($EventID, 1, 0, 0, 0, 0, 2);
            IPS_SetEventScheduleGroupPoint($EventID, 1, 1, 8, 0, 0, 1);
            IPS_SetEventScheduleGroupPoint($EventID, 1, 2, 20, 0, 0, 2);
            
            IPS_SetEventScheduleGroupPoint($EventID, 2, 0, 0, 0, 0, 2);
            IPS_SetEventScheduleGroupPoint($EventID, 2, 1, 8, 0, 0, 1);
            IPS_SetEventScheduleGroupPoint($EventID, 2, 2, 20, 0, 0, 2);
            
            IPS_SetEventScheduleGroupPoint($EventID, 3, 0, 0, 0, 0, 2);
            IPS_SetEventScheduleGroupPoint($EventID, 3, 1, 8, 0, 0, 1);
            IPS_SetEventScheduleGroupPoint($EventID, 3, 2, 20, 0, 0, 2);
            
            IPS_SetEventScheduleGroupPoint($EventID, 4, 0, 0, 0, 0, 2);
            IPS_SetEventScheduleGroupPoint($EventID, 4, 1, 8, 0, 0, 1);
            IPS_SetEventScheduleGroupPoint($EventID, 4, 2, 20, 0, 0, 2);
            
            IPS_SetEventScheduleGroupPoint($EventID, 5, 0, 0, 0, 0, 2);
            IPS_SetEventScheduleGroupPoint($EventID, 5, 1, 9, 0, 0, 1);
            IPS_SetEventScheduleGroupPoint($EventID, 5, 2, 19, 0, 0, 2);
            
            //IPS_SetEventScheduleGroupPoint($EventID, 6, 0, 0, 0, 0, 2);
            //IPS_SetEventScheduleGroupPoint($EventID, 6, 1, 0, 0, 0, 1);
        
        }
 
    }

    public function controlRoom(){
        $calcId  = $this->ReadPropertyInteger("PropertyCalctemp");
        $mode = GetValue($this->GetIDForIdent("Mode"));
        $clock = GetValue($this->GetIDForIdent("Clock"));

        switch ($mode){
            case 1: 
                if ($clock == true){

                    if (IPS_VariableExists($calcId)){
                        RequestAction($calcId,$this->GetValue("Comforttemp"));
                        SetValueInteger($this->GetIDForIdent("State"),30);
                    }
        
                }else{
                    if (IPS_VariableExists($calcId)){
                        RequestAction($calcId,$this->GetValue("Reducetemp"));
                        SetValueInteger($this->GetIDForIdent("State"),20);
                    } 
                }
                break;
            case 5:
                    if (IPS_VariableExists($calcId)){
                        RequestAction($calcId,5);
                        SetValueInteger($this->GetIDForIdent("State"),5);
                    } 
                break;
            case 10:
                if (IPS_VariableExists($calcId)){
                    RequestAction($calcId,$this->GetValue("Reducetemp")-3);
                    SetValueInteger($this->GetIDForIdent("State"),10);
                } 
                break;

            case 20:
                if (IPS_VariableExists($calcId)){
                    RequestAction($calcId,$this->GetValue("Reducetemp"));
                    SetValueInteger($this->GetIDForIdent("State"),20);
                }
                break;
             case 30:
                if (IPS_VariableExists($calcId)){
                    RequestAction($calcId,$this->GetValue("Comforttemp"));
                    SetValueInteger($this->GetIDForIdent("State"),30);
                }
                break;     

        }      



    }


}

?>