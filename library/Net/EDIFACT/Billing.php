<?php

// Maria:: Migration ISPC to CISPC 08.08.2020
class Net_EDIFACT_Billing extends Net_EDIFACT_Message
{
    public $logical_filename="";
    public $auftragssatz=[];


    /**
     * create the billing for demStepCare
     */
    public function create_billing_demstepcare(
        $sender_ik,
        $insurance_ik,
        $datenannahmestelle_ik,
        $doc_llanr,
        $doc_bsnr,
        $vertragskennzeichen,
        $ins_status,
        $ins_besondere_gruppe,
        $ins_dmp,
        $ins_vers_nummer,
        $ins_lastname,
        $ins_firstname,
        $ins_birth,
        $ins_gender,
        $diag_icd,
        $diag_date,
        $abr,
        $sum_all,
        $abrechnungszeitraum_first_day,
        $abrechnungszeitraum_last_day,
        $rechnungs_nummer,
        $rechnungs_datum,
        $continuing_file_number_ik,
        $continuing_file_number_trust,
        $is_testdata=0
    ){
        /*
         * Als Nachrichtentyp ist DIR140 zu wählen.
         * Im Auftragssatz ist an den Stellen 28-32 (VERFAHREN_KENNUNG_SPEZIFIKATION) IR140 einzutragen.
         * DIR140 - Einzelfallrechnung Integrierte Versorgung
         *
         * (UNA)
         * (UNB)
         * UNH IVK
         * IBH (? or IBL?)
         * INF Fallinformation + BSNR/LANR des Überweisers
         * INV
         * DIA
         * OPS
         * ABR
         * MND
         * FKI
         * RGI
         * UNT
         * (UNZ)
         *
         */

        $abrechnungszeitraum_first_day=new DateTime($abrechnungszeitraum_first_day);
        $abrechnungszeitraum_first_day=$abrechnungszeitraum_first_day->format('Ymd');

        $abrechnungszeitraum_last_day=new DateTime($abrechnungszeitraum_last_day);
        $abrechnungszeitraum_last_day=$abrechnungszeitraum_last_day->format('Ymd');

        $ins_birth=new DateTime($ins_birth);
        $ins_birth=$ins_birth->format('Ymd');

        $diag_date=new DateTime($diag_date);
        $diag_date=$diag_date->format('Ymd');

        $now=new DateTime();

        $ins_converted_gender="3";
        if(strtolower($ins_gender)==="m"){
            $ins_converted_gender="2";
        }
        if(strtolower($ins_gender)==="f"){
            $ins_converted_gender="1";
        }
        if($ins_gender=="1"){
            $ins_converted_gender="2";
        }
        if($ins_gender=="2"){
            $ins_converted_gender="1";
        }
        $is_correction=0;

        $this->create_logical_filename($abrechnungszeitraum_first_day, $continuing_file_number_ik, 'DRI', $is_correction, $is_testdata);
        $this->create_physical_filename($abrechnungszeitraum_first_day, $continuing_file_number_trust, 'DRI', $is_correction, $is_testdata);

        $continuing_file_number_ik=intval($continuing_file_number_ik)%100000;
        $continuing_file_number_ik=strval($continuing_file_number_ik);
        $continuing_file_number_ik=str_pad($continuing_file_number_ik,5, '0',STR_PAD_LEFT );

        //assume Rechnungssteller is sender:
        $rechnungs_steller_ik=$sender_ik;

        $unb=$this->create_unb();
        $unb->set_data(3,$rechnungs_steller_ik);
        $unb->set_data(4,$insurance_ik);
        $unb->set_data(5,[$now->format('Ymd'),$now->format('Hi')]);
        $unb->set_data(6,$continuing_file_number_ik);
        $unb->set_data(7,$this->logical_filename);
        if($is_testdata) {
            $unb->set_data(8, 1);
        }
        $this->add_segment($unb);

        $unh=$this->create_unh();
        $unh->set_data(1,"UNH");
        $unh->set_data(2,"9900000001"); //99=regional übergreifend? Kennzeichen für Vertragsbereich Arzt
        $unh->set_data(3,['DIR140','6','0','DR']);
        $unh->set_data(5,"9900000001");
        $this->add_segment($unh);

        $ivk=$this->create_ivk();
        $ivk->set_data(4,$sender_ik);
        $ivk->set_data(5,$insurance_ik);
        $ivk->set_data(7,$unb->get_data(4));
        $this->add_segment($ivk);

        $ibh=$this->create_ibh();
        $ibh->set_data(2,$doc_llanr);
        $ibh->set_data(3,$doc_bsnr);
        $this->add_segment($ibh);

        $inf=$this->create_inf();
        $inf->set_data(2,   $vertragskennzeichen); //das von der Krankenkasse vergebene und mitgeteilte Vertragskennzeichen
        $inf->set_data(4,   [0,0]);
        $this->add_segment($inf);

        $inv=$this->create_inv();
        $inv->set_data(2,   [$ins_status,$ins_besondere_gruppe, $ins_dmp]);
        $inv->set_data(3,   [$ins_vers_nummer,$insurance_ik]);
        $inv->set_data(4,   [$ins_lastname,$ins_firstname, $ins_birth]);
        $inv->set_data(5,   $ins_converted_gender);
        $this->add_segment($inv);

        $dia=$this->create_dia();
        //only demenz-diagnosis?
        $dia->set_data(2,   [$diag_icd,'G','', $diag_date]);
        $this->add_segment($dia);

        foreach ($abr as $abr_entry) {
            $abr_day=new DateTime($abr_entry['day']);
            $abr_day=$abr_day->format('Ymd');

            $abr = $this->create_abr();
            $abr->set_data(3, $abr_entry['gebuehrennummer']);
            $abr->set_data(6, $abr_entry['ammount']);
            $abr->set_data(12, $abr_day);
            $this->add_segment($abr);
        }

        $fki=$this->create_fki();
        $fki->set_data(8,   $sum_all);
        $this->add_segment($fki);

        $rgi=$this->create_rgi();
        $rgi->set_data(2,   [$abrechnungszeitraum_first_day, $abrechnungszeitraum_last_day]);
        $rgi->set_data(3,   "1"); //1=Einzelfallrechnung
        $rgi->set_data(4,   $rechnungs_steller_ik);
        $rgi->set_data(5,   $rechnungs_nummer);
        $rgi->set_data(6,   $rechnungs_datum);
        $this->add_segment($rgi);



        $unt=$this->create_unt();
        $inner_unh_segments=count($this->segments)-0; //al segments inside unh..unt including unh and unt
            //update unh-info
        $unh->set_data(4,   $inner_unh_segments);
        $unt->set_data(2,   $inner_unh_segments);
        $unt->set_data(3,   $unh->get_data(2));
        $this->add_segment($unt);


        $unz=$this->create_unz();
        $unz->set_data(2,   1); //only 1 unh-segment
        $unz->set_data(3,   $unb->get_data(6));
        $this->add_segment($unz);



        //START Aufragssatz
        $auf=[];

        $auf[]='500000'; //IDENTIFIKATOR
        $auf[]='01'; //VERSION
        $auf[]='00000348'; //Länge Auftrag, Für Version 01=‘00000348’
        $auf[]='000'; //Sequenz: 000: Nachricht komplett

        $p="E";//Echtdaten
        if($is_testdata){$p="T";}
        $auf[]=$p.'DRI0';//VERFAHREN_KENNUNG

        $continuing_file_number_trust=intval($continuing_file_number_trust)%1000;
        $continuing_file_number_trust=strval($continuing_file_number_trust);
        $continuing_file_number_trust=str_pad($continuing_file_number_trust,3, '0',STR_PAD_LEFT );
        $auf[]=$continuing_file_number_trust; //3 digits

        $auf[]='IR140'; // Im Auftragssatz ist an den Stellen 28-32 (VERFAHREN_KENNUNG_SPEZIFIKATION) IR140 einzutragen.

        $auf['sender1']=$sender_ik."      ";
        $auf['sender2']=$sender_ik."      ";

        $auf['insik']=$insurance_ik."      ";
        //$auf['trustik']=$datenannahmestelle_ik."      ";
        $auf['trustik']=$insurance_ik."      ";//ISPC-2598-Hotfix Nico 15.03.2021

        $auf[]="000000";//FEHLER Nummer
        $auf[]="000000";//FEHLER MASSNAHME

        $auf[]=$this->logical_filename;

        $date=date('YmdHis');
        $auf[]=$date;
        $auf[]="00000000000000";//DATUM gesendet
        $auf[]="00000000000000";//DATUM Empfangen
        $auf[]="00000000000000";//DATUM Empfangen Ende

        $auf[]="000000";
        $auf[]="0";//Korrektur

        $auf['size1']="000000000000";//Nutzdaten Dateigr.
        $auf['size2']="000000000000";//enced and signed Dateigr.

        $auf[]="I1";
        $auf[]="00";//Komprimierung
        $auf[]="03";
        $auf[]="03";
        $auf[]=str_pad("",138, " ");
        $aaa=strlen(implode("",$auf));
        $this->auftragssatz=$auf;
    }




    public function create_unz()
    {
        $unz_data = ['UNZ', '',''];
        $unz_meta=[
            [1,	3, 3, 'AN', 'M', 'Segmentkennung', ['fix', 'UNZ'] ],
            [2,	1, 6, 'N',  'M', 'Anzahl der UNH Segmente' ],
            [3,	1, 14,'AN', 'M', 'wie UNB.6' ],
        ];

        $unz=new Net_EDIFACT_Segment('UNZ',$unz_data, $unz_meta, []);
        return ($unz);
    }

    /**
     * Übertragungskopfsegment
     */
    public function create_unb()
    {
        $unb_data = ['UNB', ['UNOC', '3'], '','', ['',''], '', '', ''];
        $unb_meta=[
            [1,	3, 3, 'AN',  'M', 'Segmentkennung', ['fix', 'UNB'] ],
            [2,	1, 1, 'CPM', 'M', '1 Syntax-Bezeichner',
                [1, 4, 4, 'AN', 'M', '- Syntax-Kennung'         ,['fix', 'UNOC']],
                [2, 1, 1, 'N',  'M', 'Syntax-Versionsnummer'     ,['fix', '3']]
            ],
            [3,	9, 9, 'AN',  'M', 'IK der Datenversendenden Stelle' ],
            [4,	9, 9, 'AN',  'M', 'Datenannahmestelle mit Entschlüsselungsbefugnis' ],
            [5,	1, 1, 'CPM', 'M', 'Datum/Uhrzeit',
                [1, 8, 8, 'N', 'M', 'Datum'],
                [2, 4, 4, 'N', 'M', 'Uhrzeit']
            ],
            [6,	5, 14, 'AN',  'M', 'Dateinummer Genutzt werden die ersten 5 Stellen;lückenlos fortlaufende Nummer der Lieferungen zwischen Absender und Empfänger mit führenden Nullen beginnend mit „00001“ ' ],
            [7,	11, 11,'AN',  'M', 'Dateiname' ],
            [8,	1,   1, 'N',  'C', 'Testübertragung' ],
            ];

        $unb=new Net_EDIFACT_Segment('UNB',$unb_data, $unb_meta, []);
        return ($unb);
    }

    /**
     * 0 Nachrichtenkopfsegment
     */
    public function create_unh(){
        $unh_data=['UNH', '', ['', '7', '0', 'DR']];
        $unh_meta=[
            [1,	3, 3, 'AN',  'M', 'Segmentkennung', ['fix', 'UNH'] ],
            [2,	10, 10,'N',  'M', 'Nachrichtenreferenz-Nr.' ],
            //Stelle 1 und 2: Information über den Vertrags-Bereich (Schlüssel 6.1.8 bzw. 6.1.17) Stelle 3 bis 4:Einzutragen ist 00 Stellen 5 bis 10: Einzutragen ist die fortlaufende Nummer der UNH-Segmente zwischen UNB und UNZ mit führenden Nullen,z. B. 000001 für 1. UNH.
            [3,	1, 1, 'CMP', 'M', 'Nachrichtenkennung',
                [
                    [1, 1, 6, 'AN', 'M', 'Nachrichtentypkennung', ['table', '6.1.1']],
                    [2, 1, 3,  'N', 'M', 'Hauptversion' ],
                    [3, 1, 3, 'AN', 'M', 'Release' ],
                    [4, 2, 2, 'AN', 'M', 'Direktabrechner'         ,['fix', 'DR']]
                ]
            ],
            [4,1,10,'N','M', 'Anzahl der Segmente im UNHPaket inklusive derUNH- und UNTSegmente'],
            [5,1,14,'AN','M', 'NachrichtenreferenzNr. wie UNH (2)'],
        ];
        $unh_tabs['6.1.1']=
            [
                'name'=>'Kennungen der Nachrichtentypen',
                'items'=>
                    [
                        'DIR73B'=>'Einzelfallrechnung Hausarztzentrierte Versorgung',
                        'DIR73C'=>'Einzelfallrechnung Besondere ambulante ärztliche Versorgung',
                        'DIR140'=>'Einzelfallrechnung Integrierte Versorgung',

                        'DR73BL'=>'Einzelfallrechnung Leistungserbringerbezogene Abrechnung Hausarztzentrierte Versorgung',
                        'DR73CL'=>'Einzelfallrechnung Leistungserbringerbezogene Abrechnung Besondere ambulante ärztliche Versorgung',
                        'DR140L'=>'Einzelfallrechnung Leistungserbringerbezogene Abrechnung Integrierte Versorgung',

                        'RGS73B'=>'Sammelrechnung Hausarztzentrierte Versorgung',
                        'RGS73C'=>'Sammelrechnung Besondere ambulante ärztliche Versorgung',
                        'RGS140'=>'Sammelrechnung Integrierte Versorgung',

                        'RG73BL'=>'Sammelrechnung Leistungserbringerbezogene Abrechnung Hausarztzentrierte Versorgung',
                        'RG73CL'=>'Sammelrechnung Leistungserbringerbezogene Abrechnung Besondere ambulante ärztliche Versorgung',
                        'RG140L'=>'Sammelrechnung Leistungserbringerbezogene Abrechnung Integrierte Versorgung',

                        //some entries left out

                    ]
            ];

        $unh_more=['Tables'=>$unh_tabs];
        $unh=new Net_EDIFACT_Segment('UNH',$unh_data, $unh_meta, $unh_more);
        return ($unh);
    }


    /**
     * 2 Information Verarbeitung
     */
    public function create_ivk(){
        $ivk_data=['IVK','10','01','','','',''];
        $ivk_meta=[
            [1,	3, 3, 'AN',  'M', 'Segmentkennung', ['fix', 'IVK'] ],
            [2,	2, 2, 'AN',  'M', 'Verarbeitungskennzeichen', ['table', '6.1.9'] ],
            [3,	2, 2, 'AN',  'M', 'Laufende Nummer des Geschäftsvorfalls Standardwert \'01\'' ],
            [4,	9, 9, 'AN',  'M', 'Identifikation des Senders IK der Daten versendenden Stelle' ],
            [5,	9, 9, 'AN',  'M', 'IK der Krankenkasse' ],
            [6,	1,14, 'N',   'C', 'Sammelrechnungs-ID' ],
            [7,	9, 9, 'AN',  'M', 'Identifikation der Datenannahmestelle (aus UNB 0010)' ],
        ];

        $ivk_tabs['6.1.9']=
            [
                'name'=>'Verarbeitungskennzeichen',
                'items'=>
                    [
                        '10'=>'Normalfall',
                        '30'=>'Storno',
                        '40'=>'Korrektur',
                    ]
            ];

        $ivk_more=['Tables'=>$ivk_tabs];
        $ivk=new Net_EDIFACT_Segment('IVK',$ivk_data, $ivk_meta, $ivk_more);
        return ($ivk);
    }

    /**
     * 2 Information behandelnder Arzt
     */
    public function create_ibh(){
        $ibh_data=['IBH'];
        $ibh_meta=[
            [1,	3, 3, 'AN',  'M', 'Segmentkennung', ['fix', 'IBH'] ],
            [2,	9, 9, 'AN',  'M', 'Lebenslange Arztnummer' ],
            [3,	9, 9, 'AN',  'M', 'Betriebsstättennummer' ],
        ];
        $ibh=new Net_EDIFACT_Segment('IBH',$ibh_data, $ibh_meta, []);
        return($ibh);
    }

    /*
     * 3 Information Leistungserbringer
     * (only if no behand. Arzt)
     */

    /*
     * 4 Information Zahnarzt
     * (not needed)
     */

    /**
    * 5 Fallinformation
    */
    public function create_inf(){
        $inf_data=['INF','',[],['0','0'],''];
        $inf_meta=[
            [1,	3, 3, 'AN',  'M', 'Segmentkennung', ['fix', 'INF'] ],
            [2,	1, 25,'AN',  'M', 'das von der Krankenkasse vergebene und mitgeteilte Vertragskennzeichen' ],
            //Stelle 1 und 2: Information über den Vertrags-Bereich (Schlüssel 6.1.8 bzw. 6.1.17) Stelle 3 bis 4:Einzutragen ist 00 Stellen 5 bis 10: Einzutragen ist die fortlaufende Nummer der UNH-Segmente zwischen UNB und UNZ mit führenden Nullen,z. B. 000001 für 1. UNH.
            [3,	1, 1, 'CMP', 'C', 'Überweiser',
                [
                    [1, 9, 9, 'AN', 'C', 'Betriebsstättennummer Überweiser'],
                    [2, 9, 9, 'AN', 'C', 'Lebenslange Arztnummer Überweiser' ],
                    [3, 9, 9, 'AN', 'C', 'Institutionskennzeichen des Leistungserbringers Überweiser' ]
                ]
            ],
            [4,	1, 1, 'CMP', 'M', 'Zusatzinformationen',
                [
                    [1, 1, 1, 'N',  'M', 'Unfallkennzeichen/BVG'],
                    [2, 1, 1, 'AN', 'M', 'Art der Inanpruchnahme' ],
                ]
            ],
            [5,	1, 25,'AN',  'C', 'Die Vertragsnummer ist ausschließlich bei Verträgen nach §§ 73b und 140a SGB V existent und in den Abrechnungsdaten anzugeben.' ],
        ];
        $inf=new Net_EDIFACT_Segment('INF',$inf_data, $inf_meta, []);
        return ($inf);
    }

    /**
    * 6 INV Information Versicherter
    */
    public function create_inv(){
        $inv_data=['INV'];
        $inv_meta=[
            [1,	3, 3, 'AN',  'M', 'Segmentkennung', ['fix', 'INV'] ],
            [2,	1, 1, 'CMP', 'C', 'Versichertenstatus eGK',
                [
                    [1, 1, 1, 'N',  'M', 'Versichertenart', ['table', '6.1.18']],
                    [2, 1, 2, 'AN', 'M', 'Besondere Personengruppe', ['table', '6.1.19'] ],
                    [3, 1, 2, 'AN', 'M', 'DMP-Kennzeichen', ['table', '6.1.20'] ]
                ]
            ],
            [3,	1, 1, 'CMP', 'C', 'Versichertenbezug Nummer',
                [
                    [1,10, 10,'AN', 'M', 'Versichertennummer'],
                    [2, 9, 9, 'AN', 'M', 'Institutionskennzeichen: IK der Krankenkasse von der KV-Karte' ],
                ]
            ],
            [4,	1, 1, 'CMP', 'C', 'Versichertenbezug Name',
                [
                    [1, 1,45, 'AN', 'M', 'Nachname'],
                    [2, 1,45, 'AN', 'M', 'Vorname'],
                    [3, 8, 8, 'N',  'M', 'Datum im Format JJJMMDD' ],
                ]
            ],
            [5,	1, 1,'N',  'M', 'Geschlecht des Versicherten', ['table', '6.1.11'] ],
            [6,	4, 4,'N',  'C', 'Gültigkeit der Krankenversichertenkarte, leer wenn nicht mehr gültig' ],
            [7,	4, 4,'N',  'C', 'Zuzahlungsstatus Versicherte', ['table', '6.1.12']  ],
            [8,	1, 7,'AN', 'C', 'Postleitzahl des Versicherten'  ],
            [9,	3, 3,'AN', 'C', 'Länderkennzeichen',['table', '6.1.16']  ],
            [10,1,15,'AN', 'C', 'Teilnehmer ID, Identifikationsmerkmal des Teilnehmers laut Vertrag, sofern vereinbart.'  ],


        ];

        $inv_tabs['6.1.11']=
            [
                'name'=>'Geschlecht',
                'items'=>
                    [
                        '1'=>'weiblich',
                        '2'=>'männlich',
                        '3'=>'unbekannt',
                        '4'=>'divers',
                    ]
            ];
        $inv_tabs['6.1.12']=
            [
                'name'=>'Zuzahlungsstatus',
                'items'=>
                    [
                        '1'=>'zuzahlungspflichtig',
                        '2'=>'zuzahlungsbefreit'
                    ]
            ];
        $inv_tabs['6.1.16']=
            [
                'name'=>'Statuskennzeichen',
                'items'=>
                    [
                        '1'=>'Mitglied (1. Stelle des Versichertenstatus von der eGK)',
                        '3'=>'Familienversicherter(1. Stelle des Versichertenstatus von der eGK)',
                        '5'=>'Rentner(1. Stelle des Versichertenstatus von der eGK)',
                        '9'=>'Zusammenfassung der Statuskennzeichen „1“, „3“ und „5“',
                        '0'=>'unbekannt',
                    ]
            ];
        $inv_tabs['6.1.18']=
            [
                'name'=>'Versichertenart',
                'items'=>
                    [
                        '1'=>'Mitglied',
                        '3'=>'Familienversicherter',
                        '5'=>'Rentner',

                    ]
            ];
        $inv_tabs['6.1.19']=
            [
                'name'=>'Besondere Personengruppe',
                'items'=>
                    [
                        '00'=>'keine Besondere Personengruppe',
                        '04'=>'Sozialhilfeempfänger',
                        '06'=>'BVG (Gesetz über die Versorgung der Opfer des Krie-ges)',
                        '07'=>'SVA - Kennzeichnung für zwischenstaatliches Kran-kenversicherungsrecht (Personen aus dem Ausland mit Wohnsitz im Inland, Abrechnung nach Aufwand)',
                        '08'=>'SVA - Kennzeichnung für zwischenstaatliches Kran-kenversicherungsrecht (Personen aus dem Ausland mit Wohnsitz im Inland, Abrechnung pauschal)',
                        '09'=>'Leistungsempfänger nach §§ 4 und 6 AsylbLG',

                    ]
            ];
        $inv_tabs['6.1.20']=
            [
                'name'=>'DMP-Kennzeichen',
                'items'=>
                    [
                        '00'=>'kein DMP-Kennzeichen',
                        '01'=>'Diabetes mellitus Typ 2',
                        '02'=>'Brustkrebs',
                        '03'=>'Koronare Herzkrankheit',
                        '04'=>'Diabetes mellitus Typ 1',
                        '05'=>'Asthma bronchial',
                        '06'=>'COPD',
                        '07'=>'Chronische Herzinsuffizienz',
                        '08'=>'Depression',
                        '09'=>'Rückenschmerz',
                        '10'=>'Rheuma',
                        '11'=>'Osteoporose',
                    ]
            ];
        $inv_more=['Tables'=>$inv_tabs];
        $inv=new Net_EDIFACT_Segment('INV',$inv_data, $inv_meta, $inv_more);
        return ($inv);
    }

    /**
    * 7 DIA Diagnosedaten
    */
    public function create_dia(){
        $dia_data=['DIA'];

        $dia_meta=[
            [1,	3, 3, 'AN',  'M', 'Segmentkennung', ['fix', 'DIA'] ],
            [2,	1, 1, 'CMP', 'M', 'Diagnose',
                [
                    [1, 1, 12, 'AN', 'M', 'ICD-Schlüssel (Mit Punkt. Beispiel: J45.0 oder S73.0-)'],
                    [2, 1, 1,  'AN', 'M', 'Diagnosesicherheit'      ,['table', '6.1.13'] ],
                    [3, 1, 1,  'AN', 'C', 'Seitenlokalisation'      ,['table', '6.1.14'] ],
                    [4, 8, 8,  'N',  'C', 'Diagnosedatum JJJJMMTT']
                ]
            ],
        ];


        $tabs['6.1.13']=
            [
                'name'=>'Diagnosesicherheit',
                'items'=>
                    [
                        'A'=>'ausgeschlossene Diagnose',
                        'G'=>'gesicherte Diagnose',
                        'V'=>'Verdachtsdiagnose',
                        'Z'=>'symptomloser Zustand nach der betreffenden Diagnose'
                    ]
            ];

        $tabs['6.1.14']=
            [
                'name'=>'Seitenlokalisation',
                'items'=>
                    [
                        'R'=>'rechts',
                        'L'=>'links',
                        'B'=>'beidseitig'
                    ]
            ];

        $dia_more=['Tables'=>$tabs];

        $dia=new Net_EDIFACT_Segment('DIA',$dia_data, $dia_meta, $dia_more);
        return ($dia);
    }
    /**
     * 8 OPS Segment
     */
    public function create_ops(){
        $ops_data=['OPS', ];
        $ops_meta=[
            [1,	3, 3, 'AN',  'M', 'Segmentkennung', ['fix', 'OPS'] ],
            [2,	1, 1, 'CMP', 'C', 'Operationsschlüssel',
                [
                    [1, 1, 12,'AN', 'M', 'Operationsschlüssel codiert'],
                    [2, 9, 9, 'AN', 'C', 'Seitenlokalisation', ['table', '6.1.14']],
                    [3, 8, 8,  'N', 'C', 'OPS-Datum' ]
                ]
            ],
        ];
        $ops_tabs['6.1.14']=
            [
                'name'=>'Seitenlokalisation',
                'items'=>
                    [
                        'R'=>'rechts',
                        'L'=>'links',
                        'B'=>'beidseitig'
                    ]
            ];

        $ops_more=['Tables'=>$ops_tabs];
        $ops=new Net_EDIFACT_Segment('OPS',$ops_data, $ops_meta, $ops_more);
        return ($ops);
    }


    /**
     * 9 ABR Abrechnungsinformationen
     */
    public function create_abr(){
        $abr_data=['ABR', ];
        $abr_meta=[
            [1,	3, 3, 'AN',  'M', 'Segmentkennung', ['fix', 'ABR'] ],
            [2,	1, 1, 'CMP', 'C', 'Überweiser',
                [
                    [1, 9, 9, 'AN', 'C', 'BSNR Überweiser'],
                    [2, 9, 9, 'AN', 'C', 'LLANR Überweiser'],
                    [3, 8, 8, 'AN', 'C', 'IK Leistungserbringer (Überweiser)' ]
                ]
            ],
            [3,	1, 12, 'AN', 'M', 'Gebührennummer' ],
            [4,	1, 30, 'N',  'C', 'Gebührennummer-ID' ],
            [5,	1, 70, 'AN', 'C', 'Abrechnungsbegründung' ],
            [6,	1, 6,  'N',  'M', 'Anzahl  wie oft die Gebührennummer abgerechnet wurde' ],
            [7,	1, 12, 'ND2','C', 'Wert der Gebührennummer in EURO incl. Sachkosten und Dialyse-Sachkosten' ],
            [8,	1, 12, 'ND1','C', 'Punktzahl der Gebührennummer' ],
            [9,	1, 12, 'ND2','C', 'Dialyse-Sachkosten' ],
            [10,1, 12, 'ND2','C', 'Sachkosten' ],
            [11,1, 70, 'AN', 'C', 'Sachkostenbezeichnung' ],
            [12,8,  8, 'N',  'M', 'Datum der Leistungserbringung' ],
            [13,4,  4, 'N',  'C', 'Uhrzeit der Leitungserbringung' ],
            [14,8,  8, 'N',  'C', 'der letzte Tag der Leistungserbringung' ],
            [15,4,  4, 'AN', 'C', ' Information zur DRG zur Gebührennummer' ],
        ];
        $abr=new Net_EDIFACT_Segment('ABR',$abr_data, $abr_meta, []);
        return ($abr);
    }

    /**
     * 10 MND Minderung
     */
    public function create_mnd(){
        $mnd_data=['ABR', ];
        $mnd_meta=[
            [1,	3, 3,  'AN','M', 'Segmentkennung', ['fix', 'ABR'] ],
            [2,	1, 12, 'AN','M', 'Minderungsbetrag' ],
            [3,	2, 2,  'N', 'M', 'Minderungsart',['table', '6.1.5'] ],
            [4,	8, 8,  'N', 'M', 'Datum der Zuzahlung/Minderung ' ],
        ];
        $mnd_tabs['6.1.5']=
            [
                'name'=>'Minderungsart',
                'items'=>
                    [
                        '03'=>'Zuzahlung für Arznei- und Verbandmittel nach § 31 Abs. 3 und § 61 S. 1 SGB V',
                        '04'=>'Zuzahlung für Heilmittel nach § 32 Abs. 2 und § 61 S. 3 SGB V',
                        '05'=>'Zuzahlung für Hilfsmittel nach § 33 Abs. 2 und § 61 S. 1 SGB V',
                        '06'=>'Sonstige prozentuale Zuzahlungen',
                        '07'=>'Sonstige pauschale Zuzahlungen',
                        '08'=>'prozentuale Eigenbeteiligung',
                        '09'=>'pauschale Eigenbeteiligung',
                        '11'=>'Vertraglich vereinbarte Zuzahlungen',
                        '12'=>'Sicherheitseinbehalt',
                    ]
            ];

        $mnd_more=['Tables'=>$mnd_tabs];
        $mnd=new Net_EDIFACT_Segment('MND',$mnd_data, $mnd_meta, $mnd_more);
        return ($mnd);
    }

    /**
     * 11 FKI Fallkosteninformation
     */
    public function create_fki(){

        $fki_data=['FKI', ];
        $fki_meta=[
            [1,	3, 3,  'AN', 'M', 'Segmentkennung', ['fix', 'FKI'] ],
            [2,	1, 12, 'ND2','C', 'Gesamtbetrag der abgerechneten Gebührennummern ohne Abzug von Zuzahlungen (Bruttobetrag) . Das Feld ist zwingend zu füllen, wenn im Abrechnungsfall das Feld 9/9.7 mindestens einmal einen Wert enthält. Zwingend anzugeben bei Verträgen nach § 73b SGB V.' ],
            [3,	1, 12, 'ND2','C', 'Gesamtbetrag der abgerechneten Dialysesachkosten ohne Abzug von Zuzahlungen (Bruttobetrag) Zwingend bei 9.9' ],
            [4,	1, 12, 'ND2','C', 'Gesamtbetrag der abgerechneten Sachkosten ohne Abzug von Zuzahlungen (Bruttobetrag). Zwingend bei 9.10' ],
            [5,	1, 12, 'ND2','C', 'Gesamtbetrag aller geleisteten gesetzlichen Zuzahlungen ' ],
            [6,	1, 12, 'ND2','C', 'Gesamtbetrag aller geleisteten vertraglich vereinbarten Zuzahlungen ' ],
            [7,	1, 12, 'ND2','C', 'Gesamtbetrag aller Minderungsbeträge ohne Minuskennzeichen' ],
            [8,	1, 12, 'ND2','M', 'Gesamtbetrag der abgerechneten Gebührennummern nach Abzug von Zuzahlungen (Nettobetrag/Zahlbetrag)' ],
        ];
        $fki=new Net_EDIFACT_Segment('FKI',$fki_data, $fki_meta, []);
        return ($fki);
    }

    /**
     * 12 RGI Information Rechnung
     */
    public function create_rgi(){

        $rgi_data=['RGI',['',''],'', '','','','','000'];
        $rgi_meta=[
            [1,	3, 3,  'AN', 'M', 'Segmentkennung', ['fix', 'RGI'] ],
            [2,	1, 1, 'CMP', 'M', 'Abrechnungszeitraum',
                [
                    [1, 8, 8, 'N', 'M', 'Erster Tag des Abrechnungszeitraums'],
                    [2, 8, 8, 'N', 'M', 'Letzter Tag des Abrechnungszeitraums'],
                ]
            ],
            [3, 1, 1, 'N', 'M', 'Information zur Rechnung',['table', '6.1.15']],
            [4, 9, 9, 'AN','M', 'IK des Rechnungsstellers'],
            [5, 1, 20,'AN','M', 'Anzugeben ist die eindeutige Rechnungsnummer, die der Rechnungssteller vergibt'],
            [6, 8, 8, 'N', 'M', 'Rechnungsdatum'],
            [7, 9, 9, 'AN','C', 'IK des Zahlungsempfängers, sofern abweichend vom IK des Rechnungsstellers'],
            [8, 3, 3, 'N', 'M', 'Korrekturzähler. Die erste Abrechnung wird mit 000 gekennzeichnet'],
        ];
        $rgi_tabs['6.1.15']=
            [
                'name'=>'Information zur Rechnung ',
                'items'=>
                    [
                        '1'=>'Einzelabrechnung',
                        '2'=>'Sammelrechnung',
                        '3'=>'Nachtragsrechnung',
                        '4'=>'Gutschrift/Stornierung',
                        '5'=>'Zahlungserinnerung',
                        '6'=>'1. Mahnung',
                        '7'=>'2. Mahnung'
                    ]
            ];

        $rgi_more=['Tables'=>$rgi_tabs];
        $fki=new Net_EDIFACT_Segment('RGI',$rgi_data, $rgi_meta, $rgi_more);
        return ($fki);
    }

    /**
     * UNT Nachrichtenendsegemnt
     */
    public function create_unt(){

        $unt_data=['UNT', ];
        $unt_meta=[
            [1,	3, 3,  'AN', 'M','Segmentkennung', ['fix', 'UNT'] ],
            [2,	1, 10, 'N', 'M', 'Anzahl der Segmente im UNH Paket inklusive der UNH- und UNT Segment' ],
            [3,	1, 14, 'AN','M', 'wie UNH (0062)' ],

        ];
        $unt=new Net_EDIFACT_Segment('UNT',$unt_data, $unt_meta, []);
        return ($unt);
    }


    public function create_logical_filename($date,$contnumber, $type, $correction=0,$testdata=0){

        $d=new DateTime($date);
        $a1="E";
        if($testdata){
            $a1="T";
        }
        $a=array();
        $a[]=$a1;
        $a[]=$type; // DRI, DRC, DRB, DRS

        $c="0";

        if($correction!=0){
            $c="A";
        }

        $a[]=$c;

        $contnumber=intval($contnumber);
        $contnumber=$contnumber%100;
        $contnumber=strval($contnumber);
        if(strlen($contnumber)<2){
            $contnumber="0".$contnumber;
        }
        $a[]=$contnumber;//cont. number
        $a[]=$d->format('y');//YY
        $a[]=$d->format('m');//Q1..Q4 or 01-12 for months
        $b=implode("",$a);
        $this->logical_filename=$b;
    }

    public function create_physical_filename($date,$contnumber, $type, $correction=0,$testdata=0){

        $d=new DateTime($date);
        $a1="E";
        if($testdata){
            $a1="T";
        }
        $a=array();
        $a[]=$a1;
        $a[]=$type; // DRI, DRC, DRB, DRS

        $c="0";

        if($correction!=0){
            $c="A";
        }

        $a[]=$c;

        $contnumber=intval($contnumber);
        $contnumber=$contnumber%100;
        $contnumber=strval($contnumber);
        if(strlen($contnumber)<2){
            $contnumber="0".$contnumber;
        }
        $a[]=$contnumber;//cont. number
        $b=implode("",$a);
        $this->physical_filename=$b;
    }



}