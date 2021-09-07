<?php
return array(
	// 	ISPC 1994 
		"registertextslist_lang" => array( 
			"legend" => "Register standard texts ", 
			"related_textarea" => "Felder", 
			"texts" => " ", 
			"edit" => "Edit", 
			"add" => "Add", 
		),	
	// 	ISPC 2020 
		"formstextslist_lang" => array(
			"list" => "Liste Formular Satzbausteine",
			"legend" => "Formular Satzbausteine", 
			"form_name" => "Formular", 
			"related_textarea" => "Felder", 
			//"texts" => "",
			"freetext" => "Kommentar",
			"edit" => "Bearbeiten", 
			"add" => "Hinzufügen",
				
			"patientformnew/sisambulant"=> array(
				"patientformnew/sisambulant"=>"SIS - Ambulant",
				"movement_tr"=>"AWas bewegt Sie im Augenblick? Was brauchen Sie? Was können wir für Sie tun?",
		        "cognitive_tr"=>"Themenfeld 1 - kognitive und kommunikative Fähigkeiten",
		        "mobility_tr"=>"Themenfeld 2 - Mobilität und Beweglichkeit",
		        "diseaserelated_tr"=>"Themenfeld 3 - krankheitsbez. Anforderungen u. Belastungen",
		        "selfcatering_tr"=>"Themenfeld 4 -Selbstversorgung",
		        "socialrelations_tr"=>"Themenfeld 5 - Leben in sozialen Beziehungen",
		        "financialmanagement_tr"=>"Themenfeld 6 - Haushaltsführung",
			),	
				
			"patientnew/hospizregisterv3" => array(
				"patientnew/hospizregisterv3" =>"Horspizregister",
				'aufwand_mit_tr'=>'besonderer Aufwand mit: ',
				'bedarf_tr'=>'Behandlungs- und Begleitungsbedarf',
				'massnahmen_tr'=>'Maßnahmen',
			),
			
			"patientformnew/sisstationary"=> array(
					"patientformnew/sisstationary"=>"SIS - Stationär",
					"movement_tr"=>"AWas bewegt Sie im Augenblick? Was brauchen Sie? Was können wir für Sie tun?",
					"cognitive_tr"=>"Themenfeld 1 - kognitive und kommunikative Fähigkeiten",
					"mobility_tr"=>"Themenfeld 2 - Mobilität und Beweglichkeit",
					"diseaserelated_tr"=>"Themenfeld 3 - krankheitsbez. Anforderungen u. Belastungen",
					"selfcatering_tr"=>"Themenfeld 4 -Selbstversorgung",
					"socialrelations_tr"=>"Themenfeld 5 - Leben in sozialen Beziehungen",
					"financialmanagement_tr"=>"Themenfeld 6 - Haushaltsführung",
			),
		    
		    "mambo/assessment" => [
		        "mambo/assessment" => "Mambo Assessment",
		        "feedback_tr" => "Feedback",
		    ],
		    
		    "anyform" => [
		        "anyform" => "Alle Formulare",
		        'todo_tr' => 'TODO',
		    ],
// Maria:: Migration ISPC to CISPC 08.08.2020
		    // ISPC-2507 Lore 31.01.2020
		    // Liste Formular Satzbausteine  - where reasons can be defined per client
	        "patientmedication/requestchanges" => [
	            "patientmedication/requestchanges" => "Apotheke - Gründe Satzbausteine",
	            "pharmacymedicheck_tr" => "Gründe",
	        ],
		    //.
		    //. 
		),
		
		//ISPC-2508 Carmen 22.01.2020 - label/header for client artificial entries exits list start
	    // #ISPC-2512PatientCharts
		//Ancuta added to Clinic ISPC (CISPC)  on 18.03.2020
		'artificial_option_name' => 'Name', 
		'artificial_option_type' => 'Typ',
		'artificial_option_localization_available' => 'mit Lokalisation',
		'artificial_option_days_availability' => 'Wechsel nach',
		
		'artificial_entries_exits_list' => 'Liste künstl. Zugänge / Ausgänge', //list title
		'map_old_entries_exits' => 'Alte Einträge verknüpfen', //title for map old entries exits
		'clientartificialentryexit_edit' => 'Zugang / Ausgang bearbeiten', //title for edit client entry/exit
		'clientartificialentryexit_add' => 'Zugang / Ausgang hinzufügen', //title for add client entry/exit
		//ISPC-2508 Carmen 22.01.2020 - label/header for client artificial entries exits list end
		
    
        //ISPC-2520 Lore 08.04.2020   
        'organic_name' => 'Einfuhr / Ausfuhr',      //TODO-3271 Lore 15.07.2020
        'organic_type' => 'Typ',                    //TODO-3271 Lore 15.07.2020
        'organic_shortcut' => 'Kürzel',                     //TODO-3271 Lore 15.07.2020
        'organic_entries_exits_list' => 'Liste Einfuhr / Ausfuhr',              //TODO-3271 Lore 15.07.2020 
        'clientorganicentryexit_edit' => '[Einfuhr / Ausfuhr] bearbeiten',              //TODO-3271 Lore 15.07.2020
        'clientorganicentryexit_add' => '[Einfuhr / Ausfuhr] hinzufügen',              //TODO-3271 Lore 15.07.2020
        //.
        
    //ISPC-2654 Lore 05.10.2020
    'icd_ops_mre_settings_list' => '(Neue) Diagnosen Einstellungen', // title of list of settings of category diagnosis
    'icd_ops_mre_settings_edit' => '(Neue) Diagnose Kategorie bearbeiten', // button to edit setting category diagnosis
    'icd_ops_mre_sorting_list' => '(Neue) Diagnosen Sortierung',   // title of list of settings of sort diagnosis
    'icd_ops_mre_sorting_add' => '(Neue) Diagnosen Sortierung',    // button to add sort category diagnosis
    'icd_ops_mre_sorting_edit' => '(Neue) Diagnosen Sortierung',   // button to edit sort category diagnosis
    'select_category_color' => 'Diagnose Farbe',         // label of input in add/edit setting category diagnosis
    'main_sort_col' => 'Erste Sortierung',                         // label of input in add/edit setting category diagnosis
    'secondary_sort_col' => 'Zweite Sortierung',                // label of input in add/edit sorting category diagnosis
    'sort_order' => 'Reihenfolge',                               // label of input in add/edit sorting category diagnosis
    //.
	        //ISPC-2864 ANcuta 13.04.2021
    "clientproblemslist_list_Title"=>"Behandlungsprozess - Probleme",       //ISPC-2864 Lore 16.04.2021
    "clientproblemslist_problem_name"=>"Probleme",                          //ISPC-2864 Lore 16.04.2021
    "clientproblemslist_add"=>"Neuer Eintrag",                       //ISPC-2864 Lore 16.04.2021
    "clientproblemslist_edit"=>"Bearbeiten",                         //ISPC-2864 Lore 16.04.2021
    
    
    
    
);