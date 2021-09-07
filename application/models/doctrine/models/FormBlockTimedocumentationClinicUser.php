<?php
/**
 * Maria:: Migration CISPC to ISPC 22.07.2020
 */
Doctrine_Manager::getInstance()->bindComponent('FormBlockTimedocumentationClinicUser', 'MDAT');
class FormBlockTimedocumentationClinicUser extends BaseFormBlockTimedocumentationClinicUser
{

    private $editableFields = array('date','userid', 'username', 'groupid', 'groupname', 'minutes' ,'mins_patient','mins_family','mins_systemic', 'mins_profi', 'call_on_duty');//ISPC-2899,Elena,23.04.2021

    public function fill_timedocumentation_user($args){

        if (!isset($args)) //nothing to do
            return false;

        if(isset($args['date'])){
            $args['date'] =date('Y-m-d H:i:s', strtotime($args['date']));
        }

        $insert = new FormBlockTimedocumentationClinicUser();

        foreach ($this->editableFields as $value) {
            if (array_key_exists($value, $args)) {
                $insert->{$value} = $args[$value];
            }
        }


        return $insert;

    }

    /**
     * Find the list of users for the given FormBlockTimedocumentationClinic.
     *
     * @param $formid
     * @return the list of assigned FormBlockTimedocumentationClinicUser
     */
    public function get_list_user($formid)
    {

        $users = Doctrine_Query::create()
            ->select('*')
            ->from('FormBlockTimedocumentationClinicUser')
            ->where('form_id=?', $formid)
            ->andWhere('isdelete=0');
        return $users->fetchArray();
    }

    /**
     * Find the list of users for the given FormBlockTimedocumentationClinic.
     * create a list of unique, semicolon-separated user-names
     * return the list as string
     *
     */
    public function get_pretty_list_user($formid)
    {

        $erg = array();
        $sql = Doctrine_Query::create()
            ->select('*')
            ->from('FormBlockTimedocumentationClinicUser')
            ->where('form_id=?', $formid)
            ->andWhere('isdelete=0');
        $users = $sql->fetchArray();

        if(!$users)
            return false;

        foreach ($users as $user){
          $erg[$user['userid']] = $user['username'];
        }

        return implode('; ', $erg);


    }


}
?>
