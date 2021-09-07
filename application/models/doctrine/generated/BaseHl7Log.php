<?php
/**
 * this model was received from Nico 
 * he used this to log results after processing the messages (update, insert patient)
 * 
 */
abstract class BaseHl7Log extends Pms_Doctrine_Record
{

    public function setTableDefinition()
    {
        $this->setTableName('hl7_log');
        
        $this->hasColumn('id', 'bigint', NULL, array(
            'type' => 'bigint',
            'length' => NULL,
            'primary' => true,
            'autoincrement' => true
        ));
        $this->hasColumn('date', 'datetime', NULL, array(
            'type' => 'datetime',
            'length' => NULL
        ));
        $this->hasColumn('message', 'message', NULL, array(
            'type' => 'text',
            'length' => NULL
        ));
        $this->hasColumn('level', 'int', 255, array(
            'type' => 'int',
            'length' => 255
        ));
    }

    public function setUp()
    {
        parent::setUp();
               
        $this->actAs(new Timestamp());
    }
}
?>
