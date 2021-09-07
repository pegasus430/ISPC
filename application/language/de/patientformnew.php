<?php
return array(
		
		"sgbvleistungsnachweispdf" => "SGB V Leistungsnachweis",
		"sgbvleistungsnachweispdf pdf footer text" => "Seite %s von %s (%s) - SGB V Leistungsnachweis", //footer on the pdf
			
		"sgbvleistungsnachweispdf_lang" => array(
		
				"edit_action" =>"Speichern",//dialog buttons
				"remove_action" =>"Löschen",
				"noconfirm" =>"Abbrechen",
		
				"add_group_button" => "Neuen Besuch erstellen",
		
				"add_new_group_dialog_title" => "dialog add new group title",
				"add_new_group_dialog_date" => "select hours text",
		
				"add_hour_title" => "Besuchszeit festlegen",
				"add_hour_date" => "Wann ist der Besuch geplant",
		
				"add_action_dialog_title" => "Leistung buchen",
				"add_action_dialog_actions" => "Leistung auswählen",
				"add_action_dialog_startdate" => "ab wann (Datum)",
				"add_action_dialog_interval" => "Interval-Regeln",
		
		
				'interval_daily' => 'täglich',//radio label
				'interval_daily_infotext' => 'Leistung wird ab Datum täglich gebucht',//infotext
				'interval_every_x_days' => 'alle ... Tage',//radio label
				'interval_every_x_days_infotext' => 'Leistung wird alle ... Tage gebucht',
				'interval_selected_days_of_the_week' => 'Wochentage auswählen',//radio label
				'interval_selected_days_of_the_week_infotext' => 'An welchen Tagen soll die Leistung gebucht werden?',
		
    		    'interval_user_defined_days' => 'unregelmäßig',
    		    'interval_user_defined_days_infotext' => '',
    		    
    		    
				'remove_groupid_dialog_title' => 'Gruppe löschen',
				'remove_groupid_dialog_infotext' => 'Sind Sie sicher, dass Sie die Gruppe löschen wollen?',
		
				'save' => 'Nur speichern',
				'save_and_pdf' => 'Speichern und PDF erstellen',
		
				//3 gray rows
				'einsatz_std'=>'Einsatz Std.',
				'einsatz_min'=>'Einsatz Min.',
				'signature'=>'Handzeichen',
		
				//header table
				"sgb_v_pdf_title" => "Leistungsnachweis SGB V - häuslichen Krankenpflege - ",
				"head_pflegedienste" => "Pflegedienst",
				"head_patient" => "Patient",
				"head_period" => "Leistungszeitraum",
				"head_name" => "Name:",
				"head_ik_nr" => "IK-Nr.:",
				"head_vers_nr" => "Vers.-Nr.:",
				"head_insurance_name" => "Krankenversicherung:",//ISPC-2211
				"head_street" => "Straße:",
				"head_birthd" => "Geb.-Datum:",
				"head_plz_ort" => "PLZ/Ort:",
				"head_professional" => "Berufsverband:",
				"head_18abs" => "§ 18 Abs. 2 u.3 ja*)<input type=\"radio\" name=\"ja_nein\" value=\"ja\" style=\"font-size:18px;\" />, <label style=\"font-size:12px;\">nein *)<input type=\"radio\" name=\"ja_nein\" value=\"nein\" style=\"font-size:18px;\"/></label>",
		
				"infotext_table_botttom" => "*) Bitte stets angeben. Gilt die Kennzeichnung mit ja nur für Teilzeiträume, ist das Feld \"100%-Vergütung\" einsatzbezogen anzukreuzen.",
				"expand_table" => "Tabelle erweitern",//2 buttons, first to expand 31days table, second to hide the top table for more space
				"show_header" => "Zeige / Verberge Seitenkopf",
		
		
		),
		
		"sgbxileistungsnachweispdf" => "SGB XI Leistungsnachweis",
		"sgbxileistungsnachweispdf pdf footer text" => "Seite %s von %s (%s) - SGB XI Leistungsnachweis", //footer on the pdf
		"sgbxileistungsnachweispdf_lang" => array(
		
				"edit_action" =>"Speichern",//dialog buttons
				"remove_action" =>"Löschen",
				"noconfirm" =>"Abbrechen",
		
				"add_group_button" => "Neuen Besuch erstellen",
		
				"add_new_group_dialog_title" => "dialog add new group title",
				"add_new_group_dialog_date" => "select hours text",
		
				"add_hour_title" => "Besuchszeit festlegen",
				"add_hour_date" => "Wann ist der Besuch geplant",
		
				"add_action_dialog_title" => "Leistung buchen",
				"add_action_dialog_actions" => "Leistung auswählen",
				"add_action_dialog_startdate" => "ab wann (Datum)",
				"add_action_dialog_interval" => "Interval-Regeln",
		
		
				'interval_daily' => 'täglich',//radio label
				'interval_daily_infotext' => 'Leistung wird ab Datum täglich gebucht',//infotext
				'interval_every_x_days' => 'alle ... Tage',//radio label
				'interval_every_x_days_infotext' => 'Leistung wird alle ... Tage gebucht',
				'interval_selected_days_of_the_week' => 'Wochentage auswählen',//radio label
				'interval_selected_days_of_the_week_infotext' => 'An welchen Tagen soll die Leistung gebucht werden?',
		
		        'interval_user_defined_days' => 'unregelmäßig',
				'interval_user_defined_days_infotext' => '',
		    
				'remove_groupid_dialog_title' => 'Gruppe löschen',
				'remove_groupid_dialog_infotext' => 'Sind Sie sicher, dass Sie die Gruppe löschen wollen?',
		
				'save' => 'Nur speichern',
				'save_and_pdf' => 'Speichern und PDF erstellen',
		
				//3 gray rows
				'einsatz_std'=>'Einsatz Std.',
				'einsatz_min'=>'Einsatz Min.',
				'signature'=>'Handzeichen',
		
				//header table
				"sgb_xi_pdf_title" => "Leistungsnachweis SGB XI - häuslichen Krankenpflege - ",
				"sgb_xi_pdf_title" => "Leistungsnachweis SGB XI - häusliche Pflege -",
				"head_pflegedienste" => "Pflegedienst",
				"head_patient" => "Patient",
				"head_period" => "Leistungszeitraum",
				"head_name" => "Name:",
				"head_ik_nr" => "IK-Nr.:",
				"head_vers_nr" => "Vers.-Nr.:",
				"head_street" => "Straße:",
				"head_birthd" => "Geb.-Datum:",
				"head_plz_ort" => "PLZ/Ort:",
				"head_professional" => "Berufsverband:",
				"head_18abs" => "§ 18 Abs. 2 u.3 ja*)<input type=\"radio\" name=\"ja_nein\" value=\"ja\" style=\"font-size:18px;\" />, <label style=\"font-size:12px;\">nein *)<input type=\"radio\" name=\"ja_nein\" value=\"nein\" style=\"font-size:18px;\"/></label>",
    		    "head_insurance_name" => "Krankenversicherung:",//ISPC-2211
    		    "head_maintenancestage" => "Pflegegrad:",//ISPC-2211
		
				"infotext_table_botttom" => "*) Bitte stets angeben. Gilt die Kennzeichnung mit ja nur für Teilzeiträume, ist das Feld \"100%-Vergütung\" einsatzbezogen anzukreuzen.",
				"expand_table" => "Tabelle erweitern",
				"show_header" => "Zeige / Verberge Seitenkopf",
		
		),
		
		"movement_tr_label"=>"Was bewegt Sie im Augenblick? Was brauchen Sie? Was können wir für Sie tun?",
		"cognitive_tr_label"=>"kognitive und kommunikative Fähigkeiten",
		"mobility_tr_label"=>"Mobilität und Beweglichkeit",
		"diseaserelated_tr_label"=>"krankheitsbez. Anforderungen u. Belastungen",
		"selfcatering_tr_label"=>"Selbstversorgung",
		"socialrelations_tr_label"=>"Leben in sozialen Beziehungen",
		"financialmanagement_tr_label"=>"Haushaltsführung",

    
    //ispc-2070
    "You have errors on tab %s" => "Sie haben Fehler auf Tab %s",
    "Add new contact person" => "Neue Ansprechpartner hinzufügen",
    "Mobile number" => 'Handy Nummer',
    "Emergency telephone" => 'Notruf Telefon',
    "IK number" => 'IK - ­Nummer',
    "is palliative care service" => "ist Palliativ-Pflegedienst",
    
    'ulceration wound' => 'Exulcerierende Tumorwunde', //ulceration_wound
    'wound grade' => 'Dekubitus Grad', //wound_grade
    'Wound Localization'=>'Wundlokalisation',
    'wound size:' => 'Höhe:',
    'wound depth:' => 'Tiefe:',
    'wound width:' => 'Breite: ',
    
    'Wlassessment_PDF 2017' => 'Wlassessment_PDF 2017', // pdf filename
    'Please fill in start-end time' => 'Bitte geben Sie Start- und Ende Zeit an.',//alert user he must select start-end tim
    'Wlassessment_PDF 2017 was created'=> 'WL-Assessment wurde gespeichert.',// patient course entry (and link with the form)
    
    "PDF of %s was saved in files and documents" => "PDF des %s in Dateien und Dokumente wurde hinterlegt",
    
    //page5
    'Hope bow:' => 'Hope Bogen:',
    'Outpatient hospice service:' => 'Ambulanter Hospizdienst:',
    
    "freetext" => "andere", // used as placeholder for 'other' inputs
    
    "Fees" => 'Gebührenbefreiung',
    
    "Form_PatientExpectedSymptoms" => array(
        'Expected Symptoms / Problems / Difficulties:' => 'Zu erwartende Symptome/Probleme/Schwierigkeiten:',
        'possible_bleeding' => 'Mögliche Blutungen',
        'dyspnoea' => 'Dyspnoe',
        'pain' => 'Schmerzen',
        'nausea_vomiting' => 'Übelkeit/Erbrechen',
        'danger_of_social_isolation' => 'Gefahr der sozialen Isolation',
        'financial_emergency_situation' => 'finanzielle Notsituation',
        'other' => 'sonstiges',
    ),
    
    "Form_PatientHospiceCertification" => array(
        'Hospice need Certification:' => 'Hospiznotwendigkeitsbescheinigung:',
        'yes' => 'ja',
        'no' => 'nein',
        'already_made' => 'Antrag schon gestellt',
    ),
    
    "Form_PatientPsychooncological" => array(
        'Psycho-oncological support:' => 'Psychoonkologische Unterstützung:',
        'yes' => 'ja',
        'no' => 'nein',
        'already_made' => 'Antrag schon gestellt',
    ),
    "Form_PatientMaster" => array(
        'Patient Details' => 'Patient:',
        'Date of recording:' => 'Datum der Aufnahme:',
        'Visit from:' => 'Besuch von:',
        'clock to:' => 'Uhr bis:',
        'clock:' => 'Uhr:',
        'Patient:' => 'Patient:',
        'Last name:' => 'Name:',
        'Surname:' => 'Name:', 
        'First name:' => 'Vorname:',
        'Date of birth:' => 'Geburtsdatum:',
        'Main diagnosis:' => 'Hauptdiagnose:',
        'Secondary diagnoses:' => 'Nebendiagnosen:',
        'Address:' => 'Adresse:',
        'Postcode:' => 'PLZ:',
        'Responsible QPA:' => 'Zuständiger QPA:',
        'Place of residence:' => 'Wohnort:',
        'Phone:' => 'Telefonnummer:',
    ),
    
    "Form_PatientDgpKern" => array(
        'Description of the current or immediately planned supply:' => 'Beschreibung der aktuellen bzw. unmittelbar geplanten Versorgung:',
        'Voluntary service' => 'Ehrenamtlicher Dienst',
        'Hospice stationary' => 'Hospiz stationär',
        'Palliative care' => 'Palliativpflege',
        'Home' => 'Heim',
        'family doctor' => 'Hausarzt',
        'Palliativarzt' => 'Palliativarzt',
        'Palliative Care Team' => 'Palliative Care Team',
        'Outpatient care' => 'Ambulante Pflege',
        'palliative consultation AHPB' => ' Pallivberatung AHPB',
        'KH palliative ward' => 'KH Palliativstation',
        'Hospital other ward' => 'Krankenhaus andere Station',
        'MVZ' => 'MVZ',
        'KH Palliativdienst' => 'KH Palliativdienst',
        'other' => 'sonstiges',
        "ECOG:" => "ECOG:",
        
    ),
    
    "Form_PatientAnlage33a" => array(  
                
        'Annexes 3 and 3a:' => 'Anlage 3 und 3a:',
        'available' => 'vorhanden',
        'signature_family_doctor' => 'Unterschrift Hausarzt',
        'signature_patient' => 'Unterschrift Patient',
    ),
    
    "Form_Stammdatenerweitert" => array(
        'Marital status:' => 'Familienstand:',
        'single' => 'ledig',
        'married' => 'verheiratet',
        'partnership' => 'Partnerschaft',
        'divorced/separated' => 'geschieden/getrennt lebend',
        'widowed' => 'verwitwet',
        
        'Nationality:' => 'Staatszugehörigkeit:',
        
        'Vigilance:' => 'Vigilanz:',
        
        'Artificial exits:' => 'Künstliche Ausgänge:',
        
        'Excretion:' => 'Ausscheidung (alt):',                   //ISPC-2791 Lore 15.01.2021
        
    ),
        
    "Form_PatientRemedy" => array(
        'Remedy:' => 'Hilfsmittel II:',
        'care bed' => 'Pflegebett',
        'electrical insert frame' => 'elektrischer Einlegerahmen',
        'IV pole' => 'Infusionsständer',
        'commode chair' => 'Toilettenstuhl',
        'Toilet riser' => 'Toilettensitzerhöhung',
        'breathing apparatus' => 'Sauerstoffgerät',
        'wheelchair' => 'Rollstuhl',
        'rollator' => 'Rollator',
        'Alternating pressure mattress' => 'Wechseldruckmatratze',
        'Soft bedding mattress' => 'Weichlagerungsmatratze',
        'other' => 'sonstige',
    ),
    
    "Form_PatientOrientation2" => array(
        'Orientation II:' => 'Orientierung II:',
        'full' => 'voll',
        'local disorientation' => 'örtliche Desorientierung',
        'temporal disorientation' => 'zeitliche Desorientierung',
        'Personnel disorientation' => 'personelle Desorientierung',
        'situational disorientation' => 'situative Desorientierung',
        'communication restricted' => 'Verständigung eingeschränkt',
        'linguistically' => 'sprachlich',
        'cognitive' => 'kognitiv',
        'hearing problems' => 'Hörprobleme',
    ),
    
    
    "Form_PatientMobility2" => array(
        'Mobility II:' => 'Mobilität II:',
        'ambulatory' => 'gehfähig',
        'rollator' => 'Rollator',
        'wheelchair' => 'Rollstuhl',
        'bedridden' => 'bettlägerig',
    ),
    
    "Form_PatientSpiritualAttitude" => array(
        'Assessment of the spiritual attitude:' => 'Einschätzung der spirituellen Haltung:',
        'Pat wants spiritual guidance' => 'Pat wünscht spirituelle Begleitung durch',
        'pastor' => 'Pfarrer / Pastor / Rabi / Imam', 
        'family' => 'Familie', 
        'friends' => 'Freundeskreis', 
        'hospice_service' => 'Ambulanten Hospizdienst',
        'psycho_oncological' => 'Psychoonkologische Unterstützung',
        'not_company' => 'wünscht keine Begleitung',
    ),

    "Form_PatientDivergentAttitude" => array(
        'Possible ethical divergent attitudes' => 'Mögliche ethische divergierende Haltungen',
        'Question from the patient / relatives' => 'Frage vom Patienten / der Angehörigen nach',
        'medically_assisted_suicide' => 'ärztlich assistierter Suizid',
        'active_euthanasia' => 'aktiver Sterbehilfe',
        'not_addressed' => 'Wurde vom Patienten / der Angehörigen NICHT angesprochen',
        'other'=> 'Ethisches Fallgespräch sinnvoll:',
    ),
    
    "Form_PatientGeneralPractitionerInitial" => array(
        'Information to the general practitioner about the result of the initial assessment is provided by:' => 'Information an der Haus-/Facharztes über das Ergebnis des Erstassessments erfolgt durch:',
        'QPA' => 'QPA',
        'coordination' => 'Koordination',
        'by_phone' => 'telefonisch',
        'via_letter'=> 'via Brief',
    ),
    
    "Form_PatientNextContactBy" => array(
        'Next contact by:' => 'Nächster Kontakt durch:',    
        'QPA' => 'QPA',
        'coordination' => 'Koordination',
        'HB' => 'HB',
        'by_phone' => 'telefonisch',
    ),
    
    "Form_PatientCloseContact" => array(
        'Close contact for:' => 'Engmaschiger Kontakt wegen:',
		// Maria:: Migration ISPC to CISPC 08.08.2020
        'palliative_sedation' => 'palliativer Sedierung', //TODO-2743 12.12.2019
        'high_symptom_burden' => 'hohe Symptomlast durch',  
        'high_mental_stress' => 'hohe seelische Belastung',
        'other' => 'sonstige', 
    ),
    "Form_PatientChildMourning" => array(
        'Child Mourning Work: (To be completed only in minors in the household)' => 'Kindertrauerarbeit: ( Nur bei Minderjährigen im Haushalt auszufüllen)',
        'palliative_sedation' => 'palliativer Sedierung',//TODO-2743 12.12.2019
        'high_symptom_burden' => 'hohe Symptomlast durch',
        'high_mental_stress' => 'hohe seelische Belastung',
        'other' => 'sonstige',
    ),
    "Form_PatientReligions" => array(
        'Religion:' => 'Konfession:',
    ),
    
    "Form_PatientPort" => array(
        'Port:' => 'Port:',
    ),
    "Form_PatientDiagnosis" => array(
        'Diagnosis:' => 'Diagnosen:',
        'Main diagnosis:' => 'Hauptdiagnose:',
        'Secondary diagnoses:' => 'Nebendiagnosen:',
        'Add new diagnosis' => 'weitere Diagnose hinzufügen', //used as button
        'delete row' => 'löschen'//used as delete one row of diagnose
    ),
    
    "Form_PatientKarnofsky" => array(
        'ECOG status:' => 'ECOG Status:',
        'wound width:' => 'Breite: ',
    ),
    
    
    "Form_PatientMaintainanceStage" => array(
        'ECOG status:' => 'ECOG Status:',
        'wound width:' => 'Breite: ',
    ),
    
    "Form_PatientMaintainanceStage" => array(
        'stage' => 'Pflegegrade',
        'fromdate' => 'Von ',
        'maintenancestage' => 'Pflegegrade',
        
        'implemented by:' => 'umgesetzt von:',
        'required on:' => 'erforderlich am:',
        
    ),
    "Form_PatientLocation" => array(
        'Patient Location' => 'Versorgungssituation des Patienten',
    ),

    "Page %s" => "Seite %s", //used on tabs
    
    "wlassessment_lang" => array(
        
       
        "Page %s" => "Seite %s", //used on tabs
        "freetext" => "andere", // used as placeholder for 'other' inputs
        
        'What is the reason of contact, what is the treatment goal:' => 'Was ist der Grund des Kontaktes, was ist das Behandlungsziel:',
        
        'Health Insurance:' => 'Krankenkasse',
        'Policy Number:' => 'Versicherungsnummer',
        'Copayment exemption:' => 'Zuzahlungsbefreiung',
        
       
        'Visit carried out by' => 'Besuch durchgeführt von',
        
        'Formular Details' => 'Erstassessement',
        'Visit from:' => 'Besuch von:',
        'clock to:' => 'Uhr bis:',
        'to:' => 'bis:',
        'clock:' => 'Uhr:',
        
        //page2
        'Contact Person:' => 'Ansprechpartner:',
        'Spouse' => 'Ehepartner',
        'Life Partner' => 'Lebensgefährte',
        'Daughter/Son' => 'Tochter / Sohn',
        'Mother/Father' => 'Mutter/Vater',
        'Sister/Brother' => 'Schwester/Bruder',
        
        'Living Will Provisions of Care:' => 'Patientenverfügung Vorsorgevollmacht:',
        'Who is authorized:' => 'Wer ist bevollmächtigt:',
        
        'Family doctor / specialist:' => 'Haus-/Facharzt:',
        'Add new specialist' => "Neue Facharzt hinzufügen",
        
        
        
       
        
        
        //page3
        'Nursing degree:' => 'Pflegegrad:',
        'Nursing:' => 'Pflegedienst:',
        'Service of the nursing service:' => 'Leistung des Pflegedienstes:',
        'Care situation of the patient:' => 'Versorgungssituation des Patienten:',
    
        
    		'PatientLocation' => 'Versorgungssituation des Patienten',
        
        //page4
        'Wounds:' => 'Wunden:',
        
        'Medications:' => 'Medikamente:',
        'Incompatibilities:' => 'Unverträglichkeiten:', 
        
        
        
        //page6
        'Report to the QPA on the definition of a care / treatment plan and, if necessary, necessary further action will be taken on:' => 'Bericht an den QPA zur Festlegung eines Versorgungs-/Therapieplans und ggf. notwendiger weiterer Maßnahmen erfolgt am:',
        
        
        //page7
        
        
    ) ,
     
		'Add new specialist' => "Neue Facharzt hinzufügen",
		'Add new patient nursing' => "Neue Pflegedienst hinzufügen",
        'Other:' => 'Sonstiges:',
    
    //ISPC-2144
    'savoir_lang' => array(
        'Savoir Formular' => 'SAVOIR',
        
        "consent A" => "Der Patient stimmt der Datenübermittlung zu " ,
        "consent B" =>  "Der Patient stimmt der Befragung zur Patientenzufriedenheit zu",
        "consent C" => "Zustimmung zur Datenübertragung durch",
        "school education" =>  "Schulbildung",
        "working status" =>  "Erwerbsstatus",
        "job" =>  "Beruf",
        "country of birth" =>  "Geburtsland Patient",
        "country of birth mother" =>  "Geburtsland Mutter des Patienten",
        "country of birth father" =>  "Geburtsland Vater des Patienten",
        "—" =>'bitte auswählen',//used in selectbox 
        " " => 'keine Angabe',//used in radios/cb
        
        
        'Rule Approach' => 'Regelanfahrtsweg',
        'Rule arrival time' => 'Regelanfahrtszeit',
        'First Assessment carried out by' => 'Erst-Assessment durchgeführt durch',
        "by a professional group alone" => "durch eine Berufsgruppe allein",
        "by at least 2 professional groups" => "durch mindestens 2 Berufsgruppen",
        
    ),
    'savoir_sapv_lang' => array(

        'sapv symptom' => 'Symptomatik', // used as table header for sapv
        'sapv prescriber' => 'Verordner der Erstverordnung',// used as table header for sapv
        "—" =>'bitte auswählen',//used in selectbox
        " " => 'keine Angabe',//used in radios/cb
    ),
		
		
	// ISPC-2157 
	"complaintform_lang"=>array(
		
		"legend"=> "Reklamations-Management",
		"legend_saved"=> "Reklamations-Management - Historie",
		
		// form buttons	
		"save_form"=> "Speichern",// button

		// form buttons
		"save_form"=> "Speichern",// button
		"save_and_sendTodo"=> "Speichern und TODO absenden", // button
		"save_and_sendTodo"=> "Speichern und Reklamation absenden", // button
		"generate_pdf" => "PDF Erstellen",
		"save_and_CloseFile_and_sendTodo" => "Fallabschluss beantragen", // button
		// verlauf entires
		"verlauf_entry_saved"=> "Reklamation%unique_id gespeichert",
		"verlauf_entry_edited"=> "Reklamation%unique_id bearbeitet",
		"verlauf_entry_pdf_was_saved"=> "Reklamation%unique_id (PDF) wurde erstellt",
		"verlauf_entry_edited_closed"=> "Reklamation%unique_id abgeschlossen", // when an existing form is marked as closed
		"verlauf_entry_saved_closed"=> "Neue Reklamation abgeschlossen", // when a new form is directly set as closed
		// table header for saved forms
		"form_date"=> "Datum",
		"form_status" => "Status",
		"form_unique_id" => "ID",
		"create_user"=> "erledigt von",
		"create_date"=> "erstellt",
		"actions"=> "Aktionen",
		"status_opened" => "offen / in Bearbeitung", // status of form in list
		"status_closed" => "Abgeschlossen", // // status of form in list
		// alert - in closed forms
		"This form is Closed. No changes are alowed!"=> "Dieser Vorgang ist abgeschlossen. Änderungen können nicht gespeichert werden!",
		"form history log"=> "Beabeitung",
		// table header for current form history
		"changed_user"=> "Benutzer",
		"changed_date"=> "Datum",
		// top right button
		"new_form"=> "Neue Reklamation",
	    
		"comment_label"=> "Kommentar Fallabschluss",
	),

	'the comment must be filled' => "Bitte füllen Sie zwingend das Kommentarfeld für den Fallabschluss ein",
	'the comment must be filled' => "Bitte füllen Sie zwingend das Kommentarfeld für den Fallabschluss ein",
    
    
	'Complaint_users_list' => "Reklamations-Management Benachrichtigungen ",
	'complaintform' => "Reklamations-Management",
	'complaintform_name' => "Reklamations-Management",
	'mail_subject_action_complaint' => "Reklamations-Management",
	'mail_content_complaint' => " Bitte loggen Sie sich ein, um diese Nachricht zu lesen.",
	'Receive message  when complaint opened' => "Reklamation Fall eröffnet",
	'Receive message  when complaint closed' => "Reklamation Fall geschlossen",
    
    
    'ethicalform' =>'ethicalform',//this is the saved pdf document tile from Allgemein -Ethisches Assessment
    'rlppatientcontrol' => 'Leistungsübersicht Rheinland Pfalz',//this is the saved pdf document tile from Leistungsnachweis Rheinland-Pfalz

//     "mdksapvquestionnaire pdf footer text" => "Seite %s von %s (%s)", //footer on the pdf
    "mdksapvquestionnaire pdf footer text" => "B16  -   © Dr. Steiger   -   Seite %s von %s (%s)", //footer on the pdf
    //TODO-3892 Ancuta 22.02.2021
    "mdksapvquestionnaire2020 pdf footer text" => "B16  -   © Dr. Steiger   -   Seite %s von %s (%s)", //footer on the pdf
//     "mdksapvquestionnaire2020 pdf footer text" => "B16  -   © Dr. Steiger   -   Seite %s von %s (%s)", //footer on the pdf
    "mdksapvquestionnaire2020 pdf footer text" => "A67  -  © C.Ste.  -  27.12.2016  -  Seite %s von %s (%s)", //footer on the pdf//ISPC-2765,Elena,04.03.2021
    
	//ISPC - 2192
	"kvheader_lang" => array(
			"phealthinsurance_name" => "Krankenkasse bzw. Kostenträger",
			"insured_name_firstname" => "Name, Vorname des Versicherten",
			"born_on" => "geb. am",
			"health_insurance_nr" => "Kostenträgerkennung",
			"health_insurance_patient_number"=>"Versicherten-Nr.",
			"health_insurance_patient_status"=>"Status",
			"stamp_user_lanr" => "Arzt-Nr.",
			"stamp_user_bsnr" => "Betriebsstätten-Nr.",
			"formular_date" => "Datum"
	),
    
		"careregulationnew_lang" => array(
				"formular_title" => "Verordnung häuslicher Krankenpflege",
				"formular_nr" => "12",
				"icd_codes" => "Verordnungsrelevante Diagnose(n) (ICD-10-Code)",
				"topform_textarea_big" => "Einschränkungen, die häusliche Krankenpflege erforderlich machen",
				"topform_textarea_small" => "vgl. auch Lesistungsverzeichnis HKP-Richtlinie",
				'generate pre print page1 pdf' => 'PDF für Druck auf Formular Pag1',
				'generate pre print page2 pdf' => 'PDF für Druck auf Formular Pag2',
				'generate pdf page1' => 'PDF für Druck Pag1',
				'generate pdf page2' => 'PDF für Druck Pag2',
				'generate pre pdf' => 'PDF für Druck auf Formular',
				'save pdf' => 'Speichern PDF',
				'careregulationnew_course_pdf' => 'Formular Verordnung häuslicher Krankenpflege wurde erstellt',
				'careregulationnew_pdf_pag1' => 'Muster12a_Pflege Pag1',
				'careregulationnew_pdf_pag1' => 'Muster12a_Pflege Pag2'
				
		),
    
    "was updated" => "wurde aktualisiert",
	
	//ISPC - 2240 - duplicate form sis ambulant to sis stationar
	"SisStationary_PDF 2018" => "PDF des SIS - stationär in Dateien und Dokumente wurde hinterlegt",
		
	//ISPC -2234 - create form KinderHospiz
	"kh_healthinsurance_address" => "Anschrift der Krankenkasse",
	"kh_patient_surname" => "Name des<br />Versicherten:",
	"kh_patient_firstname" => "Vorname:",
	"kh_patient_dob" => "Geb.-Datum:",
	"kh_patient_street" => "Straße:",
	"kh_patient_zipcity" => "PLZ/Ort:",
	"kh_patient_kvnr" => "KV-Nr.:",
	"other1" => "Die Aufnahme erfolgt aufgrund einer palliativmedizinischen und –pflegerischen Intervention im Rahmen einer krisenhaften Verschlechterung bei weit fortgeschrittener lebenslimitierender Erkrankung, die es wahrscheinlich erscheinen lässt, dass die letzte Lebensphase begonnen hat.",
	"label_required_hospiz_treatment_justification" => "Auf Grund welcher Diagnose wird die stat. Hospizbehandlung nach §39a SGB V beantragt?",
	"label_required_hospiz_regist_current_trend_change" => "Welche aktuelle richtungweisende Veränderung macht die Hospizaufnahme zum jetzigen Zeitpunkt erforderlich?",
	"label_home_palliative_treatment_plan" => "Wurde am Wohnort bereits ein palliativmedizinischer Behandlungsplan aufgestellt?<br />Wenn ja, bitte beifügen.",
	"label_home_current_therapies" => "Angaben zu den aktuell am Wohnort durchgeführten Therapien (ggf. auf zusätzlichem Blatt notieren):",
	"other2" => "Besonderer <b>palliativ-medizinischer und palliativ-pflegerischer Bedarf</b> besteht in Bezug auf:",
	"postother" => "&nbsp;",
	"label_pain_therapy_already_started" => "<ul style='margin-bottom: 0px;'><li>bereits begonnene Schmerztherapie</li></ul>",
	"label_pain_therapy_expected" => "<ul style='margin-bottom: 0px;'><li>zu erwartende Schmerztherapie</li></ul>",
	"presymptom" => "&nbsp;",
	"label_symptom_control_crisis_intervention" => "<ul style='margin-bottom: 0px;'><li>Symptomkontrolle / Krisenintervention<br />(z. B. bei Erbrechen, Obstipation, Atemnot, Unruhe etc.)</li></ul>",
	"postsymptom" => "&nbsp;",
	"label_psychosocial_or_pastoral_support" => "<ul style='margin-bottom: 0px;'><li>psychosoziale / seelsorgliche Unterstützung der / des Betroffenen<br />(u. der Angehörigen) in der Auseinandersetzung mit dem Sterben</li></ul>",
	"label_special_wound_care" => "<ul style='margin-bottom: 0px;'><li>spezielle Wundversorgung</li></ul>",
	"label_signs_of_infectious_diseases" => "<ul style='margin-bottom: 0px;'><li>Liegen Hinweise auf eine Infektionskrankheit vor</li></ul>",
	"label_other_palliative_needs" => "<ul style='margin-bottom: 0px;'><li>Sonstiges:</li></ul>",
	"label_form_date" => str_repeat('&nbsp;', 20)."Datum".str_repeat('&nbsp;', 100)."Unterschrift des Arztes / Stempel"."<br /><br />".str_repeat('&nbsp;', 70)."- Für die Angaben des Arztes ist analog die Geb.-Pos. 73 EBM berechnungsfähig -<br /><br /><br />",
	"label_hospice_name" => "Name des Hospizes",
	"label_expected_recording_date" => "<b>(voraussichtliches) Aufnahmedatum:</b>",
	"label_hospice_address" => "Anschrift des Hospizes",
	"precontact" => "<b>Ansprechpartner für Rückfragen:</b>",
	"label_contact_for_inquiries_name" => "Name",
	"label_contact_for_inquiries_tel" => "Telefon",
	"label_medical_prescription_attached" => "Eine ärztliche Verordnung ist beigefügt",
	"label_outpatient_or_semistationary_care_alternatif" => "Ist eine ambulante oder teilstationäre Versorgung alternativ mög-<br />lich?",
	"label_required_longterm_care_insurance_based" => "Wurde bereits Pflegebedürftigkeit im Sinne der Pflege-<br />versicherung festgestellt?",
	"label_stufe_text" => "&nbsp;",		
	"label_receiving_or_entitled_careservices" => "Ich erhalte/habe einen Anspruch auf Pflegeleistungen<br />z. B. Pflegegeld/Pflegezulage)",
	"postentcare" => str_repeat('&nbsp;', 5)."kasse".str_repeat('&nbsp;', 22)."stelle".str_repeat('&nbsp;', 27)."Sozialamt".str_repeat('&nbsp;', 7)."vers.".str_repeat('&nbsp;', 25)."sorgungsamt".str_repeat('&nbsp;', 9)."Stellen",
	"label_receiving_or_entitled_careservices_from" => "wenn ja, von:",
	"prereqcare" => "&nbsp;",
	"label_careservice_name_address" => "Name und Anschrift (z.B. Pflegekasse, Beihilfestelle, Versorgungsamt, Berufsgenossenschaft, Sozialamt)",
	"other3" => "Etwaige spätere Änderungen werde ich umgehend der Kranken-/und Pflegekasse mitteilen.<br /><br /><b>Einwilligungserklärung:</b> Ich bin damit einverstanden, dass meine Kranken-/Pflegekasse von den mich behandelnden Ärzten, Krankenhäusern und Pflegepersonen ärztliche Unterlagen, Auskünfte sowie in deren Besitz befindliche Fremdbefunde anfordern kann, soweit diese für die Begutachtung und Entscheidung über meinen Antrag auf Leistungen erforderlich sind. Insoweit entbinde ich die vorgenannten Institutionen bzw. Stellen von ihrer Schweigepflicht. Unterlagen, die ich der Kranken-/Pflegekasse zur Verfügung gestellt habe, können an den zuständigen Medizinischen Dienst der Krankenversicherung (MDK) weitergegeben werden.<br /><br /><b>Datenschutzhinweis (§ 67a Abs. 3 SGB X):</b> Damit wir unsere Aufgaben rechtmäßig erfüllen können, ist Ihr Mit-wirken nach §§ 7, 28 SGB XI, § 60 SGB I erforderlich. Ihre Daten sind im vorliegenden Falle aufgrund § 94 SGB XI zu erheben. Fehlt Ihre Mitwirkung, kann dies zu Nachteilen (z.B. bei den Leistungsansprüchen) führen.<br /><br /><br />". "<u>".str_repeat('&nbsp;', 100). "</u><br />Datum, Unterschrift des Versicherten/gesetzl. Vertreters/Bevollmächtigten",
	"label_insured_consent_for_signature" => "Einverständniserklärung des Versicherten zur Unterschrift liegt vor.",
	"kh_save" => "Speichern",
	"kh_generate_pdf" => "PDF Erstellen",
	"message_info_suc_edit" => "Der Eintrag wurde aktualisiert",
	"message_info_suc_create" => "Der Eintrag wurde hinzugefügt",
		
	//ISPC - 2253 - BESD survey form
	'breathing' => 'Atmung (unabhängig von Lautäußerung)',
		'normal' => 'normal',
		'hard_occasionaly' => '<b>gelegentlich</b> angestrengt atmen',
		'short_fast_deep' => '<b>kurze</b> Phasen von Hyperventilation<br />(schnelle und tiefe Atemzüge) lautstark angestrengt',
		'loudly' => '<b>lautstark</b> angestrengt atmen',		
		'long_fast_deep' => '<b>lange</b> Phasen von Hyperventilation (schnelle und tiefe Atemzüge)',
		'cheyene_stokes' => 'Cheyne Stoke Atmung (tiefer werdende und wieder abflachende Atemzüge mit Atempausen)',
	'negative_utterance' => 'Negative Lautäußerung',
		'none' => 'keine', 
		'moan_occasionally' => 'gelegentlich stöhnen oder ächzen', 
		'disapproving_speak' => 'sich leise negativ oder missbilligend äußern',
		'repeatedly_distressed_call' => 'wiederholt beunruhigt rufen',
		'loud_moan' => 'laut stöhnen oder ächzen',
		'weep' => 'weinen', 
	'face_expression' => 'Gesichtsausdruck',
		'smiling_or_nothing' => 'lächelnd oder nichts sagend',
        'sad' => 'trauriger Gesichtsausdruck',
		'anxious' => 'ängstlicher Gesichtsausdruck',
        'worried' => 'sorgenvoller Blick',
        'grimacing' => 'grimassieren',
	'body_language' => 'Körpersprache',
		'relaxed' => 'entspannt',
		'tense' => 'angespannte Körperhaltung',
		'back_forth_nervously' => 'nervös hin und her gehen',
		'fiddle' => 'nesteln',
		'rigid' => 'Körpersprache starr',
		'clenched_fists' => 'geballte Fäuste',
		'tightened_knees' => 'angezogene Knie',
		'push_away_escape' => 'sich entziehen oder wegstoßen',
		'beat' => 'schlagen',
	'consolation' => 'Trost',
		'not_necessary' => 'trösten nicht notwendig',
		'voice_touch_consolation' => 'Stimmt es, dass bei oben genanntem Verhalten ablenken oder beruhigen durch Stimme oder Berührung möglich ist?',
		'no_consolation' => 'Stimmt es , dass bei oben genanntem Verhalten trösten, ablenken, beruhigen nicht möglich ist?',
	'calm' => 'Ruhe',
	'mob' => 'Mobilisation und zwar durch folgender Tätigkeit: ',
	'BesdSurvey_PDF 2018' => 'Ergebnis: ',
		'besd_score' => '<b>Punkt-<br />wert</b>',
		
	//ISPC -2262 - create a new form MUST
	'label_current_weight' => 'Aktuelles Körpergewicht:',
	'label_now_date' => 'Datum:',
	'label_last_3_6_month_weight' => 'Körpergewicht vor 3-6 Monaten:',
	'label_past_date' => 'Datum:',
	'label_height' => 'Körpergröße:',
	'label_square_height' => 'Körpergröße',
	'label_bmi' => '1. Body Mass Index (BMI)',
	'bmi_text' => 'BMI = ',
	'bmi_unit' => 'kg/m',
	'label_last_3_6_month_weight_proc' =>'2. Unbeabsichtigter Gewichtsverlust in den letzten 3-6 Monaten',
	'last_3_6_month_weight_proc_legend_first' => 'Unbeabsichtigter Gewichtsverlust in den letzten 3-6 Monaten',
	'acute_illness_legend_first' => 'Nahrungskarenz voraussichtlich mehr als 5 Tage',
	'label_acute_illness' => '3. Liegt eine akute Erkrankung mit einer Nahrungskarenz (stark verminderte Nahrungsaufnahme) von mehr als 5 Tagen vor oder ist abzusehen, dass der Patient in den kommenden 5 Tagen nicht ausreichend Nahrung zu sich nehmen wird?',
	'total_text' => 'GESAMTRISIKO FÜR DAS VORLIEGEN EINER MANGELERNÄRHUNG =',
	'total_legend' => 'Ergebnis:',
	'score_0' => '0 Punkte',
	'risc_level_0' => 'geringes Risiko',
	'todo_text_0' => '->Screening regelmäßig wiederholen!',
	'score_1' => '1 Punkt',
	'risc_level_1' => 'mittleres Risiko',
	'todo_text_1' => '->Beobachten, Ernährungsberatung sinnvoll!',
	'score_2' => '>=2 Punkte',
	'risc_level_2' => 'hohes Risiko',
	'todo_text_2' => '->Behandlung dringend erforderlich!',
	'PatientNutritionalStatus_PDF 2018' => 'MUST 2018',
	
    
    //  ISPC-2286
    "shortcut_text_b1"=>"Beratung Grundpauschale (Tag 1-28)",
    "shortcut_text_b2"=>"Beratung Folgepauschale (ab Tag 29)",
    "shortcut_text_k1"=>"Koordination/Assessment Grundpauschale (Tag 1-28)",
    "shortcut_text_k2"=>"Koordination/Assessment Folgepauschale (ab Tag 29)",
    "shortcut_text_tv1"=>"TV/VV Versorgungstag ambulant",
    "shortcut_text_tvh"=>"TV Versorgungstag stat. Hospiz",
    "shortcut_text_dth"=>"Fallpauschale Exitus 48h",
	
	//ISPC - 2296 
	'label_users' => 'Beteiligte Mitarbeiter:',
	'label_agreed_with' => 'Abgestimmt mit:',
	'formular_date' => 'Datum:',
	'label_history_since_last_meeting' => 'Verlauf seit letzter Besprechung:',
	'label_main_problems' => 'Hauptprobleme:',
	'label_print_save' => 'Behandlungsplan drucken:',
	
	//ISPC - 2336
	'remedies' => 'Heilmittel', //new column in Heilmittelverordnung list
	
	//ISPC - 2353
	'question1' => 'Hat der Patient während der letzten 3 Monate wegen Appetitverlust, Verdauungsproblemen, Schwierigkeiten beim Kauen oder Schlucken weniger gegessen? (Anorexie)',
	'question2' => 'Gewichtsverlust in den letzten 3 Monaten',
	'question3' => 'Mobilität/ Beweglichkeit',
	'question4' => 'Akute Krankheit oder psychischer Stress während der letzten 3 Monate?',
	'question5' => 'Psychische Situation',
	'question6' => 'Körpermassenindex (Body Mass Index, BMI) (Körpergewicht / (Körpergröße)2, in kg/m2)',
	'question7' => 'Wohnsituation: Lebt der Patient unabhängig zu Hause?',
	'question8' => 'Medikamentenkonsum: Nimmt der Patient mehr als 3 Medikamente (pro Tag)?',
	'question9' => 'Hautprobleme: Schorf oder Druckgeschwüre?',
	'question10' => 'Mahlzeiten: Wie viele Hauptmahlzeiten isst der Patient pro Tag?<br />(Frühstück, Mittag- und Abendessen)?',
	'question11' => 'Lebensmittelauswahl: Isst der Patient',
	'question12' => 'Isst der Patient mindestens zweimal pro Tag Obst oder Gemüse?',
	'question13' => 'Wieviel trinkt der Patient pro Tag? (Wasser, Saft, Kaffee, Tee, Wein, Bier…)',
	'question14' => 'Essensaufnahme mit / ohne Hilfe',
	'question15' => 'Glaubt der Patient, dass er gut ernährt ist?',
	'question16' => 'Im Vergleich mit gleichaltrigen Personen schätzt der Patient seinen Gesundheitszustand folgendermaßen ein:',
	'question17' => 'Oberarmumfang (OAU in cm)',
	'question18' => 'Wadenumfang (WU in cm)',
	'before_anamnesis' => 'Vor-Anamnese',
	'anamnesis' => 'Anamnese',
	'before_anamnesis_total' => 'Vor-Anamnese Total',
	'anamnesis_total' => 'Anamnese Total',
	'total' => 'Auswertung des Gesamt-Index = ',
		
	//ISPC - 2389,
	'canvas_reset' => 'Alles löschen',
	
	// ISPC - 2162
	'2162 - print profiles' => 'Druckprofil Name',
	'2162 - print profiles ' => 'Druckprofil Name',
	'2162 - label_select_form' => 'auswählen',
		
	//ISPC - 2353 // Maria:: Migration ISPC to CISPC 08.08.2020
	"the height has to be in m!" => "Bitte geben Sie die Größe in Meter  an (also '1,75')",
		
	//ISPC-2370
	"munster4_lang" => array(
			'generate pdf' => 'PDF erstellen und NICHT speichern',
			'generate pdf and save' => 'PDF erstellen und speichern',
			'generate pre print pdf and save' => 'PDF (preprint) erstellen und speichern',	
	),
		
    //ISPC-2461
	'Demstepcare controll page'=>'Demstepcare Kontrollseite',	
	'dsc_products'=>'Leistungen',	
    "demstepcarecontrol pdf footer text" => "Seite %s von %s (%s) - Demstepcare Kontrollseite", //footer on the pdf
    
    //ISPC-2711 Ancuta 
	'user_use_btm_text'=>"Bitte standardmäßig 'gemäß schriftlicher ärztlicher Anweisung' auf BTM Rezepten eintragen.",	
	'user_btm_default_text'=>'gemäß schriftlicher ärztlicher Anweisung',	
    
    //ISPC-2711
    'user_receipt_dosage' => 'Standardmäßige Einstellung zur Dosierungsangabe von Rezepten',        //ISPC-2711 Lore 07.04.2021
    'dosage_receipt_line_do_nothing' => ' ',
    'dosage_receipt_line_add_dj_text' => '>> Dj <<',
    'dosage_receipt_line_add_dosage' => 'Doseirung',
    'save AND Print BTM A' => "Mit 'A' speichern",

    //ISPC-2825 Dragos
    'select_date' => 'Datum auswählen',
    'No form for specified date, presenting next available forms' => 'Keine Daten an diesem Datum verfügbar',
    'No form after selected date, presenting latest patient forms' => 'Keine Daten an diesem Datum verfügbar',
    'open_for_more' => 'Öffnen um Inhalte zu sehen',
    'No data available for this patient' => 'Es liegen keine Daten vor.',
    
    //ISPC-2882 Ancuta 22.04.2021
    'save and go to anlage2kinder' => 'Speichern und Anlage 2 generieren',
    
    
    //ISPC-2921 Ancuta 28.05.2021
    //verlauf entries:
    'CAREPLANNING form was created'=>"Pflegeplanung wurde erstellt",
    'CAREPLANNING form was edited'=>"Pflegeplanung wurde bearbeitet",
    'Careplanning PDF was created'=>"Pflegeplanung (PDF) wurde erstellt",
    // form 
    'evaluation_date' =>"Datum",
    'evaluation_todo_users' =>"TODO für Benutzer",
    'Careplanning - send todo for evaluation' =>"TODO für Re-Evaluation setzen",
    'Careplanning - archived options' =>"archivierte Einträge",
    'Is completed?' =>"erledigt",
    //todo_text 
    'careplanning  todo text'=>"Re-Evaluation Pflegeplanung",
    
    
    
    //ISPC-2909 Ancuta 28.05.2021
    //verlauf entries:
    'Kontaktformular PV form was created'=>"Kontaktformular PV wurde erstellt",
    'Kontaktformular PV form was edited'=>"Kontaktformular PV wurde bearbeitet",
    'Kontaktformular PV PDF was created'=>"Kontaktformular PV (PDF) wurde erstellt",
    'Please fill data from 1 to 5'=>"Bitte füllen Sie die fehlenden Daten aus für die Punkte 1) bis 5)",
    'ident_todo_text'=>'ToDo',
    'ident_todo_users'=>"TODO für Benutzer",
    'ident_todo_date'=>'Datum',
 
);
