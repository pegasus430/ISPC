<?php

/**
 * UserLastContactsFiltersTable
 * 
 * 
 * @method createIfNotExistsOneBy($fieldName, $value = null, array $data = array())
 * @method findOrCreateOneBy($fieldName, $value = null, array $data = array())
 * 
 * @package    ISPC-2440
 * @subpackage Application (2020-03-11)
 * @author     Lore <office@originalware.com>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
class UserLastContactsFiltersTable extends Pms_Doctrine_Table
{
    /**
     * Returns an instance of this class.
     *
     * @return UserLastContactsFiltersTable (object)
     */
    public static function getInstance()
    {
        return Doctrine_Core::getTable('UserLastContactsFilters');
    }
}