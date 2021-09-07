<?php
/**
 * 
 * @author claudiu 
 * Jul 6, 2018
 * 
 * TODO: Doctrine_Record_Filter
 * http://doctrine1.readthedocs.io/en/latest/en/manual/defining-models.html
 * 
 *  public function setUp()
    {
        // ...
        $this->unshiftFilter(new MyRecordFilter());
    }
 *
 */
class MyRecordFilter extends Doctrine_Record_Filter
{
    public function filterSet(Doctrine_Record $record, $name, $value)
    {
        // try and set the property
        throw new Doctrine_Record_UnknownPropertyException(sprintf(
            'Unknown record property / related component "%s" on "%s"',
            $name,
            get_class($record)
        ));
    }

    public function filterGet(Doctrine_Record $record, $name)
    {
        // try and get the property
        throw new Doctrine_Record_UnknownPropertyException(sprintf(
            'Unknown record property / related component "%s" on "%s"',
            $name,
            get_class($record)
        ));
    }
}