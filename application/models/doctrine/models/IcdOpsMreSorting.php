<?php

Doctrine_Manager::getInstance()->bindComponent('IcdOpsMreSorting', 'SYSDAT');

/**
 * ISPC-2654 Lore 05.10.2020
 * @author Loredana
 *
 */

class IcdOpsMreSorting extends BaseIcdOpsMreSorting {
    
    
    public function getIcdOpsMreSorting($clientid)
    {
        $ioms = Doctrine_Query::create()
        ->select('*')
        ->from('IcdOpsMreSorting')
        ->where('clientid = ?',$clientid)
        ->andWhere('isdelete = 0' );
        $iomsarr = $ioms->fetchArray();
        
        return $iomsarr;
        
    }
    
    
    public static function get_sorting_columns()
    {
        $shortcut = array(
            '1' => "icd_category",
            '2' => "icd_code",
            '3' => "icd_description",
            '4' => "relevant2admission",
            '5' => "icd_start_date",
            '6' => "icd_end_date",
            '7' => "icd_comment",
        );
        return $shortcut;
    }

    
}

?>