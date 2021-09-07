<?php
return array(
	// Maria:: Migration ISPC to CISPC 08.08.2020
    'rubin_patient_name' =>'Name',
    'rubin_form_date'=>'Datum',
    'rubin_form_client'=>'Client',
    
    
    //RUBIN - IADL
    'RUBIN-IADL' => "RUBIN - Instrumentelle Aktivitäten (IADL)",
    'patient_rubin_iadl was saved' => "RUBIN-Instrumentelle Aktivitäten - Saved",
    'patient_rubin_iadl_file' => "RUBIN-Instrumentelle Aktivitäten",
    'patient_rubin_iadl PDF was created'=>'RUBIN - Instrumentelle Aktivitäten [%date] wurde erstellt',
    
    //RUBIN - MMSt    
    "RUBIN-MMST" => 'RUBIN - Mini-Mental-Status-Test (MMST)',
    'patient_rubin_mmst_file' => "RUBIN - Mini-Mental-Status-Test (MMST)",
    'patient_rubin_mmst PDF was created'=>'RUBIN - Mini-Mental-Status-Test [%date] wurde erstellt',
    'mmst_course_title' => "RUBIN - Mini-Mental-Status-Test (MMST)",//TODO-2621
    
    //RUBIN - MNA    
    "RUBIN-MNA" => 'RUBIN - Mini Nutritional Assessment (MNA)',
    'patient_rubin_mna_file' => "RUBIN - Mini Nutritional Assessment (MNA)",
    'patient_rubin_mna PDF was created'=>'RUBIN - Mini Nutritional Assessment [%date] wurde erstellt',
    
    //RUBIN-WHOQOL    
    "RUBIN-WHOQOL" => 'RUBIN-WHOQOL',
    'patient_rubin_whoqol_file' => "RUBIN - WHOQOL",
    'patient_rubin_whoqol PDF was created'=>'RUBIN - WHOQOL [%date] wurde erstellt',
    
    //RUBIN-DEMTECT    
    "RUBIN-DEMTECT" => 'RUBIN - DemTect',
    'patient_rubin_demtect_file' => "RUBIN - DemTect",
    'patient_rubin_demtect PDF was created'=>'RUBIN - DemTect [%date] wurde erstellt',
    
    
    //RUBIN - TUG
    "RUBIN-TUG"=>'RUBIN -Timed Up & Go',
    'patient_rubin_tug_file' => "RUBIN - Timed Up & Go",
    'patient_rubin_tug PDF was created'=>'RUBIN - Timed Up & Go [%date] wurde erstellt',
    
    'tug_p1' => 'Der Proband sitzt auf einem Stuhl mit Armlehne (Sitzhöhe ca. 46 cm). ',
    'tug_p2' => 'Er darf ggf. ein Hilfsmittel (z.B. Stock) benutzen. Die Arme liegen locker auf den Armstützen und der Rücken liegt der Rücklehne des Stuhles an. Beim Erreichen dieser Position hilft der Untersucher nicht mit.',
    'tug_p3' => 'Nach Aufforderung soll der Proband aufstehen und mit normalem und sicherem Gang 3 Metern gehen (z.B. bis zu einer Linie, die dort auf dem Boden angezeichnet ist), sich dort umdrehen, wieder zurück zum Stuhl gehen und sich wieder setzen. Die dafür benötigte Zeit wird in Sekunden notiert; es ist keine Stoppuhr vorgeschrieben. Vor der eigentlichen Zeitmessung kann der Proband den Bewegungsablauf üben. ',
    'tug_p4' => 'Der Untersucher darf den Bewegungsablauf einmal demonstrieren.',

    
    //RUBIN-carerelated         //ISPC-2492 Lore 02.12.2019
    "RUBIN-CARERELATED" => 'Blick auf die Versorgung (Nahestehende)',
    "carerelated_footer" => 'Blick Versorgung_Nahest',
    'patient_rubin_carerelated_file' => "Blick auf die Versorgung (Nahestehende)",
    'patient_rubin_carerelated PDF was created'=>'Blick auf die Versorgung (Nahestehende) [%date] wurde erstellt',
    
    //RUBIN-carepatient         //ISPC-2493 Lore 03.12.2019
    "RUBIN-CAREPATIENT" => 'Blick auf die Versorgung (Patientin/Patient)',
    "carepatient_footer" => 'Blick Versorgung_Patient',
    'patient_rubin_carepatient_file' => "Blick auf die Versorgung (Patientin/Patient)",
    'patient_rubin_carepatient PDF was created'=>'Blick auf die Versorgung (Patientin/Patient) [%date] wurde erstellt',
    
    
    // ISPC-2402 BDI-II (DemStepCare)
    "DEMSTEPCARE-BDI" => 'DemStepCare - BDI-II',
    'patient_demstepcare_bdi_file' => "DemStepCare - BDI-II",
    'patient_demstepcare_bdi PDF was created'=>'DemStepCare - BDI-II [%date] wurde erstellt',
    'bdi_course_title' => "BDI-II (DemStepCare)",

    //   ISPC-2403  GDS-15 (DemStepCare)
    "DEMSTEPCARE-GDS" => 'DemStepCare - GDS-15',
    'patient_demstepcare_gds_file' => "DemStepCare - GDS-15",
    'patient_demstepcare_gds PDF was created'=>'DemStepCare - GDS-15 [%date] wurde erstellt',
    'gds_course_title' => "DemStepCare - GDS-15",

    //  ISPC-2404  NPI (DemStepCare)
    "DEMSTEPCARE-NPI" => 'DemStepCare - NPI',
    'patient_demstepcare_npi_file' => "DemStepCare - NPI",
    'patient_demstepcare_npi PDF was created'=>'DemStepCare - NPI [%date] wurde erstellt',
    
    //ISPC-2418 DemStepCare - CMAI-D 
    "DEMSTEPCARE-CMAI" => 'DemStepCare - CMAI-D',
    'patient_demstepcare_cmai_file' => "DemStepCare - CMAI-D",
    'patient_demstepcare_cmai PDF was created'=>'DemStepCare - CMAI-D [%date] wurde erstellt',
    
    //ISPC-2419  DemStepCare - NOSGER II
    "DEMSTEPCARE-NOSGER" => 'DemStepCare - NOSGER II',
    'patient_demstepcare_nosger_file' => "DemStepCare - NOSGER II",
    'patient_demstepcare_nosger PDF was created'=>'DemStepCare - NOSGER II [%date] wurde erstellt',
    
    
    
    
    'Add Score without filling form(transfer from paper)'=>'Manuelle Eingabe des Scores (Übertrag von Papier)',
    'rubin_custom_from_date'=>'Datum',
    'rubin_custom_score'=>'Gesamtpunktzahl',
    
    
    'Please fill both fields - date and score'=>'Bitte überprüfen Sie die eingegebenen Daten!',
    'Date in future it is not allowed'=>'Future Datum nicht erlaubt',
    'Please check score - it should be NUMERC only'=>'Gesamtpunktzahl: Bitte einen numerischen Wert eingeben!',
    'Please check score - it should be from 0 to 63'=>'Bitte Score überprüfen - Werte von 0 bis 63 sind erlaubt',
    'Please check score - it should be from 0 to X'=>'Bitte Score überprüfen - Werte von 0 bis %score_upper_limit sind erlaubt',//TODO-2621
    
    'Form saved !'=>'Formular gespeichert!', // message when form is saved
    'Form not saved, please check data!'=>'Formular nicht gespeichert, bitte überprüfen Sie die eingegebenen Daten!', // message if form not saved 

    'dementia_diagnosis'=>'endstellige Demenzdiagnose nach ICD-10', 
    'cerebral_imaging'=>'cerebrale Bildgebung', 
    'laboratory'=>'Labor', 
    // ISPC-2423 DSV form (DemStepCare)
    "DEMSTEPCARE-DSV" => 'DemStepCare - DSV',
    'patient_demstepcare_dsv_file' => "DemStepCare - DSV",
    'patient_demstepcare_dsv PDF was created'=>'DemStepCare - DSV [%date] wurde erstellt',
    
    'integer 1-80' =>'Nur Ganzzahlen(1-80)',
     'integer 0-168' =>'Ganzzahlen(0-168)',
    'integer 0-99' =>'Ganzzahlen(0-99)',
    'integer only' =>'Nur Ganzzahlen', 
 
    //ISPC-2455 Lore DemStepCare - BADL
    'patient_demstepcare_badl_file' => "DemStepCare - Bayer-ADL-Skala", // the file iname
    'patient_demstepcare_badl PDF was created'=>'DemStepCare - Bayer-ADL-Skala [%date] wurde erstellt', // verlauf entry for pdf
    'demstepcare_badl_course_title_form_created' => "Bayer-ADL-Skala", // verlauf entry when form is created
    'demstepcare_badl_course_title_form_edited' => "Bayer-ADL-Skala wurde bearbeitet", // verlauf entry when form is edited
    
    
    
    //ISPC-2456 Lore DemStepCare - CMSCALE
    "DEMSTEPCARE-CMSCALE" => 'Modifizierte Cohen-Mansfield Skala zur Erfassung von herausforderndem Verhalten Abteilung für Gerontopsychiatrie RFK-Alzey',
    'patient_demstepcare_cmscale_file' => "DemStepCare - Cohen-Mansfield Skala",
    'patient_demstepcare_cmscale PDF was created'=>'DemStepCare - Cohen-Mansfield Skala [%date] wurde erstellt',
    'demstepcare_cmscale_course_title_form_created' => "Cohen-Mansfield Skala",
    'demstepcare_cmscale_course_title_form_edited' => "Cohen-Mansfield Skala wurde bearbeitet",
    
    
    //ISPC-2509 Lore 06.01.2020
    "DEMSTEPCARE-DSCDSV" => 'DemStepCare Assessment zur Versorgungssituation (in Anlehnung an DSV und CM4Demenz)',
    'patient_demstepcare_dscdsv_file' => "DemStepCare Assessment zur Versorgungssituation",
    'patient_demstepcare_dscdsv PDF was created'=>'DemStepCare Assessment zur Versorgungssituation [%date] wurde erstellt',
    
    
    //ISPC-2420 
    'mmst title'=>'RUBIN - Mini-Mental-Status-Test (MMST)',
    'gds title'=>'DemStepCare - GDS-15',
    'Go to mmst link'=>'MMST jetzt starten',
    'Go to gds link'=>'GDS jetzt starten'
    
    
    
);

