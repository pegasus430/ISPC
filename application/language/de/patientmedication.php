<?php
return array(
		
    "Sort by" =>"Sortieren nach",
    "SORT BY" =>"Sortieren nach",
    "medication_importance" => "Reihenfolge",
// Maria:: Migration ISPC to CISPC 08.08.2020
    "No camera available" => "Keine Kamera verfügbar",
    //ISPC-2524 pct.2)  Lore 15.01.2020
    "move_medi_actual_to_bedarf" => "zu Bedarfsmedikation verschieben", // Edit page: Checkbox label - move medication from actual to bedarf//ISPC-2664 Ancuta 16.10.2020
    "move_medi_bedarf_to_actual" => "zu Medikation verschieben", // Edit page: Checkbox label- move medication from bedarf to actual
    
    "medication was moved from bedarf to active" => "wurde von Bedarfsmedikation zu Medikation verschoben",// History - info line- move from bedarf to actual//ISPC-2664 Ancuta 16.10.2020
    "medication was moved from active to bedarf" => "wurde von Medikation zu Bedarfsmedikation verschoben",// History - info line- move from actual to bedarf//ISPC-2664 Ancuta 16.10.2020
    
    
    // --
    
    "Keine Medikation" => "Keine Medikation",
    //ISPC-2247 pct.1 Lore 11.05.2020
    'ATTENTION  - the set time scheme  does not correspond with the patient time scheme - and the dosages will not be transferred ' =>'Achtung - Dieses Zeitschema ist nicht mit dem beim Patienten  hinterlegten Zeitschema identisch. Die Dosierung kann nicht übernommen  werden.',

    //ISPC-2551  Ancuta 17.08.2020
    "datamatrix_xml_option" => "XML",
    "datamatrix_upload_option" => "Datei hochladen",
    "datamatrix_scan_option" => "Kamera",
		

	//ISPC-2664 Carmen 29.09.2020,
	'change weight' => 'Gewicht aktualisieren',
	'change height' => 'Größe aktualisieren',
	'do you want to change current item?' => 'Möchten Sie diesen Wert ändern? -> %item?', //ISPC-2713,elena,13.01.2021
	'confirm_change_title' => 'Gewicht / Körpergröße eintragen',
	'The input cant be empty' => 'Das EIngabefeld kann nicht leer sein',
	'height' => 'Größe',
	//--
		
    //TODO-3462 Ancuta 19.10.2020
	'Users that are informed about not accepted phatma requests' => 'DEMSTEPCARE: Benutzer die informiert werden, wenn eine Empfehlung nicht angenommen wurde',
	'mail_subject_action_pharma_request' => 'DemStepCare - Empfehlung wurde nicht angenommen',
	'medication_pharme_request title' => 'DemStepCare - Empfehlung wurde nicht angenommen',
    // --
    
    
    //ISPC-2797 Ancuta 17.02.2021
    'plan to delete medi  in future?' => "Medikament geplant absetzen",
    'medication_planned_removed_date_date' => "Absetzen am",
    'Medi is set to start in future' => "Medikament wird angesetzt",
    
    'medication_planned_start_date_date' => "Angesetzt für den",
    'ISPC-2797-date in past it is not allowed!' => "Ein Datum in der Vergangenheit ist nicht erlaubt.",
    'Planned medication was edited: ' => "Geplantes Medikament wurde editiert: ",
    'Plann this medi to be added in future?' => "Medikament geplant ansetzen",
    'process_plan_NOW' =>'process NOW',
    'are you sure you want to process this medi plan?' =>'are you sure you want to process this medi plan[DS]?',
    'confirmprocesstitle' =>'confirmprocesstitle[de]?',
    
    'process_plan_NOW' =>'Änderung JETZT durchführen', // BUTTON
    'are you sure you want to process this medi plan?' =>'Sind Sie sicher, dass Sie diese Änderung jetzt durchführen wollen?', //alert text
    'confirmprocesstitle' =>'Bitte bestätigen',// alert title
    
    
    
    //ISPC-2833 Ancuta 01.03.2021
    'medication_ispumpe' =>'Perfusor / Pumpe', 
    'ispumpe medication title' =>'Perfusor / Pumpe', 
    'ispumpe medication title ' =>'Perfusor / Pumpe', 
    'ispumpe_pumps' =>'Perfusor / Pumpe', 
    'ispumpe_overall_volume' =>'Zielvolumen Pumpe (ml)', 
    'ispumpe_pat_weight' =>'Gewicht (kg)',
    'ispumpe_run_rate' =>'gewünschte Laufrate (ml/h)',
    'ispumpe_used_liquid' =>'Trägerlösung',
    
    'medication_ispumpe_dosage'=>'Wirkstoffmenge', 	
    'ispumpe_overall_dosage_h'=>'Gesamtdosis pro Stunde', 	
    'ispumpe_overall_dosage_24h'=>'Gesamtdosis in 24h',
    'ispumpe_overall_dosage_pump'=>'Gesamtdosis pro Pumpe',
    'ispumpe_overall_drug_volume'=>'Volumen des Wirkstoff',
    'ispumpe_unit2ml'=>'entspricht Einheit / ml',//ISPC-2871,Elena,15.04.2021 typo
    
    
    'ispumpe medication add'=>'Neue Perfusor / Pumpe Medikation',
    'new ispumpe  medication line'=>'Neue Perfusor / Pumpe Medikation',
    'create_new_ispumpe'=>'Neue Perfusor / Pumpe',
    
    
    'ispumpe_overall_volume' => 'Zielvolumen Pumpe (ml)',
    'ispumpe_run_rate'  =>  'gewünschte Laufrate (ml/h)',//ISPC-2871,Elena,15.04.2021 typo
    'ispumpe_used_liquid'  => 'Trägerlösung',//ISPC-2871,Elena,15.04.2021 typo
    'ispumpe_pat_weight'  => 'Gewicht (kg)',
    
    'ispumpe_pumpe_overall_drug_volume'  => 'Gesamtvolumen aus Wirkstoffen',
    'ispumpe_liquid_amount'  => 'Pumpe auffullen mit (ml)',
    
    'ispumpe_overall_running_time'  => 'Laufzeit (ohne Bolus) in h',
    'ispumpe_min_running_time'  => 'Laufzeit min. (mit Bolus)',
    'ispumpe_bolus'  => 'Bolusmenge (in ml)',
    'ispumpe_max_bolus_day'  => 'max Bolus pro Tag',
    'ispumpe_max_bolus_after'  => 'max Bolus hintereiander',
    'ispumpe_next_bolus' =>'Sperrzeit (in Min.)',
    
);		