<?php
/**
 * 
 * Maria:: Migration CISPC to ISPC 22.07.2020
 *
 */
abstract class BaseExtraopsleistung extends Doctrine_Record
{


    function setTableDefinition()
	{
		$this->setTableName('extraopsleistung');
		$this->hasColumn('id', 'integer', 11, array('type' => 'integer','length' => 11, 'primary' => true, 'autoincrement' => true));
		$this->hasColumn('ipid', 'string', 255, array('type' => 'string','length' => 255));
        $this->hasColumn('caseid', 'integer', 11, array('type' => 'integer','length' => 11));

        $this->hasColumn('done_date', 'datetime', NULL, array('type' => 'datetime','length' => NULL));
        $this->hasColumn('mins', 'integer', 11, array('type' => 'integer','length' => 11));

        $this->hasColumn('mins_patient', 'integer', 11, array('type' => 'integer','length' => 11));
        $this->hasColumn('mins_angehoerige', 'integer', 11, array('type' => 'integer','length' => 11));
        $this->hasColumn('mins_profi', 'integer', 11, array('type' => 'integer','length' => 11));
        $this->hasColumn('mins_systemisch', 'integer', 11, array('type' => 'integer','length' => 11));

		$this->hasColumn('done_group', 'string', 255, array('type' => 'string','length' => 255));
		$this->hasColumn('done_name', 'string', 255, array('type' => 'string','length' => 255));
		$this->hasColumn('memo', 'string', NULL, array('type' => 'string','length' => NULL));

		
		$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer','length' => 1));
		$this->hasColumn('create_date', 'datetime', NULL, array('type' => 'datetime','length' => NULL));
		$this->hasColumn('create_user', 'bigint', 20, array('type' => 'bigint','length' => 20));
		$this->hasColumn('change_date', 'datetime', NULL, array('type' => 'datetime','length' => NULL));
		$this->hasColumn('change_user', 'bigint', 20, array('type' => 'bigint','length' => 20));
	}

	function setUp()
	{
		$this->actAs(new Timestamp());
	}

}

?>
