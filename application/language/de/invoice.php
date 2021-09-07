<?php
return array(
    'generate_csv_unna' => "Export Vivendi",//button label
    'generate_csv_e_s' => "Export E+S",         //ISPC-2505 Lore 17.12.2019  // Maria:: Migration ISPC to CISPC 08.08.2020
    
    'by_invoice_label' =>'Alte Bayern Abrechnung', //TODO-3727 Ancuta 21.01.2021 'Original-OLD Rechnung',
	/* ISPC - 2087 - invoice warning*/
	'confirm_generate_warnings1' => 'Möchten Sie eine Zahlungserinnerung für diese Rechnung(en) erstellen?', //message generare bulk 1 warnings
	'confirm_generate_warnings2' => 'Möchten Sie eine Mahnungen für diese Rechnung(en) erstellen?', //message generare bulk 2 warnings
	"warn1_invoices" => "Zahlungserinnerung", //button for bulk generate 1 warning
	"warn2_invoices" => "Mahnung", //button for bulk generate 2 warning
	"warnings_table_lang" => array( //table with generate warnings in invoice
			"title" => "Erstellte Mahnungen",
				
			"reminder_type" => "Mahnungtyp",
			"reminder_doc_type" => "Datei-Typ",
			"reminder_date" => "Datum",
			"first_warning" => "Zahlungserinnerung",
			"second_warning" => "Mahnung"
	),
	"template_no_reminder_invoice_type" => "keine Vorlage für eine Zahlungserinnerung / Mahnung", //text in warning type column in warnings list if no warning type found
	"reminder_template_type" => "Vorlagentyp", //label for reminder type in edit/add
	"edit_reminder_file_check" => "Vorlage austauschen", //label for checkbox to change template file
	"editreminderinvoicetemplate" => "Vorlage bearbeiten", //edit warning title
	"addreminderinvoicetemplate" => "Vorlage anlegen", //add warning title
	"reminders_invoice_templates_list" => "Liste der Vorlagen", //list warnings title
	"first_warning" => "Zahlungserinnerung", //warning type in select in edit/add template and button for generate warning in invoice
	"second_warning" => "Mahnung", //warning type in select in edit/add template and button for generate warning in invoice
	"default_warning" => "Zahlungserinnerung", //warning type in select in edit/add template
	/* ISPC - 2087 - invoice warning*/
	"This certificate of achievement was printed on" => "Dieser Leistungsnachweis wurde ausgedruckt am", //ISPC - 2187 -please add a line with the print date (like in duty roster) for this PDF
	// ISPC-2171
	"Export options"=>"Export options",
	"ivj_pdf_label"=>"PDF",
	"ivj_csv_label"=>"CSV",
	"ivj_csv_text_label"=>"CSV / TXT",
	"ivj_oldenburg_label"=>"Oldenburg - CSV",
	"ivj_sap_label"=>"SAP",
	"ivj_unna_label"=>"Vivendi - CSV",
    
    
    "SAP-only not exported"=>"SAP-nur neue Buchungen",


    "generate_v1_csv"=>"CSV - Export",
    "generate_v2_csv"=>"CSV - Export.v2", 
    "generate_nie_csv"=>"CSV - Oldenburg",
    "generate_unna_csv"=>"CSV - Vivendi",
    "generate_e_s_csv"=>"CSV - E+S",            //ISPC-2505 Lore 17.12.2019
    "generate_sap_text"=>"TXT - SAP", 
    "generate_sap_ii_text"=>"TXT - SAP Debitoren", //ISPC-2452
    "sap_txt"=>"TXT - SAP", 
    "sap_ii_txt"=>"TXT - SAP Debitoren", 
    "sh_txt"=>"TXT SH Internal", 
    "sh_external_txt"=>"TXT SH External", 
    "generate_sh_external_txt"=>"TXT - SH External", 
	
    "export_type"=>"Export Typ",
    "export_button"=>"Export", // buton for pdf
    "Export options"=>"Export Optionen",
    // ISPC-2272 - message if no invoice is selected
    
    "please select invoice type then filter"=>"Bitte Rechnungs-Art auswählen",

    //ISPC-2424
    "export_button_sh"=>"FIBU Protokoll SH", 
    "generate_fibu_excel"=>"Excel - FIBU Protokoll SH", // TODO-2915 Lore 20.02.2020 
    
    
    
    // ISPC-2286
    
    "nr price list  2018"=>"Nordrhein",
    
    "nr_invoice_lang"=>array(
        "patient_name"=>"Name",
        "patient_birth"=>"Geburtsdatum",
        
        "price"=>"Preis",
        "dta_price"=>"DTA Price",
        "dta_id"=>"DTA id",
    
        	
        	
        "nrinvoice_action_name"=>"Name",
        "nrinvoice_price"=>"Preis",
        "nrinvoice_dta_price"=>"DTA Preis",
        "nrinvoice_dta_digits_3_4"=>"DTA digit 3+4",// column in pricelist
        "nrinvoice_dta_digits_7_10"=>"DTA digit 7-10",// column in pricelist
        "nrinvoice_dta_location"=>"Typ Aufenthaltsort",// column in pricelist
        // RLP PATIWNT CONTROL PAGE
        "nrinvoice_control_title"=>"Leistungsnachweis Kinder",
        "expand_table" => "Tabelle erweitern",//2 buttons, first to expand 31days table, second to hide the top table for more space
    
        'save' => 'Nur speichern',
        'save_and_pdf' => 'Speichern und PDF erstellen',
        'reset' => 'RESET',
        	
        	
        "products" => array(
            	
            "shortcut_name_b1"=>"Beratung Grundpauschale (Tag 1-28)",// ;[can be billed one time per LIFE 	is billed for FIRST ever XT entry or "Beratung" shortcut in verlauf in the first 28 days of treatment ]
            "shortcut_name_b2"=>"Beratung Folgepauschale (ab Tag 29)",// [can be billed one time per LIFE 	is billed for FIRST ever XT entry or "Beratung" shortcut in verlauf from day 29 of treatment ]
            "shortcut_name_k1"=>" Koordination/Assessment Grundpauschale (Tag 1-28)",// [for 1-n assessment forms being filled in the days 1- 28 	is billed for the ASSESMENT being filled ]
            "shortcut_name_k2"=>"Koordination/Assessment Folgepauschale (ab Tag 29)",// [ONE TIME BILLED ! 	for 1-n FOLGE-assessment forms being filled from day 29 of reatment ]
            "shortcut_name_tv1"=>"TV/VV Versorgungstag ambulant",// [ONE per day max. 	billed for every day WITH A VISIT ]
            "shortcut_name_tvh"=>"TV Versorgungstag stat. Hospiz",//  [ONE per day max.  -  	billed for every day WITH A VISIT in HOSPIZ ]
            "shortcut_name_dth"=>"allpauschale Exitus 48h",//  [can be billed one time per LIFE ]
            
            "b1_label"=>"Beratung Grundpauschale (Tag 1-28)",// ;[can be billed one time per LIFE 	is billed for FIRST ever XT entry or "Beratung" shortcut in verlauf in the first 28 days of treatment ]
            "b2_label"=>"Beratung Folgepauschale (ab Tag 29)",// [can be billed one time per LIFE 	is billed for FIRST ever XT entry or "Beratung" shortcut in verlauf from day 29 of treatment ]
            "k1_label"=>" Koordination/Assessment Grundpauschale (Tag 1-28)",// [for 1-n assessment forms being filled in the days 1- 28 	is billed for the ASSESMENT being filled ]
            "k2_label"=>"Koordination/Assessment Folgepauschale (ab Tag 29)",// [ONE TIME BILLED ! 	for 1-n FOLGE-assessment forms being filled from day 29 of reatment ]
            "tv1_label"=>"TV/VV Versorgungstag ambulant",// [ONE per day max. 	billed for every day WITH A VISIT ]
            "tvh_label"=>"TV Versorgungstag stat. Hospiz",//  [ONE per day max.  -  	billed for every day WITH A VISIT in HOSPIZ ]
            "dth_label"=>"allpauschale Exitus 48h",//  [can be billed one time per LIFE ]
            	
        ),
        "locations" => array(
            
            "private_house_label" => "privater Haushalt",
            "complete_care_facility_label" => "vollstat. Pflege",
            "partial_care_facility_label" =>"teilstat. Pflege",
            "disabled_care_facility_label" =>"Behinderteneinrichtung",
            "hospiz_location_label" =>"stat. Hospiz",
            "other_locations_label" =>"sonst. Ort",
            
            "location_type_private_house" => "privater Haushalt",
            "location_type_complete_care_facility" => "vollstat. Pflege",
            "location_type_partial_care_facility" =>"teilstat. Pflege",
            "location_type_disabled_care_facility" =>"Behinderteneinrichtung",
            "location_type_hospiz_location" =>"stat. Hospiz",
            "location_type_other_locations" =>"sonst. Ort",
            
        ),
         
        "location_type_heim"=>"Altenheim / Pflegheim",
        "location_type_home"=>"zu Hause",
    
        	
    ),
    
    
  /*   "shortcut_name_nr_invoice_b1"=>"Beratung Grundpauschale (Tag 1-28)",// ;[can be billed one time per LIFE 	is billed for FIRST ever XT entry or "Beratung" shortcut in verlauf in the first 28 days of treatment ]
    "shortcut_name_nr_invoice_b2"=>"Beratung Folgepauschale (ab Tag 29)",// [can be billed one time per LIFE 	is billed for FIRST ever XT entry or "Beratung" shortcut in verlauf from day 29 of treatment ]
    "shortcut_name_nr_invoice_k1"=>" Koordination/Assessment Grundpauschale (Tag 1-28)",// [for 1-n assessment forms being filled in the days 1- 28 	is billed for the ASSESMENT being filled ]
    "shortcut_name_nr_invoice_k2"=>"Koordination/Assessment Folgepauschale (ab Tag 29)",// [ONE TIME BILLED ! 	for 1-n FOLGE-assessment forms being filled from day 29 of reatment ]
    "shortcut_name_nr_invoice_tv1"=>"TV/VV Versorgungstag ambulant",// [ONE per day max. 	billed for every day WITH A VISIT ]
    "shortcut_name_nr_invoice_tvh"=>"TV Versorgungstag stat. Hospiz",//  [ONE per day max.  -  	billed for every day WITH A VISIT in HOSPIZ ]
    "shortcut_name_nr_invoice_dth"=>"allpauschale Exitus 48h",//  [can be billed one time per LIFE ]
    
    "b1_nr_invoice_label"=>"Beratung Grundpauschale (Tag 1-28)",// ;[can be billed one time per LIFE 	is billed for FIRST ever XT entry or "Beratung" shortcut in verlauf in the first 28 days of treatment ]
    "b2_nr_invoice_label"=>"Beratung Folgepauschale (ab Tag 29)",// [can be billed one time per LIFE 	is billed for FIRST ever XT entry or "Beratung" shortcut in verlauf from day 29 of treatment ]
    "k1_nr_invoice_label"=>" Koordination/Assessment Grundpauschale (Tag 1-28)",// [for 1-n assessment forms being filled in the days 1- 28 	is billed for the ASSESMENT being filled ]
    "k2_nr_invoice_label"=>"Koordination/Assessment Folgepauschale (ab Tag 29)",// [ONE TIME BILLED ! 	for 1-n FOLGE-assessment forms being filled from day 29 of reatment ]
    "tv1_nr_invoice_label"=>"TV/VV Versorgungstag ambulant",// [ONE per day max. 	billed for every day WITH A VISIT ]
    "tvh_nr_invoice_label"=>"TV Versorgungstag stat. Hospiz",//  [ONE per day max.  -  	billed for every day WITH A VISIT in HOSPIZ ]
    "dth_nr_invoice_label"=>"allpauschale Exitus 48h",//  [can be billed one time per LIFE ]
    
    // NOT OK
    "location_type_nr_invoice_private_house" => "privater Haushalt",
    "location_type_nr_invoice_complete_care_facility" => "vollstat. Pflege",
    "location_type_nr_invoice_partial_care_facility" =>"teilstat. Pflege",
    "location_type_nr_invoice_disabled_care_facility" =>"Behinderteneinrichtung",
    "location_type_nr_invoice_hospiz_location" =>"stat. Hospiz",
    "location_type_nr_invoice_other_locations" =>"sonst. Ort", */
    // -- ISPC-2286  
    "bre kinder new link" =>"Bre Kinder 2018",
    "nr new link" =>"Nordrhein 2018",
    
	// ISPC-2438
    "booking_account" =>"Buchungskonto",
    'bw_debitornumber'=>'Debitorennummer',
    'bw_booking_account'=>'Buchungskonto',
    'generate_bw_external_2_csv'=>'CSV - BW Extern.v2',
    
    // ISPC-2566 Andrei 28.05.2020
    'generate_bw_external_3_csv'=>'CSV - BW Extern.v3',
    'bw_billing_month' => 'Abrechnungsmonat',
    'bw_product_totalprice' => 'Gesamtsumme',
    'bw_cost_center' => 'Kostenstelle',
    
    // ISPC-2424 @Lore 10.10.2019
    'debitornumber_of_invoice_receiver'=>'Konto',
    'account_assessment_or_teilversorgung'=>'Gegenkonto',
    'number_kst'=>'KST',
    'billed_period_fibu'=>'Buch.Datum',
    'invoice_date_fibu'=>'Belegdatum',
    'invoice_number_fibu'=>'BelegNr',
    'booking_text_fibu'=>'Buchungstext',
    
    //ISPC-2461
    'demstepcare_invoice' => 'Demstepcare Kontrollseite', 
    'Demstepcare controll page' => 'Demstepcare Kontrollseite', 
    'list_dta_invoices_demstepcare_invoice' => 'DTA Erzeugung Demstepcare', 
    'quarter_billing_methods'=>'Quartalsmethode',// Client page New billing method
    'quarter_fall'=>'Quartalsmethode',// Label in invoices generating page(Abrechnung)
    
);