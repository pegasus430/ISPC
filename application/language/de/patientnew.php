<?php
return array(
		// Maria:: Migration ISPC to CISPC 08.08.2020
		"datamatrix_import_button"		=> "Import Medikationsplan",//"Import Datamatrix", //button
		"pdf_patient_datamatrix"		=> "Bundeseinheitlicher Medikationsplan",//"Medikationsplan Bundeseinheitlicher",//Plan ausdrucken option
		"Patient names do not match"	=> "Achtung: Name des Patienten stimmt nicht überein!",//"Achtung: Patient names do not match !",//import from datamatrix xml
		
		//dialog to import datamatrix xml
		"datamatrix_lang" => array(
								
				"btn_close" 		=> "Abbrechen",
				"btn_continue" 		=> "Weiter",
				
				"step_in_progress_title"	=> "Importvorgang",//"In Progress", //this are displayed in between steps 
				"step_in_progress_infotext"	=> "Bitte warten Sie... Wir verarbeiten die Daten für SIe.",//"Please wait... we are processing the data",
				
				//step 1 - input the xml
				"step1_title"		=> "Importiere Datamatrix Daten",//"Import medication from DataMatrix XML",
				"step1_label"		=> "XML :",
				"step1_infotext"	=> "* Bitte in dieses Feld die XML Daten hereinkopieren.",//"Please copy/paste in the filed above the xml you have from the scanner",
				
				//step 2 - select from the list
				"step2_title"		=> "Medikamente auswählen",//"Select Medications from the list",
				"step2_label"		=> "Schritt 2", //"step2 label", // not used
				"step2_infotext"	=> "* Bitte wählen Sie mit der Checkbox die Medikamente aus die Sie importieren wollen.", //"* step2 bitte use checkbox to select what medications to import", //text under the medication list
				
				//step 3 - finish import, redirect to New Medication
				"step3_title"		=> "Abschließen",//"Finish",
				"step3_label"		=> "Schritt 3", // "step3 label", // not used
				"step3_infotext"	=> "Wir haben erfolgreich Daten eingelesen. Bitte warten Sie auf die Medikationsseite.",//"All went ok, please wait for the medication page",
								
				"xml2array processed ok" => "xml2array processed ok", // not displayed to the user, no translation needed
				
				//errors for the user, that are displayed as alerts
				"general error" => "Entschuldigung, die XML Daten konnten nicht verarbeitet werden.",//"Sorry, something went wrong with your xml, please check.",
				"medication array is empty" =>  "In den eingelesen Daten befinden sich keine Medikationseinträge", // "Cannot find any medication on the xml you submited, nothing to import.", // the xml dosen't contain any medication
				"no medication was selected" =>  "Es wurden keine Medikamente zum Import ausgewählt. Bitte wählen Sie mindestens ein Medikament wenn Sie fortfahren möchten.",//"No medication was checked from the list, please select at least one.",
				"failed to import the selected medis" => "Entschuldigung, wir konnten die Medikamente nicht importieren. Bitte rufen Sie unsere Hotline an.", // "Failed to import the selected medis , inform the admin.", // this should never happen
				
		),
		
		"hospizregister_lang_XX" => array(
				"dgp_admission_block_adm"=>"Nationales Hospiz- und Palliativregister",
				"dgp_discharge_block"=>"Nationales Hospiz- und Palliativregister",
				"dgp_admission_block_symptom"=>"Symptomatik - Aufnahme",
				"dgp_discharge_block_symptom"=>"Symptomatik - Entlassung",
				
				"partners" => array(
						"ehrenamtlicher_dienst"=>"Ehrenamtlicher Dienst",
						"Hospiz_stationaer"=>"Hospiz stationaer",
						"palliativpflege"=>"Palliativpflege",
						"Heim"=>"Heim",
						"Hausarzt"=>"Hausarzt",
						"Palliativarzt"=>"Palliativarzt",
						"Palliative_Care_Team"=>"Palliative Care Team",
						"ambulante_pfleg"=>"ambulante Pflege",
						"palliativberatung_AHPB"=>"Palliativberatung AHPB",// new
						"KH_Palliativstation"=>"KH Palliativstation",
						"Krankenhaus_Andere_Station"=>"Krankenhaus Andere Station",
						"MVZ"=>"MVZ", // new
						"KH_Palliativdienst"=>"KH Palliativdienst",// new
						"sonstiges"=>"sonstiges",// new
				),
				"medication_references"=>array(
						"analgetika"=>"Analgetika",
						"who2"=>"Opioide WHO Stufe 2",
						"who3"=>"Opioide WHO Stufe 3",
						"nicht_opioide"=>"Nicht Opioide",
						"co_analgetika"=>"Co Analgetika",
						"steroide"=>"Steroide",
						"anxiolytika"=>"Anxiolytika",
						"laxantien"=>"Laxantien",
						"sedativa"=>"Sedativa",
						"neuroleptika"=>"Neuroleptika",
						"anti_eleptika"=>"Anti Epileptika",
						"anti_emetika"=>"Anti Emetika",
						"anti_biotika"=>"Anti Biotika",
						"magenschutz"=>"Magenschutz",
				),
				"symptoms"=>array(
						'schmerzen'=>'Schmerzen',
						'ubelkeit'=>'Übelkeit',
						'erbrechen'=>'Erbrechen',
						'luftnot'=>'Luftnot',
						'verstopfung'=>'Verstopfung',
						'swache'=>'SchwächeSchwäche',
						'appetitmangel'=>'Schwäche',
						'mudigkeit'=>'Müdigkeit',
						'dekubitus'=>'Pflegeprobleme wegen Wunden / Dekubitus',
						'hilfebedarf'=>'Hilfebedarf bei Aktivitäten des tägl. Lebens',
						'depresiv'=>'Depressivität',
						'angst'=>'Angst',
						'anspannung'=>'Anspannung',
						'desorientier'=>'Desorientiertheit, Verwirrtheit',
						'versorgung'=>'Probleme mit Organisation der Versorgung',
						'umfelds'=>'Überforderung der Familie, des Umfeldes',
						'sonstige_probleme'=>'Sonstige'
				),
				"symptoms_values"=>array(
						"no"=>"kein",
						"light"=>"leicht",
						"medium"=>"mittel",
						"strongly"=>"stark",
				),
		
				"no"=>"nein",
				"continued"=>"fortgesetzt",
				"initiated"=>"initiiert",
				"living will and others"=>"living will and others",
		
				"living_will"=>array(
						"living will and others"=>"LABEL: Patientenverfügung / ACP",
						"pverfuegung"=>"Patientenverfügung",
						"vollmacht"=>"Vorsorgevollmacht",
						"betreuungsurkunde"=>"Betreuungsurkunde",
						"acp"=>"ACP",
				),
		
		),
		
    // THIS IS THE ONE USED IN REGISTER FORM
		"hospizregister_lang" => array(
				"dgp_admission_block_adm"=>"Nationales Hospiz- und Palliativregister",
				"dgp_discharge_block"=>"Nationales Hospiz- und Palliativregister",
				"dgp_admission_block_symptom"=>"Symptomatik - Aufnahme",
				"dgp_discharge_block_symptom"=>"Symptomatik - Entlassung",
				"partners" => array(
						"ehrenamtlicher_dienst"=>"Ehrenamtlicher Dienst",
						"Hospiz_stationaer"=>"Hospiz stationär",
						"palliativpflege"=>"Palliativpflege",
						"Heim"=>"Heim",
						"Hausarzt"=>"Hausarzt",
						"Palliativarzt"=>"Palliativarzt",
						"Palliative_Care_Team"=>"Palliative Care Team",
						"ambulante_pfleg"=>"ambulante Pflege",
						"palliativberatung_AHPB"=>"Palliativberatung AHPB",// new
						"KH_Palliativstation"=>"KH Palliativstation",
						"Krankenhaus_Andere_Station"=>"Krankenhaus andere Station",
						"MVZ"=>"MVZ", // new
						"KH_Palliativdienst"=>"KH Palliativdienst",// new
						"sonstiges"=>"sonstiges",// new
												// ISPC-2144
						"Sozialdienst"=>"Sozialdienst",// new
						"Ernährungsteam"=>"Ernährungsteam",// new
						"Wundmanagement"=>"Wundmanagement",// new
						"Physiotherapie"=>"Physiotherapie",// new
						"Stomapflege"=>"Stomapflege",// new
						"Psychotherapeut"=>"Psychotherapeut / Psychologe",// new
						"SAPV-Team"=>"SAPV-Team",// new
				),
				"medication_references"=>array(
						"analgetika"=>"Analgetika",
						"who2"=>"Opioide WHO Stufe 2",
						"who"=>"Opioide WHO Stufe 3",
						"nicht_opioide"=>"Nicht Opioide",
						"co_analgetika"=>"Co Analgetika",
						"steroide"=>"Steroide",
						"anxiolytika"=>"Anxiolytika",
						"laxantien"=>"Laxantien",
						"sedativa"=>"Sedativa",
						"neuroleptika"=>"Neuroleptika",
						"anti_eleptika"=>"Anti-Epileptika",
						"antiemetika"=>"Antiemetika",
						"antibiotika"=>"Antibiotika",
						"magenschutz"=>"Magenschutz",
				        //ISPC-2496 Ancuta 02.12.2019
    				    "secretioninhibiting_sub"=>"Sekretionshemmende Substanzen",// - BL_sekretionshemmend
    				    "benzodiazepines"=>"Benzodiazepine",// - BL_Benzodiazepine
    				    "antidepressants"=>"Antidepressiva",// - BL_Antidepressiva
    				    "antipsychotics"=>"Antipsychotika",// - BL_Antipsychotika
    				    "anti_infectives"=>"Antiinfektiva",// - BL_Antiinfektiva
    				    "anticoagulants"=>"Antikoagulantien",// - BL_Antikoagulantien
    				    "other_meds"=>"Sonstige",// - BL_Sonstige
    				    //--
				    
				     
				    
				    
				),
				"symptoms"=>array(
						'schmerzen'=>'Schmerzen',
						'ubelkeit'=>'Übelkeit',
						'erbrechen'=>'Erbrechen',
						'luftnot'=>'Luftnot',
						'verstopfung'=>'Verstopfung',
						'swache'=>'Schwäche',
						'appetitmangel'=>'Appetitmangel',
						'mudigkeit'=>'Müdigkeit',
						'dekubitus'=>'Pflegeprobleme wegen Wunden / Dekubitus',
						'hilfebedarf'=>'Hilfebedarf bei Aktivitäten des tägl. Lebens',
						'depresiv'=>'Depressivität',
						'angst'=>'Angst',
						'anspannung'=>'Anspannung',
						'desorientier'=>'Desorientiertheit, Verwirrtheit',
						'versorgung'=>'Probleme mit Organisation der Versorgung',
						'umfelds'=>'Überforderung der Familie, des Umfeldes',
						'sonstige_probleme'=>'Sonstige',
						'unruhe'=>'Unruhe'
				),
				"symptoms_values"=>array(
						"no"=>"kein",
						"light"=>"leicht",
						"medium"=>"mittel",
						"strongly"=>"schwer",
				),
				
				"living_will"=>array(
						"living will and others"=>"Patientenverfügung / ACP",
						"pverfuegung"=>"Patientenverfügung",
						"vollmacht"=>"Vorsorgevollmacht",
						"betreuungsurkunde"=>"Betreuungsurkunde",
						"acp"=>"ACP",
				),
		),
		
		'maintenancestage_lang'=>array(
				"stage"=>"Pflegegrade",
				"fromdate"=>"von",
				"erstantrag" =>"Erstantrag",
				"horherstufung" =>"Höherstufung beantragt",
		),
		
	'Interval for dosage' => 'Intervall', //patientnew/medicationedit only on isbedarfs medication for SINGLE dosage, NOT when you have hour-time
	
    //ISPC-2176
    "intubated_medication_lang" => array(
        "empty_select" => "",
        "one_chamber_bag" => "1-Kammerbeutel",
        "two_chamber_bag" => "2-Kammerbeutel",
        "three_chamber_bag" => "3-Kammerbeutel",
        "seven_chamber_bag" => "7-Kammerbeutel (Heptatube)",
        "eight_chamber_bag" => "8-Kammerbeutel (Octatube)",
        "nine_chamber_bag" => "9-Kammerbeutel (Nonatube)",
    ),
    
    // new block on new mediapge
    "isintubated medication title"=>"Parenterale Ernährung", // THE NEW BLOC TITLE/ NAME
    
    "medication_type_PM" => "Parenterale Ernährung", // Icon title
    "medication_isintubated" => "Parenterale Ernährung", // title in Zeitschema
    "isintubated medication title time"=>"Parenterale Ernährung",
    "isintubated medication add"=>"Neue parenterale Ernährung",
    "isintubated medication edit"=>"Bearbeiten",
    "medication_packaging"=>"Kammerbeutel Info",// label for the new dropdown
    "medication_kcal"=>"Kcal",
    "medication_volume"=>"Volumen",
    "medication_clock_error_messaje"=>"Bitte Uhrzeit eintragen",//TODO-3828 CRISTI C. 09.02.2021
    
    
    /*
     * Nico's versorger = provider
     */
    "Patient wish" => "Wunsch des Patienten",
    "versorger" => "provider",
    "nice_name" => "Name",
    "Emergency comments" => "Kommentare",//"Hausnotruf Kommentare",
    "contact_lastname" => "Kontakt Nachname",
    "contact_firstname" => "Kontakt Vorname",
    "hospice_service" => "Hospizdienst",
    "Memo for" => "Memo für",
    "no light" => "keine Markierung",
    "green light" => "grüne Ampel",
    "red light" => "rote Ampel",
    "save changes" => "Änderungen speichern",
    "processing" => "Verarbeitung",
    
    "with partner" => "mit Partner",
    "with child" => "mit Kindern",
    
    
    "Copy address" => "Adresse kopieren",
    "Delete versorger" => "Eintrag entfernen",
    
    "[The patient is already discharged, are you sure you want to make this change ?]" => "Der Patient ist bereits entlassen. Sind Sie sicher, dass Sie diese Änderung durchführen wollen?",
    
    "[Are you sure you want to delete this ?]" => "Sind Sie sicher, dass Sie das löschen möchten ?",
    "[Are you sure you want to delete this?]" => "Sind Sie sicher, dass Sie diesen Eintrag löschen wollen?",
     
    //left
    "[FamilyDoctor Box Name]" => "Hausarzt",
    "[Familydoctor Box Name]" => "Hausarzt",
    "[PatientSpecialists Box Name]" => "Facharzt",
    "[PatientPflegedienste Box Name]" => "Pflegedienst",
    "[PatientPflegediensts Box Name]" => "Pflegedienst",
    "[PatientCaseStatus Box Name]" => "Klinische Fälle",//Maria:: Migration CISPC to ISPC 22.07.2020
    
    //right
    "[PatientHealthInsurance Box Name]" => "Krankenkasse",
    "[PatientSupplies Box Name]" => "Sanitätshäuser",
    "[PatientPharmacy Box Name]" => "Apotheke",
    "[PatientSuppliers Box Name]" => "sonst. Versorger",
    "[PatientHomecare Box Name]" => "Homecare",
    "[PatientPhysiotherapist Box Name]" => "Physiotherapeuten",
    "[PatientVoluntaryworkers Box Name]" => "Ehrenamtlichen / Koordinator",
    "[PatientChurches Box Name]" => "Pfarreien",
    "[PatientHospiceassociation Box Name]" => "Hospizdienst",
    
    "[PatientRemedies Box Name]" => "Hilfsmittel II",
    "[PatientDiagnosis Box Name]" => "Diagnosen",
    "[xxxxxxxxx Box Name]" => "xxxxxxxxxxx",

    "[Anamnese Box Name]" => "Anamnese", //ISPC-2694, elena,18.12.2020
    
    
    //patient Details
    "[PatientMaster Box Name]" => "Patient",
    "[ContactPersonMaster Box Name]" => "Ansprechpartner",
    "[Contact_Persons Box Name]" => "Ansprechpartner",
    "[PatientOrientation Box Name]" => "Orientierung II",
    
    
    "[Stammdatenerweitert_ausscheidung Box Name]" => "Ausscheidung (alt)",          //ISPC-2791 Lore 15.01.2021
    "[Stammdatenerweitert_vigilanz Box Name]" => "Vigilanz",
    "[Stammdatenerweitert_kunstliche Box Name]" => "Künstliche Ausgänge",
    "[Stammdatenerweitert_familienstand Box Name]" => "Familienstand",
    "[Stammdatenerweitert_stastszugehorigkeit Box Name]" => "Herkunft und Sprache",
    "[Stammdatenerweitert_wunsch Box Name]" => "Wunsch des Patienten",
    "[Stammdatenerweitert_orientierung Box Name]" => "Orientierung",
    
    "[Stammdatenerweitert_hilfsmittel Box Name]" => "Hilfsmittel",
    "[Stammdatenerweitert_ernahrung Box Name]" => "Ernährung (alt)",          //ISPC-2788 Lore 15.01.2021
    
    "[PatientMobility Box Name]" => "Mobilität",
    "[PatientMobility2 Box Name]" => "Mobilität II",
    "[PatientLives Box Name]" => "Patient lebt",
    "[PatientGermination Box Name]" => "Keimbesiedelung",
    "[PatientReligions Box Name]" => "Religionszugehörigkeit",
    "[PatientSupply Box Name]" => "Versorgung",
    "[PatientMaintainanceStage Box Name]" => "Pflegegrade",
    "[PatientTherapieplanung Box Name]" => "Vorausschauende Therapieplanung",
    "[PatientHospizverein Box Name]" => "Hospizverein",
    "[PatientMaster_Hospiz_Hospizverein_SAPV_AAPV Box Name]" => "Hospiz - Hospizverein - SAPV/AAPV",
    "[PatientMedipumps Box Name]" => "Hilfsmittel Verleih",
    "[PatientVisitsSettings Box Name]" => "Tourenplanung",
    "[PatientAcp Box Name]" => "Advance Care Planning",
    "[PatientLocation Box Name]" => "Aufenthaltsort",
    "[PatientReadmission Box Name]" => "Fallhistorie",
    "[PatientCrisisHistory Box Name]" => "DemStepCare - Status",
    "[SYSTEMSYNC Box Name]" => "SYSTEMSYNC",
    
    "[SapvVerordnung Box Name]" => "SAPV Verordnung",
    "[PatientEmploymentSituation Box Name]" => "Erwerbssituation",
    
    "[PatientSurveySettings Box Name]" => "Palli-Monitor (IPOS)",

    "[MePatientDevices Box Name]" => "docMemo Geräteverwaltung",//ISPC-2801 Ancuta 18.06.2020 "mePatient Geräte",//ISPC-2432
    "[MePatientDevicesNotifications Box Name]" => "docMemo Benachrichtigungen",//ISPC-2801 Ancuta 18.06.2020 "mePatient Nachrichten", //ISPC-2432
    
    "[xxxxxxxxx Box Name]" => "xxxxxxxxxxx",
    "[xxxxxxxxx Box Name]" => "xxxxxxxxxxx",
    "[xxxxxxxxx Box Name]" => "xxxxxxxxxxx",
    "[xxxxxxxxx Box Name]" => "xxxxxxxxxxx",
    
    "cnt_custody" => "Sorgerecht Kommentare",
    "cnt_custody_val" => "Sorgerecht",
	
	"since when" => "Seit wann",
	"supplementary services" => "ergänzende Leistungen",
	
	//ISPC - 2129
	'emergencyplan' => "Notfallplan",
	'your are about to upload an emergency plan. shall this file be taken as latest version?' => 'Sie laden gerade einen Notfallplan hoch. Soll dieser Plan als aktuelle Fassung hinterlegt werden und somit eventuell andere Versionen ersetzen?',
		
    //ispc-2291
    "Doctor wants infusion protocol" => "Arzt wünscht Infusionsprotokoll",
    "Emergency call number" => "Notfallrufnummer",
    "Emergency Preparedness" => "Bereitschaft im Notfall",
    
    "Pharmacy delivers from" => "Apotheke liefert aus",
    "Delivery rhythm" => "Rythmus der Belieferung",
    "Pharmacy produces infusion" => "Apotheke produziert Infusion",
    "Rhythm of preparation" => "Rhytmus der Zubereitung",
    "once" => "Einmal",
    "every_x_days" => "alle ... Tage",
    "selected_days_of_the_week" => "Wochentage auswählen",
    "Name Nursing Career" => "Name versorgende Pflegefachkraft",
    "Qualification" => "Qualifikation",
    "Additional qualification" => "Zusatzqualifikation",
    "Name nurse providing care in case of substitution" => "Name versorgende Pflegefachkraft im Vertretungsfall",
    
    
    "Dosage according to the product information" => "Dosierung gemäß Fachinformation",
    "Interval according to technical information" => "Intervall gemäß Fachinformation",

    
    "Health and Healthcare" => "Gesundheits- und Krankenpfleger",
    "Health and child nurses" => "Gesundheits- und Kinderkrankenpfleger",
    "Nurse" => "Krankenschwester",
    "Caregiver" => "Altenpfleger",
    
    "HL7 PV1-19 visit_number" => "Fallnummer",
    "HL7 PV1-19 admit_date" => "Datum",
    "xxxxxxxxxx" => "yyyyyyyyyyy",
    "xxxxxxxxxx" => "yyyyyyyyyyy",
    "xxxxxxxxxx" => "yyyyyyyyyyy",
    "xxxxxxxxxx" => "yyyyyyyyyyy",

    
    // TODO-2336 - Add validate for family doctor on admission page    
    "fd_lastname_error" => "Eingabe überprüfen: Nachname",
    "fd_firstname_error" => "Eingabe überprüfen: Vorname",
 
	//Ancuta added to Clinic ISPC (CISPC)  on 18.03.2020
    //'Artificial entries exits:'=>'Künstliche Zu-& Ausgänge',//modal title
    'Artificial entries exits:'=>'Zu und Abgänge',          //ISPC-2517 pct.K Lore 20.05.2020 
    
	//ISPC-2508 Carmen 31.01.2020
	'artificial_entries' => 'Künstliche Zugänge',
	'artificial_exits' => 'Künstliche Ausgänge',
	'entries_exits' => 'Zugänge / Ausgänge',

	//stammdanten edit labels
	'artificial_option_date' => 'Datum',	
	'artificial_option_localization' => 'Lokalisation',
	'artificial_option_age' => 'Zeit',
		
	//icons settings title and question
	'[Are you sure you want to refresh this artificial entry/exit ?]' => 'Ausgewechselt? Sind Sie sicher?',
	'[Are you sure you want to remove this artificial entry/exit ?]' => 'Sie wollen diesen Zu- / Ausgang ZIEHEN oder ENTFERNEN?',
	'[Are you sure you want to delete this artificial entry/exit ?]' => 'Sie wollen diesen fälschlicherweise dokumentierten Zugang LÖSCHEN?',
	'notneeded' => 'nicht benötigt', 
	'refresh' => 'erneuern',
	
	// #ISPC-2512PatientCharts
	'[PatientArtificialEntriesExits Box Name]' => 'Künstliche Zugänge - Ausgänge', //stammdanten box title
	
	'icon_patient_artificial_entries_exits_expired' => 'Zugänge / Ausgänge',//title for icon
	'today new' => 'heute neu',

    //ISPC-2513 Ancuta
	'adm_type_planned' => 'geplant',
	'adm_type_unplanned' => 'ungeplant',
    
    //ISPC-2513 Lore 14.04.2020
    'readmission_edit_details' => 'AUFNAHME/AUFNAGMEGRUND',
    'was_planned' => 'geplant',
    'unplanned' => 'ungeplant',
    
	//ISPC-2508 added for charts Carmen 23.04.2020
	'artificial_start_date' => 'Datum',
	'artificial_remove_date' => 'Zu/ Abgang entfernt am',
	'artificial_refresh_date' => 'neu gelegt am',
	'End date cant be before start date' => 'Datum muss nach dem Startdatum liegen',
	'artificial_entry_removed on day' => 'entfernt an Tag ',
		
	//TODO-3433 Carmen 21.09.2020
	'artificial_option_name' => 'Name',
	'wrongentry' => 'Daten löschen',
	
	
    //ISPC-2667 Lore 21.09.2020
    "[PatientCareInsurance Box Name]" => "Pflegeversicherung",
    "PatientCareInsurance_legend" => "Pflegeversicherung",
    "kind_of_insurance" => "Art der Versicherung",
    "name_of_insurer" => "Name des Versicherers",
    "function_contactperson" => "Funktion des Ansprechpartners",
    "mailbox" => "Postfach",
    "description_care_ins" => "Beschreibung/Besonderheiten",
    "kind_ins_legally" => "Gesetzlich",
    "kind_ins_private" => "Privat*",
    "kind_ins_no" => "Keine",
    "kind_ins_others" => "Sonstiges",
    "power" => "Leistung",
    "power_ins_daily_care" => "Pflegetagegeld",
    "power_ins_nursing_pension" => "Pflegerentenversicherung",
    "power_ins_nursing_care" => "Pflegekostenversicherung",
    "power_ins_others" => "Sonstiges",
    
    //.
    
    //ISPC-2672 Lore 22.10.2020
    "[PatientPlaygroup Box Name]" => "Spielgruppe",
    "PatientPlaygroup_legend" => "Spielgruppe",
    "name_playgroup" => "Name der Spielgruppe",
    "picked_up" => "Wird abgeholt/nach Hause gebracht",
    "picked_up_text" => "von",
    "accom_required" => "Wegbegleitung erforderlich",
    "accom_required_text" => "durch",
    "accom_pg_required" => "Begleitung in Spielgruppe erforderlich",
    "aids_available" => "Hilfsmittel in Spielgruppe vorhanden",
    "aids_available_text" => "welche",
    "last_visit" => "Letzter Besuch",
    
    //.
    
    //ISPC-2672 Lore 23.10.2020
    "[PatientSchool Box Name]" => "Schule",
    "PatientSchool_legend" => "Schule",
    "School" => "Schule",
    "accom_pg_required_school" => "Begleitung in Schule erforderlich",
    "aids_available_school" => "Hilfsmittel in Schule vorhanden",
    //.
    
    //ISPC-2672 Lore 26.10.2020
    "[PatientWorkshopDisabledPeople Box Name]" => "Werkstatt für behinderte Menschen",
    "PatientWorkshopDisabledPeople_legend" => "Werkstatt für behinderte Menschen",
    "Workshop" => "Werkstatt",
    "accom_pg_required_workshop" => "Begleitung in Werkstatt erforderlich",
    "aids_available_workshop" => "Hilfsmittel in Werkstatt vorhanden",
    "name_workshop" => "Name der Werkstatt",
    //.
    
    //ISPC-2672 Lore 26.10.2020
    "[PatientOtherSuppliers Box Name]" => "Sonstiges",
    "PatientOtherSuppliers_legend" => "Sonstiges",
    "accom_pg_required_other" => "Begleitung in Sonstiges erforderlich",
    "aids_available_other" => "Hilfsmittel in Sonstiges vorhanden",
    "name_other" => "Name des Sonstigen",
    //.
    
    //ISPC-2672 Lore 26.10.2020
    "[PatientSapvTeam Box Name]" => "SAPV Team",
    "PatientSapvTeam_legend" => "SAPV Team",
    "name_sapv" => "Name des SAPV Teams",
    "amount_time" => "Zeitumfang",
    //.
    
    //ISPC-2672 Lore 26.10.2020
    "[PatientChildrensHospice Box Name]" => "Kinderhospiz",
    "PatientChildrensHospice_legend" => "Kinderhospiz",
    "name_hospice" => "Name des Kinderhospizes",
    //.
    
    //ISPC-2672 Lore 26.10.2020
    "[PatientFamilySupportService Box Name]" => "Familien unterstützender Dienst",
    "PatientFamilySupportService_legend" => "Familien unterstützender Dienst",
    "func_cnt_person" => "Funktion des Ansprechpartners",
    //.
    
    //ISPC-2669 Lore 23.09.2020
    "[PatientHandicappedCard Box Name]" => "Schwerbehindertenausweis",
    "PatientHandicappedCard_legend" => "Schwerbehindertenausweis",
    "since" => "Seit",
    "hc_approved_by" => "Bewilligt bis",
    "unlimited" => "unbefristed",
    "expiry_date" => "Ablaufdatum",
    "marks" => "Merkzeichen",
    //.
    
    //ISPC-2673 Lore 30.09.2020
    "[FormBlockResources Box Name]" => "Ressourcen",
    "FormBlockResources_legend" => "Ressourcen",
    
    //.
    
	//ISPC-2672 Carmen 22.10.2020
	'kindergarten_contactperson' => 'Ansprechpartner (Name, Vorname)',
	"[PatientKindergarten Box Name]" => "Kindergarten",
	'PatientKindergarten_legend' => 'Patient Kindergarten',
	'name_of_kindergarten' => 'Name des Kindergartens',
	'type_of_kindergarten' => 'Art',
	'kindergarten_regular' => 'Regelkindergarten',
	'kindergarten_integrative' => 'integrativer Kindergarten',
	'kindergarten_special_educational' => 'Heilpädagogischer Kindergarten',
	'kindergarten_last_visit' => 'Letzter Besuch',
	'kindergarten_last_visit_date' => 'Datum',
	'kindergarten_last_visit_other' => 'Sonstiges',
	'kindergarten_date_field' => 'Datum',
	'kindergarten_regularly' => 'Regelmäßig',
	'kindergarten_irregular' => 'Unregelmäßig',
	'kindergarten_unknown' => 'Unbekannt',
	'kindergarten_other' => 'Sonstiges',
	'kindergarten_picked_up_brought_home' => 'Wird abgeholt/nach Hause gebracht',
	'kindergarten_picked_up_brought_home_other' => 'von:',
	'kindergarten_accompaniment_required' => 'Wegbegleitung erforderlich',
	'kindergarten_accompaniment_required_other' => 'durch:',
	'kindergarten_accompaniment_required_in_kindergarten' => 'Begleitung im Kindergarten erforderlich',
	'kindergarten_accompaniment_required_in_kindergarten_other' => 'durch:',		
	'kindergarten_aids_available_in_kindergarten' => 'Hilfsmittel in Kindergarten vorhanden',
	'kindergarten_aids_available_in_kindergarten_other' => 'welche:',
	//--
	
	//ISPC-2672 Carmen 26.10.2020
	'name_of_children_hospice_service' => 'Name des Kinderhospizdienstes',
	"[PatientAmbulantChildrenHospiceService Box Name]" => "Ambulanter Kinderhospizdienst",
	'PatientAmbulantChildrenHospiceService_legend' => 'Ambulanter Kinderhospizdienst',
	
	'name_of_youth_welfare_office' => 'Name des Jugendamtes',
	'department_of_youth_welfare_office' => 'Abteilung',
	'youth_contactperson' => 'Ansprechpartner',
	"[PatientYouthWelfareOffice Box Name]" => "Jugendamt",
	'PatientYouthWelfareOffice_legend' => 'Jugendamt',
		
	'name_of_service_provider' => 'Name des Leistungserbringers',
	'amount_of_time' => 'Zeitumfang',
	"[PatientIntegrationAssistance Box Name]" => "Eingliederungshilfe",
	'PatientIntegrationAssistance_legend' => 'Eingliederungshilfe',
	//--
	
    //ISPC-2773 Lore 14.12.2020
    "[PatientFamilyInfo Box Name]" => "Familie",
    "PatientFamilyInfo_legend" => "Familie",
    "Marital_status_of_parents" => "Familienstand der Eltern",
    "divorced_text" => "Getrennt/geschieden seit:",
    "widowed_text" => "Verwitwet seit:",
    "parental_consanguinity" => "Konsanguinität der Eltern",
    "child_residing" => "Kind wohnhaft bei",
    "child_residing_text" => "Sonstiges:",
    //.
    
    //ISPC-2776 Lore 15.12.2020
    "[PatientChildrenDiseases Box Name]" => "(Kinder)Krankheiten",
    "PatientChildrenDiseases_legend" => "(Kinder)Krankheiten",
    "last_vaccination" => "Letzte Impfung:",
    "next_vaccination" => "Nächste Impfung:",
    "rotavirus" => "Rotaviren",
    "varicella" => "Varizellen",
    "measles" => "Masern",
    "mumps" => "Mumps",
    "rubella" => "Röteln",
    "pertussis" => "Pertussis",
    "diphtheria" => "Diphterie",
    "tetanus" => "Tetanus",
    "hib" => "HIB",
    "polio" => "Polio",
    "pneumococci" => "Pneumokokken",
    "meningococci" => "Meningokokken",
    "hepatit_a" => "Hepatitis A",
    "hepatit_b" => "Hepatitis B",
    "tuberculosis" => "Tuberkulose",
    "hpv" => "HPV - Humane Papillomviren",
    //.
    
	//ISPC-2774 Carmen 17.12.2020
	"[PatientTherapy Box Name]" => "Therapien",
	"therapies" => "Therapien",
	"therapy" => "Therapie",
	"extratherapy" => " ",
    
    'positiv_afirmation'=>"ja",
    'neutral_afirmation'=>"neutral",
    'negative_afirmation'=>"nein",
    
	//--
	
    //ISPC-2788 Lore 08.01.2021
    "[PatientNutritionInfo Box Name]" => "Ernährung II",
    "PatientNutritionInfo_legend" => "Ernährung II",
    "nutritional_status" => "Ernährungszustand",
    "General" => "Allgemein",
    "allergies_opt" => "Allergien",
    "food" => "Nahrung",
    "oral_opt" => "Oral",
    "oral_offer_opt" => "Orales Angebot",
    "tube_feeding_opt" => "Sondenkost",
    "rinsing_required_opt" => "Nachspülen erforderlich",
    "food_consistency_opt" => "Nahrungskonsistenz",
    "independence" => "Selbstständigkeit",
    "enrichment_required_opt" => "Anreicherung erforderlich",
    "food_preferences_text" => "Vorlieben",
    "food_dislikes_text" => "Abneigungen",
    "food_particular_text" => "Gewohnheiten/Besonderheiten",
    "food_particular_label" => "Gewohnheiten/ Besonderheiten",
    "food_meals_text" => "Mahlzeiten",
    "liquid" => "Flüssigkeit",
    "manufacturer_text" => "Hersteller",
    "thicken_opt" => "Andicken",
    "administration_opt" => "Verabreichung",
    "amount_text" => "Max. Menge",
    "liquid_preferences_text" => "Vorlieben",
    "liquid_dislikes_text" => "Abneigungen",
    //.
    
    //ISPC-2787 Lore 11.01.2021
    "[PatientStimulatorsInfo Box Name]" => "Stimulatoren",
    "PatientStimulatorsInfo_legend" => "Stimulatoren",
    "vagus_opt" => "Vagusstimulator",
    "pacemaker_opt" => "Herzschrittmacher",
    //.
    
    //ISPC-2790 Lore 12.01.2021
    "[PatientFinalPhase Box Name]" => "Finale Phase",
    "PatientFinalPhase_legend" => "Finale Phase",
    "preparation" => "Vorbereitung",
    "death_discussed_opt" => "Das Thema Sterben/ Tod wurde thematisiert",
    "undertaker_informed_opt" => "Bestatter wurde informiert",
    "coffin_chosen_opt" => "Sarg wurde ausgesucht",
    "how_was_informed_opt" => "Information an",
    "After_dying" => "Nach dem Versterben",
    "care_child_opt" => "Versorgung des Kindes",
    "laying_out_opt" => "Aufbahrung",
    "memento_desired_opt" => "Erinnerungsstück gewünscht",    
    //.
    
    //ISPC-2791 Lore 13.01.2021
    "[PatientExcretionInfo Box Name]" => "Ausscheidung",
    "PatientExcretionInfo_legend" => "Ausscheidung",
    "incontinence" => "Inkontinenz",
    "wears_diapers_opt" => "Trägt Windeln",
    "toilet_training_opt" => "Toilettentraining",
    "toilet_chair_opt" => "Toilettenstuhl",
    "abdominal_massages_opt" => "Bauchmassagen",
    "Last bowel movement" => "Letzter Stuhlgang",
    "consistency" => "Konsistenz",
    "frequency_text" => "Häufigkeit",
    "stimulate_opt" => "Stimulieren",
    "intestinal_tube_opt" => "Darmrohr",
    "digital_clearing_opt" => "Digitales Ausräumen",
    "urninating" => "Urninieren",
    "uses_templates_opt" => "Benutzt Vorlagen",
    "urine_bottle_opt" => "Urinflasche",
    "urine_condom_opt" => "Urinalkondom",
    "catheterization_opt" => "Einmalkatheterismus",
    "Menstruation" => "Menstruation",
    "vomit_opt" => "Erbrechen",
    //.
    
	//ISPC-2381 Carmen 13.01.2021
	"[PatientAids Box Name]" => "Hilfsmittel",
	'patient_aids' => 'Hilfsmittel',
	'aid_err' => 'Es wird ein Wert benötigt. Dieser darf nicht leer sein',
	'needed' => ' ',
	'aids_type' => 'Art',
	'aids_other_type' => 'Sonstiges',
	'aids_first_prescription' => 'Seit(Erstverordnung)',
	'aids_first_prescription_unknown' => 'Seit(Erstverordnung)',
	'aids_diopter_right_eye' => 'Diopter rechtes Auge',
	'aids_diopter_left_eye' => 'Diopter linkes Auge',
	'aids_manufaturer_or_model' => 'Hersteller/Modell',
	'aids_last_change' => 'letzte Änderung',
	'aids_last_change_info' => ' ',
	'aids_supplier' => 'Ansprechpartner(Versorger)',
	'aids_special_features' => 'Beschreibung/Besonderheiten',
	'aid' => 'Hilfsmittel',
	'aids_localization' => 'Lokalisation',
	'aids_other_localization' => 'Sonstiges',
	'wearing_time' => 'Tragedauer',
	'wearing_time_period' => 'Tragezeit',
	'aids_put_on_socks' => 'Socken unterziehen',
	'aids_other_put_on_socks' => 'Sonstiges',
	'current_running_rate' => 'Aktuelle Laufrate',
	'aids_communication_help' => 'Kommunikationshilfen',
	'aids_other_communication_help' => 'Sonstiges',
	'aids_wearing_time_period' => 'Tragezeit',
	'aids_other_wearing_time_period' => 'Sonstiges',
	'aids_bruises' => 'Druckstellen',
	'aids_other_bruises' => 'Sonstiges',
	'aids_cloth_size' => 'Tuchgröße',
	
    //ISPC-2792 Lore 15.01.2021
    "[PatientPersonalHygiene Box Name]" => "Haut- und Körperpflege",
    "PatientPersonalHygiene_legend" => "Haut- und Körperpflege",
    "maintenance_condition" => "Pflegezustand",
    "mucosal_texture" => "Schleimhaut beschaffenheit",
    "skin_texture" => "Hautbeschaffenheit",
    "assessment_scale_opt" => "Einschätzungsskala",
    "pressure_ulcer_risk_opt" => "Dekubitusrisiko",
    "personal_hygiene" => "Körperpflege",
    "pressure_ulcer_opt" => "Dekubitus",
    "own_care_products_opt" => "Eigene Pflegeprodukte",
    "nail_care_allowed_opt" => "Nagelpflege erlaubt",
    "basal_stimulation_opt" => "Basale Stimulation bekannt",
    "habits_particularities" => "Gewohnheiten/ Besonderheiten",
    "mattress_opt" => "Matratze",
    "tools_opt" => "Hilfsmittel",
    "Dental_and_oral_care" => "Zahn- und Mundpflege",
    "dental_care_opt" => "Zahnpflege",
    "braces_opt" => "Zahnspange",
    "oral_care_opt" => "Mundpflege",
    //.
    
    //ISPC-2793 Lore 18.01.2021
    "[PatientCommunicationEmployment Box Name]" => "Kommunikation und Beschäftigung",
    "PatientCommunicationEmployment_legend" => "Kommunikation und Beschäftigung",
    "verbal_utterances_opt" => "Verbale Äußerungen möglich",
    "speech_understanding_opt" => "Sprachverständnis vorhanden",
    "communication_opt" => "Kommunikationsweg",
    "restlessness_opt" => "Unruhe",
    "communication" => "Kommunikation",
    'preferences_interests' => 'Vorlieben und Interessen',
    'habits' => 'Gewohnheiten',
    'dislikes' => 'Abneigungen',
    'sole_occupations' => 'Alleinige Beschäftigungen',
    'employment' => 'Beschäftigung',
    //.

    //ISPC-2831 Dragos 15.03.2021
    "required_meta_name" => "Name der Datei wird benötigt.",
    "meta_name" => "Vergeben Sie einen Namen",
);		
