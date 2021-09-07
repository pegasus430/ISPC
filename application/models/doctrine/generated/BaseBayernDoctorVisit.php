<?php

	abstract class BaseBayernDoctorVisit extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('bayern_doctor_visit');
			$this->hasColumn('id', 'integer', 8, array('type' => 'integer', 'length' => 8, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('start_date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('end_date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('begin_date_h', 'integer', 10, array('type' => 'integer', 'length' => 10));
			$this->hasColumn('begin_date_m', 'integer', 10, array('type' => 'integer', 'length' => 10));
			$this->hasColumn('end_date_h', 'integer', 10, array('type' => 'integer', 'length' => 10));
			$this->hasColumn('end_date_m', 'integer', 10, array('type' => 'integer', 'length' => 10));
			$this->hasColumn('visit_date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('documantation_time', 'integer', 10, array('type' => 'integer', 'length' => 10));
			$this->hasColumn('fahrtzeit', 'integer', 10, array('type' => 'integer', 'length' => 10));
			$this->hasColumn('fahrtstreke_km', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('peg', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('peg_text', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('port', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('port_text', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('pumps', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('pumps_text', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('dk', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('dk_text', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('kunstliche', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('kunstliche_text', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('darm', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('darm_text', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('blase', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('blase_text', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('luftrohre', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('luftrohre_text', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('ablaufsonde', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('ablaufsonde_text', 'string', 255, array('type' => 'string', 'length' => 255));
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
			$this->hasColumn('ecog', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('sonstiges', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('comment_apotheke', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('case_history', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('conversation_phonecall', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('global', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('medizini_a', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('medizini_b', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('related_users', 'string', 255, array('type' => 'string', 'length' => 255));
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