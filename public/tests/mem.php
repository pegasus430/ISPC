<?php
//phpinfo();
$m=new Memcached;
$m->addServer("62.138.248.67", 11211);
print_r($m->getStats());
?>