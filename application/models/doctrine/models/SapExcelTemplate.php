<?php
Doctrine_Manager::getInstance()->bindComponent('SapExcelTemplate', 'SYSDAT');

class SapExcelTemplate extends BaseSapExcelTemplate
{

    /**
     * @author Ancuta
     * ISPC-2171 16.04.2018 
     *
     * Changes for ISPC-2452  on 20.09.2019
     * select specific columns and for specific export type 
     * @return Ambigous <multitype:, Doctrine_Collection>
     */
    
    public function grab_sap_export_data()
    {
        $array_result = array();
        $query = Doctrine_Query::create()
            ->select('id,xls_line,line,nr,field,description,type,value_length,import_date,explanation')
            ->from('SapExcelTemplate')
            ->where('export_type = "sap_txt" ');
        $array_result = $query->fetchArray();
        
        return $array_result;
    }

    
    /**
     * @author Ancuta
     * ISPC-2452 20.09.2019
     * @return Ambigous <multitype:, Doctrine_Collection>
     */
    public function grab_sap_ii_export_data()
    {
        $array_result = array();
        $array_result = Doctrine_Query::create()
            ->select('*')
            ->from('SapExcelTemplate')
            ->where('export_type = "sap_ii_txt" ')
            ->andWhere(' line != 4 ')
            ->orderBy('line,nr ASC')
            ->fetchArray();
        
        return $array_result;
    }
}

?>