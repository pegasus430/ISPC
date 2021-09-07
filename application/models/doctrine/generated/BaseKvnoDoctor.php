<?php

	abstract class BaseKvnoDoctor extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('kvno_doctor');
			$this->hasColumn('id', 'integer', 8, array('type' => 'integer', 'length' => 8, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('start_date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('end_date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));

			$this->hasColumn('kvno_begin_date_h', 'integer', 10, array('type' => 'integer', 'length' => 10));
			$this->hasColumn('kvno_begin_date_m', 'integer', 10, array('type' => 'integer', 'length' => 10));
			$this->hasColumn('kvno_end_date_h', 'integer', 10, array('type' => 'integer', 'length' => 10));
			$this->hasColumn('kvno_end_date_m', 'integer', 10, array('type' => 'integer', 'length' => 10));
			$this->hasColumn('vizit_date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('quality', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('fahrtzeit', 'integer', 10, array('type' => 'integer', 'length' => 10));
			$this->hasColumn('fahrtstreke_km', 'string', 255, array('type' => 'string', 'length' => 255));

			$this->hasColumn('kvno_peg', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('kvno_peg_text', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('kvno_port', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('kvno_port_text', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('kvno_pumps', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('kvno_pumps_text', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('kvno_dk', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('kvno_dk_text', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('kvno_kunstliche', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('kvno_kunstliche_text', 'string', 255, array('type' => 'string', 'length' => 255));
			//newly added 21.05.2012
			$this->hasColumn('kvno_darm', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('kvno_darm_text', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('kvno_blase', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('kvno_blase_text', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('kvno_luftrohre', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('kvno_luftrohre_text', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('kvno_ablaufsonde', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('kvno_ablaufsonde_text', 'string', 255, array('type' => 'string', 'length' => 255));

			$this->hasColumn('kopf', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('kopf_text', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('thorax', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('thorax_text', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('abdomen', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('abdomen_text', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('extremitaten', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('extremitaten_text', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('haut_wunden', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('haut_wunden_text', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('neurologisch_psychiatrisch', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('neurologisch_psychiatrisch_text', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('kvno_ecog', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('kvno_fotodocumentation', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('kvno_sonstiges', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('comment_apotheke', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('care_instructions', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('kvno_global', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('kvno_medizini_a', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('kvno_medizini_b', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('sub_user', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));

			$this->hasColumn('create_date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('change_date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('create_user', 'bigint', 20, array('type' => 'bigint', 'length' => 20));
			$this->hasColumn('change_user', 'bigint', 20, array('type' => 'bigint', 'length' => 20));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>