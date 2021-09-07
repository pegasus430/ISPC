<?php

	Doctrine_Manager::getInstance()->bindComponent('InternalInvoicesDayProducts', 'SYSDAT');

	abstract class BaseInternalInvoicesDayProducts extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('internal_invoices_day_products');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('client', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('list', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('usergroup', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('grouped', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('sapv', 'integer', 1, array('type' => 'integer', 'length' => 1));
			
			$this->hasColumn('normal_price', 'decimal', 10, array('scale' => 2));
			$this->hasColumn('normal_price_name', 'string', 255, array('type' => 'string', 'length' => 255));

			$this->hasColumn('hosp_adm_price_name', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('hosp_adm_price', 'decimal', 10, array('scale' => 2));

			$this->hasColumn('hosp_price_name', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('hosp_price', 'decimal', 10, array('scale' => 2));

			$this->hasColumn('hosp_dis_price_name', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('hosp_dis_price', 'decimal', 10, array('scale' => 2));

			$this->hasColumn('hospiz_adm_price_name', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('hospiz_adm_price', 'decimal', 10, array('scale' => 2));

			$this->hasColumn('hospiz_price_name', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('hospiz_price', 'decimal', 10, array('scale' => 2));

			$this->hasColumn('hospiz_dis_price_name', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('hospiz_dis_price', 'decimal', 10, array('scale' => 2));

			$this->hasColumn('standby_price_name', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('standby_price', 'decimal', 10, array('scale' => 2));

			$this->hasColumn('hosp_dis_hospiz_adm_price_name', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('hosp_dis_hospiz_adm_price', 'decimal', 10, array('scale' => 2));

			$this->hasColumn('hospiz_dis_hosp_adm_price_name', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('hospiz_dis_hosp_adm_price', 'decimal', 10, array('scale' => 2));

			$this->hasColumn('holiday', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>