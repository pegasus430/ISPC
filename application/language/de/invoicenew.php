<?php
return [
    "Transmit HL7-FT1"=>"HL7 Übertragung", //label used for batch and also for each individual invoice that was never sent
    "No invoice selected for which to transmit HL7" =>"Es wurden keine Rechnungen für die HL7 Übermittlung ausgewählt." ,//if no chekbox is checked
    "Failed to identify invoice to transmit HL7, please contact admin" =>"Ein Fehler bei HL7 Übertragung ist aufgetreten. Bitte informieren Sie einen Admin von smart-Q", //this should never happen
    
    "HL7-FT1 was sent" =>"HL7 Nachricht wurde erfolgreich übertragen.", //if delivered OK
    "HL7-FT1 was sent but failed" =>"HL7 Nachricht wurde gesendet, aber vom KIS nicht angenommen", //if hl7 server will respond with a MSA-1 AE or AC
    "(%s times)" => "(%s Versuche)",
    
    "There are no case numbers, transmission is not possible"=> "Es liegen keine Fallnummern vor, Übertragung nicht möglich",
    // Maria:: Migration ISPC to CISPC 08.08.2020
    //ISPC-2459
    "HL7-FT1- ACTIVATION- was sent" =>"HL7 Nachricht wurde erfolgreich übertragen.[Aktivierung]", //if delivered OK
    "HL7-FT1- ACTIVATION- was sent but failed" =>"HL7 Nachricht wurde gesendet, aber vom KIS nicht angenommen[Aktivierung]", //if hl7 server will respond with a MSA-1 AE or AC
    "Failed to identify invoice to transmit HL7-Activation, please contact admin" =>"Ein Fehler bei HL7 Übertragung ist aufgetreten. Bitte informieren Sie einen Admin von smart-Q[Aktivierung]", //this should never happen
    
    "client_movement_number" =>"Movement number [DE]", //ISPC-2459 Ancuta 06.08.2020 
    "zbe_start_number" =>"Start movement number", //ISPC-2459 Ancuta 06.08.2020 
    //--
    
    "For invoice number x no activation was sent for date y" =>"For invoice number %invoicenumber no activation was sent for date %date [DE]",
    'There are no MOVEMENT numbers for days X, transmission is not possible'=>'Es gibt keine Bewegungsdaten für die Daten: %dates. Eine Übertragung ist nicht möglich.',
    
    /* 'shift_item_family_doctor'=>'Hausarzt Preis',
    'shift_item_pflege'=>'Pflegedienst Preis',
    'shift_item_day'=>'Arzt Preis - Tag',
     */'shift_item_night'=>'Arzt Preis - Nacht',
    
    'shift_item_family_doctor'=>'Vergütung Hausarzt',
    'shift_item_pflege'=>'Vergütung Pflegedienst',
    'shift_item_day'=>'Vergütung Tagschicht',
    'shift_item_night'=>'Vergütung Nachtschicht',
    
    // TODO-2315:: 08.07.2019 Ancuta
    'There are  generated invoices, included in this period. Continue?'=>'Es gibt bereits Rechnungen fü diesen Zeitraum. Wollen Sie trotzdem fortfahren',
    'yes, continue generating the invoice for this period'=>'Ja, bitte Rechnung erneut erzeugen',
    'selected period is incomplete - continue ?'=>'Achtung. Der Fall des Patienten ist noch nicht abgeschlossen',
    
    // Lore 19.12.2019
    'There are  generated invoices, included in this period!' => 'Es gibt bereits Rechnungen fü diesen Zeitraum!',


    "demstepcare_invoice_label"=>"Demstepcare Rechnungen",
    "system_invoice_headline_demstepcare_invoice"=>"Demstepcare Rechnungen",
    "system_invoice_headline_demstepcare_internal_invoice"=>"Demstepcare Interne Rechnungen",
    "demstepcare_internal_invoice_label"=>"Demstepcare Interne Rechnungen",//ISPC-2585 Ancuta 09.06.2020
    
	//TODO-3392 Carmen 03.09.2020
    "demstepcare price list  2018"=>"DemStepCare",		
	'Demstepcare price list' => 'DemStepCare Preisliste',
	'Price List Details' => 'Preisliste Details',
	//--
    'demstepcare_invoice_lang' => array(
        "patient_name"=>"Name",
        "patient_birth"=>"Geburtsdatum",
    
        "demstepcare_action_name"=>"Name",
        "price"=>"Preis",
        "dta_price"=>"DTA Price",
        "dta_id"=>"DTA id",
        'demstepcare_generate_invoices'=>'Rechnungen für das Aktive Quartal erzeugen',	
        
    ),
    //    TODO-2788 ISPC: Invoice generating loads without end
    "Client invoice type -Hospz register- does not use this page, invoices are generated from patient menu" => 'Ihr REchnungstyp unterstützt keine Hospiz-Rechnungen. Bitte erzeugen Sie die Rechnungen aus dem Patientenmenu.',
    
    //ISPC-2623 Carmen 18.08.2020
    'DATEV-import for paid invoices' => 'DATEV-Import für bezahlte Rechnungen',
	'browsefile for import' => 'Datei für den Import suchen',
	'Delimiter' => 'Delimiter',
	'process import' => 'Import durchführen',
	'No Payments were imported because no invoices were found' => 'Es wurden keine Zahlungen importiert, da keine Rechnungen gefunden wurden',
	'File corrupted or no data to be imported' => 'File corrupted or no data to be imported',
    'payments were imported for X invoices' => 'Zahlung(en) wurden für %invoice_payed Rechnung(en) importiert',//TODO-3800 Ancuta 16.02.2021
	'You have to select a csv file!' => 'Bitte .csv-Datei auswählen',
    "The import was not done completely" => "Der Import wurde nicht vollständig durchgeführt",//TODO-3800 Ancuta 16.02.2021
	
    
    //ISPC-2609 Ancuta
    "print_job_table_headline" =>'Aktuelle Druck-Warteschlange',
    "queue_nr" =>'Warteschlange Nummer',
    "Queue_nr" =>'Warteschlange Nummer',
    "print_type" =>'Print Typ',
    "print_status" =>'Aktueller Status / Fortschritt',
    "print_link" =>'#',
    "print_date" =>'Datum',
    'ps_completed' => 'Erledigt',
    'ps_canceled' => 'abgesagt',
    'ps_in_progress' => 'In Bearbeitung',
    'ps_active' => 'Offen',
    'ps_error' => 'Fehler',
    
    //ISPC-2609 Ancuta 07.09.2020
    'inform_print_job_created' => 'Druck-Auftrag wurde erstellt. Das Dokument wird unten auf der Seite in der Druckwarteschlange erzeugt.', // Message   to inform print job was createdn, and it van be seen in the print jobs table
    'Clear_all_prints' => 'Liste der erzeugten Druckaufträge löschen',//  "buton"  name to clear all prints- af the user for the  
    'confirmdeletepjobtitle' => 'Druck löschen.',
    'confirmdeletepjob' => 'Bitte bestätigen Sie das Löschen der erzeugten Druckaufträge.',// delete alert
    'confirm_clear_print_jobs_title' => 'Druckdateien löschen', // clear all alert 
    'confirm_clear_print_jobs' => 'Bitte bestätigen Sie das Löschen der erzeugten Druckaufträge',
    
    
		
];
