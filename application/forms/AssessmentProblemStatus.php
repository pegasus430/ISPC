<?php
/**
 * 
 * @author claudiu✍ 
 * Jan 4, 2019
 *
 */
class Application_Form_AssessmentProblemStatus extends Pms_Form
{
	
    protected $_model = 'AssessmentProblemStatus';
    
	private $triggerformid = AssessmentProblemStatus::TRIGGER_FORMID;
	private $triggerformname = AssessmentProblemStatus::TRIGGER_FORMNAME;
	protected $_translate_lang_array = AssessmentProblemStatus::LANGUAGE_ARRAY;
	
	
	protected $_block_feedback_options = [
	];
	
	
	public function isValid($data)
	{
	    return parent::isValid($data);   
	}

    
    public function getColumnMapping($fieldName, $revers = false) 
    {

        $overwriteMapping = [
//             $fieldName => [ value => translation]
        ];
        
        
        $values = AssessmentProblemStatusTable::getInstance()->getEnumValues($fieldName);
        
        $values = array_combine($values, array_map("self::translate", $values));
        
        if (isset($overwriteMapping[$fieldName])) {
            $values = $overwriteMapping[$fieldName] + $values;
        }
        
        return $values;
        
    }
        
   
    
    
}

