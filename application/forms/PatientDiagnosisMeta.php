<?php

require_once("Pms/Form.php");

class Application_Form_PatientDiagnosisMeta extends Pms_Form
{
	public function validate($post)
	{

	}

	public function InsertData($post)
	{

	}

	public function UpdateData($post)
	{
		foreach($post['hidd_ids'] as $key=>$val)
		{
			if($val>0)
			{

				$loc = Doctrine_Query::create()
				->select("*")
				->from('PatientDiagnosisMeta')
				->where("diagnoid='".$val."'");


				$locexe = $loc->execute();
				if($locexe)
				{
					$diagno = $locexe->toArray();


					if(count($diagno)>0)
					{
						$tc = 0;
						$cnt = 0;
						$cnt  = count($post['meta_title'][$key]) - count($diagno);


						foreach($diagno as $k=>$v)
						{

							$loc = Doctrine_Query::create()
							->update("PatientDiagnosisMeta")
							->set('metaid',"'".$post['meta_title'][$key][$k]."'")
							->where("id='".$diagno[$k]['id']."' and ipid = '".$diagno[$k]['ipid']."'");
							$loc->execute();

							if($v['metaid']!=$post['meta_title'][$key][$k])
							{
								Pms_Triggers::updateMetaDiagnosistocourse($post['meta_title'][$key][$k],$post['ipid']);
							}

							$tc = $k;

						}

						$triggerarr  = array();


						for($i=1;$i<=$cnt;$i++) //if new value added
						{
							$cust = new PatientDiagnosisMeta();
							$cust->ipid = $post['ipid'];
							$cust->metaid = $post['meta_title'][$key][$tc+$i];
							$cust->diagnoid = $post['hidd_ids'][$key];
							$cust->save();

							$triggerarr['meta_title'][$key][$tc+$i] = $post['meta_title'][$key][$tc+$i];
						}

						$triggerarr['ipid'] = $post['ipid'];
						Pms_Triggers::addMetaDiagnosistocourse($triggerarr);


					}
					else
					{
						$triggerarr = array();
						foreach($post['meta_title'][$key] as $k=>$v)
						{
							if($v>0 && $post['hidd_ids'][$key]>0)
							{
								$cust = new PatientDiagnosisMeta();
								$cust->ipid = $post['ipid'];
								$cust->metaid = $v;
								$cust->diagnoid = $post['hidd_ids'][$key];
								$cust->save();
									
								$triggerarr['meta_title'][$key][$k] = $v;
							}
						}
							
						$triggerarr['ipid'] = $post['ipid'];
						Pms_Triggers::addMetaDiagnosistocourse($triggerarr);
					}

			 }//val>0
			}
		}
	}
}

?>