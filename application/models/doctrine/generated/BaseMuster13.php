<?php
	Doctrine_Manager::getInstance()->bindComponent('Muster13', 'MDAT');
	
	abstract class BaseMuster13 extends Doctrine_Record {
	
		function setTableDefinition()
		{
			$this->setTableName('muster13');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('client', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('client_ik_number', 'integer', 9, array('type' => 'integer', 'length' => 9));
			$this->hasColumn('insurance_name', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('patient_name', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('street', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('zipcode', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('city', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('birthdate', 'date',  NULL, array('type' => 'date', 'length' =>  NULL));
			$this->hasColumn('ins_kassenno', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('ins_insuranceno', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('ins_status', 'integer', 1, array('type' => 'integer', 'length' => 1));			
			$this->hasColumn('datum', 'date',  NULL, array('type' => 'date', 'length' =>  NULL));
			$this->hasColumn('bsnr', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('lanr', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('gesamt_zuzahlung', 'decimal', 5, array('scale' => 2));
			$this->hasColumn('gesamt_brutto', 'decimal', 6, array('scale' => 2));
			$this->hasColumn('heilmittel_pos_1', 'integer', 5, array('type' => 'integer', 'length' => 5));
			$this->hasColumn('faktor_1', 'integer', 2, array('type' => 'integer', 'length' => 2));
			$this->hasColumn('heilmittel_pos_2', 'integer', 5, array('type' => 'integer', 'length' => 5));
			$this->hasColumn('faktor_2', 'integer', 2, array('type' => 'integer', 'length' => 2));
			$this->hasColumn('wegegeld', 'integer', 5, array('type' => 'integer', 'length' => 5));
			$this->hasColumn('faktor_3', 'integer', 2, array('type' => 'integer', 'length' => 2));
			$this->hasColumn('km', 'integer', 3, array('type' => 'integer', 'length' => 3));
			$this->hasColumn('hausbesuch_1', 'integer', 5, array('type' => 'integer', 'length' => 5));
			$this->hasColumn('faktor_4', 'integer', 2, array('type' => 'integer', 'length' => 2));
			$this->hasColumn('hausbesuch_2', 'integer', 5, array('type' => 'integer', 'length' => 5));
			$this->hasColumn('faktor_5', 'integer', 2, array('type' => 'integer', 'length' => 2));
			$this->hasColumn('rechnungsnummer', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('belegnummer', 'string', 100, array('type' => 'string', 'length' => 100));
			$this->hasColumn('verordnungs_menge_1', 'integer', 30, array('type' => 'string', 'length' => 30));
			$this->hasColumn('heilmittel_1', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('anzahl_woche_1', 'integer', 20, array('type' => 'integer', 'length' => 20));
			$this->hasColumn('verordnungs_menge_2', 'integer', 30, array('type' => 'string', 'length' => 30));
			$this->hasColumn('heilmittel_2', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('anzahl_woche_2', 'integer', 20, array('type' => 'integer', 'length' => 20));
			//ISPC-2530, elena, 14.10.2020
			$this->hasColumn('verordnungs_menge_3', 'integer', 30, array('type' => 'string', 'length' => 30));
			$this->hasColumn('heilmittel_3', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('anzahl_woche_3', 'integer', 20, array('type' => 'integer', 'length' => 20));

            $this->hasColumn('verordnungs_menge_4', 'integer', 30, array('type' => 'string', 'length' => 30));
			$this->hasColumn('heilmittel_4', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('anzahl_woche_4', 'integer', 20, array('type' => 'integer', 'length' => 20));


			$this->hasColumn('indikation_key', 'string', 10, array('type' => 'string', 'length' => 10));
			$this->hasColumn('indikation_name', 'string', 255, array('type' => 'string', 'length' => 255));
			
			$this->hasColumn('icd_code1', 'string', 10, array('type' => 'string', 'length' => 10));
			$this->hasColumn('icd_diagnosis1', 'string', 255, array('type' => 'string', 'length' => 255));
            //ISPC-2530, elena, 14.10.2020
            $this->hasColumn('verordnung_gruppe', 'integer', 2, array('type' => 'integer', 'length' => 2));
            $this->hasColumn('diagnosis_freetext', 'string', 255, array('type' => 'string', 'length' => 255));
            $this->hasColumn('diaggroup', 'string', 255, array('type' => 'string', 'length' => 255));
            //TODO-3735 Cristi.C
			$this->hasColumn('mainsymptomatic_letter', 'string', 255, array('type' => 'string', 'length' => 255));						
			$this->hasColumn('therapie_frequenz', 'string', 2, array('type' => 'string', 'length' => 2));
			//
			$this->hasColumn('mainsymptomatic_freetext', 'string', 255, array('type' => 'string', 'length' => 255));

            $this->hasColumn('dringlicher_behandlungsbedarf', 'integer', 2, array('type' => 'integer', 'length' => 2));

            $this->hasColumn('therapieziele', 'string', 255, array('type' => 'string', 'length' => 255));

            $this->hasColumn('formvalid', 'string', 255, array('type' => 'string', 'length' => 255, 'default' => ''));
			
			$this->hasColumn('icd_code2', 'string', 10, array('type' => 'string', 'length' => 10));
			$this->hasColumn('icd_diagnosis2', 'string', 255, array('type' => 'string', 'length' => 255));
			
			$this->hasColumn('gegebenenfalls_spezifizierung', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('medizinische_begrundung_verordnungen', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('verordnung_radio', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('behandlungsbeginn_date', 'datetime',  NULL, array('type' => 'datetime', 'length' =>  NULL));
			$this->hasColumn('hausbesuch_radio', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('therapiebericht_radio', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('gebuhr_radio', 'string', 10, array('type' => 'string', 'length' => 10));
			$this->hasColumn('unfall_radio', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('stampuser', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('stampid', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('isduplicated', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}
		
		function setUp()
		{
			$this->actAs(new Timestamp());
		}
	}