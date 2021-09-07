<?php
/**
 * 
 * @author claudiu
 * @date 11.01.2018
 *
 * @method createIfNotExistsOneBy($fieldName, $value = null, array $data = array())
 * @method findOrCreateOneBy($fieldName = '', $value = null, array $data = array())
 * 
 * KNOWN Limitation for both createIfNotExistsOneBy & findOrCreateOneBy :
 * if model does not have an auto-increment primary, and you want to use your unique field as primary, 
 * this fn will not work, nothing is created 
 * $entity = $this->__instance->create(($fieldName == $primaryKey ? [] : [$fieldName => $value])); 
 * prevents you...
 * 
 */
abstract class Pms_Doctrine_Table extends Doctrine_Table
{
    /**
     * 
     * @var Doctrine_Table
     */
    private $__instance = null;
    
    public static function getInstance() {}
    
    /**
     * 
     * @var unknown
     * @deprecated, use behaviours SoftEncrypt & SoftDecrypt 
     */
    protected $_encypted_columns = null;
    

    /**
     * 
     * @var Application_Controller_Helper_Log
     */
    protected static $_logger = null;
    
    
    
    
    /**
     * @since 18.01.2019 $fieldName & $value can be arrays

     *
     * @param string $fieldName
     * @param string $value
     * @param array $data
     * @param integer $hydrationMode
     * @return Doctrine_Record
     * @return mixed              Doctrine_Record or false if failed
     */
    public function createIfNotExistsOneBy($fieldName, $value = null, array $data = array())
    {
        
        $this->__instance = $this->getInstance();
        
        if (empty($fieldName)) {
            throw new Doctrine_Table_Exception('Field must allways have a name.');
        }
        
        $fieldName = is_array($fieldName) ? array_values($fieldName) : [$fieldName];
        
        $fieldNameQ = array_map('strtolower', $fieldName);
        
        $columnsDiff = array_diff($fieldNameQ, array_keys($this->__instance->getColumns()));
        
        if ( empty($fieldName) || ! empty($columnsDiff)) {
            throw new Doctrine_Table_Exception('Unknown column(s) '. implode(", ", $columnsDiff));
        }

        if (empty($value)) {
            return false; // as the name states...
        }
        
        $values = is_array($value) ? array_values($value) : [$value];
        
        
        $primaryKey = $this->__instance->getIdentifier(); // notice this assumes a one and only one field defined as 'primary' => true,
        
        /*
         * do not allow to overwrite the $primaryKey
         */
        if (isset($data[$primaryKey])) {
            unset($data[$primaryKey]);
        }
        
        /*
         * do not allow to overwrite the field by which you searched
         */
//         if (isset($data[$fieldName])) {
//             unset($data[$fieldName]);
//         }
        
        foreach ($fieldName as $k => $field) {
            if (isset($data[$field])) {
                unset($data[$field]);
            }
        }
        
        
        /*
         * prevent changes to fields populated by Timestamp Listener
         */
        if (isset($data['create_date']) || isset($data['change_date']) || isset($data['create_user']) || isset($data['change_user'])) {
            unset($data['create_date'], $data['change_date'], $data['create_user'], $data['change_user']);
        }
        
//         $this->__instance->getConnection()->beginTransaction();

        $q = $this->__instance
        ->createQuery("dctrn_find")
        ->forUpdate(true)
        ->limit(1);
        
        foreach ($fieldName as $k => $field) {    
            if (isset($values[$k])) {
                $q->andWhere($this->__instance->buildFindByWhere($field), $values[$k]);
            } else {
                $q->andWhere($this->__instance->getConnection()->expression->isNull($field));
            }
        }        
        
        if ( ! ($entity = $q->fetchOne(null, Doctrine_Core::HYDRATE_RECORD))) 
        {
            //new Doctrine_Record must be created
            $entity = $this->__instance->create();
            
            foreach ($fieldName as $k => $field) {
                
                if ($field != $primaryKey) {
                    if (isset($values[$k])) {
                        //$entity->{$field} = $values[$k]; //this are not _encryptData, add them to $data if you need them to be
                        $entity->set($field, $values[$k], false); // so we can bypass mutator just for this one
                        
                    } else {
                        /**
                         * TODO: this was not tested
                         */
                        $entity->set($field, null, false); // so we can bypass mutator just for this one
                    }
                }
            }
            
            
//             if ($fieldName != $primaryKey) {
//                 $entity->set($fieldName, $value, false); // so we can bypass mutator just for this one
//             }
            
            
            $entity->assignDefaultValues(false);
            
            $this->_encryptData($data); // encrypt model->_encypted_columns
            
            $entity->fromArray($data); //update
            
            $entity->save(); //at least one field must be dirty in order to persist
            
            //if any of the fields is a Doctrine_Expression Object, we must re-fetch that
            if ($hasDoctrineExpression = array_filter($entity->toArray(), function($field) {return ($field instanceof Doctrine_Expression);}))
            {
                $entity = $this->__instance->find($entity->$primaryKey);
            }
            
            
        }
        
        
//         if ($this->__instance->getConnection()->commit()) {
            
//         } else {
//             $this->__instance->getConnection()->rollback();
//         }
        
        return $entity;
    
    }
    
    
    
    /**
     * @since 27.08.2018 ->forUpdate(true)
     * @since 19.10.2018 $fieldName & $value can be arrays
     *
     * example:
     * ModelXTable::getInstance()->findOrCreateOneBy(['id', 'ipid', 'client'], [1], [ 'filed_X' => 'this', 'filed_Y' => 'is', 'filed_Y' => 'insert'])
     * ModelXTable::getInstance()->findOrCreateOneBy(['id', 'ipid', 'client'], [1,'xox', 77], [ 'filed_X' => 'this', 'filed_Y' => 'can be', 'filed_Y' => 'update or createnew'])
     * ModelXTable::getInstance()->findOrCreateOneBy('id', null, [ 'filed_X' => 'this', 'filed_Y' => 'is', 'filed_Y' => 'insert'])
     *  
     * !! it will return (boolean on error) !!
     *
     * @param string|array $fieldName
     * @param string|array $value
     * @param array $data
     * @return Doctrine_Record
     */
    public function findOrCreateOneBy($fieldName = '', $value = null, array $data = array())
    {
        $this->__instance = $this->getInstance();
        
        if (empty($fieldName)) {
            throw new Doctrine_Table_Exception('Field must allways have a name.');
        }
        
        $fieldName = is_array($fieldName) ? array_values($fieldName) : [$fieldName];
        
        $fieldNameQ = array_map('strtolower', $fieldName);
        
        $columnsDiff = array_diff($fieldNameQ, array_keys($this->__instance->getColumns()));
        
        if ( ! empty($columnsDiff)) {
            throw new Doctrine_Table_Exception('Unknown column(s) '. implode(", ", $columnsDiff));
        }
        
        $values = is_array($value) ? array_values($value) : [$value];
        
        $primaryKey = $this->__instance->getIdentifier();
        
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
    
            
            $q = $this->__instance
            ->createQuery("dctrn_find")
            ->forUpdate(true)
            ->limit(1);
    
            foreach ($fieldName as $k => $field) {
                
                if (isset($values[$k])) {
                    $q->andWhere($this->__instance->buildFindByWhere($field), $values[$k]);
                } else {
                    $q->andWhere($this->__instance->getConnection()->expression->isNull($field));
                }
            }
    
            $entity = $q->fetchOne(null, Doctrine_Core::HYDRATE_RECORD);
            
            
        }
        
        

        if ( $isInsert || ! $entity) {//this is insert
        
            $entity = $this->__instance->create();
      
            foreach ($fieldName as $k => $field) {
                if ($field != $primaryKey) {
                    if (isset($values[$k])) {
                        $entity->{$field} = $values[$k]; //this are not _encryptData, add them to $data if you need them to be
                    } else {
                        /**
                         * TODO: this was not tested 
                         */
                        $entity->set($field, null, false); // so we can bypass mutator just for this one
                    }
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
            
            
            //dd($entity->toArray(), $data);
        
        }
        
        
        $this->_encryptData($data); // encrypt model->_encypted_columns
        
        $entity->fromArray($data); //update
         
        $entity->save(); //at least one field must be dirty in order to persist

      
        //if any of the fields is a Doctrine_Expression Object, we must re-fetch that
        if ($hasDoctrineExpression = array_filter($entity->toArray(), function($field) {return ($field instanceof Doctrine_Expression);})) 
        {
            $entity = $this->__instance->find($entity->$primaryKey);
        }
        
    
        return $entity;
    }
    
    
    
    
    
    
    
    
    /**
     * TODO : NOT done.. maybe leave with the name findOrCreateOneByIpidAndPrimaryKey
     * UPDATE-or-CREATE
     * 
     * !! This is in fact findOrCreateOne By Ipid And primaryKey .. aka updateOrCreateOneByIpidAndPrimaryKey ... aka findOrCreateOneByIpidAndId
     * 
     * @param string $ipid
     * @param number $id
     * @param array $data
     * @return Doctrine_Record
     */
    public function updateOrCreateOneByIpidAndPrimaryKey($ipid = '', $id = 0, array $data = array())
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
     * @deprecated, use behaviours SoftEncrypt & SoftDecrypt 
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
    public static function _log_info($message)
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
    public static function _log_error($message = '')
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
    
    
}