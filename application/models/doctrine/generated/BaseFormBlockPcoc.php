<?php

/**
 * Class BaseFormBlockPcoc
 * IM-147
 * Maria:: Migration CISPC to ISPC 22.07.2020
 */
abstract class BaseFormBlockPcoc extends Doctrine_Record
{

    function setTableDefinition()
    {
        $this->setTableName('form_block_pcoc');
        $this->hasColumn('id', 'integer', 11, array('type' => 'integer','length' => 11, 'primary' => true, 'autoincrement' => true));
        $this->hasColumn('contact_form_id', 'integer', 11, array('type' => 'integer','length' => 11));
        $this->hasColumn('ipid', 'string', 255, array('type' => 'string','length' => 255));

        $this->hasColumn('problems_enabled', 'integer', 1, array('type' => 'integer','length' => 1));
        $this->hasColumn('problems_p1', 'string', 255, array('type' => 'string','length' => 255));
        $this->hasColumn('problems_p2', 'string', 255, array('type' => 'string','length' => 255));
        $this->hasColumn('problems_p3', 'string', 255, array('type' => 'string','length' => 255));

        $this->hasColumn('ipos_enabled', 'integer', 1, array('type' => 'integer','length' => 1));
        $this->hasColumn('ipos2a', 'integer', 11, array('type' => 'integer','length' => 11));
        $this->hasColumn('ipos2b', 'integer', 11, array('type' => 'integer','length' => 11));
        $this->hasColumn('ipos2c', 'integer', 11, array('type' => 'integer','length' => 11));
        $this->hasColumn('ipos2d', 'integer', 11, array('type' => 'integer','length' => 11));
        $this->hasColumn('ipos2e', 'integer', 11, array('type' => 'integer','length' => 11));
        $this->hasColumn('ipos2f', 'integer', 11, array('type' => 'integer','length' => 11));
        $this->hasColumn('ipos2g', 'integer', 11, array('type' => 'integer','length' => 11));
        $this->hasColumn('ipos2h', 'integer', 11, array('type' => 'integer','length' => 11));
        $this->hasColumn('ipos2i', 'integer', 11, array('type' => 'integer','length' => 11));
        $this->hasColumn('ipos2j', 'integer', 11, array('type' => 'integer','length' => 11));
        $this->hasColumn('ipos_add', 'string', null, array('type' => 'string','length' => null));

        $this->hasColumn('pcpss_enabled', 'integer', 1, array('type' => 'integer','length' => 1));
        $this->hasColumn('pcpss_pain', 'integer', 11, array('type' => 'integer','length' => 11));
        $this->hasColumn('pcpss_other', 'integer', 11, array('type' => 'integer','length' => 11));
        $this->hasColumn('pcpss_psy', 'integer', 11, array('type' => 'integer','length' => 11));
        $this->hasColumn('pcpss_rel', 'integer', 11, array('type' => 'integer','length' => 11));

        $this->hasColumn('nps_enabled', 'integer', 1, array('type' => 'integer','length' => 1));
        $this->hasColumn('nps_verwirrtheit', 'integer', 11, array('type' => 'integer','length' => 11));
        $this->hasColumn('nps_unruhe', 'integer', 11, array('type' => 'integer','length' => 11));

        $this->hasColumn('phase_enabled', 'integer', 1, array('type' => 'integer','length' => 1));
        $this->hasColumn('phase_phase', 'integer', 11, array('type' => 'integer','length' => 11));

        $this->hasColumn('psysoz_enabled', 'integer', 1, array('type' => 'integer','length' => 1));
        $this->hasColumn('ipos3', 'integer', 11, array('type' => 'integer','length' => 11));
        $this->hasColumn('ipos4', 'integer', 11, array('type' => 'integer','length' => 11));
        $this->hasColumn('ipos5', 'integer', 11, array('type' => 'integer','length' => 11));
        $this->hasColumn('ipos6', 'integer', 11, array('type' => 'integer','length' => 11));
        $this->hasColumn('ipos7', 'integer', 11, array('type' => 'integer','length' => 11));
        $this->hasColumn('ipos8', 'integer', 11, array('type' => 'integer','length' => 11));
        $this->hasColumn('ipos9', 'integer', 11, array('type' => 'integer','length' => 11));

        $this->hasColumn('akps_enabled', 'integer', 1, array('type' => 'integer','length' => 1));
        $this->hasColumn('akps_akps', 'integer', 11, array('type' => 'integer','length' => 11));

        $this->hasColumn('barthel_enabled', 'integer', 1, array('type' => 'integer','length' => 1));
        $this->hasColumn('barthel1', 'integer', 11, array('type' => 'integer','length' => 11));
        $this->hasColumn('barthel2', 'integer', 11, array('type' => 'integer','length' => 11));
        $this->hasColumn('barthel3', 'integer', 11, array('type' => 'integer','length' => 11));
        $this->hasColumn('barthel4', 'integer', 11, array('type' => 'integer','length' => 11));
        $this->hasColumn('barthel5', 'integer', 11, array('type' => 'integer','length' => 11));
        $this->hasColumn('barthel6', 'integer', 11, array('type' => 'integer','length' => 11));
        $this->hasColumn('barthel7', 'integer', 11, array('type' => 'integer','length' => 11));
        $this->hasColumn('barthel8', 'integer', 11, array('type' => 'integer','length' => 11));
        $this->hasColumn('barthel9', 'integer', 11, array('type' => 'integer','length' => 11));
        $this->hasColumn('barthel10', 'integer', 11, array('type' => 'integer','length' => 11));

        $this->hasColumn('misc_by', 'string', null, array('type' => 'string','length' => null));
        $this->hasColumn('misc_date', 'datetime', NULL, array('type' => 'datetime','length' => NULL));

        $this->hasColumn('pcoc_full', 'integer', 1, array('type' => 'integer','length' => 1));

        //Start TODO-4163
        $this->hasColumn('shortstatus', 'integer', 1, array('type' => 'integer','length' => 1));
        $this->hasColumn('pcoc_assessment', 'integer', 1, array('type' => 'integer','length' => 1));
        //END TODO-4163

        $this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer','length' => 1));
        $this->hasColumn('create_date', 'datetime', NULL, array('type' => 'datetime','length' => NULL));
        $this->hasColumn('create_user', 'bigint', 20, array('type' => 'bigint','length' => 20));
        $this->hasColumn('change_date', 'datetime', NULL, array('type' => 'datetime','length' => NULL));
        $this->hasColumn('change_user', 'bigint', 20, array('type' => 'bigint','length' => 20));
    }

    function setUp()
    {
        $this->actAs(new TimeStamp());
    }

}
