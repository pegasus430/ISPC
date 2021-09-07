<?php

	abstract class BaseMdkSchne extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('mdk_schne');
			$this->hasColumn('id', 'integer', 11, array('type' => 'integer', 'length' => 10, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('pflegeversicherung', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('cntpers1', 'string', 300, array('type' => 'string', 'length' => 300));
			$this->hasColumn('cntpers2', 'string', 300, array('type' => 'string', 'length' => 300));
			$this->hasColumn('pflegeperson', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('pflege_benefits', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('maindiagnosis', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('ambulante', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('kurative', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('behandlungsansatz', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('aufklarung', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('livingwill', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('livingwill_wird', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('livingwill_txt', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('palliativer', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('palliativer_txt', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('palliativer_wird', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('erfolgen', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('erfolgen_txt', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('schem_symptomatik', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('extreme_symptome', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('extreme_symptome_txt', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('psychosoziale_a', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('psychosoziale_a_txt', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('psychosoziale_b', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('psychosoziale_b_txt', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('psychosoziale_c', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('psychosoziale_c_txt', 'string', 255, array('type' => 'string', 'length' => 255));

			$this->hasColumn('angehorige', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('angehorige_txt', 'string', 255, array('type' => 'string', 'length' => 255));

			$this->hasColumn('krakenpflege', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('krakenpflege_txt', 'string', 255, array('type' => 'string', 'length' => 255));

			$this->hasColumn('liegen_sapv', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('liegen_sapv_txt', 'string', 255, array('type' => 'string', 'length' => 255));

			$this->hasColumn('medizinische_txt', 'string', 255, array('type' => 'string', 'length' => 255));

			$this->hasColumn('weitere', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('weitere_txt', 'string', 255, array('type' => 'string', 'length' => 255));

			$this->hasColumn('sonstiges', 'string', 500, array('type' => 'string', 'length' => 255));
			$this->hasColumn('new_instance', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>