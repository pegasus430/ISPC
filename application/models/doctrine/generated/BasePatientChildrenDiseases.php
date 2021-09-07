<?php
/*
 * ISPC-2776 Lore 15.12.2020
 */

Doctrine_Manager::getInstance()->bindComponent('PatientChildrenDiseases', 'IDAT');


abstract class BasePatientChildrenDiseases extends Pms_Doctrine_Record {
    
    function setTableDefinition()
    {
        $this->setTableName('patient_children_diseases');
        $this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
        $this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
        $this->hasColumn('rotavirus_opt', 'string', 255, array('type' => 'string', 'length' => 255));
        $this->hasColumn('rotavirus_last_vaccination', 'datetime');
        $this->hasColumn('rotavirus_next_vaccination', 'datetime');
        $this->hasColumn('rotavirus_text', 'string', 255, array('type' => 'string', 'length' => 255));
        $this->hasColumn('varicella_opt', 'string', 255, array('type' => 'string', 'length' => 255));
        $this->hasColumn('varicella_last_vaccination', 'datetime');
        $this->hasColumn('varicella_next_vaccination', 'datetime');
        $this->hasColumn('varicella_text', 'string', 255, array('type' => 'string', 'length' => 255));
        $this->hasColumn('measles_opt', 'string', 255, array('type' => 'string', 'length' => 255));
        $this->hasColumn('measles_last_vaccination', 'datetime');
        $this->hasColumn('measles_next_vaccination', 'datetime');
        $this->hasColumn('measles_text', 'string', 255, array('type' => 'string', 'length' => 255));
        $this->hasColumn('mumps_opt', 'string', 255, array('type' => 'string', 'length' => 255));
        $this->hasColumn('mumps_last_vaccination', 'datetime');
        $this->hasColumn('mumps_next_vaccination', 'datetime');
        $this->hasColumn('mumps_text', 'string', 255, array('type' => 'string', 'length' => 255));
        $this->hasColumn('rubella_opt', 'string', 255, array('type' => 'string', 'length' => 255));
        $this->hasColumn('rubella_last_vaccination', 'datetime');
        $this->hasColumn('rubella_next_vaccination', 'datetime');
        $this->hasColumn('rubella_text', 'string', 255, array('type' => 'string', 'length' => 255));
        $this->hasColumn('pertussis_opt', 'string', 255, array('type' => 'string', 'length' => 255));
        $this->hasColumn('pertussis_last_vaccination', 'datetime');
        $this->hasColumn('pertussis_next_vaccination', 'datetime');
        $this->hasColumn('pertussis_text', 'string', 255, array('type' => 'string', 'length' => 255));
        $this->hasColumn('diphtheria_opt', 'string', 255, array('type' => 'string', 'length' => 255));
        $this->hasColumn('diphtheria_last_vaccination', 'datetime');
        $this->hasColumn('diphtheria_next_vaccination', 'datetime');
        $this->hasColumn('diphtheria_text', 'string', 255, array('type' => 'string', 'length' => 255));
        $this->hasColumn('tetanus_opt', 'string', 255, array('type' => 'string', 'length' => 255));
        $this->hasColumn('tetanus_last_vaccination', 'datetime');
        $this->hasColumn('tetanus_next_vaccination', 'datetime');
        $this->hasColumn('tetanus_text', 'string', 255, array('type' => 'string', 'length' => 255));
        $this->hasColumn('hib_opt', 'string', 255, array('type' => 'string', 'length' => 255));
        $this->hasColumn('hib_last_vaccination', 'datetime');
        $this->hasColumn('hib_next_vaccination', 'datetime');
        $this->hasColumn('hib_text', 'string', 255, array('type' => 'string', 'length' => 255));
        $this->hasColumn('polio_opt', 'string', 255, array('type' => 'string', 'length' => 255));
        $this->hasColumn('polio_last_vaccination', 'datetime');
        $this->hasColumn('polio_next_vaccination', 'datetime');
        $this->hasColumn('polio_text', 'string', 255, array('type' => 'string', 'length' => 255));
        $this->hasColumn('pneumococci_opt', 'string', 255, array('type' => 'string', 'length' => 255));
        $this->hasColumn('pneumococci_last_vaccination', 'datetime');
        $this->hasColumn('pneumococci_next_vaccination', 'datetime');
        $this->hasColumn('pneumococci_text', 'string', 255, array('type' => 'string', 'length' => 255));
        $this->hasColumn('meningococci_opt', 'string', 255, array('type' => 'string', 'length' => 255));
        $this->hasColumn('meningococci_last_vaccination', 'datetime');
        $this->hasColumn('meningococci_next_vaccination', 'datetime');
        $this->hasColumn('meningococci_text', 'string', 255, array('type' => 'string', 'length' => 255));
        $this->hasColumn('hepatit_a_opt', 'string', 255, array('type' => 'string', 'length' => 255));
        $this->hasColumn('hepatit_a_last_vaccination', 'datetime');
        $this->hasColumn('hepatit_a_next_vaccination', 'datetime');
        $this->hasColumn('hepatit_a_text', 'string', 255, array('type' => 'string', 'length' => 255));
        $this->hasColumn('hepatit_b_opt', 'string', 255, array('type' => 'string', 'length' => 255));
        $this->hasColumn('hepatit_b_last_vaccination', 'datetime');
        $this->hasColumn('hepatit_b_next_vaccination', 'datetime');
        $this->hasColumn('hepatit_b_text', 'string', 255, array('type' => 'string', 'length' => 255));
        $this->hasColumn('tuberculosis_opt', 'string', 255, array('type' => 'string', 'length' => 255));
        $this->hasColumn('tuberculosis_text', 'string', 255, array('type' => 'string', 'length' => 255));
        $this->hasColumn('hpv_opt', 'string', 255, array('type' => 'string', 'length' => 255));
        $this->hasColumn('hpv_last_vaccination', 'datetime');
        $this->hasColumn('hpv_next_vaccination', 'datetime');
        $this->hasColumn('hpv_text', 'string', 255, array('type' => 'string', 'length' => 255));
        $this->hasColumn('other_text', 'string', 255, array('type' => 'string', 'length' => 255));
        
    }
    
    function setUp()
    {
        $this->actAs(new Softdelete());
        
        $this->actAs(new Timestamp());
        
        
        
    }
    
}