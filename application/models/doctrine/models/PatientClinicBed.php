<?php
/**
 * Maria:: Migration CISPC to ISPC 22.07.2020
 */
Doctrine_Manager::getInstance()->bindComponent('PatientClinicBed', 'IDAT');

class PatientClinicBed extends BasePatientClinicBed
{


    /**
     * Find the current assigned Bed for a List of Patients.
     *
     * @param $ipids array of patient-ipids
     * @param $clientid
     * @return the current assigend beds
     */
    public function get_patients_beds_assignment($ipids, $clientid)
    {

        if (is_array($ipids)) {
            $ipids_arr = $ipids;
        } else {
            $ipids_arr = array($ipids);
        }

        $patbeds = Doctrine_Query::create()
            ->select('*')
            ->from('PatientClinicBed')
            ->whereIn('ipid', $ipids)
            ->andWhere('isdelete="0"')
            ->andWhere("valid_till= 0")
            ->andWhere('clientid= ?', $clientid);
        return $patbeds->fetchArray();
    }

    /**
     * Find the current assigned Bed for one Patient.
     *
     * @param $ipid ipid for a single patient
     * @param $clientid
     * @return the current assigend beds
     */
    public function get_patient_bed_assignment($ipid, $clientid)
    {

        $patbeds = $this->getTable()->createQuery()
            ->select('*')
            ->from('PatientClinicBed')
            ->where('ipid = "' .$ipid. '"')
            ->andWhere("valid_till= 0")
            ->andWhere('isdelete="0"')
            ->andWhere('clientid=?',$clientid);
         $bed = $patbeds->fetchOne(array(), Doctrine_Core::HYDRATE_ARRAY);

        return $bed;
    }



    /**
     * Finish the occupancy for the given bed, if it is not assigned to the given patient so far.
     *
     * @param $bedid the bed
     * @param $ipid the new resident. If he is still assigned, do nothing.
     * @param $clientid
     * @throws Exception
     */
    public function finish_bed_occupancy($bedid, $ipid, $clientid)
    {
         $del_stat = Doctrine_Query::create()
            ->select('id')
            ->from('PatientClinicBed')
            ->where('bed_id = ?', $bedid)
            ->andWhere("ipid != ?", $ipid)
            ->andWhere("valid_till= 0")
            ->andWhere('clientid = ?',$clientid);
        $rows = $del_stat->fetchArray();

        foreach ($rows as $row) {
            $pr = Doctrine::getTable('PatientClinicBed')->findOneBy('id', $row['id']);
            $pr->valid_till = date('Y-m-d H:i:s');
            $pr->save();
        }
    }

    /**
     * Finish the occupance for the given Patient, if he has assigned to another bed so far.
     *
     * @param $bedid the new bed the patient has assigned to
     * @param $ipid  the patient, who is to transfer
     * @param $clientid
     * @throws Exception
     */
    public function finish_patient_occupancy($bedid, $ipid, $clientid)
    {
        $del_stat = Doctrine_Query::create()
            ->select('id')
            ->from('PatientClinicBed')
            ->where('bed_id!=?',$bedid)
            ->andwhere('ipid=?',$ipid)
            ->andWhere("valid_till= 0")
            ->andWhere('clientid=?',$clientid);
        $rows = $del_stat->fetchArray();

        foreach($rows as $row){
            $pr = Doctrine::getTable('PatientClinicBed')->findOneBy('id', $row['id']);
            $pr->valid_till= date('Y-m-d H:i:s');
            $pr->save();
        }
    }


    /**
     * ISPC-2682, elena, 05.10.2020
     * Finish the occupance for the given Patient, if he has discharged.
     *
     *
     * @param $ipid  the patient, who is to discharge
     * @param $clientid
     * @param $discharge_date
     * @throws Exception
     */
    public function finish_patient_occupancy_to_discharge_date($ipid, $clientid, $discharge_date)
    {
        $del_stat = Doctrine_Query::create()
            ->select('id')
            ->from('PatientClinicBed')
            ->where('ipid=?',$ipid)
            ->andWhere("valid_till= 0")
            ->andWhere('clientid=?',$clientid);
        $rows = $del_stat->fetchArray();

        foreach($rows as $row){
            $pr = Doctrine::getTable('PatientClinicBed')->findOneBy('id', $row['id']);
            $pr->valid_till= $discharge_date;
            $pr->save();
        }
    }
    /**
     * Assigned a patient to an new bed, if he isn't assigned to this bed so far.
     *
     * @param $bedid the new bed the patient has assigned to
     * @param $ipid  the patient, who is to transfer
     * @param $clientid
     * @throws Exception
     */
    public function create_patient_occupancy($bedid, $ipid, $clientid)
    {
        $locationAll = Doctrine_Query::create()
            ->select('id')
            ->from('PatientClinicBed')
            ->where('bed_id=?',$bedid)
            ->andwhere('ipid=?',$ipid)
            ->andWhere("valid_till= 0")
            ->andWhere('clientid=?',$clientid);
        $locAll = $locationAll->fetchArray();
        if($ipid && count($locAll)==0) {
            $pl = new PatientClinicBed();
            $pl->ipid = $ipid;
            $pl->clientid = $clientid;
            $pl->bed_id = $bedid;
            $pl->valid_from = date('Y-m-d H:i:s');
            $pl->save();
        }
    }


}


?>