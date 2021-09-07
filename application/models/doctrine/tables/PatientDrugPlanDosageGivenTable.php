<?php

/**
 * PatientDrugPlanDosageGivenTable
 * #ISPC-2512PatientCharts
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @method createIfNotExistsOneBy($fieldName, $value = null, array $data = array())
 * @method findOrCreateOneBy($fieldName, $value = null, array $data = array())
 * 
 * @package    ISPC-2547
 * @subpackage Application (2020-03-03)
 * @author     Carmen <office@originalware.com>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
class PatientDrugPlanDosageGivenTable extends Pms_Doctrine_Table
{
    /**
     * Returns an instance of this class.
     *
     * @return PatientDrugPlanDosageGivenTable (object)
     */
    public static function getInstance()
    {
        return Doctrine_Core::getTable('PatientDrugPlanDosageGiven');
    }
    
    /**
     *
     * @param mixed $hydrationMode
     * @return void|Doctrine_Collection
     */
    public static function findAllIpidsCurrentDayGiven($ipids, $hydrationMode = Doctrine_Core::HYDRATE_ARRAY)
    {
    	return self::getInstance()->createQuery('pdg')
    	->select("pdg.*")
    	->whereIn('pdg.ipid', $ipids)
    	//->andWhere('pdg.undocumented = "0"') //ISPC-2583 Carmen 27.04.2020
    	->andWhere('DATE(pdg.documented_date) = ?', date('Y-m-d', time()))
    	->execute(null, $hydrationMode);

    }
    
    /**
     *
     * @param mixed $hydrationMode
     * @return void|Doctrine_Collection
     */
    public static function findOneCurrentDayGiven($ipid, $drugplan_id, $dosage_time_interval, $hydrationMode = Doctrine_Core::HYDRATE_RECORD)
    {
    	return self::getInstance()->createQuery('pdg')
    	->select("pdg.*")
    	->where('pdg.ipid=?', $ipid)
    	->andWhere('pdg.drugplan_id=?', $drugplan_id)
    	->andWhere('pdg.undocumented = "0"')
    	->andWhere('pdg.dosage_time_interval=?', $dosage_time_interval)
    	->andWhere('DATE(pdg.documented_date) = ?', date('Y-m-d', time()))
    	->limit(1)
        ->fetchOne();
    
    }
    
    /**
     * ISPC-2538
     * @param unknown $ipids
     * @param unknown $hydrationMode
     * @return Doctrine_Collection
     */
    public static function findAllByIpids($ipids, $hydrationMode = Doctrine_Core::HYDRATE_ARRAY)
    {
        return self::getInstance()->createQuery('pdg')
        ->select("pdg.*")
        ->whereIn('pdg.ipid', $ipids)
        ->andWhere('pdg.undocumented = "0"')
        ->execute(null, $hydrationMode);
        
    }
    
}