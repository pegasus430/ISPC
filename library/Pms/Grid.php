<?php
/**
 * isset($column_output[0][0]) was added.. the correct way would be if (preg_match...)
 */
// require_once 'Zend/View.php';

	class Pms_Grid {

		public function Pms_Grid($a_array, $noofcols, $totalrows, $gridfile)
		{
			$this->a_array = $a_array;
			$this->noofcols = $noofcols;
			$this->totalrows = $totalrows;
			$this->rowcount = count($a_array);
			//$this->gridfile=realpath(APPLICATION_PATH.'/grids/'.$gridfile);
			$this->gridfile = $gridfile;

			$this->gridview = new Zend_View();
			$this->gridview->setScriptPath(APPLICATION_PATH . "/grids/");
		}

		public function renderGrid()
		{

			$view = Zend_Layout::getMvcInstance()->getView();

			$noofcols = $this->noofcols;
			$gridfile = $this->gridfile;

			if($noofcols == 0)
			{
				$noofcols++;
			}

			$totalrows = count($this->a_array);

			$res_file_path = RES_FILE_PATH; //set correct path(s) to grids

			$newgridfile = $this->gridview->render($this->gridfile);

			/* $file=fopen($gridfile,"r") or exit("Unable to open file!");
			  while(!feof($file))
			  {
			  $newgridfile.=fgets($file);
			  }
			  fclose($file); */



			/* check the gridrow content */

			preg_match_all("!(<gridrow([^>]*>))(.*?)(</gridrow\s*>)!is", $newgridfile, $row_output, PREG_SET_ORDER);
			//print_r($row_output);
			/* check if script exists */

			preg_match_all("!(<phpscript([^>]*>))(.*?)(</phpscript\s*>)!is", $row_output[0][0], $script_output, PREG_SET_ORDER);

			if(strlen($script_output[0][0]))
			{
				$phpscript = $script_output[0][3];
				$newgridfile = preg_replace("!(<phpscript([^>]*>))(.*?)(</phpscript\s*>)!is", "", $newgridfile);
			}

			preg_match_all("!(<phprowscript([^>]*>))(.*?)(</phprowscript\s*>)!is", $row_output[0][0], $script_output, PREG_SET_ORDER);

			if(isset($script_output[0][0]) && strlen($script_output[0][0]))
			{
				$phprowscript = $script_output[0][3];
				$newgridfile = preg_replace("!(<phprowscript([^>]*>))(.*?)(</phprowscript\s*>)!is", "", $newgridfile);
			}


			/* get the grid row */
			preg_match_all("!(<gridrow([^>]*>))(.*?)(</gridrow\s*>)!is", $newgridfile, $row_output, PREG_SET_ORDER);

			//print_r($row_output);

			/* check if column tag exists */
			preg_match_all("!(<gridcolumn([^>]*>))(.*?)(</gridcolumn\s*>)!is", $row_output[0][0], $column_output, PREG_SET_ORDER);

			//print_r($column_output);

			
			if(isset($column_output[0][0]) &&  strlen($column_output[0][0]) > 0)
			{
				preg_match_all("!(<gridrow([^>]*>))(.*?)(<gridcolumn\s*>)!is", $newgridfile, $getrowstart_output, PREG_SET_ORDER);
				$gridcolumn = $column_output[0][3];
				$gridrow = $getrowstart_output[0][3];

				preg_match_all("!(</gridcolumn([^>]*>))(.*?)(</gridrow\s*>)!is", $newgridfile, $getrowend_output, PREG_SET_ORDER);
				$gridrowend = $getrowend_output[0][3];
			}
			else
			{
				$gridcolumn = $row_output[0][3];
				$gridrow = "";
			}

			//preg_match_all('/\[\[(.*)\]\]/', $gridcolumn, $variable_output,PREG_SET_ORDER);

			preg_match_all('/\[\[([a-z_A-Z0-9])*\]\]/', $gridcolumn, $firstvariable_output, PREG_SET_ORDER);


			//print_r($firstvariable_output);
			foreach($firstvariable_output as $vars => $values)
			{
				preg_match_all('/\[\[(.*)\]\]/', $values[0], $temp_output, PREG_SET_ORDER);
				//print_r($temp_output);
				$variable_output[] = $temp_output[0];
			}

			preg_match_all('/\[\[([a-z_A-Z0-9])*\]\]/', $gridrow, $rowvariable_output, PREG_SET_ORDER);


			//print_r($firstvariable_output);
			foreach($rowvariable_output as $vars => $values)
			{
				preg_match_all('/\[\[(.*)\]\]/', $values[0], $temp_output, PREG_SET_ORDER);
				//print_r($temp_output);
				$rowvariable_output[] = $temp_output[0];
			}

			//print_r($variable_output);

			$rows = (int) ($totalrows / $noofcols);
			if(($totalrows % $noofcols) > 0)
			{
				$rows++;
			}

			if(!is_array($this->a_array))
			{
				$this->a_array = array();
			}

			reset($this->a_array);

			for($i = 0; $i < $rows; $i++)
			{

				$newgridrow = isset($gridrow) ? $gridrow : '';
				
                if (isset($phprowscript) && ! empty($phprowscript)) {
				    eval($phprowscript);
                }

				foreach($rowvariable_output as $vars => $values)
				{
				    if ( ! isset(${$values[1]})) {
				        ${$values[1]} = '';
				    }
				    
					$newrowstr = str_replace("[[" . $values[1] . "]]", ${$values[1]}, $newgridrow);

					$newgridrow = $newrowstr;
				}
				
				if ( ! isset ($tempgrid)) {
				    $tempgrid = '';
				}
				
				$tempgrid .= $newgridrow;

				for($j = 0; $j < $noofcols; $j++)
				{

					$rowno = ($i * $noofcols) + $j;

					if($rowno < $this->rowcount)
					{
						//mysql_data_seek($this->result,$rowno);
						//$rowdata = mysql_fetch_assoc($this->result);
						//$rowdata = $this->a_array[ key($array)];
						$rowdata = current($this->a_array);
						//$rowdata = $this->a_array[$rowno];



						$tempgridcolumn = $gridcolumn;

						if(is_array($rowdata))
						{
							foreach($rowdata as $field => $value)
							{
								$$field = $value;
							}
						}
						if (isset($phpscript) && ! empty($phpscript)) {
						    eval($phpscript);
						}

						foreach($variable_output as $vars => $values)
						{

						    if ( ! isset(${$values[1]})) {
						        ${$values[1]} = '';
						    }
						    
							$newstr = str_replace("[[" . $values[1] . "]]", ${$values[1]}, $tempgridcolumn);

							$tempgridcolumn = $newstr;
						}
					}
					else
					{
					    if (isset($phpscript) && ! empty($phpscript)) {
						  eval($phpscript);
					    }
						/* $tempgridcolumn = $gridcolumn;
						  foreach($variable_output as $vars=>$values)
						  {

						  $newstr = str_replace("[[".$values[1]."]]","&nbsp;",$tempgridcolumn);
						  $tempgridcolumn = $newstr;
						  } */
						$tempgridcolumn = $blank;
					}
					$tempgrid .= $tempgridcolumn;
					next($this->a_array);
				}



				$tempgrid .= isset($gridrowend) ? $gridrowend : '';
			}
			//echo $tempgrid;
			/* put  the grid in the original file */
			preg_match_all("!(<gridrow([^>]*>))(.*?)(</gridrow\s*>)!is", $newgridfile, $row_output, PREG_SET_ORDER);

			//print_r($row_output);
			if (! isset($tempgrid)) {
			    $tempgrid = '';
			}
			$grid = str_replace("<gridrow>" . $row_output[0][3] . "</gridrow>", $tempgrid, $newgridfile);
			//$grid = preg_replace("!(<gridrow([^>]*>))(.*?)(</gridrow\s*>)!is",$tempgrid,$newgridfile);
			//echo $grid;

			return $grid;
		}

		public function navigation($navdesign, $limit)
		{
			$totalrows = (int) ($this->totalrows / $limit);

			if($totalrows % $limit > 0)
			{
				$totalrows++;
			}

			for($i = 0; $i < $totalrows; $i++)
			{
				$a_nav[] = array("text" => ($i + 1), "pageno" => $i);
			}


			$nav = new Pms_Grid($a_nav, 50, count($a_nav), 'navigation/' . $navdesign);
			return $nav->renderGrid();
		}

		function blocknavigation($navdesign, $pageperblock, $pageno, $limit)
		{

			//$blockno = (int)($pageno/$pageperblock);
			//$noofblocks = (int)(($total)/($pageperblock*$limit));



			$totalpages = (int) ($this->totalrows / $limit);
			if($this->totalrows % $limit > 0)
			{
				$totalpages++;
			}


			$noofblocks = (int) (($this->totalrows) / ($pageperblock * $limit));

			if($this->totalrows % ($pageperblock * $limit) > 0)
			{
				$noofblocks++;
			}





			$blockno = (int) ($pageno / $pageperblock);



			if($blockno < $noofblocks - 1)
			{
				$showpages = $pageperblock;
			}
			else
			{
				if($totalpages % $pageperblock > 0)
				{
					$showpages = $totalpages % $pageperblock;
				}
				else
				{
					$showpages = $pageperblock;
				}
			}



			$a_nav = array();

			if($blockno > 0)
			{
				$a_nav[] = array("text" => "Prev", "pageno" => ($blockno * $pageperblock) - 1);
			}
			//else{$a_nav[] = array("text"=>"Prev","pageno"=>($blockno*$pageperblock)-1);}

			for($i = $blockno * $pageperblock; $i < ($blockno * $pageperblock) + $showpages; $i++)
			{
				//$blocknavigation .= '&nbsp;|&nbsp;<a href="'.$baselocation.$reqpage.".html?startrow=".$i.$qst.'">'.($i+1).'</a>'; 

				$a_nav[] = array("text" => ($i + 1), "pageno" => $i);
			}

			if($blockno < ($noofblocks - 1))
			{
				$a_nav[] = array("text" => "Next", "pageno" => $i);
			}

			$nav = new Pms_Grid($a_nav, 50, count($a_nav), 'navigation/' . $navdesign);

			return $nav->renderGrid();
		}

		public function dotnavigation($navdesign, $pageperblock = 5, $pageno, $limit = 0)
		{

			if (is_null($limit)){
				$limit = 0;
			}
			$totalpages = (int) ($this->totalrows / $limit);
			if( $limit != 0 && $this->totalrows % $limit > 0)
			{
				$totalpages++;
			}

			$noofblocks = (int) (($this->totalrows) / ($pageperblock * $limit));


			if($totalpages < 2)
			{
				return "";
			}

			if($this->totalrows % ($pageperblock * $limit) > 0)
			{
				$noofblocks++;
			}

			$blockno = (int) ($pageno / $pageperblock);

			if($blockno < $noofblocks - 1)
			{
				$showpages = $pageperblock;
			}
			else
			{
				if($totalpages % $pageperblock > 0)
				{
					$showpages = $totalpages % $pageperblock;
				}
				else
				{
					$showpages = $pageperblock;
				}
			}
			$a_nav = array();

			if($totalpages > 1)
			{
				$a_nav[] = array("text" => "First", "pageno" => 0);
			}

			//else{$a_nav[] = array("text"=>"Prev","pageno"=>($blockno*$pageperblock)-1);}


			$startpage = $pageno + 1 - $pageperblock >= 0 ? $pageno - $pageperblock + 2 : 0;

			$showpages = $pageno + 3 > $pageperblock ? $pageperblock : $pageno + 3;

			$startpage = $pageno + 1 - $pageperblock >= 0 ? $pageno - $pageperblock + 2 : 0;



			if($totalpages > $pageperblock && $totalpages - $pageperblock < $pageno)
			{

				$startpage = $totalpages - $pageno <= 4 ? $pageno - 2 : $pageno;
			}

			$nopages = $showpages + $startpage;

			if($nopages > $totalpages)
			{

				$nopages = $totalpages;
			}

			if($pageno > 0)
			{
				$a_nav[] = array("text" => "Prev", "pageno" => $pageno - 1);
			}
			//$showpages = $totalpages-$pageno<=3 ?
// 		if($startpage!=0){$a_nav[] = array("text"=>"1,... ","pageno"=>0);}
			if($startpage != 0)
			{
				$a_nav[] = array("text" => "1 ... ", "pageno" => 0);
			}

			for($i = $startpage; $i < $nopages; $i++)
			{
				//$blocknavigation .= '&nbsp;|&nbsp;<a href="'.$baselocation.$reqpage.".html?startrow=".$i.$qst.'">'.($i+1).'</a>'; 

				$a_nav[] = array("text" => $sep . ($i + 1), "pageno" => $i);
// 			$sep = ",";
			}


// 		if($nopages!=$totalpages){$a_nav[] = array("text"=>" ...,".$totalpages,"pageno"=>$totalpages-1);}
			if($nopages != $totalpages)
			{
				$a_nav[] = array("text" => " ... " . $totalpages, "pageno" => $totalpages - 1);
			}
			if($pageno < $totalpages - 1)
			{
				$a_nav[] = array("text" => "Next", "pageno" => $pageno + 1);
			}
			if($totalpages > 0 && $pageno != $totalpages - 1)
			{

				$a_nav[] = array("text" => "Last", "pageno" => $totalpages - 1);
			}
			$nav = new Pms_Grid($a_nav, 50, count($a_nav), 'navigation/' . $navdesign);
			$nav->gridview->lastpage = $totalpages;
			return $nav->renderGrid();
		}

	}

?>