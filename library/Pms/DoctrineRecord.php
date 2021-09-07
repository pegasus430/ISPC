<?php
/**
 * 
 * @author claudiu
 * @date 11.01.2018
 *
 * add here functions that you want available for all your doctrine records
 * avoid naming your functions getXX(), because of magic __get and __set that Doctrine is using for formating the data
 *
 * please use Pms_Doctrine_Record
 * @deprecated
 */
abstract class Pms_DoctrineRecord extends Doctrine_Record
{
    


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
     * 
     * @param string $fieldName
     * @param string $value
     * @param array $data
     * @return boolean|Doctrine_Record
     */
    public function findOrCreateOneBy($fieldName = '', $value = null, array $data = array())
    {
        if (empty($fieldName) || ! $this->getTable()->hasColumn($fieldName)) {
            return false;
        }
        
        if ( is_null($value) || ! $entity = $this->getTable()->findOneBy($fieldName, $value, Doctrine_Core::HYDRATE_RECORD)) {
            //this is insert
            if ($fieldName != $this->getTable()->getIdentifier()) {
                $entity = $this->getTable()->create(array( $fieldName => $value));
            } else {
                $entity = $this->getTable()->create();
            }
            
            $entity->assignDefaultValues(false);
            
        } else {
            //this is update
        }
        
        unset($data[$this->getTable()->getIdentifier()]);
        
        //prevent changes to this
        unset($data['create_date']);
        unset($data['change_date']);
        unset($data['create_user']);
        unset($data['change_user']);
        
        //$this->_encryptData($data);
        $entity->fromArray($data); //update
         
        $entity->save(); //at least one field must be dirty in order to persist
       
        return $entity;
    }
    
    
    private function _encryptData(&$data)
    {
        
        if (empty($data) || ! is_array($data)) {
            return;
        }
        
        if ( ! property_exists($this, "_encypted_columns") || ! is_array($this->_encypted_columns)) {
            return;
        }
        
        $data_encrypted = Pms_CommonData::aesEncryptMultiple($data);
        
        foreach($data_encrypted as $column=>$val) {
            if (in_array($column, $this->_encypted_columns)) {
                $data[$column] = $val;
            }
        }
    }
    
    
}