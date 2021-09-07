<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('SurveyAnswersFaces', 'IDAT');

/**
 * BaseSurveyAnswersFaces
 * ISPC-2695 Ancuta 04.11.2020
 */
abstract class BaseSurveyAnswersFaces extends Pms_Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('survey_answers_faces');
    }    
            

    public function setUp()
    {
        parent::setUp();
        
    }
}