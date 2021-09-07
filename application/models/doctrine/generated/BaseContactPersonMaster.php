<?php

abstract class BaseContactPersonMaster extends Pms_Doctrine_Record
{

    function setTableDefinition()
    {
        $this->setTableName('contactperson_master');
        $this->hasColumn('id', 'bigint', NULL, array(
            'type' => 'bigint',
            'length' => NULL,
            'primary' => true,
            'autoincrement' => true
        ));
        $this->hasColumn('ipid', 'string', 255, array(
            'type' => 'string',
            'length' => 255
        ));
        $this->hasColumn('cnt_first_name', 'string', 255, array(
            'type' => 'string',
            'length' => 255
        ));
        $this->hasColumn('cnt_middle_name', 'string', 255, array(
            'type' => 'string',
            'length' => 255
        ));
        $this->hasColumn('cnt_last_name', 'string', 255, array(
            'type' => 'string',
            'length' => 255
        ));
        $this->hasColumn('cnt_title', 'string', 255, array(
            'type' => 'string',
            'length' => 255
        ));
        $this->hasColumn('cnt_salutation', 'string', 255, array(
            'type' => 'string',
            'length' => 255
        ));
        $this->hasColumn('cnt_street1', 'string', 255, array(
            'type' => 'string',
            'length' => 255
        ));
        $this->hasColumn('cnt_street2', 'string', 255, array(
            'type' => 'string',
            'length' => 255
        ));
        $this->hasColumn('cnt_zip', 'string', 255, array(
            'type' => 'string',
            'length' => 255
        ));
        $this->hasColumn('cnt_city', 'string', 255, array(
            'type' => 'string',
            'length' => 255
        ));
        $this->hasColumn('cnt_phone', 'string', 255, array(
            'type' => 'string',
            'length' => 255
        ));
        $this->hasColumn('cnt_mobile', 'string', 255, array(
            'type' => 'string',
            'length' => 255
        ));
        $this->hasColumn('cnt_email', 'string', 255, array(
            'type' => 'string',
            'length' => 255
        ));
        $this->hasColumn('cnt_birthd', 'integer', 10, array(
            'type' => 'integer',
            'length' => 10
        ));
        $this->hasColumn('cnt_sex', 'integer', 1, array(
            'type' => 'integer',
            'length' => 1
        ));
        $this->hasColumn('cnt_denomination_id', 'integer', 11, array(
            'type' => 'integer',
            'length' => 11
        ));
        $this->hasColumn('cnt_familydegree_id', 'integer', 11, array(
            'type' => 'integer',
            'length' => 11
        ));
        $this->hasColumn('old_familydegree_id', 'integer', 11, array(
            'type' => 'integer',
            'length' => 11
        ));
        $this->hasColumn('cnt_nation', 'string', 255, array(
            'type' => 'string',
            'length' => 255
        ));
        $this->hasColumn('cnt_custody', 'string', 255, array(
            'type' => 'string',
            'length' => 255
        ));
        $this->hasColumn('cnt_hatversorgungsvollmacht', 'integer', 1, array(
            'type' => 'integer',
            'length' => 1
        ));
        $this->hasColumn('cnt_legalguardian', 'integer', 1, array(
            'type' => 'integer',
            'length' => 1
        ));
        $this->hasColumn('notify_funeral', 'integer', 1, array(
            'type' => 'integer',
            'length' => 1
        ));
        $this->hasColumn('quality_control', 'integer', 1, array(
            'type' => 'integer',
            'length' => 1
        ));
        $this->hasColumn('cnt_kontactnumber', 'integer', 1, array(
            'type' => 'integer',
            'length' => 1
        ));
        $this->hasColumn('cnt_comment', 'text', NULL, array(
            'type' => 'text',
            'length' => NULL
        ));
        $this->hasColumn('isdelete', 'integer', 1, array(
            'type' => 'integer',
            'length' => 1
        ));
        
        $this->hasColumn('is_contact', 'integer', 1, array(
            'type' => 'integer',
            'length' => 1,
            'default' => 0,
            'comments' => 'ist die Kontakt-Telefonnummer'
        ));
        
        // ISPC-2128
        $this->hasColumn('cnt_custody_val', 'integer', 1, array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => false,
            'default' => NULL,
            'comments' => '0=unchecked, 1=checked'
        ));
        
        
        //ISPC-2292
        $this->hasColumn('cnt_residence_determination', 'integer', 1, array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => false,
            'default' => NULL,
            'comments' => '0=unchecked, 1=checked'
        ));
        $this->hasColumn('cnt_representation_authorities', 'integer', 1, array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => false,
            'default' => NULL,
            'comments' => '0=unchecked, 1=checked'
        ));
        $this->hasColumn('cnt_asset_custody', 'integer', 1, array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => false,
            'default' => NULL,
            'comments' => '0=unchecked, 1=checked'
        ));
        $this->hasColumn('cnt_receipt_post', 'integer', 1, array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => false,
            'default' => NULL,
            'comments' => '0=unchecked, 1=checked'
        ));
        
    }

    function setUp()
    {
        $this->actAs(new Timestamp());
        $this->actAs(new Trigger());
        $this->actAs(new PatientInsert());
        
        $this->actAs(new Softdelete());
        
        $this->addListener(new PatientContactPhoneListener(array(
            "is_contact" => "is_contact",
            "phone" => "cnt_phone",
            "mobile" => "cnt_mobile",
            "first_name" => "cnt_first_name",
            "last_name" => "cnt_last_name",
            "other_name" => "cnt_familydegree_id",
            "extra" => [
                'comment' => 'cnt_comment',
                'street' => 'cnt_street1',
                'city' => 'cnt_city',
                'zip' => 'cnt_zip',
            ]
        )));
        
        //ISPC-2614 Ancuta 16-17.07.2020
        $this->addListener(new IntenseConnectionListener(array(
            
        )), "IntenseConnectionListener");
        //
        
    }
}

?>