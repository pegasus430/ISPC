<?php
/**
 * 
 * @author claudiu 
 * Jun 5, 2018
 * 
 * $manager->registerHydrator('my_hydrator', 'Doctrine_Hydrator_MyHydrator');
 * $query->fetchOne(array(), 'my_hydrator');
 * ~ <=>
 * setHydrationMode(Doctrine_Core::HYDRATE_SCALAR) 
 *
 *
 * https://www.doctrine-project.org/projects/doctrine-orm/en/2.6/cookbook/aggregate-fields.html
 */
class Pms_Doctrine_Hydrator_MyHydrator extends Doctrine_Hydrator_ArrayHierarchyDriver
{
    public function hydrateResultSet($stmt)
    {
        $results = parent::hydrateResultSet($stmt);
        $array = array();

        $array[] = array('User' => array(
            'id'         => $results['User']['id'],
            'username'   => $results['User']['username'],
            'first_name' => $results['Profile']['first_name'],
            'last_name'  => $results['Profile']['last_name'],
        ));

        return $array();
    }
}