<?php

	abstract class BaseStammdatenerweitert extends Pms_Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('stammdatenerweitert');
			$this->hasColumn('id', 'bigint', 20, array('type' => 'bigint', 'length' => 20, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('familienstand', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('vigilanz', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('ernahrung', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('orientierung', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('kunstliche', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('kunstlichemore', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('ausscheidung', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('ausgepragte', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('sprachlich', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('kognitiv', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('anderefree', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('horprobleme', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('stastszugehorigkeit', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('schmerzen', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('neuropat', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('viszerale', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('respiratorische', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('atemnot', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('reizhusten', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('verschleimung', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('gastrointestinale', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('aszites', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('ubelkeit', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('bluterbrechen', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('durchfall', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('obstipation', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('soor', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('schluckstorungen', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('neurologische', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('angst', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('depression', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('unruhe', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('desorientierung', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('krampfanfalle', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('lahmungen', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('gangunsicherheit', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('schwindel', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('sensibilitatsstogg', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('ulzerierende', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('decubitus', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('exulcerationen', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('lymph_odeme', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('urogenitale', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('harnverhalt', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('soziale', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('lebensqualitat', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('organisationsprob', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('finanzprobleme', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('sonstiges', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('fatique', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('juckreiz', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('kachexie', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('hilfsmittel', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('wunsch', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('wunschmore', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('sprachstorung', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('ethische', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('sozial_rechtliche', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('unterstutzungsbedarf', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('existentielle', 'integer', 1, array('type' => 'integer', 'length' => 1));
            //TODO-1890 
			$this->hasColumn('2ndstastszugehorigkeit', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('2ndanderefree', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('dolmetscher', 'string', 255, array('type' => 'string', 'length' => 255));
			
			
			
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
			
			
			//ISPC-2614 Ancuta 16.07.2020
			$this->addListener(new IntenseConnectionListener(array(
			    
			)), "IntenseConnectionListener");
			//
		}

	}

?>
