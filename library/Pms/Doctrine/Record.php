<?php
/**
 * 
 * @author claudiu
 * @date 11.01.2018
 *
 * add here functions that you want available for all your doctrine records
 * avoid naming your functions getXX(), because of magic __get and __set that Doctrine is using for formating the data
 * 
 * + @cla on 05.06.2018 refactoring class name from Pms_DoctrineRecord
 * 
 * + @cla on 09.06.2018 added 2 new methods _log_error and _log_info
 * this 2 logger fn have an extra ELSE
 * this is untill we switch to the new bootstrap + logger helper
 * TODO: remove this extra ELSE
 * 
 * + @cla on 02.07.2018 $_encypted_columns 
 * and enabled $this->_encryptData for findOrCreateOneBy..  
 * 
 * +@cla on 08.08.2018
 * fail-safe update, by unset(primary-Key) and unset(searched-Field)
 * 
 * TODO: findOrCreateOneByIpidAndPrimaryKey
 * TODO: implements Pms_Doctrine_ISPC_MODEL 
 * 
 */
abstract class Pms_Doctrine_Record extends Doctrine_Record
{
    /*
     * de display to user format
     */
    protected $_date_format_datepicked      = Zend_Date::DAY.'.'.Zend_Date::MONTH.'.'.Zend_Date::YEAR;
    protected $_date_format_datetime        = Zend_Date::DAY.'.'.Zend_Date::MONTH.'.'.Zend_Date::YEAR . " ". Zend_Date::HOUR.":".Zend_Date::MINUTE;
    
    /*
     * database format
     */
    protected $_date_format_db      = Zend_Date::YEAR.'-'.Zend_Date::MONTH.'-'.Zend_Date::DAY;
    protected $_datetime_format_db  = Zend_Date::YEAR.'-'.Zend_Date::MONTH.'-'.Zend_Date::DAY . " " . Zend_Date::HOUR."-".Zend_Date::MINUTE . "-". Zend_Date::SECOND;
    
    
    protected $_encypted_columns = null;
    

    /**
     * 
     * @var Application_Controller_Helper_Log
     */
    protected static $_logger = null;
    
    //abstract public function findByIpid();
    
    /**
     * Translator translate wrapper
     * 
     * @param unknown $string
     * @return mixed|NULL
     */
    public static function translate($string)
    {
        $translator = new Zend_View_Helper_Translate();
//         $translator = Zend_Registry::get('Zend_Translate');
        $lang_array = (defined('static::LANGUAGE_ARRAY') && strlen(static::LANGUAGE_ARRAY)) ? call_user_func(array($translator, 'translate'), static::LANGUAGE_ARRAY) :  null;
//         $lang_array = null;
        
        if (empty($lang_array) || ! isset($lang_array[$string])) {
            //original translator
            return call_user_func_array(array($translator, 'translate'), func_get_args());
        } else {
            //...i've groupped translations into arrays, a good idea at the time.. a BAD ideea now
            $messageid =  $lang_array[$string];
    
            //from original translate
            $options   = func_get_args();
            array_shift($options);
            $count  = count($options);
            $locale = null;
            if ($count > 0) {
                if (Zend_Locale::isLocale($options[($count - 1)], null, false) !== false) {
                    $locale = array_pop($options);
                }
            }
    
            if ((count($options) === 1) and (is_array($options[0]) === true)) {
                $options = $options[0];
            }
             
            if (count($options) === 0) {
                return $messageid;
            }
             
            return vsprintf($messageid, $options);
        }
    }
    
    
    
    /** 
     * @since 27.08.2018 ->forUpdate(true)
     * @since 19.10.2018 $fieldName & $value can be arrays
     * 
     * example: 
     * (new ModelX())->findOrCreateOneBy(['id', 'ipid', 'client'], [1], [ 'filed_X' => 'this', 'filed_Y' => 'is', 'filed_Y' => 'insert'])
     * (new ModelX())->findOrCreateOneBy(['id', 'ipid', 'client'], [1,'xox', 77], [ 'filed_X' => 'this', 'filed_Y' => 'can be', 'filed_Y' => 'update or createnew'])
     * (new ModelX())->findOrCreateOneBy('id', null, [ 'filed_X' => 'this', 'filed_Y' => 'is', 'filed_Y' => 'insert'])
     * 
     * @param string|array $fieldName
     * @param string|array $value
     * @param array $data
     * @return boolean|Doctrine_Record
     */
    public function findOrCreateOneBy($fieldName = null, $value = null, array $data = array())
    {
        if (empty($fieldName)) {
            throw new Doctrine_Table_Exception('Field must allways have a name.');            
        }
        
        $fieldName = is_array($fieldName) ? array_values($fieldName) : [$fieldName];
        
        $values = is_array($value) ? array_values($value) : [$value];
        
        $primaryKey = $this->getTable()->getIdentifier();
        
        /*
         * do not allow to overwrite the $primaryKey
         */
        if (isset($data[$primaryKey])) {
            unset($data[$primaryKey]);
        }
        
        /*
         * prevent changes to fields populated by Timestamp Listener
         */
        if (isset($data['create_date']) || isset($data['change_date']) || isset($data['create_user']) || isset($data['change_user'])) {
            unset($data['create_date'], $data['change_date'], $data['create_user'], $data['change_user']);
        }
        
        
        
        $isInsert = false;
        $entity = null;
        
        if (is_null($value)) {//nothing to search for..this must be an insert (here NULL = nothing to seearch for)
            
            $isInsert = true;
            
        } else {//try to find the record
            
            $q = $this->getTable()
            ->createQuery("dctrn_find")
            ->forUpdate(true)
            ->limit(1);
            
            foreach ($fieldName as $k => $field) {
                $q->andWhere($this->getTable()->buildFindByWhere($field), isset($values[$k]) ? $values[$k] : NULL);
            }
            
            $entity = $q->fetchOne(null, Doctrine_Core::HYDRATE_RECORD);
        }
            
        
        if ( $isInsert || ! $entity) {//this is insert
            
            $entity = $this->getTable()->create();
            
            foreach ($fieldName as $k => $field) {
                if ($field != $primaryKey && isset($values[$k])) {
                    $entity->{$field} = $values[$k]; //this are not _encryptData, add them to $data if you need them to be
                }                
            }
            
            $entity->assignDefaultValues(false);
            
        } else { //this is an update of $entity
            
            /*
             * do not allow to overwrite the fields by which you searched
             */
            foreach ($fieldName as $k => $field) {
                if (isset($data[$field])) {
                    unset($data[$field]);
                }
            }
            
        }
        
        $this->_encryptData($data); // encrypt model->_encypted_columns
        
        $entity->fromArray($data); //update
         
        $entity->save(); //at least one field must be dirty in order to persist
       
        return $entity;
    }
    
    
    
    
    /**
     * UPDATE-or-CREATE
     * 
     * !! This is in fact findOrCreateOne By Ipid And primaryKey .. aka updateOrCreateOneByIpidAndPrimaryKey
     * 
     * @param string $ipid
     * @param number $id
     * @param array $data
     * @return Doctrine_Record
     */
    public function findOrCreateOneByIpidAndId($ipid = '', $id = 0, array $data = array())
    {
    
        $primaryKey = $this->getTable()->getIdentifier();
        
        /*
         * magic Doctrine finder
         */
        $fn = "findOneByIpidAnd". Doctrine_Inflector::classify($primaryKey);
        
        /*
         * do not allow to overwrite the $primaryKey
         */
        if (isset($data[$primaryKey])) {
            unset($data[$primaryKey]);
        }
        
        /*
         * do not allow to overwrite the $ipid
         */
        if (isset($data['ipid'])) {
            unset($data['ipid']);
        }
        
        /*
         * prevent changes to fields populated by Timestamp Listener
         */
        if (isset($data['create_date']) || isset($data['change_date']) || isset($data['create_user']) || isset($data['change_user'])) {
            unset($data['create_date'], $data['change_date'], $data['create_user'], $data['change_user']);
        }
        
        
        if ( empty($id) || ! ($entity = $this->getTable()->{$fn}($ipid, $id, Doctrine_Core::HYDRATE_RECORD))) {
    
            $entity = $this->getTable()->create(['ipid' => $ipid]);
            
            $entity->assignDefaultValues(false);
            
        } else {
            /*
             * this is an update of $entity
             */
        }
        
        
        

        $this->_encryptData($data); // encrypt model->_encypted_columns
        
        //TODO maybe add a check ??? empty($data) is_array($data) count($data, COUNT_RECURSIVE)) ... what?
        $entity->fromArray($data); //update
        
    
        $entity->save(); //at least one field must be dirty in order to persist
    
        return $entity;
    }
    
    
    /**
     * encrypt model->_encypted_columns
     * 
     * @param unknown $data
     * @deprecated, use SoftEncryptListener and SoftDecryptListener
     */
    private function _encryptData(&$data)
    {
    
        if (empty($data) || ! is_array($data)) {
            return;
        }
    
        if ( is_null($this->_encypted_columns) || ! is_array($this->_encypted_columns)) {
            return;
        }
    
        $data_encrypted = Pms_CommonData::aesEncryptMultiple($data);
    
        foreach($data_encrypted as $column=>$val) {
            if (in_array($column, $this->_encypted_columns)) {
                $data[$column] = $val;
            }
        }
    }
    
    
    
    
    /**
     *
     * @param string $message
     * @param int $errorLevel  Optional
     */
    protected static function _log_info($message)
    {
        
        $num_args = func_num_args();
        
        $errorLevel = $num_args > 1 ? func_get_arg(1) : Zend_Log::INFO;
        
        if (is_null(self::$_logger)) {
            
            try {
                self::$_logger = Zend_Controller_Action_HelperBroker::getStaticHelper('Log');
            } catch (Zend_Controller_Action_Exception $e) {
                //die($e->getMessage());
            }
        }
    
        if (self::$_logger) {
    
            self::$_logger->log($message, $errorLevel);
    
        } else {
             
            $writer_dgp = new Zend_Log_Writer_Stream(APPLICATION_PATH . '/../public/log/app.log');
            $log_dgp = new Zend_Log($writer_dgp);
            $log_dgp->log($message, Zend_Log::INFO);
    
        }
    
    }
    
    
    /**
     *
     * @param string $message
     * @param int $errorLevel  Optional
     */
    protected static function _log_error($message = '')
    {
        $num_args = func_num_args();
        
        $errorLevel = $num_args > 1 ? func_get_arg(1) : Zend_Log::ERR;
        
        if (is_null(self::$_logger)) {
            
            try {
                self::$_logger = Zend_Controller_Action_HelperBroker::getStaticHelper('Log');
            } catch (Zend_Controller_Action_Exception $e) {
                //die($e->getMessage());
            }
        }
    
        if (self::$_logger) {
    
            self::$_logger->log($message, $errorLevel);
    
        } else {
    
            $writer_dgp = new Zend_Log_Writer_Stream(APPLICATION_PATH . '/../public/log/app.log');
            $log_dgp = new Zend_Log($writer_dgp);
            $log_dgp->log($message, Zend_Log::ERR);
    
        }
    }
    
    
}