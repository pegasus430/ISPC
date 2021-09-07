<?php

/**
 * Class UserRequest
 *
 * ISPC-2913,Elena,11.05.2021
 */
class UserRequest extends BaseUserRequest
{
    const DEACTIVATE_USER_REQUEST = 'deactivateuserrequest';
    /**
     * for table generating purposes only
     * helpful if the table have more than 10 fields
     *
     * @return string
     * @throws Doctrine_Export_Exception
     * @throws Doctrine_Table_Exception
     */
    public function getSql(){

        $data = $this->getTable()->getExportableFormat();
        $export = new Doctrine_Export();
        $sql = $export->createTableSql($data['tableName'], $data['columns'], $data['options']);
        return ($sql) ;
    }

    public function findDeactivateRequestsForUser($user_id, $with_solved = false){
        $source = self::DEACTIVATE_USER_REQUEST;
        $drop =  Doctrine_Query::create()
            ->select('*')
            ->from('UserRequest')
            ->where("subject_id = ?", $user_id)
        ;
        $drop->andWhere('request_source = ?', $source);
        if(!$with_solved){
            $drop->andWhere('is_solved = ?', 0);
        }


        $droparray = $drop->fetchArray();
        return $droparray;
    }

    public function saveDeactivateRequest($user_id, $deactivate_date, $comment, $clientid){
        $ur = new UserRequest();
        $ur->subject_id = $user_id;
        $ur->note = $comment;
        $ur->request_date = $deactivate_date;
        $ur->request_source = self::DEACTIVATE_USER_REQUEST;
        $ur->clientid = $clientid;
        $ur->save();
    }

    public function markSolved($id){
        $upd =  Doctrine_Query::create()
            ->update('UserRequest')
            ->set('is_solved', 1)
            ->where('id=?', $id)
            ->execute();
    }

}