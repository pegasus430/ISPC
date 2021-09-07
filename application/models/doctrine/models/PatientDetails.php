<?php
class PatientDetails
{
    function PatientDetails($ipid)
    {
        $this->ipid = $ipid;
        //here we can cache some frequently used rows
        $this->db_cache = array();

        $this->export_mode=false;

        $this->categories = array();

        $this->categories["patient_therapieplanung"] = array(
            "table" => "PatientTherapieplanung",
            "label" => "Vorausschauende Therapieplanung",
            "cols" => array(
                array("label" => "Keine Ernährungstherapie", "db" => "ernahrungstherapie", "uiclass" => "checkbox", "to_course" => true),
                array("label" => "Keine Infusionstherapie", "db" => "infusionstherapie", "uiclass" => "checkbox", "to_course" => true),
                array("label" => "Keine Antibiose bei Pneumonie", "db" => "antibiose_bei_pneumonie", "uiclass" => "checkbox", "to_course" => true),
                array("label" => "Keine Antibiose bei HWI", "db" => "antibiose_bei_HWI", "uiclass" => "checkbox", "to_course" => true),
                array("label" => "Keine Tumorreduktionstherapie / Chemo", "db" => "tumorreduktionstherapie_chemo", "uiclass" => "checkbox", "to_course" => true),
                array("label" => "Keine Krankenhausverlegung im Notfall (z.B. Harnverhalt)", "db" => "krankenhausverlegung", "uiclass" => "checkbox", "to_course" => true),
                array("label" => "Keine Lagerung durch Pflege", "db" => "lagerung_durch_pflege", "uiclass" => "checkbox", "to_course" => true),
                array("label" => "Keine orale Medikation mehr", "db" => "orale_medikation_mehr", "uiclass" => "checkbox", "to_course" => true),
                array("label" => "Keine Blut- / Volumenersatztherapie", "db" => "blut_volumenersatztherapie", "uiclass" => "checkbox", "to_course" => true),
                array("label" => "Palliative Sedierung bei unkontrollierbaren Symptomen", "db" => "palliative", "uiclass" => "checkbox", "to_course" => true),
                array("label" => "", "db" => "freetext", "uiclass" => "textline", "to_course" => true),
            ),
            "features" => array('directinput', 'dbmodel-1', 'sync1'),
            "history_id" => "grow37",
        );

        $this->categories["ernahrung"] = array(
            "label" => "Ernährung",
            "cols" => array(
                array("label" => "selbstständig", "db" => "ernahrung-1", "uiclass" => "checkbox", "multicheckbox" => 1),
                array("label" => "teilweise Hilfe", "db" => "ernahrung-2", "uiclass" => "checkbox", "multicheckbox" => 2),
                array("label" => "vollst. Hilfe", "db" => "ernahrung-3", "uiclass" => "checkbox", "multicheckbox" => 3),
                array("label" => "PEG", "db" => "peg", "uiclass" => "checkbox"),
                array("label" => "Ablauf PEG", "db" => "pegmore", "uiclass" => "textline", "conditional" => "peg"),
                array("label" => "Port", "db" => "port", "uiclass" => "checkbox"),
                array("label" => "Ablauf Port", "db" => "portmore", "uiclass" => "textline", "conditional" => "port"),
                array("label" => "ZVK", "db" => "zvk", "uiclass" => "checkbox"),
                array("label" => "Magensonde", "db" => "magensonde", "uiclass" => "checkbox"),
            ),
            "features" => array('directinput', 'multicheckbox','sync1'),
            "getfun" => "ernahrung_get",
            "setfun" => "ernahrung_set",
            "history_id" => "grow20",
        );

        $this->categories["kuenstliche_ausgaenge"] = array(
            "label" => "Künstliche Ausgänge",
            "cols" => array(
                array("label" => "Darm", "db" => "kunstliche-1", "uiclass" => "checkbox", "multicheckbox" => 1),
                array("label" => "Blase", "db" => "kunstliche-2", "uiclass" => "checkbox", "multicheckbox" => 2),
                array("label" => "Luftröhre", "db" => "kunstliche-3", "uiclass" => "checkbox", "multicheckbox" => 3),
                array("label" => "Ablaufsonde", "db" => "kunstliche-4", "uiclass" => "checkbox", "multicheckbox" => 4),
                array("label" => "besonderer Aus-/ Eingang", "db" => "kunstliche-5", "uiclass" => "checkbox", "multicheckbox" => 5),
                array("label" => "", "db" => "kunstlichemore", "uiclass" => "textline", "conditional" => "kunstliche-5"),
            ),
            "features" => array('directinput', 'multicheckbox', 'dbmodel-1', 'sync1'),
            "getfun" => "kuenstliche_ausgaenge_get",
            "setfun" => "kuenstliche_ausgaenge_set",
            "history_id" => "grow22",
        );

        $this->categories["familienstand"] = array(
            "label" => "Familienstand",
            "table" => "Stammdatenerweitert",
            "cols" => array(
                array("db" => "familienstand", "uiclass" => "radio", "items" => $this->familienstand_radioitems_get()),
            ),
            "features" => array('directinput', 'dbmodel-1', 'sync1'),
            "history_id" => "grow16",
        );

        $this->categories["ausscheidung"] = array(
            "label" => "Ausscheidung",
            "cols" => array(
                array("label" => "selbstständig", "db" => "ausscheidung-1", "uiclass" => "checkbox", "multicheckbox" => 1),
                array("label" => "teilw. Hilfe", "db" => "ausscheidung-2", "uiclass" => "checkbox", "multicheckbox" => 2),
                array("label" => "vollst. Hilfe", "db" => "ausscheidung-3", "uiclass" => "checkbox", "multicheckbox" => 3),
                array("label" => "dk", "db" => "ausscheidung-4", "uiclass" => "checkbox", "multicheckbox" => 4),
                array("label" => "spf", "db" => "ausscheidung-5", "uiclass" => "checkbox", "multicheckbox" => 5),
//                 array("label" => "DK", "db" => "dk", "uiclass" => "checkbox"),
            ),
            "features" => array('directinput', 'multicheckbox','sync1'),
            "getfun" => "ausscheidung_get",
            "setfun" => "ausscheidung_set",
            "history_id" => "grow21",
        );

        $this->categories["wunsch"] = array(
            "label" => "Wunsch des Patienten",
            "cols" => array(
                array("label" => "Zu Hause bleiben können", "db" => "wunsch-1", "uiclass" => "checkbox", "multicheckbox" => 1),
                array("label" => "kein Krankenhaus", "db" => "wunsch-2", "uiclass" => "checkbox", "multicheckbox" => 2),
                array("label" => "Autonomie", "db" => "wunsch-3", "uiclass" => "checkbox", "multicheckbox" => 3),
                array("label" => "Leidenslinderung", "db" => "wunsch-4", "uiclass" => "checkbox", "multicheckbox" => 4),
                array("label" => "Symptomlinderung", "db" => "wunsch-5", "uiclass" => "checkbox", "multicheckbox" => 5),
                array("label" => "mehr Kraft", "db" => "wunsch-6", "uiclass" => "checkbox", "multicheckbox" => 6),
                array("label" => "wieder aufstehen können", "db" => "wunsch-7", "uiclass" => "checkbox", "multicheckbox" => 7),
                array("label" => "noch eine Reise machen", "db" => "wunsch-8", "uiclass" => "checkbox", "multicheckbox" => 8),
                array("label" => "in Ruhe gelassen werden", "db" => "wunsch-9", "uiclass" => "checkbox", "multicheckbox" => 9),
                array("label" => "keine Angabe", "db" => "wunsch-10", "uiclass" => "checkbox", "multicheckbox" => 10),
                array("label" => "Frage nach aktiver Sterbehilfe", "db" => "wunsch-11", "uiclass" => "checkbox", "multicheckbox" => 11),
                array("label" => "Lebensbeendigung", "db" => "wunsch-12", "uiclass" => "checkbox", "multicheckbox" => 12),
                array("label" => "Expliziter Wunsch", "db" => "wunsch-13", "uiclass" => "checkbox", "multicheckbox" => 13),
                array("label" => "", "db" => "wunschmore", "uiclass" => "textline", "conditional" => "wunsch-13"),
            ),
            "features" => array('directinput', 'multicheckbox','sync1'),
            "getfun" => "wunsch_get",
            "setfun" => "wunsch_set",
            "history_id" => "grow25",
        );

        $this->categories["patient_mobility"] = array(
            "label" => "Mobilität",
            "table" => "PatientMobility",
            "cols" => array(
                array("label" => "Bett", "db" => "bed", "uiclass" => "checkbox"),
                array("label" => "", "db" => "bedmore", "uiclass" => "textline", "conditional" => "bed"),
                array("label" => "Rollator", "db" => "walker", "uiclass" => "checkbox"),
                array("label" => "", "db" => "walkermore", "uiclass" => "textline", "conditional" => "walker"),
                array("label" => "Rollstuhl", "db" => "wheelchair", "uiclass" => "checkbox"),
                array("label" => "", "db" => "wheelchairmore", "uiclass" => "textline", "conditional" => "wheelchair"),
                array("label" => "gehfähig", "db" => "goable", "uiclass" => "checkbox"),
                array("label" => "", "db" => "goablemore", "uiclass" => "textline", "conditional" => "goable"),
                array("label" => "Nachtstuhl", "db" => "nachtstuhl", "uiclass" => "checkbox"),
                array("label" => "", "db" => "nachtstuhlmore", "uiclass" => "textline", "conditional" => "nachtstuhl"),
                array("label" => "Wechseldruckmatraze", "db" => "wechseldruckmatraze", "uiclass" => "checkbox"),
                array("label" => "", "db" => "wechseldruckmatrazemore", "uiclass" => "textline", "conditional" => "wechseldruckmatraze"),
            ),
            "features" => array('directinput', 'dbmodel-1', 'sync1'),
            "history_id" => "grow5",
        );

        $this->categories["patient_mobility2"] = array(
            "label" => "Mobilität II",
            "table" => "PatientMobility2",
            "cols" => array(
                array("db" => "selected_value", "uiclass" => "checkbox", "items" => PatientMobility2::getEnumValuesDefaults()),
            ),
            "features" => array('isdelete', 'dbmodel-2'),
            "history_id" => "grow55",
        );

        $this->categories["patient_lives"] = array(
            "label" => "Patient lebt",
            "table" => "PatientLives",
            "cols" => array(
                array("label" => "alleine", "db" => "alone", "uiclass" => "checkbox", "multicheckbox" => 1),
                array("label" => "im Haus der Angehörigen", "db" => "house_of_relatives", "uiclass" => "checkbox", "multicheckbox" => 2),
                array("label" => "Wohnung", "db" => "apartment", "uiclass" => "checkbox", "multicheckbox" => 3),
                array("label" => "Heim", "db" => "home", "uiclass" => "checkbox", "multicheckbox" => 4),
                array("label" => "Hospiz", "db" => "hospiz", "uiclass" => "checkbox", "multicheckbox" => 5),
                array("label" => "mit Partner", "db" => "with_partner", "uiclass" => "checkbox", "multicheckbox" => 6),
                array("label" => "mit Kindern", "db" => "with_child", "uiclass" => "checkbox", "multicheckbox" => 7),
                array("label" => "Sonstige", "db" => "sonstiges", "uiclass" => "checkbox", "multicheckbox" => 8),
            ),
            "features" => array('directinput', 'multicheckbox', 'dbmodel-1', 'sync1'),
            "history_id" => "grow1",
        );



        $rels = $this->religions_radioitems_get();
        $rel_condval = array_search('Sonstige', $rels);
        $this->categories["patient_religionszugehorigkeit"] = array(
            "label" => "Religion",
            "table" => "PatientReligions",
            "cols" => array(
                array("db" => "religion", "uiclass" => "radio", "items" => $rels),
                array("db" => "religionfreetext", "uiclass" => "textline", "conditional" => "religion", "conditional_val" => $rel_condval),
            ),
            "features" => array('directinput', 'dbmodel-1', 'sync1'),
            "history_id" => "grow8",
        );

        $pflegestufen_selectitems = PatientMaintainanceStage::get_MaintainanceStage_array();
        $this->categories["patient_maintenance_stage"] = array(
            "label" => "Pflegegrade",
            "table" => "PatientMaintainanceStage",
            "cols" => array(
                array("label" => "Pflegegrad", "db" => "stage", "uiclass" => "select", "in_extract" => true, 'items' => $pflegestufen_selectitems, 'itemsarray' => array_map(function ($a, $b) {
                    return array($a, $b);
                }, array_keys($pflegestufen_selectitems), $pflegestufen_selectitems)),
                array("label" => "Gültig ab", "db" => "fromdate", "uiclass" => "date", "in_extract" => true,),
                array("label" => "Gültig bis", "db" => "tilldate", "uiclass" => "date", "in_extract" => true,),
                array("label" => "Erstantrag", "db" => "erstantrag", "uiclass" => "checkbox", "in_extract" => true,),
                array("label" => "", "db" => "e_fromdate", "uiclass" => "date", "conditional" => "erstantrag", "in_extract" => true, "samerow" => true),
                array("label" => "Höherstufung", "db" => "horherstufung", "uiclass" => "checkbox", "in_extract" => true,),
                array("label" => "", "db" => "h_fromdate", "uiclass" => "date", "conditional" => "horherstufung", "in_extract" => true, "samerow" => true),
            ),
            "features" => array('isdelete', 'dbmodel-2'),
            "history_id" => "grow7",
        );
        //ISPC-2400
        $this->categories["crisishistory"] = array(
            "label" => "PatientKrisengeschichte",
            "table" => "PatientCrisisHistory",
            "cols" => array(
                array("label" => "Status", "db" => "crisis_status", "uiclass" => "textline", "encrypt" => true, "in_extract" => true),            
                array("label" => "", "db" => "status_date", "uiclass" => "date", "in_extract" => true, "samerow" => true),
                array("label" => "", "db" => "status_create_user", "uiclass" => "textline", "in_extract" => true, "samerow" => true),
            ),
        );

        $sexselect = array("" => "Geschlecht wählen", 0 => "divers", 1 => "männlich", 2 => "weiblich"); //ISPC-2442 @Lore   30.09.2019
        $this->categories["masterdata"] = array(
            "label" => "Patient",
            "table" => "PatientMaster",
            "cols" => array(
                array("label" => "Vorname", "db" => "first_name", "uiclass" => "textline", "encrypt" => true, "in_extract" => true),
                array("label" => "Nachname", "db" => "last_name", "uiclass" => "textline", "encrypt" => true, "in_extract" => true,),
                array("label" => "Straße", "db" => "street1", "uiclass" => "textline", "encrypt" => true, "in_extract" => true),
                array("label" => "PLZ", "db" => "zip", "uiclass" => "textline", "encrypt" => true, "in_extract" => true,),
                array("label" => "Ort", "db" => "city", "uiclass" => "textline", "encrypt" => true, "in_extract" => true,),
                array("label" => "Telefon", "db" => "phone", "uiclass" => "textline", "encrypt" => true, "in_extract" => true,),
                array("label" => "Mobiltelefon", "db" => "mobile", "uiclass" => "textline", "encrypt" => true, "in_extract" => true,),
                array("label" => "Geburtstag", "db" => "birthd", "uiclass" => "date", "in_extract" => true,),
                array("label" => "Geschlecht", "db" => "sex", "uiclass" => "select", "encrypt" => true, "in_extract" => true, "items" => $sexselect, 'itemsarray' => array_map(function ($a, $b) {
                    return array($a, $b);
                }, array_keys($sexselect), $sexselect)),
            ),
            "features" => array('dbmodel-1'),
            "getfun" => "masterdata_get",
            "setfun" => "masterdata_set",
        );

        $stm = new Stammdatenerweitert();
        $natselect = array('' => "") + $stm->getStastszugehorigkeitfun();
        $natselect_arry = array_map(function ($a, $b) {
            return array($a, $b);
        }, array_keys($sexselect), $sexselect);
        $this->categories["staatszugehoerigkeit"] = array(
            "label" => "Herkunft und Sprache",
            "table" => "Stammdatenerweitert",
            "cols" => array(
                array("label" => "Staatsangehörigkeit", "db" => "stastszugehorigkeit", "uiclass" => "select", "items" => $natselect, 'itemsarray' => $natselect_arry),
                array("label" => " ", "db" => "anderefree", "conditional" => "stastszugehorigkeit", "conditional_val" => "2", "uiclass" => "text",),
                array("label" => "zweite Staatsangehörigkeit", "db" => "2ndstastszugehorigkeit", "uiclass" => "select", "items" => $natselect, 'itemsarray' => $natselect_arry),
                array("label" => " ", "db" => "2ndanderefree", "conditional" => "2ndstastszugehorigkeit", "conditional_val" => "2", "uiclass" => "text",),
                array("label" => "Dolmetscher nötig", "db" => "dolmetscher", "uiclass" => "text",)
            ),
            "features" => array('directinput', 'dbmodel-1', 'sync1')
        );

        $fd = new FamilyDegree();
        $familydegree = $fd->getFamilyDegrees(1, $this->ipid);
        $familydegree_arry=array_map(function($a,$b){return array($a,$b);}, array_keys($familydegree), $familydegree);
        $this->categories["contactperson"]=array(
            "label"=>"Ansprechpartner",
            "table"=>"ContactPersonMaster",
            "cols"=>array(
                array("label"=>"Vorname",       "db"=>"cnt_first_name",     "uiclass"=>"textline",  "encrypt"=>true,"in_extract"=>true),
                array("label"=>"Nachname",      "db"=>"cnt_last_name",      "uiclass"=>"textline",  "encrypt"=>true,"in_extract"=>true,),
                array("label"=>"Straße",        "db"=>"cnt_street1",        "uiclass"=>"textline",  "encrypt"=>true,),
                array("label"=>"PLZ",           "db"=>"cnt_zip",            "uiclass"=>"textline",  "encrypt"=>true,),
                array("label"=>"Ort",           "db"=>"cnt_city",           "uiclass"=>"textline",  "encrypt"=>true,),
                array("label"=>"Telefon",       "db"=>"cnt_phone",          "uiclass"=>"textline",  "encrypt"=>true,"in_extract"=>true,),
                array("label"=>"Mobiltelefon",  "db"=>"cnt_mobile",         "uiclass"=>"textline",  "encrypt"=>true,"in_extract"=>true,),
                array("label"=>"Beziehung",  "db"=>"cnt_familydegree_id",         "uiclass"=>"select",    "items"=>$familydegree, "itemsarray"=>$familydegree_arry),
                array("label"=>"Vorsorgevollmacht","db"=>"cnt_hatversorgungsvollmacht", "uiclass"=>"checkbox","in_extract"=>true,"readonly"=>true),
                array("label"=>"gesetzl. Betreuer","db"=>"cnt_legalguardian", "uiclass"=>"checkbox","in_extract"=>true,"readonly"=>true),
                array("label"=>"Kommentar","db"=>"cnt_comment", "uiclass"=>"textarea", "encrypt"=>true, "in_extract"=>true), //ISPC-2772 Carmen 15.12.2020
                ),
            "features"=>array('isdelete','dbmodel-2'),
        );
        
        //ISPC-2432 Ancuta 04.02.2020
		/*
        $this->categories["MePatientDevices"]=array(
            "label"=>"MePatientDevices",
            "table"=>"MePatientDevices",
            "cols"=>array(
                array("label"=>"device_name",       "db"=>"device_name",     "uiclass"=>"textline",  "encrypt"=>false,"in_extract"=>true),
                array("label"=>"allow_photo_upload",  "db"=>"allow_photo_upload",         "uiclass"=>"select",    "items"=>$familydegree, "itemsarray"=>$familydegree_arry),
                ),
            "features"=>array('isdelete','dbmodel-2'),
        );
		*/
        // -- 
 
                            
                            
        $fallselect=array('konsil'=>"Palliativdienst", 'station'=>"Station", 'sapv'=>"SAPV", 'ambulant'=>"Ambulant");
        $fallselect_arry=array_map(function($a,$b){return array($a,$b);}, array_keys($fallselect), $fallselect);
        $this->categories["fallhistorie"]=array(
            "label"=>"Fallhistorie",
            "table"=>"PatientReadmission",
            "cols"=>array(
                array("label"=>"Fallnummer",   "db"=>"case_number", "uiclass"=>"text", "in_extract"=>true),
                array("label"=>"Fallart",      "db"=>"case_type",   "in_extract"=>true,"uiclass"=>"select","items"=>$fallselect, 'itemsarray'=>$fallselect_arry),
                array("label"=>"Aufnahme",     "db"=>"date",        "uiclass"=>"date", "in_extract"=>true),
                array("label"=>"Entlassung",   "db"=>"disdate",     "uiclass"=>"date", "in_extract"=>true),
            ),
            "features"=>array('dbmodel-2','editlink'),
            "editlink"=>"patient/editreadmission?id=",
        );

        $this->categories["keimbesiedelung"]=array(
            "label"=>"Keimbesiedelung",
            "table"=>"PatientGermination",
            "cols"=>array(
                array("label"=>"Keimbesiedelung",   "db"=>"germination_cbox",   "uiclass"=>"checkbox",  ),
                array("label"=>"Keim",              "db"=>"germination_text",   "uiclass"=>"textline",  "conditional"=>"germination_cbox"),
                array("label"=>"Isolationspflicht", "db"=>"iso_cbox",           "uiclass"=>"checkbox",  "conditional"=>"germination_cbox"),
            ),
            "features"=>array('dbmodel-1','directinput', 'sync1'),
            "post_insert"=>"seticon_keim"
        );
        
        $listsmodel= new Selectboxlist();
        $infektionen=$listsmodel->getList('infektionen');
        $infektionen=array_combine($infektionen,$infektionen);
        unset($listmodel);
        if(count($infektionen)>1){
            $this->categories["keimbesiedelung"]["cols"][1]=array(
                "label"=>"Keim",
                "db"=>"germination_text",
                "uiclass"=>"select",
                "conditional"=>"germination_cbox",
                "items"=>$infektionen,
                );
        }

        $ortselect=PatientLocation::getPatientLocationNames($ipid);
        $ortselect_arry=array_map(function($a,$b){return array($a,$b);}, array_keys($ortselect), $ortselect);
        $this->categories["patientlocation"]=array(
            "label"=>"Aufenthaltsort",
            "table"=>"PatientLocation",
            "cols"=>array(
                array("label"=>"Aufenthaltsort",      "db"=>"location_id",   "in_extract"=>true,"uiclass"=>"select","items"=>$ortselect, 'itemsarray'=>$ortselect_arry),
                array("label"=>"Von",       "db"=>"valid_from", "uiclass"=>"date", "in_extract"=>true),
                array("label"=>"Bis",       "db"=>"valid_till", "uiclass"=>"date", "in_extract"=>true),
            ),
            "features"=>array('dbmodel-2','editlink'),
            "editlink"=>"patient/patientlocationlistedit?id=",
        );

        $this->categories["acp"]=array(
            "label"=>"Advance Care Planning",
            "custom_category"=>true,
            "getfun"=>"acp_get",
            "setfun"=>"acp_set",
            "history_id"=>"grow6new",
            );
        $this->history_ids_to_categories=array();
        foreach($this->categories as $cat=>$det){
            if(isset($det['history_id'])) {
                $this->history_ids_to_categories[$det['history_id']] = array($cat,$det['label']);
            }
        }
        $this->history_ids_to_categories['grow24']=array("special","Hilfsmittel");//Versorger-Tab
        
        //ISPC-2774 Carmen 16.12.2020
        $this->categories["patient_therapy"] = array(
        		"label" => 'Therapien',
        		"table" => "PatientTherapy",
        		"cols" => array(
        				array("label" => "Therapie", "db" => "therapy", "uiclass" => "textline", "in_extract" => true),
        				array("label" => "", "db" => "extratherapy", "array" => true, "in_extract" => true,),
        				),
        				"features" => array('isdelete', 'dbmodel-2'),
        				"history_id" => "grow117",
        				);
        //--
        //ISPC-2381 Carmen 14.01.2021
        $this->categories["patient_aids"] = array(
        		"label" => 'Hilfsmittel Elsa',
        		"table" => "PatientAids",
        		"cols" => array(
        				array("label" => " ", "db" => "aid", "uiclass" => "textline", "in_extract" => true),
        				array("label" => "", "db" => "extraaid", "array" => true, "in_extract" => true,),
        		),
        		"features" => array('isdelete', 'dbmodel-2'),
        		"history_id" => "grow124",
        );
        //--
    }

    public function getAllCategories(){
        $out=$this->categories;
        return $out;
    }

    public function kuenstliche_ausgaenge_get(){
        $stammdaten=$this->cached_db_fetch('Stammdatenerweitert',0);
        $out=array('kunstliche-1'=>0, 'kunstliche-2'=>0, 'kunstliche-3'=>0, 'kunstliche-4'=>0, 'kunstliche-5'=>0, 'kunstlichemore'=>"");
        if($stammdaten){
            $entry=$stammdaten[0]['kunstliche'];
            $entry=explode(',',$entry);
            foreach ($entry as $item){
                $out['kunstliche-' . $item]=1;
            }
            $out['kunstlichemore'] = $stammdaten[0]['kunstlichemore'];
            $out['create_date']=$stammdaten[0]['create_date'];
            $out['change_date']=$stammdaten[0]['change_date'];
        }
        $oarr=array($this->aggregate_row($out, 'kuenstliche_ausgaenge', $this->categories['kuenstliche_ausgaenge']));
        return $oarr;
    }

    public function kuenstliche_ausgaenge_set($input){
        $cust = Doctrine::getTable('Stammdatenerweitert')->findOneByIpid($this->ipid);
        if(!$cust) {
            $cust = new Stammdatenerweitert();
            $cust->ipid = $this->ipid;
        }
        $mcb=$this->multiselectbox_processor('kunstliche', $input, $cust->kunstliche);
        $cust->kunstliche=$mcb[0];
        $cust->kunstlichemore=$input['kunstlichemore'];
        $cust->save();

        $lm=$cust->getLastModified();
        unset($lm['kunstliche']);
        $diffs=$mcb[1]+$lm;
        $out=array('',$diffs);
        return $out;
    }

    public function multiselectbox_processor($prefix,$input,$dbval){
        $checked=array();
        $old=explode(',',$dbval);
        $changed=array();

        foreach (array(1,2,3,4,5,6,7,8,9,10,11,12,13) as $k){
            $coln=$prefix . '-' .$k;
            if($input[$coln]){
                $checked[]=$k;
                if(!in_array($k, $old)){
                    $changed[$coln]=1;
                }
            }else{
                if(in_array($k, $old)){
                    $changed[$coln]=0;
                }
            }
        }
        $out=array(implode(',',$checked), $changed);
        return $out;
    }

    public function ernahrung_get(){
        $stammdaten=$this->cached_db_fetch('Stammdatenerweitert',0);
        $out=array('ernahrung-1'=>0, 'ernahrung-2'=>0, 'ernahrung-3'=>0, 'peg'=>0, 'pegmore'=>"", 'port'=>0, 'portmore'=>"", 'zvk'=>0, 'magensonde'=>0);
        if($stammdaten){
            $entry=$stammdaten[0]['ernahrung'];
            $entry=explode(',',$entry);
            foreach ($entry as $item){
                $out['ernahrung-' . $item]=1;
            }
            $out['create_date']=$stammdaten[0]['create_date'];
            $out['change_date']=$stammdaten[0]['change_date'];
        }
        $moreinfo=$this->cached_db_fetch('PatientMoreInfo',0);
        if($moreinfo){
            foreach(array('peg', 'pegmore', 'port', 'portmore', 'zvk', 'magensonde') as $col) {
                $out[$col]=$moreinfo[0][$col];
            }
        }
        $oarr=array($this->aggregate_row($out, 'ernahrung', $this->categories['ernahrung']));
        return $oarr;
    }

    public function ernahrung_set($input){
        $cust = Doctrine::getTable('Stammdatenerweitert')->findOneByIpid($this->ipid);
        if(!$cust) {
            $cust = new Stammdatenerweitert();
            $cust->ipid = $this->ipid;
        }
        $mcb=$this->multiselectbox_processor('ernahrung', $input, $cust->ernahrung);
        $cust->ernahrung=$mcb[0];
        $cust->save();

        $cust = Doctrine::getTable('PatientMoreInfo')->findOneByIpid($this->ipid);
        if(!$cust) {
            $cust = new PatientMoreInfo();
            $cust->ipid = $this->ipid;
        }
        foreach(array('peg',  'port', 'zvk', 'magensonde') as $col) {
            if(intval($input[$col])!=intval($cust->$col)) {
                $cust->$col=$input[$col];
            }
        }
        foreach(array('pegmore', 'portmore') as $col) {
            $cust->$col=$input[$col];
        }
        $cust->save();
        $lm=$cust->getLastModified();
        $diffs=$mcb[1]+$lm;
        $out=array('',$diffs);
        return $out;
    }

    public function ausscheidung_get(){
        $stammdaten=$this->cached_db_fetch( 'Stammdatenerweitert',0);
        $out=array('ausscheidung-1'=>0, 'ausscheidung-2'=>0, 'ausscheidung-3'=>0, 'dk'=>0);
        if($stammdaten){
            $entry=$stammdaten[0]['ausscheidung'];
            $entry=explode(',',$entry);
            foreach ($entry as $item){
                $out['ausscheidung-' . $item]=1;
            }
            $out['create_date']=$stammdaten[0]['create_date'];
            $out['change_date']=$stammdaten[0]['change_date'];
        }
        $moreinfo=$this->cached_db_fetch('PatientMoreInfo',0);
        if($moreinfo){
            $moreinfo=$moreinfo[0];
            foreach(array('dk') as $col) {
                $out[$col]=intval($moreinfo[$col]);
            }
        }
        $oarr=array($this->aggregate_row($out, 'ausscheidung', $this->categories['ausscheidung']));
        return $oarr;
    }

    public function ausscheidung_set($input){
        $cust = Doctrine::getTable('Stammdatenerweitert')->findOneByIpid($this->ipid);
        if(!$cust) {
            $cust = new Stammdatenerweitert();
            $cust->ipid = $this->ipid;
        }
        $mcb=$this->multiselectbox_processor('ausscheidung', $input, $cust->ausscheidung);
        $cust->ausscheidung=$mcb[0];
        $cust->save();

        $cust = Doctrine::getTable('PatientMoreInfo')->findOneByIpid($this->ipid);
        if(!$cust) {
            $cust = new PatientMoreInfo();
            $cust->ipid = $this->ipid;
        }
        foreach(array('dk') as $col) {
            if(intval($input[$col])!=intval($cust->$col)) {
                $cust->$col=$input[$col];
            }
        }
        $cust->save();

        $lm=$cust->getLastModified();
        $diffs=$mcb[1]+$lm;
        $out=array('',$diffs);
        return $out;
    }

    public function wunsch_get(){
        $stammdaten=$this->cached_db_fetch('Stammdatenerweitert',0);
        $out=array();
        foreach ($this->categories['wunsch']['cols'] as $col){
            $out[$col['db']]=0;
        }
        $out['wunschmore']="";
        if($stammdaten){
            $entry=$stammdaten[0]['wunsch'];
            $entry=explode(',',$entry);
            foreach ($entry as $item){
                $out['wunsch-' . $item]=1;
            }
            $out['wunschmore']=$stammdaten[0]['wunschmore'];
            $out['create_date']=$stammdaten[0]['create_date'];
            $out['change_date']=$stammdaten[0]['change_date'];
        }
        $oarr=array($this->aggregate_row($out, 'wunsch', $this->categories['wunsch']));
        return $oarr;
    }

    public function wunsch_set($input){
        $cust = Doctrine::getTable('Stammdatenerweitert')->findOneByIpid($this->ipid);
        if(!$cust) {
            $cust = new Stammdatenerweitert();
            $cust->ipid = $this->ipid;
        }
        $mcb=$this->multiselectbox_processor('wunsch', $input, $cust->wunsch);
        $cust->wunsch=$mcb[0];
        $cust->wunschmore=$input['wunschmore'];
        $cust->save();

        $lm=$cust->getLastModified();
        unset($lm['wunschmore']);
        $diffs=$mcb[1]+$lm;
        $out=array('',$diffs);
        return $out;
    }

    public function masterdata_get(){
        $db=$this->cached_db_fetch('PatientMaster',0);

        $out=array();
        foreach ($this->categories['masterdata']['cols'] as $col){
            if($col['db']=="birthd"){
                $out[$col['db']] = date('d.m.Y',strtotime($db[0][$col['db']]));
            }else {
                $out[$col['db']] = $db[0][$col['db']];
            }
        }
        $oarr=array($this->aggregate_row($out, 'masterdata', $this->categories['masterdata']));
        return $oarr;
    }
    public function masterdata_set($input){
        $cust = Doctrine::getTable('PatientMaster')->findOneByIpid($this->ipid);
        if(!$cust) {
            die();
        }
        foreach ($this->categories['masterdata']['cols'] as $col){
            $myval=$input[$col['db']];
            $mycol=$col['db'];
            if($mycol=="birthd"){
                if(strlen($myval)>9) {
                    $cust->birthd = date('Y-m-d', strtotime($myval));
                }
            }else {
                $cust->$mycol = Pms_CommonData::aesEncrypt($myval);
            }
        }
        $cust->save();
        $out=$cust->_data;
        $oarr=$this->aggregate_row($out, 'masterdata', $this->categories['masterdata']);
        $out=array($oarr,array());
        return array($out);
    }

    public function acp_get(){
        $pdata = array();

        $pacp_obj = new PatientAcp();
        $pacp_array = $pacp_obj->getByIpid( array($this->ipid) );

        if( ! empty($pacp_array) && ! empty($pacp_array[$this->ipid])) {
            foreach ($pacp_array[$this->ipid] as $row) {
                $pdata[ $row['division_tab'] ] = $row;
            }
        }
        $out=array('data'=>$pdata, 'extract'=>'', 'meta'=>$this->categories['acp']);
        return $out;
    }

    public function acp_set($input){
        $subdivs=array("living_will","care_orders","healthcare_proxy");
        $pat_obj = Doctrine::getTable('PatientAcp')->findBy('ipid', $this->ipid);
        $contacts_obj = Doctrine::getTable('ContactPersonMaster')->findBy('ipid', $this->ipid);
        $modifieds=array();

        foreach($subdivs as $division_tab){
            if(isset($input[$division_tab]['active']) && strlen($input[$division_tab]['active'])>1){
                $row_found=false;
                foreach ( $pat_obj->getIterator() as $i => $item) {
                    if ($item->division_tab == $division_tab) {
                        //update this row, no need for a new one
                        $existing_contact_person = $item->contactperson_master_id;
                        $row_found = true;
                        break;
                    }
                }
                if ( ! $row_found ) {
                    //nothing saved yet for this ipid+division_tab
                    //insert new
                    $item = new PatientAcp();
                    $item->ipid=$this->ipid;
                    $item->division_tab=$division_tab;
                }
                $item->comments = $input[$division_tab]['comments'];
                $item->active = $input[$division_tab]['active'];
                $item->contactperson_master_id = $input[$division_tab]['contactperson_master_id'];
                if(!empty($item->contactperson_master_id) && !empty($division_tab)){//ISPC-2565,Elena,06.04.2021
                    $item->save();
                }
                ;

                $lm=$item->getLastModified();
                foreach($lm as $modc=>$modv){
                    $modifieds[$division_tab.",".$modc]=$modv;
                }

                if(isset($lm['contactperson_master_id']) && !empty($lm['contactperson_master_id'] )) {//ISPC-2565,Elena,06.04.2021
                    foreach ($contacts_obj->getIterator() as $i => $item) {
                        if($item->id==$input[$division_tab]['contactperson_master_id']){
                            if($division_tab=="care_orders") {
                                $item->cnt_legalguardian = true;
                            }
                            if($division_tab=="healthcare_proxy") {
                                $item->cnt_hatversorgungsvollmacht = true;
                            }
                        }else{
                            if($division_tab=="care_orders") {
                                $item->cnt_legalguardian = false;
                            }
                            if($division_tab=="healthcare_proxy") {
                                $item->cnt_hatversorgungsvollmacht = false;
                            }
                        }
                        $item->save();
                    }
                }
            }
        }
        $out=array('',$modifieds);
        return $out;
    }

    public function patientdata_get_by_cat($cat){
        $mycat=$this->categories[$cat];
               
        $data=array();
        if(isset($mycat['getfun'])){
            //special function available, so we use it
            $myfun=$mycat['getfun'];
            $data=call_user_func(array($this, $myfun));
        }elseif(in_array('dbmodel-1',$mycat['features'])){
            //case dbmodel-1: one simple row per ipid
            $isdelte_feature=in_array('isdelete',$mycat['features']);
            $db=$this->cached_db_fetch($mycat['table'],$isdelte_feature);
            if ($db){
                foreach($mycat['cols'] as $col){
                    $colname=$col['db'];
                    $dbrow[$colname]=$db[0][$colname];
                    if($col['uiclass']=="date"){
                        if($dbrow[$colname]<"1970-01-02"){
                            $dbrow[$colname]="";
                        }else{
                            $dbrow[$colname]=date("d.m.Y",strtotime($dbrow[$colname]));
                        }
                    }
                }
                if(in_array('sync1',$mycat['features'])){
                    $dbrow['change_date'] = $db[0]['change_date'];
                    $dbrow['create_date'] = $db[0]['create_date'];
                }
                $data[]=$this->aggregate_row($dbrow, $cat, $mycat);
            }else{
                $data[]=$this->aggregate_row(array(), $cat, $mycat);
            }
        }elseif(in_array('dbmodel-2',$mycat['features'])) {
            //case dbmodel-2: multiple entries per ipid
            $isdelte_feature=in_array('isdelete',$mycat['features']);
            $db=$this->cached_db_fetch($mycat['table'],$isdelte_feature);
            if ($db) {
                foreach($db as $dbrow) {
                    foreach ($mycat['cols'] as $col) {
                        $colname = $col['db'];
                        if($col['uiclass']=="date"){
                            if($dbrow[$colname]<"1970-01-02"){
                                $dbrow[$colname]="";
                            }
                        }
                    }
                    $data[]=$this->aggregate_row($dbrow, $cat, $mycat);
                }
            }
        }
        return $data;
    }

    /**
     * Provide a dataset and the category it belongs to.
     * Returns the most interesting fields with the corresponding labels
     * For Example: array(array("Name", "PflegedienstA"), array("Telefon", "012345.."))
     */
    function getExtract($catkey, $row){
        $extractmap=$this->categories[$catkey]['cols'];
        $rows=array();
        foreach ($extractmap as $col){
            if(isset($col['in_extract']) && $col['in_extract'] ) {
                $entry = $row[$col['db']];
                $insert = true;
                if ($col['uiclass'] == "checkbox") {
                    if ($entry) {
                        $entry = "ja";
                    } else {
                        $insert = false;
                    }
                }
                if ($col['uiclass'] == "select") {
                    $entry = $col['items'][$entry];
                }
                if (isset($col['conditional'])) {
                    if (!$row[$col['conditional']]) {
                        $insert = false;
                    }
                }
                if(strlen($entry)<1){
                    $insert=false;
                }
                if ($insert) {
                    $rows[] = array($col['label'], $entry);
                }
            }
        }
        return $rows;
    }

    public function patientdata_get(){
        $this->db_cache=array();
        $data=array();
        foreach ($this->categories as $cat=>$meta){
            $data[$cat]=$this->patientdata_get_by_cat($cat);
        }
        //reset cache!
        $this->db_cache=array();
        return $data;
    }
	//Maria:: Migration CISPC to ISPC 22.07.2020
    public function patientdata_get_pretty($categories){
        $Tr = new Zend_View_Helper_Translate();
        $fdata = $this->patientdata_get();
        $nice_data = array();

        foreach($fdata as $catkey => $cat) {
            if(! in_array($catkey, $categories))
                continue;
            if($catkey == "acp"){
                $_acp_box_lang	= $Tr->translate('acp_box_lang');
                $nice_data[$catkey]['label'] = $_acp_box_lang['box_title'];
                foreach ($cat['data'] as $value){
                    if($value['division_tab'] == '')
                        continue;
                    $label = '';
                    if($value['active'] == 'no')
                        $label = 'Ist nicht vorhanden';
                    elseif($value['active'] == 'yes')
                        $label = 'Ist vorhanden';
                    elseif($value['active'] == 'no_wanted')         //ISPC-2671 Lore 07.09.2020
                        $label = 'Ist nicht gewollt';
                    else
                        $label = 'nicht bekannt';
                    $nice_data[$catkey]['keyvalue'][] = array($_acp_box_lang[$value['division_tab']] => $label);

                }
                continue;
            }

            $nice_data[$catkey]['label'] = $this->categories[$catkey]['label'];
            if($catkey == "staatszugehoerigkeit"){
                foreach ($cat as $entry) {
                    $extract = array();
                    foreach ($entry['meta']['cols'] as $col) {
                        $db = $col['db'];
                        $label = $col['label'];
                        $value = $entry['data'][$db];
                        if ($value != 0 || ($value != false && $value != '')) {
                            if ($db == '2ndanderefree' || $db == 'anderefree')
                                $extract[$db] = $value;
                            elseif ($db == 'dolmetscher')
                                $extract[$label] = $value;
                            else{
                                $text = $col['items'][$value];
                                $extract[$label] = $text;
                            }
                        }


                    }
                }
                $nice_data[$catkey]['keyvalue'][] = $extract;
                continue;
            }



            foreach ($cat as $entry) {

                if (count($entry['extract']) == 0) {
                    foreach ($entry['meta']['cols'] as $col) {
                        $db = $col['db'];
                        $label = $col['label'];
                        $value = $entry['data'][$db];
                        if ($value != 0 || ($value != false && $value != '')) {
                            if ($db == 'freetext' || $db == 'germination_text' ||$db == '2ndanderefree' || $db == 'pegmore' || $db == 'portmore')
                                $nice_data[$catkey]['singleline'][] = $value;
                            elseif(isset($col['items']))
                                $nice_data[$catkey]['singleline'][] = $col['items'][$value];
                            else
                                $nice_data[$catkey]['singleline'][] = $label;
                        }
                    }
                } else {

                    $extract = array();
                    foreach ($entry['extract'] as $line) {

                        $extract[$line[0]] = $line[1];
                    }

                    $nice_data[$catkey]['keyvalue'][] = $extract;
                }

                if (strlen($entry['data']['street'])) {
                    $nice_data[$catkey]['entry'][] = array('Straße', $entry['data']['street']);
                }
                if (strlen($entry['data']['zip'] . $entry['data']['city'])) {
                    $nice_data[$catkey]['entry'][] = array('PLZ Ort', trim($entry['data']['zip'] . " " . $entry['data']['city']));
                }
            }
        }

        return $nice_data;
    }

    public function newPatientEntry($data){
        $catkey=$data['_category'];
        $cat=$this->categories[$catkey];

        $table_name=$cat['table'];
        $features=$cat['features'];

        if(in_array('editlink',$features)){
            //Prevent editing of Features that are edited on a special page
            //The GUI should never lead here
            return null;
        }
        if (isset($cat['setfun'])){
            //special function available, so we use it
            $myfun=$cat['setfun'];
            $out=call_user_func(array($this, $myfun),$data);
            $this->log_history($cat, $out[1]);
            return($out[0]);
        }elseif(in_array('dbmodel-1',$features)) {
            $myentry = Doctrine::getTable($table_name)->findOneBy('ipid', $this->ipid);
            if(!$myentry){
                $myentry = new $table_name();
                $myentry->ipid=$this->ipid;
            }
            foreach($cat['cols'] as $col){
                $colname=$col['db'];
                $val=$data[$colname];

                if($myentry->$colname!=$val) {
                    $myentry->$colname = $val;
                }
            }
            $myentry->save();
            if(isset($cat['post_insert'])){
                $fun=$cat['post_insert'];
                $this->$fun($myentry->toArray());
            }
            $this->log_history($cat, $myentry->getLastModified());

        }elseif(in_array('dbmodel-2',$features)){
            if(isset($data['_id']) && isset($data['_just_update']) && $data['_just_update'] && intval($data['_id'])){
                $myentry = Doctrine::getTable($table_name)->findOneBy('id', intval($data['_id']));
                if(isset($data['__delete']) && $data['__delete'] && in_array('isdelete',$features)){
                    $myentry->isdelete=true;
                    $myentry->save();
                    return null;
                }
            }else{
                $myentry=new $table_name();
                $myentry->ipid=$this->ipid;
            }

            if(isset($myentry)){
                foreach($cat['cols'] as $col){
                    if(isset($col["readonly"])){
                        continue;
                    }
                    $colname=$col['db'];
                    $val=$data[$colname];
                    if($col['uiclass']=="date" && strlen($val)>9){
                        $val=date('Y-m-d',strtotime($val));
                    }
                    if(isset($col["encrypt"]) && $col["encrypt"]){
                        $val=Pms_CommonData::aesEncrypt($val);
                    }
                    $myentry->$colname=$val;
                }
                $myentry->save();
                $this->log_history($cat, $myentry->getLastModified());
                $x="_data";
                $as_array=$myentry->$x;
                if(!$this->export_mode) {
                $out_array=$this->aggregate_row($as_array, $catkey, $cat);
                return($out_array);
            }
        }
    }
    }

    /**
     * Convert db-row to model
     */
    function aggregate_row($row, $catkey, $cat){
        $myrow = array('_id'=>$row['id']);
        if(isset($row['create_date'])){
            $myrow['create_date']=$row['create_date'];
            $myrow['change_date']=$row['change_date'];
        }
        foreach ($cat['cols'] as $col) {
            $myrow[$col['db']] = $row[$col["db"]];
            if(isset($col["encrypt"]) && $col["encrypt"]){
                $myrow[$col['db']]=Pms_CommonData::aesDecrypt($myrow[$col['db']]);
            }
            if(isset($col["uiclass"]) && $col["uiclass"]=="date"){
                if(strlen($myrow[$col['db']])>9) {
                    $myrow[$col['db']] = date('d.m.Y', strtotime($myrow[$col['db']]));
                }else{
                    $myrow[$col['db']]="";
                }
            }
            //ISPC-2774 Carmen 17.12.2020
            if(isset($col["array"]) && $col["array"]){
            	//ISPC-2381 Carmen 14.01.2021
            	if($catkey != "patient_aids")
            	{
            	$myrow[$col['db']]=implode(" ", $myrow[$col['db']]);
            }
            	else 
            	{
            		$Tr = new Zend_View_Helper_Translate();
            		$text= '<table width="100%">';
            		foreach($myrow[$col['db']] as $kcol => $vcol)
            		{
            			if(!is_array($vcol))
            			{
	            			if($vcol != "0")
	            			{
	            				if($vcol != "")
	            				{
	            					if($vcol == 'Ja')
	            					{
		            					$text .= '<tr>';
		            					$text .= '<td width="50%">'.$Tr->translate($kcol) . '</td><td width="50%">vorhanden</td>';
		            					$text .= '</tr>';
	            					}
	            					else if($vcol == 'Nein')
	            					{
	            						$text .= '<tr>';
	            						$text .= '<td width="50%">'.$Tr->translate($kcol) . '</td><td width="50%">nicht vorhanden</td>';
	            						$text .= '</tr>';
	            					}
	            					else
	            					{
	            						$text .= '<tr>';
	            						$text .= '<td width="50%">'.$Tr->translate($kcol) . '</td><td width="50%">' . $vcol . "</td>";
	            						$text .= '</tr>';
	            					}
	            				}
	            			}
            			}
            			else 
            			{
            				$text .= '<tr>';
            				$text .= '<td width="50%">'.$Tr->translate($kcol) . '</td><td width="50%">' .implode(",", $vcol) . "</td>";
            				$text .= '</tr>';
            			}            			
            		}
            		$text .= '</table>';
            		$myrow[$col['db']] = htmlentities($text);
            	}
            	//--
            }
            //--
        }
        if($this->export_mode){
            $out = array('data' => $myrow);
        }else {
        $out=array('data'=>$myrow, 'extract'=>$this->getExtract($catkey, $myrow), 'meta'=>$cat);
        }
        return $out;
    }

    function cached_db_fetch($table, $isdelete){
        if(isset($this->db_cache[$table])){
            return $this->db_cache[$table];
        }
        $drop = Doctrine_Query::create()
            ->select("*")
            ->from($table)
            ->where("ipid=?", $this->ipid);

        if($isdelete){
            $drop->andWhere('isdelete=0');
        }
        $db = $drop->fetchArray();

        $this->db_cache[$table]=$db;

        return $db;
    }

    public function religions_radioitems_get(){
        $o=new PatientReligions();
        return ($o->getReligionsNames());
    }

    public function familienstand_radioitems_get(){
        $stam = new Stammdatenerweitert();
        return($stam->getFamilienstandfun());
    }

    /**
     * translate cryptic history-rows to human readble columns
     * if $history-array is passed, it wioll translate all rows in $history
     * if no $history-array present, it grabs all patient-entries
     */
    public function get_history($history=null){
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $userdata = User::getUsersFast($logininfo->clientid);

        if(!isset($history)) {
            $hist = new BoxHistory();
            $history = $hist->getHistoryByPatient($this->ipid);
        }

        foreach($history as $hid=>$hist){
            $cat=$this->history_ids_to_categories[$hist['formid']];
            $history[$hid]['date']=date("d.m.Y H:i",strtotime($hist['create_date']));
            $history[$hid]['user']=$userdata[$hist['create_user']]['name'];
            if(!isset($cat)){
                continue;
            }
            $history[$hid]['formid']=$cat[1];
            if(!isset($this->categories[$cat[0]])){
                continue;
            }
            $mycat=$this->categories[$cat[0]];
            $colmap=array_combine(array_column($mycat['cols'],'db'), $mycat['cols']);

            if(in_array('multicheckbox',$mycat['features'])&& preg_match("/^[0-9]+\-[0-1]$/",$hist['fieldvalue'])){
                $parts=explode("-",$hist['fieldvalue']);
                foreach ($mycat['cols'] as $col) {
                    if (isset($col["multicheckbox"]) && intval($parts[0])==$col["multicheckbox"]) {
                        $hist['fieldname']=$col['db'];
                        $history[$hid]['fieldname']=$col['db'];
                        $hist['fieldvalue']=$parts[1];
                        $history[$hid]['fieldvalue']=$parts[1];
                        break;
                    }
                }
            }

            if(isset($colmap[$hist['fieldname']])){
                $mycol=$colmap[$hist['fieldname']];
                if(isset($mycol['items'])){
                    $history[$hid]['fieldvalue']=$mycol['items'][$hist['fieldvalue']] . " ausgewählt";
                }elseif($mycol['uiclass']=="checkbox"){
                    $history[$hid]['fieldvalue']=$mycol['label'];
                    if($hist['fieldvalue']){
                        $history[$hid]['fieldvalue'].=" hinzugefügt";
                    }else{
                        $history[$hid]['fieldvalue'].=" entfernt";
                    }
                }
            }
        }
        $out=$history;
        return $out;
    }

    public function log_history($cat,$modified_array){
        $logininfo = new Zend_Session_Namespace('Login_Info');

        if($cat['history_id']=="grow6new"){
            //special for ACP
            foreach($modified_array as $dbcol=>$val){
                $parts=explode(',',$dbcol);
                $division_tab=$parts[0];
                $col=$parts[1];
                if(in_array($col, array('active','comments','contactperson_master_id'))){
                    $history = new BoxHistory();
                    $history->ipid = $this->ipid;
                    $history->clientid = $logininfo->clientid;
                    $history->fieldname = json_encode(array( "division_tab"=>$division_tab, 'fieldname'=>$col));;
                    $history->fieldvalue = $val;
                    $history->formid = $cat['history_id'];
                    $history->save();
                }
            }
            return;
        }

        if(!isset($cat['history_id'])){
            return;
        }

        $db_to_col=array_combine(array_column($cat['cols'],'db'), $cat['cols']);

        foreach($modified_array as $dbcol=>$val){
            if(!isset($db_to_col[$dbcol])){
                //filters create_date etc.
                continue;
            }
            $mycol=$db_to_col[$dbcol];

            if(isset($mycol['multicheckbox'])){
                $ndb=explode('-',$mycol['db']);
                $dbcol=$ndb[0];
                $val=$mycol['multicheckbox']."-".$val;
            }

            $history = new BoxHistory();
            $history->ipid = $this->ipid;
            $history->clientid = $logininfo->clientid;
            $history->fieldname = $dbcol;
            $history->fieldvalue = $val;
            $history->formid = $cat['history_id'];
            $history->save();

            if(isset($mycol['to_course']) && $mycol['to_course']) {

                $histarr=$history->toArray();
                $histparse=$this->get_history(array($histarr));
                $histparse=$histparse[0];

                if(strlen($histparse['fieldvalue'])) {
                    $cust = new PatientCourse();
                    $cust->ipid = $this->ipid;
                    $cust->course_date = date("Y-m-d H:i:s", time());
                    $cust->tabname = Pms_CommonData::aesEncrypt('patientdetails');
                    $cust->course_type = Pms_CommonData::aesEncrypt("K");
                    $cust->course_title = Pms_CommonData::aesEncrypt($cat['label'] . ": " . $histparse['fieldvalue']);
                    $cust->user_id = $logininfo->userid;
                    //$cust->recordid = maybe add hist_id?
                    $cust->save();
                }
            }
        }
    }

    public function process_tests(){
        $errors=array();
        foreach($this->categories as $cat=>$mycat){
            $this->db_cache=array();
            $features=$mycat['features'];
            $cols=$mycat['cols'];
            if(in_array('editlink',$features)){
                //not write-tests because external edit-link
                continue;
            }
            if(in_array('dbmodel-1',$features)){
                $data=array();
                $data['_category']=$cat;
                $data=$this->tests_col_filler($cols, $data);
                $this->newPatientEntry($data);
                $this->db_cache=array();
                $entry=$this->patientdata_get_by_cat($cat);
                $entry=$entry[0]['data'];
                foreach($cols as $col){
                    if($entry[$col['db']]!=$data[$col['db']]){
                        $errors[]="!".$cat .":\tdb=".$col['db'].",class=".$col['uiclass'].",val=".$data[$col['db']]."->".$entry[$col['db']];
                    }
                }
            }
            if(in_array('dbmodel-2',$features)){
                $data=array();
                $data['_category']=$cat;
                $data=$this->tests_col_filler($cols, $data);
                $newe=$this->newPatientEntry($data);
                $data['_id']=$newe['data']['_id'];
                $this->db_cache=array();
                $entries=$this->patientdata_get_by_cat($cat);
                $count=count($entries);
                foreach($entries as $e){
                    if($e['data']['_id']==$newe['data']['_id']){
                        $entry=$e['data'];
                        break;
                    }
                }
                foreach($cols as $col){
                    if($entry[$col['db']]!=$data[$col['db']]){
                        $errors[]="!".$cat .":\tdb=".$col['db'].",class=".$col['uiclass'].",val=".$data[$col['db']]."->".$entry[$col['db']];
                    }
                }
                if(in_array('isdelete',$features)){
                    $data['__delete']=1;
                    $data['_just_update']=1;
                    $newe=$this->newPatientEntry($data);
                    $this->db_cache=array();
                    $entries=$this->patientdata_get_by_cat($cat);
                    if(count($entries)>=$count){
                        $errors[]="!".$cat .":\tEntry not deleted";
                    }
                }
            }
        }
        return($errors);
    }

    public function tests_col_filler($cols, $data){
        foreach($cols as $col){
            $data[$col['db']]=rand(1,1000);
            if($col['uiclass']=="checkbox"){
                $data[$col['db']]=rand(0,1);
            }
            if($col['uiclass']=="date"){
                $data[$col['db']]=date('d.m.Y');
            }
            if(isset($col['items'])){
                $its=array_keys($col['items']);
                $itv=rand(0,count($its));
                $data[$col['db']]=$its[$itv];
            }
        }
        return $data;
    }

    public function seticon_keim($data){
        $lmu=new LmuPatientSpecialAttributes();
        $arr=array();
        if($data['germination_cbox']) {
            $arr['list'] = $data['germination_text'];
            $arr['iso']=0;
            if(strlen($arr['list'])<1){
                $arr['list']="unbekannt";
            }
            if($data['iso_cbox']){
                $arr['iso']=1;
            }
        }
        $lmu->updateInfection($this->ipid, $arr);
    }



    public function create_syncpackage($to_db){
        $meta=$this->getAllCategories();

        $this->export_mode=true;
        $a=$this->patientdata_get();
        $this->export_mode=false;

        $export=array();

        foreach($a as $cat=>$catdata){
            $row_data=$catdata[0];
            $row_meta=$meta[$cat];

            if(in_array('sync1',$row_meta['features'])){
                //map items to their values e.g. 1->ledig
                foreach($row_meta['cols'] as $col){
                    if(isset($col['items'])){
                        $oval=$row_data['data'][$col['db']];
                        if($oval !== null) {
                            $row_data['data'][$col['db']] = $col['items'][$oval];
                        }
                    }
                }
                unset($row_data['data']['_id']);
                $export[$cat]=$row_data;
            }
        }

        if($to_db) {
            SystemsSyncPackets::createPacket($this->ipid, $export, "stammdaten", 1);
        }else{
            return $export;
        }
    }

    public function update_from_syncpackage()
    {

        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;
        $sql = Doctrine_Query::create()
            ->select('*')
            ->from('SystemsSyncPackets')
            ->where('ipid=?', $this->ipid)
            ->andwhere('outgoing=0')
            ->andwhere('actionname=?', 'stammdaten')
            ->andWhere('clientid=?', $clientid)
            ->orderBy('id DESC')
            ->limit(1);     //all we are interested in is the most recent entry
        $vdata = $sql->fetchArray();

        if ($vdata && $vdata[0]['done'] == 0) {
            $payload = $vdata[0]['payload'];
            $import = json_decode($payload, 1);
            $this->patient_data_import($import);
            $myentry = Doctrine::getTable('SystemsSyncPackets')->findOneBy('id', $vdata[0]['id']);
            $myentry->done = 1;
            $myentry->save();
        }
    }

    public function patient_data_import($import){

        $actual_items=$this->patientdata_get();

        $this->export_mode=true;
        foreach($actual_items as $cat=>$catdata){
            if(in_array('sync1',$catdata[0]['meta']['features'])) {
                $catdata=$catdata[0];
                $db_info = $this->importexportdatainfo($catdata['data']);
                $import_info = $this->importexportdatainfo($import[$cat]['data']);

                $do_import = false;
                if (!$import_info['empty']) {
                    if ($db_info['empty']) {
                        $do_import = true;
                    } else {
                        if ($db_info['last_update'] < $import_info['last_update'] || $db_info['last_update']==null) {
                            $do_import = true;
                        }
                    }
                }

                if ($do_import) {
                    //do the import!
                    $i_arr = $import[$cat]['data'];

                    //remap itemlists
                    foreach ($catdata['meta']['cols'] as $col) {
                        if (isset($col['items'])) {
                            $oval = $i_arr[$col['db']];
                            if ($oval !== null) {
                                $i_arr[$col['db']] = array_search($oval, $col['items']);
                            }
                        }
                    }

                    $i_arr['_category'] = $cat;
                    $this->newPatientEntry($i_arr);
                }
            }
        }
        $this->export_mode=false;
    }

    private function importexportdatainfo($datarow){
        $last_update=max($datarow['create_date'], $datarow['change_date']);
        unset($datarow['create_date']);
        unset($datarow['change_date']);
        unset($datarow['_id']);
        $x=implode('',array_values($datarow));
        $empty=true;
        if(strlen($x) && strlen(str_replace("0","",$x))){
            $empty=false;
        }
        $return=['last_update'=>$last_update, 'empty'=>$empty];
        return ($return);
    }


	//Maria:: Migration CISPC to ISPC 22.07.2020
    public function renderreportextract($data){
        $newview = new Zend_View();
        $newview->f_values=$data;
        $newview->belongsto='FormBlockPsychosocialStatus';
        $newview->setScriptPath(APPLICATION_PATH."/views/scripts/patientnew/");
        $out=$newview->render('patientdetails_report.html');
        return $out;
    }
}