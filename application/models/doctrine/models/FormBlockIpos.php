<?php
Doctrine_Manager::getInstance()->bindComponent('FormBlockIpos', 'MDAT');
class FormBlockIpos extends BaseFormBlockIpos
{

	public function getPatientFormBlockIpos ( $ipid, $contact_form_id, $allow_deleted = false)
	{

		$groups_sql = Doctrine_Query::create()
		    ->select('*')
		    ->from('FormBlockIpos')
		    ->where('ipid LIKE "' . $ipid . '"')
		    ->andWhere('contact_form_id ="' . $contact_form_id . '"');
		if(!$allow_deleted)
		{
			$groups_sql->andWhere('isdelete = 0');
		}
		
		$groupsarray = $groups_sql->fetchArray();


		if ($groupsarray)
		{
			return $groupsarray;
		}
	}

    public static function mapToScorevalues($in){
        $out=6-intval($in);
        if($out<0){
            $out="";
        }
        if($out>4){
            $out="";
        }
        return $out;
    }


    public function getMostRecentMainprobs($ipid, $getdate=0){
        $Q= Doctrine_Query::create()
            ->select('*')
            ->from('FormBlockIpos')
            ->where("isdelete=0")
            ->andWhere('ipid LIKE "' . $ipid . '"')
            ->orderBy('id DESC');
        $patient_ipos_hist_values = $Q->fetchArray();

        $mps=array();
        $lastdate=0;
        foreach ($patient_ipos_hist_values as $val){
            $mps=array($val['ipos1a'],$val['ipos1b'],$val['ipos1c']);
            if ($lastdate==0){
                $lastdate = $val['create_date'];
            }
            break;
        }
        if ($getdate){
            return array($mps, $lastdate);
        }
        return $mps;
    }

    public function getIposHistory($ipid,$rendertable=0){
        $Q= Doctrine_Query::create()
            ->select('*')
            ->from('FormBlockIpos')
            ->where("isdelete=0")
            ->andWhere('ipid LIKE "' . $ipid . '"')
            ->orderBy('date DESC');
        if($rendertable==1){
            $Q->andWhere('special IS NULL');
        }
        $patient_ipos_hist_values = $Q->fetchArray();


        $Q= Doctrine_Query::create()
            ->select('*')
            ->from('FormBlockKeyValue')
            ->where("isdelete=0")
            ->andWhere("block='ipos_add'")
            ->andWhere('ipid LIKE "' . $ipid . '"')
            ->orderBy('id DESC');
        $iposaddarr=$Q->fetchArray();

        foreach ($patient_ipos_hist_values as $iposh_k=>$iposh_v){
            foreach($iposaddarr as $ipadd_v){
                if($ipadd_v['contact_form_id'] == $iposh_v['contact_form_id']){
                    $patient_ipos_hist_values[$iposh_k]['ipos_add'][$ipadd_v['k']]=$ipadd_v['v'];
                }
            }
        }

        if($rendertable){
//             $iposview = new Zend_View();
//             $iposview->setScriptPath(APPLICATION_PATH."/views/scripts/templates/");
//             $iposview->patient_ipos_hist_values=$patient_ipos_hist_values;
//             $renderedtable=$iposview->render('iposhist.html');

//             return array($patient_ipos_hist_values, $renderedtable);
        }

        return $patient_ipos_hist_values;
    }

}
?>