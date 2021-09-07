<?php

	Doctrine_Manager::getInstance()->bindComponent('Munster', 'MDAT');

	abstract class BaseMunster extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('munster63');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('erst_verordnung', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('folge_verordnung', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('unfall_unfallfolgen', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('vom', 'datetime', 255, array('type' => 'varchar', 'length' => 255));
			$this->hasColumn('bis', 'datetime', 255, array('type' => 'varchar', 'length' => 255));
			$this->hasColumn('verordnungsrelevante_diagnose', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('ausgepragte_schmerzsymptomatik', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('ausgepragte_urogenitale_symptomatik', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('ausgepragte_respiratorische', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('ausgepragte_gastrointestinale_symptomatik', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('ausgepragte_ulzerierende_exulzerierende', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('ausgepragte_neurologische_psychiatrische', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('sonstiges_komplexes_symptomgeschehen', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('nahere_beschreibung', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('aktuelle_medikation', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('folgende_beratung', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('des_behandelnden_arztes', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('der_behandelnden_pflegefachkraft', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('des_patienten_der_angehorigen', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('koordination_der_palliativversorgung', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('mit_folgender_inhaltlicher', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('additiv_unterstutzende_teilversorgung', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('vollstandige_versorgung', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('nahere_angaben_zu_den_notwendigen', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('bra_options', 'string', 255, array('type' => 'string', 'length' => 255));
			
			$this->hasColumn('iscompleted', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('completed_date', 'datetime', 255, array('type' => 'varchar', 'length' => 255));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>