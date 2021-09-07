<?php
return array(
	//ISPC - 2281
	'list_dressings' => 'Liste Verbandsstoffe der Bestellung',
	'list_drugs' => 'Liste Arzneimittel der Bestellung',
	'list_auxiliaries' => 'Liste Hilfsmittel der Bestellung',
	'list_nursingauxiliaries' => 'Liste Pflegehilfsmittel der Bestellung',
	'list_recipients' => 'Liste Apotheke der Bestellung',
	'material_name' => 'Bezeichnung',
	'material_unit' => 'Einheit',
	'material_pzn' => 'PZN',
	'material_pieces' => 'Stück/VE',
	'material_quantity' => 'Bestellmenge',
	'recipient_username' => 'Benutzername',
	'recipient_user_title' => 'Titel',
	'recipient_last_name' => 'Nachname',
	'recipient_first_name' => 'Vorname',
	"adddefault" => "adddefault", //button to import default materials
	
    '[medication]' => 'Medikation',
    '[dosage]' => 'Dosierung',//TODO-2842 Ancuta 23.01.2020 // Maria:: Migration ISPC to CISPC 08.08.2020
    '[packaging]' => 'Kammerbeutel Info',
    '[volume]' => 'Volumen',
    '[kcal]' => 'Kcal',
    
    
    'Order management page' =>'Order management page XX',// page title

    'mail_subject_order' =>'Bestellung aus ISPC',// subject 
    
    '[Orders - assigned to me (active and paused)]' => '[Orders - assigned to me (active and paused)]', //Tab
    '[Orders - all (active and paused)]'=>'[Orders - all (active and paused)]', //Tab
    '[Orders - completed/ended]'=>'[Orders - completed/ended]', //Tab
    
    'Active and paused orders - of user'=>'Active and paused orders - of user', // tab title/info
    'Active and paused orders - of client'=>'Active and paused orders - of client', // tab title/info
    'All closed orders of client'=>'All closed/stopped orders of client ', // tab title/info
    
    
    '[Patients name column]'=>'[Patients name column]',
    // add patient modal
    '[add patient to active grid]'=>'[add patient to active grid]',// button
    '[Add new patient to active grid]'=>'[Add new patient to active grid]',// modal title
    
    // remove patient modal
    '[Remove Patient from order Grid]'=>'[Remove Patient from order Grid]X',
    'Are you sure you want to remove patient from order grid' => '[Are you sure you want to remove patient from order grid?]X',
    '[Remove]'=>'[Remove]X',
    
    
    '[Order modal]'=>'[Order modal x]',// modal title
    '[PLACE ORDER]'=>'[PLACE ORDER]',// modal button
    '[VERIFY ORDER]'=>'[VERIFY ORDER]',// modal button
    '[CANCEL ORDER]'=>'Bestellung löschen',// modal button
    '[close modal]'=>'CLOSE X', // modal button
    'paused'=>'Paused',
    'no aditional items ' => 'no aditional items  XX',
    
    'Orders that are after this were already verified/delivered - changes are NOT allowed!'=>'[Orders that are after this were already verified/delivered - changes are NOT allowed!]',
    'are you sure you want to cancel the order and all the following orders ?' => '[are you sure you want to cancel the order and all the following orders] ?',
    'are you sure you want to cancel the order and all the following orders ' => '[are you sure you want to cancel the order and all the following orders] DE ?',
    'Select order interval!' => 'Bestell-Intervall setzen',
    'Select medications!' => 'Präparate auswählen',
    'Select additional materials!' => 'Hilfsmittel auswählen',
    'Select pharmacy!' => 'Apotheke auswählen',
    'Select delivery date!' => 'Select delivery date!',
    '[please select patient and date]' => '[please select patient and date]',
    
    '[are you sure you want to change? Changes can affect all the followeing scheduled orders, also the Pharmacy will be notified]'=>'[are you sure you want to change? Changes can affect all the followeing scheduled orders, also the Pharmacy will be notified !]',
    
    'Patient already has active orders in future. New order is not allowed here!' => '[Patient already has orders scheduled after this date. New order is not allowed here!]',
    
    'order_details'=> array(
        '[Delivery For]' => 'Bestellung für: ',
        '[Dates]' => 'Datum',
        '[Order date]' => 'gewünschtes Lieferdatum ', // 'Bestell-Datum ' TODO-1577 01.03.2019 Ancuta
        '[Delivery date]' => 'bestätigtes Lieferdatum', //'Liefer-Datum ', TODO-1577 01.03.2019 Ancuta
        '[previous delivery]' => '[latest delivery] ',
        '[Order intervall]' => 'Bestell-Intervall',
        '[Once]' => 'Einmalig',
        '[Every x days]' => 'Alle x Tage',
        '[every week on the following work days]' => 'Jede Woche an den folgenden Tagen',
        '[latest delivery]' => 'Alle x Tage',
      
        
                
        '[Order Medication]' => 'Bestellte Präparate',
        '[Order Additional lists]' => 'zusätzliche bestellte Hilfs-, Verbandsmittel, etc',
        '[Pharmacy]' => 'Apotheke',
        '[Cancel Order]' => 'Bestellung löschen',
        '[Please fill here the reason why you canceled the order]' => 'Grund der Stornierung',
        '[Comment order]' => 'Bestell-Kommentar',
        
        '[no aditional items]' => 'no aditional items XX',
        '[Category_dressings]' => 'Verbandsstoffe',
        '[Category_drugs]' => 'Arzneimittel',
        '[Category_auxiliaries]' => 'Hilfsmittel',
        '[Category_nursingauxiliaries]' => 'Pflegehilfsmittel* nicht Erstattungsfähig',
        
        

        '' => '',
    ),
    'delivery_date_info' => 'Das Lieferdatum wird ausschließlich durch die Apotheke ausgefüllt und damit bestätigt',
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    'Order management page' =>'Bestellungen',// page title
    
    '[Orders - assigned to me (active and paused)]' => 'Meine Bestellungen (aktiv / pausiert)', //Tab
    '[Orders - all (active and paused)]'=>'Alle Bestellungen (aktiv / pausiert)', //Tab
    '[Orders - completed/ended]'=>'inaktive Bestellungen', //Tab
    
    'Active and paused orders - of user'=>'', // tab title/info <--- isnt that double? as the tab is selected?
    'Active and paused orders - of client'=>'', // tab title/info<--- isnt that double? as the tab is selected?
    'All closed orders of client'=>'', // tab title/info<--- isnt that double? as the tab is selected?
    
    '[Patients name column]'=>'Patient/in',
    // add patient modal
    '[add patient to active grid]'=>'Bestellungen für Patient anlegen',// button
    '[Add new patient to active grid]'=>'Patient hinzufügen',// modal title
    
    // remove patient modal
    '[Remove Patient from order Grid]'=>'Patient aus Bestellungen entfernen',
    'Are you sure you want to remove patient from order grid' => 'Sind Sie sicher, dass Sie diesen Patienten aus den aktiven Bestellungen entfernen wollen?',
    '[Remove]'=>'Entfernen',
    
    '[Order modal]'=>'Bestellung anlegen und bearbeiten',// modal title
    '[PLACE ORDER]'=>'Bestellung anlegen',// modal button
    '[VERIFY ORDER]'=>'Bestellung bestätigen',// modal button
    '[CANCEL ORDER]'=>'Bestellung löschen',// modal button
    '[GENERATE PDF ORDER]'=>'Pdf Erstellen', //ISPC-2639 Carmen 16.07.2020 modal button
    '[close modal]'=>'Schließen', // modal button
    'paused'=>'pausiert',
    'no aditional items ' => 'Keine zusätzlichen Mittel',
    
    'Orders that are after this were already verified/delivered - changes are NOT allowed!'=>'Bestellungen NACH dieser ausgewählten Bestellung wurden bereits ausgeführt und bestätigt. Änderungen sind nicht wirksam!',
    'are you sure you want to cancel the order and all the following orders ?' => 'Sind Sie sicher, dass Sie diese und zukünftige Bestellungen stornieren wollen?',
    'Select order interval!' => 'Bestell-Intervall setzen',
    'Select order interval - options!'=> 'Bestell-Intervall setzen',
    'Select medications!' => 'Präparate auswählen',
    'Select additional materials!' => 'Hilfsmittel auswählen',
    'Select pharmacy!' => 'Apotheke auswählen',
    'Select delivery date!' => 'Liefer-Datum auswählen!',
    '[please select patient and date]' => 'Datum und Patient auswählen',
    'Please provide reason of cancel !' => 'Bitte Grund der Stornierung angeben',
    
//    '[are you sure you want to change? Changes can affect all the followeing scheduled orders, also the Pharmacy will be notified]'=>'Sie ändern gerade eine Bestellung. Dies wirkt sich auf alle folgenden Bestellungen aus und beteiligte Mitarbeiter werden darüber informiert. Wollen Sie fortfahren?',
    '[are you sure you want to change? Changes can affect all the followeing scheduled orders, also the Pharmacy will be notified]'=>'Sie ändern gerade eine Bestellung. Dies wirkt sich auf alle folgenden Bestellungen aus. Wollen Sie fortfahren?',   //TODO-2872 Lore 27.03.2020
    
    'Patient already has active orders in future. New order is not allowed here!' => 'Dieser Patient hat bereits aktive Bestellungen. Eine neue Bestellung ist daher hier nicht erlaubt.',
    
    'order_details'=> array(
        '[Delivery For]' => 'Bestellung für: ',
        '[Dates]' => 'Datum',
        '[Order date]' => 'gewünschtes Lieferdatum ', // 'Bestell-Datum ' TODO-1577 01.03.2019 Ancuta
        '[Delivery date]' => 'bestätigtes Lieferdatum', //'Liefer-Datum ', TODO-1577 01.03.2019 Ancuta
        '[previous delivery]' => 'letzte Lieferung(en)',
        '[Order intervall]' => 'Bestell-Intervall',
        '[Once]' => 'Einmalig',
        '[Every x days]' => 'Alle x Tage',
        '[every week on the following work days]' => 'Jede Woche an den folgenden Tagen',
    
        '[Order Medication]' => 'Bestellte Präparate',
        '[Order Additional lists]' => 'zusätzliche bestellte Hilfs-, Verbandsmittel, etc',
        '[Pharmacy]' => 'Apotheke',
        '[Cancel Order]' => 'Bestellung löschen',
        '[Please fill here the reason why you canceled the order]' => 'Grund der Stornierung',
        '[Comment order]' => 'Bestell-Kommentar',
    
        '[no aditional items]' => 'keine zusätzlichen Mittel',
        '[Category_dressings]' => 'Verbandsstoffe',
        '[Category_drugs]' => 'Arzneimittel',
        '[Category_auxiliaries]' => 'Hilfsmittel',
        '[Category_nursingauxiliaries]' => 'Pflegehilfsmittel* nicht Erstattungsfähig',
        
        
        '[medication]' => 'Medikation',
        '[dosage]' => 'Dosierung',//TODO-2842  Ancuta 23.01.2020
        '[packaging]' => 'Kammerbeutel Info',
        '[volume]' => 'Volumen',
        '[kcal]' => 'Kcal',
        '[no medication]' => '--',
        '[Order verified by user:]' => 'Bestellung bestätigt durch :',
 
    ),
    
    
    '[stop active orders]'=>'Aktive Bestellung stoppen', // stop modal
    'stop orders starting with'=>'Bestellungen ab folgendem Datum stoppen:',
    '[Pause active orders]'=>'Aktive Bestellungen pausieren', // pause modal
    'pause orders starting with'=>'Bestellungen ab folgendem Datum pausieren:' , 
    '[Save]'=>'Speichern',  
    
    'Please check dates, there were orders verified/delivered'=>'Stoppen nicht möglich, da nach diesem Termin schon bearbeitete Bestellungen sind.',
    
    'order_pdf_title'=>'Bestellung',       
    //ISPC-2464 Ancuta 31.10.2019 
//     '[save order options]'=>'[save order options DE]', // Option modal title
//     '[Yes, change current order]'=>'[Ja, change current order]',       // Modal button to change only current
//     '[Yes, change current and all following]'=>'[Ja, change current and all following]', // Modal button to change all        
//     'This are the SAVED orders that will also be changed if \"change all\" is selected:'=>'[This are the SAVED orders that will also be changed if "change all" is selected:]', // info about saved orders that wil be changed       
//     '[are you sure you want to change? Select which orders you want the changes to be applied! Please note that the Pharmacy will be notified]'=>"[are you sure you want to change? Select which orders you want the changes to be applyed!<br/> Please note that the Pharmacy will be notified DE]", // Modal text        
    


    '[save order options]'=>'Bestelloptionen speichern',
    '[Yes, change current order]'=>'Ja, aktuelle Bestellung ändern',
    '[Yes, change current and all following]'=>'Ja, aktuelle und folgende ändern',
    'This are the SAVED orders that will also be changed if \"change all\" is selected:'=>'Dieses sind die gespeicherten Bestellungen die auch geändert werden, wenn "alle ändern" ausgewählt ist:',
    '[are you sure you want to change? Select which orders you want the changes to be applied! Please note that the Pharmacy will be notified]'=>"Sie sie sicher, dass sie die Änderungen vornehmen wollen? Wählen sie aus für welche Bestellungen die Änderungen angewendet werden sollen!<br/> Bitte beachten sie, dass die Apotheke benachrichtig wird.",
    
    
    
    
    
    //TODO-2872 Ancuta 24-25.03.2020
    '[Choose Order modal]' => 'Bestell-Modalitäten auswählen',// modal title
    'Patient already has active orders in future. Only Bestell-Intervall:  Einmalig  are allowed' => 'Patient hat bereits aktive zukünftige Bestellungen. Nur das Bestell-Intervall: "Einmalig" ist erlaubt',// if user tries to add not "only once" order
    'It is not allowed to delete VERIFIED order from the past! ' => 'Zurückliegende bereits bestätigte Bestellungen können nicht gelöscht werden! ',// message when a user tries  to cancel a verified order from past
    'are you sure you want to cancel the order and all the following orders ' => 'Sind Sie sicher, dass sie die Bestellung und alle folgenden stornieren wollen?', // message 
    'choose_order_details'=> array(
        '[Add new order - for date:]'=>'Neue Bestellung anlegen - für Datum:',// new button
        '[Edit order: started on %start_order_date%  status :]'=>'Bestellung bearbeiten: begonnen am %start_order_date%  status :',// new button
        //'Patient already has active orders in future. Only  orders type "Einmalig" are allowed' => 'Dieser Patient hat bereits aktive Bestellungen. Eine neue Bestellung ist daher hier nicht erlaubt.',
    )
    
    
);