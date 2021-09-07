<?php

	abstract class BaseStammblatt7 extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('stammblatt7');
			$this->hasColumn('id', 'integer', 11, array('type' => 'integer', 'length' => 10, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('zuzahlung', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('comments', 'text', NULL, array('type' => 'text', 'length' => NULL));
			
			$this->hasColumn('pattel', 'string', 255, array('type' => 'string', 'length' => 255));// $post['pattel'];// 1234567890
			$this->hasColumn('pathandy', 'string', 255, array('type' => 'string', 'length' => 255));// $post['pathandy'];// pathandy
			$this->hasColumn('loctel', 'string', 255, array('type' => 'string', 'length' => 255));// $post['loctel'];// 1234567890
			$this->hasColumn('lochandy', 'string', 255, array('type' => 'string', 'length' => 255));// $post['lochandy'];// lochandy
			$this->hasColumn('locfax', 'string', 255, array('type' => 'string', 'length' => 255));// $post['locfax'];// locfax
			$this->hasColumn('cntpers1tel', 'string', 255, array('type' => 'string', 'length' => 255));// $post['cntpers1tel'];// 1234567890
			$this->hasColumn('cntpers1handy', 'string', 255, array('type' => 'string', 'length' => 255));// $post['cntpers1handy'];// cntpers1handy
			$this->hasColumn('cntpers2tel', 'string', 255, array('type' => 'string', 'length' => 255));// $post['cntpers2tel'];// 1234567890
			$this->hasColumn('cntpers2handy', 'string', 255, array('type' => 'string', 'length' => 255));// $post['cntpers2handy'];// cntpers2handy
			$this->hasColumn('cntpers3tel', 'string', 255, array('type' => 'string', 'length' => 255));// $post['cntpers3tel'];// 1234567890
			$this->hasColumn('cntpers3handy', 'string', 255, array('type' => 'string', 'length' => 255));// $post['cntpers3handy'];// cntpers3handy
			$this->hasColumn('healthinsurance_comment', 'string', 255, array('type' => 'string', 'length' => 255));// $post['healthinsurance_comment'];// healthinsurance_comment
			$this->hasColumn('hausarzt_tel', 'string', 255, array('type' => 'string', 'length' => 255));// $post['hausarzt_tel'];// 1234567890
			$this->hasColumn('hausarzt_fax', 'string', 255, array('type' => 'string', 'length' => 255));// $post['hausarzt_fax'];// hausarzt_fax
			$this->hasColumn('facharzt_tel', 'string', 255, array('type' => 'string', 'length' => 255));// $post['facharzt_tel'];// 1234567890
			$this->hasColumn('facharzt_fax', 'string', 255, array('type' => 'string', 'length' => 255));// $post['facharzt_fax'];// facharzt_fax
			$this->hasColumn('pflegedienst_tel', 'string', 255, array('type' => 'string', 'length' => 255));// $post['pflegedienst_tel'];// 1234567890
			$this->hasColumn('pflegedienst_fax', 'string', 255, array('type' => 'string', 'length' => 255));// $post['pflegedienst_fax'];// pflegedienst_fax
			$this->hasColumn('pflegedienst_comment', 'string', 255, array('type' => 'string', 'length' => 255));// $post['pflegedienst_comment'];// pflegedienst_comment
			
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>