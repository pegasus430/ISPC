<?php

	abstract class BaseKvnoAssessment extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('kvno_assessment');
			$this->hasColumn('id', 'integer', 11, array('type' => 'integer', 'length' => 10, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('start_date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			
			$this->hasColumn('fammore', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('depresiv', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('angst', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('anspannung', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('desorientier', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('angstemore', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('wuschemore', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('behandlungmore', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('ressourcenmore', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('dekubitus', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('hilfebedarf', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('versorgung', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('umfelds', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('hilfsmore', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('vigilanz', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('vigilanzmore', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('mobilitatmore', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('schmerzen', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('who', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('whomore', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('ubelkeit', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('erbrechen', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('luftnot', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('verstopfung', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('swache', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('appetitmangel', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('anderemore', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('biographiemore', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('sapvteam', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('hausarzt', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('pflege', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('palliativ', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('palliativpf', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('palliativber', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('dienst', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('stationar', 'integer', 2, array('type' => 'integer', 'length' => 2));
			$this->hasColumn('psycho', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('ps_nochunklar_txt', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('anzahl', 'integer', 2, array('type' => 'integer', 'length' => 2));
			$this->hasColumn('absprache', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('kachexie', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('mager', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('normal', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('adipos', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('nromaleaktiv', 'integer', 1, array('type' => 'integer', 'length' => 1));

			$this->hasColumn('pverfungung', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('versorgevoll', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('gesetzl', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('a_nochunklar', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('a_nochunklar_txt', 'text', NULL, array('type' => 'text', 'length' => NULL));

			$this->hasColumn('pflegebedurftig', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('kopf', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('kopfmore', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('thorax', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('thoraxmore', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('abdomen', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('abdomenmore', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('extremitaten', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('extremitatenmore', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('haut', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('hautmore', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('fotodokmore', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('sonstigesmore', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('estimation', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('reeval', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('doc_id', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('pfl_id', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('billing_mode', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('iscompleted', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('completed_date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('issaved', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('status', 'integer', 1, array('type' => 'integer', 'length' => 1));
			// ISPC-2193
			$this->hasColumn('care_at_admission', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('partners', 'object', null, array(
			    'type' => 'object',
			    'fixed' => false,
			    'unsigned' => false,
			    'values' =>
			    array(
			        0 => "7",
			        1 => "9",
			        2 => "5",
			        3 => "11",
			        4 => "2" ,
			        5 => "4" ,
			        6 => "12",
			        7 => "3" ,
			        8 => "14",
			        9 => "8" ,
			        10 => "10",
			        11 => "15", 
			        12 => "16",
			        13 => "17",
			        14 => "18",
			        15 => "19",
			        16 => "20",
			        17 => "21",
			        18 => "22",
			        19 => "23",
			        20 => "24",
			        
			    ),
			    'primary' => false,
			    'notnull' => true,
			    'autoincrement' => false,
			));
				
			
			
			$this->index('ipid', array(
					'fields' => array('ipid')
			));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>