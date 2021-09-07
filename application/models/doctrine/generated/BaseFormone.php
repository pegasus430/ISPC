<?php

	Doctrine_Manager::getInstance()->bindComponent('Formone', 'MDAT');

	abstract class BaseFormone extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('sapv_formone');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('company_name', 'string', 255, array('type' => 'string', 'length' => 255));

			$this->hasColumn('insurance_no', 'bigint', 20, array('type' => 'bigint', 'length' => 20));
			$this->hasColumn('betriebsstatten_nr', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('institutskennzeichen', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('gender', 'integer', 1, array('type' => 'integer', 'length' => 1));

			$this->hasColumn('grundkrankheit', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('folgende_mabnahme_wurden_durch', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
			$this->hasColumn('verordnet', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('erstkontakt', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('wohnsituation', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('wohnsituation_other', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('pflegestufe', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('wunsch_des_pat_zu_beginn', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('wunsch_identisch_mit_sapv_behandlungsziel', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('av_beratung', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('av_koordination', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('av_teilversorgung', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('av_vollversorgung', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('betreuungsrelevante_nebendiagnosen', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('betreuungsrelevante_nebendiagnosen_textone', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('betreuungsrelevante_nebendiagnosen_texttwo', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('betreuungsrelevante_nebendiagnosen_textthree', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('komplexes_symptomgeschehen', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('komplexes_symptomgeschehen_other', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('weiteres_komplexes_geschehen', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('am_patient', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('fur_angehorige', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('systemische_tatigkeiten', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('betreuungsnetz', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('pat_wunsch_erfullt', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('beendigung_der_sapv_wegen', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('beendigung_der_sapv_wegen_other', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('pflegestufe_b_abschluss', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('zusatzliche_angaben_bei_verstorbene', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('ggf_weitere_angaben', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('sterbeort_n_wunsch', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('behandlungsdauer_in_tagen', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('besuche_pc_team_gesamt', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('notarzteinsatze', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('kh_einweisungen', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('anfahrtsweg_in_km', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('valid_from', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('valid_till', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>