<?php

/**
 * PatientKindergartenTable
 * #ISPC-2672 Carmen 21.10.2020
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @method createIfNotExistsOneBy($fieldName, $value = null, array $data = array())
 * @method findOrCreateOneBy($fieldName, $value = null, array $data = array())
 * 
 * @package    ISPC-2672
 * @subpackage Application (2020-10-21) 
 * @author     Carmen <office@originalware.com>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
class PatientKindergartenTable extends Pms_Doctrine_Table
{
    /**
     * Returns an instance of this class.
     *
     * @return PatientKindergartenTable (object)
     */
    public static function getInstance()
    {
        return Doctrine_Core::getTable('PatientKindergarten');
    }
    
    
}