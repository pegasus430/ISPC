<?php
use Dompdf\Dompdf;
use Dompdf\Options;

/**
 * Class AokprojectsKurzassessmentTable
 * ISPC-2625, AOK Kurzassessment, 07.07.2020, elena
 */
class AokprojectsKurzassessmentTable extends Pms_Doctrine_Table
{

    /**
     * Returns an instance of this class.
     *
     * @return AokprojectsKurzassessmentTable (object)
     */
    public static function getInstance()
    {
        return Doctrine_Core::getTable('AokprojectsKurzassessment');
    }


     /*
     * @param string $ipid
     * @param unknown $hydrateMode
     * @return mixed
     */
    public function findOneByIpidAndStatus($ipid = '', $status = '', $hydrateMode = Doctrine_Core::HYDRATE_ARRAY)
    {
        if (empty($ipid) || !is_string($ipid)) {
            return;
        }

        return self::getInstance()->createQuery()
            ->select('*')
            ->where('ipid = :ipid')
            ->andWhere('status= :status')
            ->fetchOne(array('ipid' => $ipid, 'status' => $status), $hydrateMode)
            ;


    }




}