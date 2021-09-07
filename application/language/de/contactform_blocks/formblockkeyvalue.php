<?php
// Maria:: Migration CISPC to ISPC 22.07.2020
return [
    //IM-46
    'block_talkcontent'=> 'Gesprächsinhalt',
    'talkcontent'=> 'Gesprächsinhalt',

    //IM-51
    'block_medication_clinic' => 'Medikamente für Klinik-Bericht',
    'medication_clinic' => 'Medikamente',
    'medication_clinic_no_plan' => 'Der Patient hat noch keinen Medikationsplan.',
    'medication_clinic_plan' => 'Medikamente auswählen, die im Dokument erscheinen sollen.',
    'medication_clinic_change_plan' => 'Medikationsplan ändern',
    'medication_clinic_load_plan' => 'aktuellen Medikationsplan holen',
    'medication_clinic_description' => 'Hier können nur Medikamente für das Dokument ausgewählt werden. Der aktuelle Medikationsplan des Patienten wird nicht verändert.',
    'medication_clinic_legaltext' => 'Wir weisen darauf hin, dass während der Einstellungsphase von starken Schmerzmitteln das Führen eines Fahrzeugs nicht möglich ist, da in den ersten 14 Tagen sowie nach jeder Dosiserhöhung aufmerksamkeitseinschränkende Nebenwirkungen häufig sind. Bei gut eingestellten Patienten, die nicht mehr unter Sehstörungen, Müdigkeit, Übelkeit, Erbrechen oder Schwindel leiden, steht dem Autofahren nichts entgegen. Fahrtüchtige Patienten sollten einen Opioid-Ausweis mit sich führen.',
    //IM-56
    'block_talkwith'=>'Gespräch mit',
    'talkwith'=>'Gespräch mit',
    'talkwith_contact'=>'Kontakt mit',
    'talkwith_further_information'=>'weitere Angaben',
    //IM-65
    'block_palliativ_support'=>'Begleitung',
    'palliativsupport'=>'Begleitung',
    'palliativ_support_further' => 'tägliche multiprofessionelle Begleitung durch Palliativmedizinischen Dienst wird fortgeführt',
    'palliativ_support_closed' => 'Begleitung durch Palliativmedizinischen Dienst abgeschlossen',
    //IM-66
    'block_palliativ_assessment'=>'Beurteilung und Empfehlung',
    'palliativassessment'=>'Beurteilung und Empfehlung',
    //IM-87
    'block_clinic_soap'=>'Körperliche Anamnese',
    'clinic_soap'=>'Körperliche Anamnese',
    'clinic_soap_patsympt'=>'Eigen-/Fremdanamnese',
    'clinic_soap_docdiag'=>'Professionelle Einschätzung',
    //IM-91
    'clinic_diagnosis' => 'Diagnosen bei Aufnahme',
    'block_clinic_diagnosis' => 'Diagnosen bei Aufnahme',
    //IM-92
    'clinic_shift' => 'Schicht',
    'block_clinic_shift' => 'Schicht',
    'block_clinic_early_shift' => 'Frühschicht',
    'block_clinic_late_shift' => 'Spätschicht',
    'block_clinic_night_shift' => 'Nachtschicht',
    'block_clinic_begin_of_night_shift' => ' ist der Beginn der Nachtschicht',
    'block_clinic_end_of_night_shift' => ' ist das Ende der Nachtschicht',
    'block_clinic_please_select' => 'Bitte wählen',
    //IM-103
    'clinic_measure' => 'Maßnahmen',
    'block_clinic_measure' => 'klinische Maßnahmen',
    'clinic_measure_status_button' =>'Diagnosen neu einlesen',
    'clinic_measure_status_warning' => 'Dieser Block enthält die mit diesem Formular abgespeicherten Diagnosen. Die aktuellen Diagnosen des Patienten können mittlerweile anders sein. <b>Achtung: </b>Änderungen an diesem Block ändern auch die Diagnosen',
    'clinic_measure_status_refresh' => 'Über diesen Button werden die aktuellen Diagnosen des Patienten eingelesen und in das Formular übernommen.',
    //IM-134
    'clinic_measure_diagnosis_button' =>'Aktuelle Diagnosen ansehen und bearbeiten',
    'clinic_measure_diagnosis_warning' => 'Dieser Block enthält die mit diesem Formular abgespeicherten Diagnosen. Die aktuellen Diagnosen des Patienten können mittlerweile anders sein. ',
    'clinic_measure_diagnosis_refresh' => 'Über diesen Button werden die aktuellen Diagnosen des Patienten im Popup angezeigt mit der Möglichkeit, sie direkt zu bearbeiten.',
    'clinic_measure_diagnosis_send_to_form' => 'Ins Formular übernehmen',
    'clinic_measure_diagnosis_save_and_send_to_form' => 'Speichern und ins Formular übernehmen',
    //IM-105
    'actual_problems' => 'aktuelle Probleme',
    'block_actual_problems' => 'aktuelle Probleme',
    //IM-104
    'report_recipient' => 'Empfänger des Berichtes',
    'block_report_recipient' => 'Empfänger des Berichtes',
    'block_report_recipient_recipient' => 'Empfänger',
    'block_report_recipient_nachrichtlich' => 'Nachrichtlich',
    'block_report_recipient_salutation' => 'Anrede',
    'block_report_recipient_as_recipient' => 'Als Empfänger',
    'block_report_recipient_salutation_f' => 'unsere gemeinsame Patientin, Frau ',
    'block_report_recipient_salutation_m' => 'unseren gemeinsamen Patienten, Herrn ',
    'block_report_recipient_salutation_text' => "Sehr geehrte Frau Kollegin, sehr geehrter Herr Kollege, wir berichten Ihnen über #verb# #name# geb. am #dob#, die sich am #date# in unserer Ambulanz vorstellte.",
    //IM-137
    'block_documentation' => 'Dokumentation',
    'block_documentation_legend' => 'Dokumentation',
    //ISPC-2599
    'pflegeba' => 'Basisassessment Pflege',
    'block_pflegeba' => 'Basisassessment Pflege',
    'block_pflegeba_legend' => 'Basisassessment Pflege',
    'block_pcpss'=>'PCPSS Index',
    'block_anforderer'=>'Anforderer',
    'block_bericht_fbe'=>'Fragestellung / Befund / Empfehlung',
    //ISPC2626
    'block_coordinationtime' => 'Koordinationszeit',
    'coordinationtime' => 'Koordinationszeit',

	//ISPC-2663 Carmen 02.09.2020
	'block_talkwithsingleselection'=>'Gespräch mit (Einzelauswahl)',
	'talkwithsingleselection'=>'Gespräch mit (Einzelauswahl)',
	'talkwithsingleselection_contact'=>'Kontakt mit',
	'talkwithsingleselection_further_information'=>'weitere Angaben',
	//--



];