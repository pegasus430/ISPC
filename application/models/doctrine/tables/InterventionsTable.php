<?php

/**
 * Class InterventionsTable
 * ISPC-2530, ELSA . Interventionen, elena, 31.07.2020
 *  //Maria:: Migration CISPC to ISPC 20.08.2020
 */
class InterventionsTable extends Pms_Doctrine_Table
{

    /**
     * Returns an instance of this class.
     *
     * @return InterventionsTable (object)
     */
    public static function getInstance()
    {
        return Doctrine_Core::getTable('Interventions');
    }

    public function findOneByIpid( $ipid = '', $hydrationMode = Doctrine_Core::HYDRATE_RECORD )
    {
        if (empty($ipid) || ! is_string($ipid)) {

            return;

        } else {
            return self::getInstance()->createQuery('interv')
                ->where('ipid = ?')
                ->orderBy('id DESC') // just in case the delete is not ok
                ->limit(1)
                ->fetchOne(array($ipid), $hydrationMode);
        }
    }

}