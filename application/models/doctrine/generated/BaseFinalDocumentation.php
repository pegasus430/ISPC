<?php

	abstract class BaseFinalDocumentation extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('final_documentation');
			$this->hasColumn('id', 'integer', 11, array('type' => 'integer', 'length' => 10, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));

			// block Einsatzart
			$this->hasColumn('im_hausliche_begleitung', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('hospizappartement', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('einsatz_ausschlieblich', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('ausschlieblich_klinik', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('ausschlieblich_telefonische', 'integer', 1, array('type' => 'integer', 'length' => 1));

			//Quantitative Aufwandsverteilung
			$this->hasColumn('quantitative_patient', 'decimal', 10, array('scale' => 2));
			$this->hasColumn('angehorige', 'decimal', 10, array('scale' => 2));
			$this->hasColumn('systemische_tatigkeiten', 'decimal', 10, array('scale' => 2));

			// block Erschwerte Bedingungen: für die häusliche Betreuung
			$this->hasColumn('symptome_bzw', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('patient_lasst', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('angehorige_lassen', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('belastbarkeit_der', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('angst_vor_sozialen', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('zusammenarbeit_mit_niedergelassenen', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('zusammenarbeit_mit', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('medizin', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('wohnsituation', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('finanzielle_notlage', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('kulturelle_unterschiede', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('sprachbarrieren', 'integer', 1, array('type' => 'integer', 'length' => 1));

			//Abschlussgrund right
			$this->hasColumn('abschlussgrund', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('beendigung_der_begleitung', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('beendigung_der_begleitung_chk', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('stabilisierung_des_gesundheitszustandes', 'integer', 1, array('type' => 'integer', 'length' => 1));

			//Todesdatum
			$this->hasColumn('todesdatum', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('death_date', 'string', 255, array('type' => 'string', 'length' => 255));
			
			//Ort des Sterbens
			$this->hasColumn('Wunsch', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('tatsachlich', 'integer', 1, array('type' => 'integer', 'length' => 1));

			//Pflegestufe        Lore 13.01.2020 ISPC-2500
			//$this->hasColumn('pflegestufe_beginn', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('pflegestufe_beginn', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('beantragt', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('beantragt_am_txt', 'string', 255, array('type' => 'string', 'length' => 255));
			//$this->hasColumn('abschluss', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('abschluss', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('abschluss_seit', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('abschluss_seit_txt', 'string', 255, array('type' => 'string', 'length' => 255));

			//Symptomentwicklung rückblickend
			$this->hasColumn('beginn_ausgepragte_schmerzsymptomatik', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('abschluss_ausgepragte_schmerzsymptomatik', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('beginn_kardiale_symptomatik', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('abschluss_kardiale_symptomatik', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('beginn_gastrointestinale_symptomatik', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('abschluss_gastrointestinale_symptomatik', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('beginn_neurologische', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('abschluss_neurologische', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('beginn_ulzerierende', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('abschluss_ulzerierende', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('beginn_urogenitale_symptomatik', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('abschluss_urogenitale_symptomatik', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('beginn_soziale_situation', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('abschluss_soziale_situation', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('beginn_sonstiges', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('abschluss_sonstiges', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('beginn_ethische_konflikte', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('abschluss_ethische_konflikte', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('beginn_rechtliche_problematik', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('abschluss_rechtliche_problematik', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('beginn_unterstutzung_bezugssystem', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('abschluss_unterstutzung_bezugssystem', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('beginn_existentielle_krisen', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('abschluss_existentielle_krisen', 'integer', 1, array('type' => 'integer', 'length' => 1));

			//Betreuungsnetz / Kooperationspartner während der Begleitung
			$this->hasColumn('pcf', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('palliativmediziner', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('hausarzt', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('facharzt', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('amb_hospizdienst', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('pflegedienst', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('sozialstation', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('sozialarbeit', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('stationares_hospiz', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('krankenhaus', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('palliativstation', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('stationare_pflege', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('physiotherapie', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('psychologe', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('apotheke_sanitatshaus', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('weitere_berufe', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('angehorige_grundpflege', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('angehorige_behandlungspflege', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('seelsorge', 'integer', 1, array('type' => 'integer', 'length' => 1));

			// Begleitungsprobleme
			$this->hasColumn('begleitungs_arzt', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('begleitungs_pflegedienst', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('begleitungs_krankenkasse', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('begleitungs_pflegekasse', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('begleitungs_mdk', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('begleitungs_homecare', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('begleitungs_apotheke', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('begleitungs_klinikum', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('begleitungs_altenheim', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('begleitungs_hospiz', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('begleitungs_seelsorge', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('begleitungs_seelsorge1', 'integer', 1, array('type' => 'integer', 'length' => 1));


			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('new_instance', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>