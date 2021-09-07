<?php

	abstract class BaseStammblatt extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('stammblatt');
			$this->hasColumn('id', 'integer', 8, array('type' => 'integer', 'length' => 8, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('geschlecht', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('familienstand', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('staatszugehorigkeit', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('staatszugehorigkeit_text', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('religionszugehorigkeit', 'string', 255, array('type' => 'string', 'length' => 255));

			$this->hasColumn('relevante_diagnose', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('relevante_nebendiagnosen', 'text', NULL, array('type' => 'text', 'length' => NULL));

			$this->hasColumn('diagnosegruppe', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('primartumor', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('primartumor_text', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('metastasen', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('metastasen_text', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('nicht_tumor_erkrankungen', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('sapv_relevante_symptome', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('sapv_verordnung_durch', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('als', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('vom', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('bis', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('schmerzen', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('neuropat_schmerzen', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('viszerale_schmerzen', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('atemnot', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('reizhusten', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('verschleimung', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('aszites', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('ubelkeit_erbrechen', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('bluterbrechen', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('durchfall', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('obstipation', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('soor', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('schluckstorungen', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('angst', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('depression', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('unruhe', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('desorientierung', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('krampfanfalle', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('lahmungen', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('gangunsicherheit', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('schwindel', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('sensibilitatsstogg', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('decubitus', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('exulcerationen', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('lymph_odeme', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('harnverhalt', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('lebensqualitat', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('sprachstorung', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('organisationsprob', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('finanz_probleme', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('fatique', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('juckreiz', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('kachexie', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('wohnsituation', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('pflegeversicherung', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('aktuelle_pflegerische_situation', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('hauptprobleme', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('patientenwunsch', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('wunschort_des_sterbens', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('sapv_ziel', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('sapv_ziel_text', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('vigilanz', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('ernahrung_one', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('orientierung', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('ernahrung_two', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('ausscheidung', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('mobilitat', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('kunstliche_ausgange', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('apparative_palliativmedizinische', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('apparative_palliativmedizinische_text', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('patientenverfugung', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('patientenverfugung_vom', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('gesetzliche_vertretung', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('gesetzliche_vertretung_text', 'string', 255, array('type' => 'string', 'length' => 255));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>