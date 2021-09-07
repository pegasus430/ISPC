<?php
/**
 * Maria:: Migration CISPC to ISPC 22.07.2020
 */
Doctrine_Manager::getInstance()->bindComponent('FormBlockTimedocumentationClinic', 'MDAT');
class FormBlockTimedocumentationClinic extends BaseFormBlockTimedocumentationClinic
{
    private $args = array();
    private $editableFields = array('contact_form_date', 'patient_case_status_id', 'patient_case_type', 'minutes' ,'mins_patient','mins_family','mins_systemisch', 'mins_profi');

    public function create_timedocumentation($ipid, $contactformid, $args){

        if (!isset($args)) //nothing to do
            return false;


        $insert = new FormBlockTimedocumentationClinic();

        $insert->contact_form_id = $contactformid;
        $insert->ipid = $ipid;

        //Start TODO-4163
        $logininfo = new Zend_Session_Namespace('Login_Info');

        //ISPC-2815
        $clientid = $logininfo->clientid;
        $insert->clientid=$clientid;
        //END TODO-4163

        if(isset($args['timelog'])) {
            $child = new FormBlockTimedocumentationClinicUser();
            foreach ($args['timelog'] as $timelog){
                $insert->FormBlockTimedocumentationClinicUser[] = $child->fill_timedocumentation_user($timelog);
            }
        }

        foreach ($this->editableFields as $value) {
            if (array_key_exists($value, $args)) {
                $insert->{$value} = $args[$value];
            }
        }


        $insert->save();

    }

    /**
     * Delete the entity FormBlockTimedocumentation with the given
     * contact_form_id and ipid.
     * Also delete the child-Elements FormBlockTimedocumentationUser, because there
     * is ab CASCADIAN DELETE definied.
     * Exceutes a soft delete for both tables, so the marker 'isdelete' ist set to '1'.
     * @param $contactformid
     * @param $ipid
     * @return boold
     */
    public function delete_timedocumentation($contactformid, $ipid)
    {
        if (empty($contactformid))
            return false;

        $sql = Doctrine_Query::create()
            ->select('*')
            ->from('FormBlockTimedocumentationClinic')
            ->where('contact_form_id=?', $contactformid)
            ->andWhere('ipid=?', $ipid);
        $entity = $sql->fetchOne();
        $entity->delete();


        return true;
    }

    /**
     * Find the timedocumentation by statusid.
     *
     * @param $statusid
     * @param $clientid
     * @return array of Timedocumentation with the given case_id
     */
    public function find_timedocumentation_by_case_id($case_id, $ipid)
    {

        $patstatus = Doctrine_Query::create()
            ->select('*')
            ->from('FormBlockTimedocumentationClinic')
            ->where('ipid=?', $ipid)
            ->andWhere('patient_case_status_id=?', $case_id);
        return $patstatus->fetchArray();
    }


}
?>
