<?php
/**
 * this aims to unify all tokens, with a Pms_Tokens
 * 
 * ! ALLWAYS PREFIX (or suffix) your "token variable language specific" with the group name,
 * so you avoid overwriting it (prefix is more easy to read by a user)
 * 
 * !! STOP using tokens like address... cause client has address, user has address... everybody has an address !! 
 * 
 * tokens_email_lang = array(
 * 
 * 		token group = array( column in db for this token , array( token variable language specific(this will be in the docx) , token help info)) ,
 * 		.
 * 		.
 * 		.
 * 
 * )
 * 
 * YOU ANC ALLWAYS WRITE A NEW ONE, IN A NEW TRANSLATION FILE !
 * return array(
 * 	"default_tokens" => "Standard Token", // label translation for a group
 *  "MY_VERY_SPECIAL_TOKENS_lang" => array(
 *  .
 *  .
 *  .
 *  
 * 
 */
return array(

	"default_tokens" => "Standard Token", // label translation for a group

    "tokens_email_lang" => array(

		//table_header is used as info only
		'table_header' => array(
				'lang' => 'Token-Liste',
				'key' => 'Variablen',
		),
		
		
		'default_tokens' => array(
				
		    /**
		     * tokens with PREFIX default_ will be available on ALL pages
		     * you MUST define their values on Pms_Tokens, or send them as usual
		     * be carefull with the default ones, and allways create a default value for them
		     */
				'default_current_date' =>  array("aktuelles_datum", "Aktuelles Datum (d.m.Y)"),
		    
		    
		    
		    /**
		     * this are tokens specific to a action, but will be listed First in the viewer!
		     * use as example : Pms_Tokens -> create_Studypool()
		     * and write your own specific create_Action()
		     * 
		     */
				'survey_url' =>  array("studypool_url", "Ihre studypool übersichten url"),
				/**
				 * ISPC-2411
				 */
		        'survey_link' =>  array("survey_link", "SURVEY LINK"),
		),

        
		//ISPC-2411 // Maria:: Migration ISPC to CISPC 08.08.2020
        'recipient' =>array(
            'Ipos_recipient_firstname' =>  array("Ipos_recipient_firstname", "FIRSTNAME of the selected IPOS reicipient"),
            'Ipos_recipient_surname' =>  array("Ipos_recipient_surname", " SURNAME of the selected IPOS reicipient"),
        ),
        
			
		'patient' => array(
				//column name from dbf => array( token name without $, infotext)
				'epid' =>  array("patienten_id", "ISPC Patientennummer"),
				'last_name' =>  array(
				    ["nachname_patient", "Nachname des Patienten"],
				    ["last_name", "Nachname des Patienten"],
				    
				),
				'first_name' => array(
				    ["vorname_patient",  "Vorname des Patienten"],
				    ["first_name",  "Vorname des Patienten"],
				),
				'birthd' =>  array(
				    ["geb_datum_patient", "Geb.-Datum des Patienten"],
				    ["birthd", "Geb.-Datum des Patienten"],
				),
				'street1' =>  array(
				    ["straße_patient", "Strasse des Patienten"],
				    ["street", "Strasse des Patienten"],
				),
				'zip' =>  array(
				    ["plz_patient", "PLZ des Patienten"],
				    ["zip", "PLZ des Patienten"]
				    
				),
				'city' =>  array(
				    ["ort_patient", "Ort des Patienten"],
				    ["city", "Ort des Patienten"]
				    
				),
				'phone' =>  array("telefon_patient", "Telefonnummer des Patienten"),
				'mobile' =>  array("mobiltelefon_patient", "Mobilfunknummer des Patienten"),
				'admission_date' => ['admission_date'],
		),

		'client'=> array(
				'team_name' =>  array("name_mandant", "Name Mandant"),
				'phone' => array("telefonnummer_mandant", "Telefonnummer Mandant"),
				'street1' => array(
				    ["straße_mandant", "Straße Mandant"],
				    ["strasse_mandant", "Straße Mandant"],
				),
				'postcode' => array("plz_mandant", "PLZ Mandant"),
				'city' => array("ort_mandant", "Ort Mandant"),
				'institutskennzeichen' => array("client_ik", "IK Mandant"),
				'client_address' => ['client_address'],
				'lbg_city' => array("lbg_city", "lbg_city"),
				
		    /*
		    'what is the column for token $bank_name$' => array("bank_name", "token description"),
		    'what is the column for token $iban$' => array("iban", "token description"),
		    'what is the column for token $bic$' => array("bic", "token description"),
		    */
		),

		'user'=> array(
				'first_name' => array(
							array("user_vorname", "Benutzer Vorname"),
							array("benutzer_vorname", "Benutzer Vorname"),
						),
				'last_name' => array(
							array("user_nachname", "Benutzer Nachname"),
							array("benutzer_nachname", "Benutzer Nachname"),
						),
				'control_number' => array("steuernummer", "Steuernummer"),//ISPC-1236 08.07.2019
				'fax' => array("fax_number_user", "Fax number"),
				'bank_name' => array("bank_name", "Bank name"),
				'bank_account_number' => array("kontonummer", "Bank account number"),
				"bank_number" => array("blz", "Bank number"),
				'iban' => array("iban", "IBAN"),
				'bic' => array("bic", "BIC"),
				'city' => ['user_city', 'user_city'],
		        'benutzer_adresse' => ['benutzer_adresse','benutzer_adresse'],
		    
		),

		'voluntaryworker'=> array(
					
				'first_name' => array("ehrenamtliche_vorname", 'Vorname des Ehrenamtlichen'),
				'last_name' => array("ehrenamtliche_nachname", 'Nachname des Ehrenamtlichen'),
				'street' => array("ehrenamtliche_straße", 'Strasse des Ehrenamtlichen'),
				'zip' => array("ehrenamtliche_plz", 'PLZ des Ehrenamtlichen'),
				'city' => array("ehrenamtliche_ort", 'Ort des Ehrenamtlichen'),
				'salutation' => array("ehrenamtliche_anrede", 'Anrede des Ehrenamtlichen'),
				'phone' => array("ehrenamtliche_telefon", 'Telefonnummer des Ehrenamtlichen'),
				'email' => array("ehrenamtliche_email", 'E-Mailadresse des Ehrenamtlichen'),
				//next 4 are not column names
				'zugeordneter' => array("zugeordneter_Ehrenamtlicher", 'Ehrenamtlicher der dem Patient zugeordnet ist - wird mit Vorname und Nachname angezeigt'),
				'Versorgungsstart' => array("Versorgungsstart_Ehrenamtlicher", 'Startdatum der Ehrenamtlichen Begleitung'),
				'versorgungsende' => array("versorgungsende_Ehrenamtlicher", 'Endatum der Ehrenamtlichen Begleitung'),
				'AnzahlBesuche' => array("AnzahlBesuche_voluntaryworker", 'Anzahl der Ehrenamtlichen Besuche'),

		),

		'member'=> array(
				'first_name' => array("mitglied_vorname", "Mitglied Vorname"),
				'last_name' => array("mitglied_nachname", "Mitglied Nachname"),
		),
			
			
		'contact_person'=> array(
				'cnt_first_name' => array("contact_vorname", "Vorname der Kontaktperson des Patienten"),
				'cnt_last_name' => array("contact_nachname", "Nachname der Kontaktperson Kontaktperson des Patienten"),
				'cnt_street1' => array("contact_straße", "Strasse der Kontaktperson Kontaktperson des Patienten"),
				'cnt_zip' => array("contact_plz", "PLZ der Kontaktperson Kontaktperson des Patienten"),
				'cnt_city' => array("contact_ort", "Ort der Kontaktperson Kontaktperson des Patienten"),
				'cnt_phone' => array("contact_telefon", "Telefonnummer der Kontaktperson Kontaktperson des Patienten"),
				'cnt_mobile' => array("contact_mobiltelefon", "Mobilfunknummer der Kontaktperson Kontaktperson des Patienten"),
				'cnt_familydegree' => array('contact_relationship', "contact_relationship")
				
		),
	    
	    'invoice' => array(
	        
	        'letterdate' => ['invoice_date', 'München | Bochum'],
	        
	        //multiple tokens for same value
	        'invoicedate' => array(
	            array('invoice_date' , 'München = Invoice date'),
	        	array('completed_date', "Hi Invoice/Bw SGBV Invoice"), //ISPC-2745 Carmen 17.11.2020
	        ),
	        
	        
	        'word_addition' => ['Rechnungstext_kopfzeile', 'Rechnungstext_kopfzeile token description'],
	        'invoicefreetext' => ['Rechnungstext_kopfzeile', 'Rechnungstext_kopfzeile token description'],
	        
	        'healthinsurancenumber' => ['insurance_no' , 'Krankenversicherungsnummer'],
	        
	        //'cycle' => ['SAPV_Fall' , 'Versorgungszeitraum'],
	        //'highsapv' => ['SAPV_Leistung' , 'Leistung'],
	        
	        'admissionlocation' => ['ort_leistung', 'Versorgungsort'],

	        'prefix' => [
	            ['Rechnungs_prefix', 'Invoice Prefix'],
	            ['prefix', 'Invoice Prefix'],
	        ],
	        'invoicenumber' => [
	            ['Rechnungs_nummer', 'Invoice Number'],
	            ['invoice_number', 'Invoice Number'],
	        ],
	        
	        'full_invoice_number' => ['full_invoice_number', 'Rechnungsnummer'],
	        
	        
	        
	        
	        
	        'invoiceamount' => ['invoice_total', 'Rechnungsbetrag'],
	        
	        
	        'date21' => ['faellig_am', 'Wir bitten um Ausgleich bis zum'],
	        
	        'healthinsuranceaddress' => ['healthinsurancename', 'row 1 from to address'],
	        'healthinsurancestreet' => ['healthinsurancename', 'row 2 from to address'],
	        'healthinsurancecontact' => ['healthinsurancename', 'row 3 from to address'],
	        'healthinsurancename' => ['healthinsurancename', 'row 4 from to address'],
	         
	        'healthinsurancename' => ['healthinsurancename', 'row 4 from to address'],
	    		
	    	'sapv_recipient' => ['SAPV_Rechnungsempfaenger', 'SAPV subdivision address'],
	         
	        'first_name' => ['first_name'],
	        
	        'address' => ['address'],
	        
	        'invoice_items_html' => ['invoice_items_html' , 'token description'],
	    		
	    	'invoice_items_html_short' => ['invoice_items_html_short' , 'token description'],// Added by Carmen 20.02.2019 ISPC-1236
	        
	        'internal_invoice_items_html' => ['internal_invoice_items_html' , 'token description'],// Added by Ancuta 30.08.2018 ISPC-2233
	        
	    	'internal_invoice_items_html_short' => ['internal_invoice_items_html_short' , 'token description'],// Added by Carmen 08.03.2019 ISPC-1236
	        
	        'healthinsurance_debtor_number' => [
	            ['debitor_number', 'for client which have the DEBITOREN modules active' ],
	            ['debitorennummer_krankenkasse' ,  'for client which have the DEBITOREN modules active'],
	            ['debitoren_nummer_oder_pv', 'for client which have the DEBITOREN modules active' ],
	        ],
	        
	        
	        // ADDED BY ANCUTA 
	        'invoiced_month' => ['invoiced_month', 'invoiced_month'],
	        'first_active_day' => ['first_active_day', 'first_active_day'],
	        'last_active_day' => ['last_active_day', 'last_active_day'],
	        'first_sapv_day' => ['first_sapv_day', 'first_sapv_day'],
	        'last_sapv_day' => ['last_sapv_day', 'last_sapv_day'],
	        'sapv_approve_date' => ['sapv_approve_date', 'sapv_approve_date'],
	        'sapv_approve_nr' => ['sapv_approve_nr', 'sapv_approve_nr'],
	        'footer' => ['footer', 'footer'],
	        'footer_text' => ['footer_text', 'footer_text'],
	        'unique_id' => ['unique_id', 'unique_id'],
	        'invoiced_month' => ['invoiced_month', 'invoiced_month'],

	        'patient_pflegestufe' => ['patient_pflegestufe','patient_pflegestufe'],
	        
	        'current_date' => ['aktuelles_datum','aktuelles_datum'],
	        
	        'sapv_footer' => ['invoice_footer','invoice_footer'],
	        'benutzer_adresse' => ['benutzer_adresse','benutzer_adresse'],
	        'beneficiary_address' => ['beneficiary_address','beneficiary_address'],
	        'invoiced_period' => ['invoiced_period','invoiced_period'],
	        'Abrechnungszeitraum' => ['Abrechnungszeitraum','invoiced_period'],//TODO-2713 Ancuta 04.12.2019
	    		
	    	//ADDED BY CARMEN
	    	'invoiceheader' => ['invoiceheader', 'invoiceheader'],
	    	'invoicefooter' => ['invoicefooter', 'invoicefooter'],
	    	'recipient' => ['recipient', 'recipient'],
	        'ikuser' => ['ikuser', 'ikuser'],
	    	'user_address' => ['user_address', 'user_address'],
	    	//ISPC-2745 Carmen 17.11.2020
	    	'start_sgbv_activity' => ['start_sgbv_activity', 'start_sgbv_activity'],
	    	'end_sgbv_activity' => ['end_sgbv_activity', 'end_sgbv_activity'],
    		'start_sgbxi_activity' => ['start_sgbxi_activity', 'start_sgbxi_activity'],
    		'end_sgbxi_activity' => ['end_sgbxi_activity', 'end_sgbxi_activity'],
	    	'health_insurance_ik' => ['health_insurance_ik' , 'Institutskennzeichen'],
	    	'healthinsurance_versnr' => ['healthinsurance_versnr' , 'Versichertennummer'],
	    	'healthinsurance_kassennr' => ['healthinsurance_kassennr' , 'Kassennummer'],
	    	'healthinsurance_status' => ['healthinsurance_status' , 'Versicherungsstatus'],
	    		
	    	'invoiced_period_start' => [
	    			['invoiced_period_start','invoiced_period_start'],
	    			['current_period_start','current_period_start'],
	    			['invoice_date_from', 'invoice_date_from'] //RP invoice
	    	],
    		'invoiced_period_end' => [
    				['invoiced_period_end','invoiced_period_end'],
    				['current_period_end','current_period_end'],
    				['invoice_date_till', 'invoice_date_till'] //RP invoice
    		],
	    	'topdatum' => ['topdatum'], //RP invoice
	    	'stample' => ['stample'], //RP invoice
	    	'main_diagnosis' => ['main_diagnosis'] //RP invoice
	        //--
	    		
	    ),
			
	    
	    'sapv' => array(
	        /*
	        'what is the column for token $first_sapv_day$' => ['first_sapv_day', 'token description'],
	        */
	        'cycle' => ['SAPV_Fall' , 'Versorgungszeitraum'],
	        'highsapv' => ['SAPV_Leistung' , 'Leistung'],
			//ISPC-2745 Carmen 24.11.2020
	    	'sapv_erst' => ['sapv_erst', 'Verordnung Ort erst'],
	    	'sapv_folge' => ['sapv_folge', 'Verordnung Ort folge'],
	    	'sapv_from' => ['sapv_from', 'SAPV from'],
	    	'sapv_till' => ['sapv_till', 'SAPV till'],
	    	//
	    ),
			
	    'anlage6' => array(
	    		'html_anlage2a' => ['html_anlage2a' , 'token description'],
	    		'html_anlage2b' => ['html_anlage2b' , 'token description'],
	    ),
    		
    	'healthinsurance' => array(
    			'healthinsurance_name' => ['healthinsurance_name'],
    			'healthinsurance_address' => ['healthinsurance_address'],
    	),
    		
    	'familydoctor' => array(
    			'doctor_name' => ['doctor_name'],
    			'doctor_address' => ['doctor_address'],
    			//ISPC-2745 Carmen 24.11.2020
    			'doctor_bsnr' => [
    					['doctor_bsnr', 'Betriebsstättennr'],
	            		['bsnr', 'Betriebsstättennr']
    			],
    			'doctor_lbnr' => [
    					['doctor_lbnr', 'Lebenslange Arztnummer'],
    					['arztnr', 'Lebenslange Arztnummer']
    			],
    			//--
    	),
    		
    	'hospicereport' => array(
    			'html_new' => ['html_new' , 'token description'],
    			'html_old' => ['html_old' , 'token description'],
    			'set_new' => ['set_new', 'Neu eingeschriebene Patienten'],
    			'set_old' => ['set_old', 'bereits aktive Patienten'],
    			'report_period' => ['report_period'],
    			'report_date' => ['report_date'],
    	)
	    
	),
    		
);
		