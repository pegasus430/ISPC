<?php
return array(
		
		// 	ISPC 2056 - ACP box in Stammdaten
		"acp_file_living_will" => "ACP-Patientenverfügung", //if not empty, will use this to prefix the filenames
		"acp_file_care_orders" => "ACP-Betreuungsverfügung", //if not empty, will use this to prefix the filenames
		"acp_file_healthcare_proxy" => "ACP-Vorsorgevollmacht", //if not empty, will use this to prefix the filenames 
		
		"acp_box_lang" => array(
				
				"box_title"=>"Advance Care Planning",
				"living_will"=>"Patientenverfügung",
				"care_orders"=>"Betreuungsverfügung",
				"healthcare_proxy"=>"Vorsorgevollmacht",
				
				//this 3 are used for box history only
				"active" => "Status",
				"contactperson_master_id" => "Ansprechpartner",
				"comments" => "wo hinterlegt",
				
				
				"contact person in charge" => "Ansprechpartner",
				"from when" => "ausgestellt am:", // file date
				"where deposited" => "wo hinterlegt", // comment
// 				"Please first select file date" => "Bitte wähle die erste Datei aus",
				"Please first select file date" => "Bitte füllen Sie die Felder, 'ausgestellt am:' und 'wo hinterlegt' aus.",
				"file download" => "Datei Download",
				"old version" => "alte Version",
				
		),	
    
    "orientationII_box_lang" => array(
        "box_title"=>"Orientierung II",
    ),
    
    "mobilityII_box_lang" => array(
        "box_title"=>"Mobilität II",
    ),
    
    
    'Legal guardian' => 'Gesetzlicher Betreuer',
    'is the contact phone number' => 'ist die Kontakt-Telefonnummer',
    
    "Please select a patient first" => "Bitte vor dem Speichern Patient auswählen",
    // ISPC-ISPC-2171
    "ppun_label" => "Debitoren-Nr. Privatpatient", // label in form
    "generate_ppun" => "Nummer erzeugen", // name of button
    "ppun_error" => "Debitorennummer muss einzigartig sein!",// error
    
    //ISPC-2204
    "[WL Anlage 3 2018]" => "Teilnahme-/Einwilligungserklärung des Versicherten", // legend for form
    "[Anlage 3 2018]" => "Teilnahmeerklärung (Anlage 3) 2018", // filename
    "[Page %s from %s (%s) - Anlage 3 2018]" => "Seite %s von %s (%date) - Teilnahmeerklärung (Anlage 3) 2018", //footer on the pdf
    
	//ISPC -2225
	"uploadfile_user_new" => "Schritt 1",
	"file_upload_tags_new" => "Schritt 2: Etiketten hinzufügen",
		
	//ISPC - 2243
	"To the patient has been" => "Dem Patienten/ der Patientin wurde",
	"assigned" => "zugeordnet",
	"The user" => 'Der Benutzer',
	"has been unassigned" => "wurde als Behandler entfernt.",
    
    // ISPC-2257
	"shift_billing" => "Möchte abrechnen",
		
	//ISPC - 1148
	"sapv_verordnung" => "Verordnung",

    
    "Sort by ftype" => "Sort by ftype",
    "Sorted by ftype" => "Sorted by ftype",
    "Sort by title" => "Sort by title",
    "Sorted by title" => "Sorted by title",
    "Sort by date" => "Sort by date",
    "Sorted by date" => "Sorted by date",
    "icon sort order asc" => "↑",
    "icon sort order desc" => "↓",
    
    //ISPC - 2365 // Maria:: Migration ISPC to CISPC 08.08.2020
	'print_rp_forms' => 'RP Leistungsnachweis-Stapeldruck',
    
    
    //ISPC-2411
    'survey_email'=>'Email Adresse',
    'survey_interval_days' => 'Intervall(Tage)',
    'no_survey_email' => 'Keine Email Adresse',
	
	//ISPC-2396 Carmen 08.10.2019 
	/* 'delete_clienticons_from_patient' => 'delete_clienticons_from_patient', //bulk button in patientlist
	'clienticon_select' => 'clienticon_select', //label for select icons to be added at admission - box in admission
	'2396 - bulk remove patient icons' => '2396 - bulk remove patien ticons', //title for the modal in patientlist
	'Info text - For all selected patients can choose the icons to be removed' => 'Info text - For all selected patients can choose the icons to be removed', //info text for remove icon
	'remove patient icons' => 'remove patient icons', //title for the modal in patientlist
	'no patient selected' => 'no patient selected', //alert if no patient was selected from patientlist
	'no icons to be deleted selected' => 'no icons to be deleted selected', //alert if no icon to be remove was selected
	'Info text - Choose the icons to be assigned to patient at admission' => 'Info text - Choose the icons to be assigned to patient at admission', //info text for add icon at admission
	'Aufnahme - Client Icons to be added at admission' => 'Aufnahme - Client Icons to be added at admission', //form name in formslist and box name in overviewboxsettings
	 */	
    
    'client icons to be added at admission' => 'Icons zum Patienten hinzufügen', //Admission Block
    'no icons to be deleted' => 'Keine Icons zum Löschen vorhanden', //No Icons can be deleted from selected patients in list
    
    'delete_clienticons_from_patient' => 'Icons bei ausgewählten Patienten löschen', //bulk button in patientlist
    'clienticon_select' => 'Icon auswählen', //label for select icons to be added at admission - box in admission
    '2396 - bulk remove patient icons ' => 'Icons entfernen', //title for the modal in patientlist
    'Info text - For all selected patients can choose the icons to be removed' => 'Für alle ausgewählten Patienten werden die selektierten icons entfernt', //info text for remove icon
    'remove patient icons' => 'Icons entfernen', //title for the modal in patientlist
    'no patient selected' => 'Kein Patient ausgewählt', //alert if no patient was selected from patientlist
    'no icons to be deleted selected' => 'keine Icons ausgewählt', //alert if no icon to be remove was selected
    'Info text - Choose the icons to be assigned to patient at admission' => 'Wählen Sie die Icons aus die bei der Aufnahme hinzugefügt werden sollen', //info text for add icon at admission
    'Aufnahme - Client Icons to be added at admission' => 'Aufnahme - Auto. Hinzufügen von Icons', //form name in formslist and box name in overviewboxsettings
    
	//ISPC-2426 Carmen 05.11.2019
	'print_sh_forms_filled' => 'Leistungsnachweis-Stapeldruck (befüllt)', //group name for filled sh Leistungsnachweis
	
	//ISPC-2409 
	'patient print excel  -all columns' => 'Excel(*.xlsx) - Alle Spalten ', 
	'patient print excel  -viewable columns' => 'Excel(*.xlsx) - nur sichtbare Spalten ', 
		
    
    //ISPC-2479 Ancuta 01.11.2020
    "pl_off"=> "nicht anzeigen",
    "pl_on" =>"anzeigen",
    "pl_primary" =>"als Primärspalte anzeigen (max. 6)",
    
    //TODO-3901 Ancuta 28.05.2021
    "patient_address_details" =>"Wohnort Patient",
    
    
    
);