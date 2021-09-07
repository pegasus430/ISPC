<?php
require_once ("Pms/Form.php");

class Application_Form_UserPageResults extends Pms_Form
{

    public function set_page_results($user, $client, $data)
    {
        if (isset($data['tab'])) {
            $tabname = $data['tab'];
        } else{
            $tabname = false;
        }
        $reset_filter = $this->reset_page_results($user, $client,$data['page'],$tabname);
        $usercf = new UserPageResults();
        $usercf->user = $user;
        $usercf->client = $client;
        $usercf->page = $data['page'];
        if ($tabname) {
            $usercf->tab = $tabname;
        }
        $usercf->results = $data['results'];
        $usercf->save();
        
        return true;
    }

    public function reset_page_results($user, $client, $page, $tab = false)
    {
        $q = Doctrine_Query::create()->update('UserPageResults ')
            ->set('isdelete', "1")
            ->where('user = "' . $user . '"')
            ->andWhere('client = "' . $client . '"')
            ->andWhere('page = "' . $page . '"');
        if ($tab) {
            $q->andWhere('tab = "' . $tab . '"');
        }
        $q->execute();
    }
}
?>