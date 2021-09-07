<?php
//ISPC-2694, elena,14.12.2020

/**
 * Class AnamneseTable
 */
class AnamneseTable extends Pms_Doctrine_Table
{
    /**
     * Returns an instance of this class.
     *
     * @return AnamneseTable (object)
     */
    public static function getInstance()
    {
        return Doctrine_Core::getTable('Anamnese');
    }
}