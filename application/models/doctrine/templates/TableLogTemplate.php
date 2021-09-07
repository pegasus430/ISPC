<?php

class TableLogTemplate extends Doctrine_Template {
    
    public function setTableDefinition()
    {
        $this->addListener(new TableLogListener());
    }
    
}

?>
