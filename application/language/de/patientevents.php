<?php
//#ISPC-2512PatientCharts  
return array(
    //ISPC-2517 Ancuta 15.04.2020
    "eventsModal_lang" => array(
        
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
    
    
    
    //ISPC-2517 Ancuta 15.04.2020
    "vital_signs_modal_button" => "Vitalzeichen",
    //"awake_sleep_status_modal_button" => "Verhaltensbeobachtung",
    "awake_sleep_status_modal_button" => "Wach-Schlaf-Rhythmus",        //ISPC-2517 pct.i Lore 21.05.2020
    "organic_entries_exits_modal_button" => "Ein- und Ausfuhr",
    "contact_form_main_modal_button" => "Kontaktformular",
    "contact_form_items_modal_button" => "",
    "custom_events_modal_button" => "Ereignisse",
    "positioning_modal_button" => "Positionierung",
    "suckoff_modal_button" => "Absaugen",
	"artificial_entries_exits_modal_button" => "Zu- und Abgänge",
	"symptomatology_modal_button" => "Symptome",
	"medication_dosage_interaction_modal_button" => "Medikation",
	"medication_dosage_interaction_label" => "Medikation",
	"symptomatologyII_modal_button" => "Symptome II", //ISPC-2516 Carmen 15.07.2020
    
    // MANAGENENT PAGE
    //"Fieberkurve - CLient permissions to  events:"=>"Patientenkurve - Rechte für Blöcke der Patientenkurve", //Administration page - allow events per client
    //ISPC-2841 Lore 22.03.2021
    "Fieberkurve - CLient permissions to  events:"=>"Button - Rechtevergabe", //Administration page - allow events per client
    "show in chart"=>"in der Patientenkurve anzeigen",          //ISPC-2841 Lore 29.03.2021
    //"available in + button"=>"in 'Button verfügbar'",//Administration page - allow events per client -column name
    "available in + button"=>"im Plus-Button verfügbar",       //ISPC-2841 Lore 29.03.2021
    "ventilation_info_label" => "Beatmung II",                             //ISPC-2841 Lore 29.03.2021
    
    "Fieberkurve - Groups events management"=>"Fieberkurve Gruppenrechte",// Client setting page- permission per groups and order 
    "For this client there are no events available!"=>"For this client there are no events available!",
    "events_names"=>"  ",       //TODO-3271 Lore 27.07.2020 // Maria:: Migration ISPC to CISPC 08.08.2020
    "vital_signs_label"=>"Vitalzeichen",
    "organic_entries_exits_label"=>"Ein- und Ausfuhr",
    "awake_sleep_status_label"=>"Verhaltensbeobachtung",
    "contact_form_main_label"=>"Kontaktformular",
    "contact_form_items_label"=>"Kontaktformular - Arten*",
    "custom_events_label"=>"Ereignisse",
    "positioning_label"=>"Positionierung",
    "suckoff_label"=>"Absaugen",
	"artificial_entries_exits_label"=>"Zu- und Abgänge",
    "symptomatology_label" => "Symptome",
	"symptomatologyII_label" => "Symptome II",
    
    
    "positioning_type_err"=>"Bitte Art auswählen",
    "custom_name_type_err"=>"Name fehlt",
    "custom_date_time_type_err"=>"Datum fehlt",
    
	"medication_dosage_view_label" => "Medikation",
		
	//ISPC-2517
	'Symptom values are from 0 to 10, you entered: ' => 'Symptom Werte dürfen zwischen 0 und 10 liegen. Sie haben  eingegeben: ',
	'save and go back to chart' => 'Speichern und zurück zur Kurve', // button
	//--
		
	//ISPC-2661 pct.14 Carmen 16.09.2020
	'start_bilancing' => 'Starte Bilanzierung',
	'end_bilancing' => 'Ende Bilanzierung',
	"positioning_date_time_type_err"=>"Datum fehlt",
	"awake_sleeping_date_time_type_err"=>"Datum fehlt",
	'Start date and time must be filled' => 'Bitte das Start-Datum ausfüllen.',
	'End date and time must be filled' => 'Bitte das Ende-Datum ausfüllen.',
	'end date and time must be filled' => 'Bitte das Ende-Datum ausfüllen.',
	'err_datefuture' => 'Ein Datum in der Zukunft ist nicht erlaubt.',
	'End time must be bigger than start date' => 'Das Ende-Datum muss nach dem Start-Datum liegen.',
	//--
		
	//ISPC-2683 carmen 15.10.2020
	"vigilance_awareness" => "Vigilanz & Bewusstsein",
	"vigilance_awareness_label" => "Vigilanz & Bewusstsein",
	"vigilance_awareness_modal_button" => "Vigilanz & Bewusstsein",
	'vigilance_awareness_date' => 'Datum',
	'awareness_name_type_err' => 'Name fehlt',
	'awareness' => 'Bewusstsein',
	'aw_orientation' => 'Orientierung',
	'aw_awake' => 'wach',
	'aw_somnolent' => 'somnolent',
	'aw_soporous' => 'soporös',
	'aw_comatose' => 'komatös',
	'aw_ort' => 'Ort',
	'aw_person' => 'Person',
	'aw_situation' => 'Situation',
	'aw_zeit' => 'Zeit',
	'aw_keineorient' => 'Keine Orientierung ',
	//--
	
    //ISPC-2697, elena, 10.11.2020
    'beatmung_modal_button' => 'Beatmung',
    'beatmung_label' => 'Beatmung',
);		