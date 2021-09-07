<?php

/**
 * PatientTherapyTable
 * #ISPC-2774 Carmen 16.12.2020
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @method createIfNotExistsOneBy($fieldName, $value = null, array $data = array())
 * @method findOrCreateOneBy($fieldName, $value = null, array $data = array())
 * 
 * @subpackage Application (2020-12-16) 
 * @author     Carmen <office@originalware.com>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
class PatientTherapyTable extends Pms_Doctrine_Table
{
    /**
     * Returns an instance of this class.
     *
     * @return PatientTherapyTable (object)
     */
    public static function getInstance()
    {
        return Doctrine_Core::getTable('PatientTherapy');
    }
    
    
}