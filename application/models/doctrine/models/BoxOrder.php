<?php
Doctrine_Manager::getInstance()->bindComponent('BoxOrder', 'MDAT');

class BoxOrder extends BaseBoxOrder
{
    
    /**
     * !!!!! this is for INFO !!!
     * ! you too, must write here :I have used the next numbers... !
     */
    public static function boxcol_Defaults()
    {
        return [
            
            //old patient/details used 1 and 2 for left and right
            
            //patientnew/patientdetails
            'patientdetailsAction' => [
                'left' => 101,
                'right' => 102,        
            ],
            
            'versorgerAction' => [
                'left' => 201,
                'right' => 202,        
            ],
            
            //roster/dayplanningnew
            'dayplanningnewAction' => [
                301, 302, 303, //users
                304, 305, 306 //pseudogroups
            ]
            
        ];
    }
    

    /**
     * @deprecated - this is magic @see BoxOrder::fetchUserCol
     */
    public function getBoxOrder($userid, $col)
    {
        $drop = Doctrine_Query::create()->select("boxid")
            ->from('BoxOrder')
            ->where("userid= ?", $userid)
            ->andWhere("boxcol =  ? ", $col)
            ->orderBy("boxorder ASC");
        
        $loc = $drop->execute();
        if ($loc) {
            $livearr = $loc->toArray();
            return $livearr;
        }
    }

    public function deleteOrder($userid, $col)
    {
        $q = Doctrine_Query::create()->delete()
            ->from('BoxOrder')
            ->where("userid = ?", $userid)
            ->andWhere("boxcol = ? ", $col);
        $r = $q->execute();
    }
    
    public static function fetchUserCol($userid = 0, $cols = array())
    {
        if (empty($userid) || empty($cols)) {
            return;
        }
        
        $cols = is_array($cols) ? $cols : [$cols];
        
        return Doctrine_Query::create()
        ->from('BoxOrder')
        ->select("boxcol, boxid, boxorder")
        ->where("userid = ?", $userid)
        ->andWhereIn("boxcol", $cols)
        ->orderBy("boxorder ASC")
        ->fetchArray();
        
    }
}

?>