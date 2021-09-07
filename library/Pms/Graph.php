<?php
 require_once(APPLICATION_PATH.'/../library/Pms/JpGraph/jpgraph.php');
 require_once(APPLICATION_PATH.'/../library/Pms/JpGraph/jpgraph_bar.php');
 require_once(APPLICATION_PATH.'/../library/Pms/JpGraph/jpgraph_pie.php');
 require_once(APPLICATION_PATH.'/../library/Pms/JpGraph/jpgraph_pie3d.php');

class Pms_Graph extends Graph {
	public function piechart($gdata = array(), $gtitle = '', $pdf = false) {
		if(is_array($gdata) && sizeof($gdata) > 0){
			foreach($gdata as $slice) {
				$data[] = $slice['v'];
				$labels[] = $slice['l'];
			}
		}
			


		// Create the Pie Graph.
		$graph = new PieGraph(1000,400);
		$graph->SetShadow();

		// Set A title for the plot
		// 				$graph->title->Set('String labels with values');
// 		$graph->title->Set($imagedata['gtitle']);
		$graph->title->Set($gtitle);
		$graph->title->SetFont(FF_VERDANA,FS_BOLD,12);
		$graph->title->SetColor('black');
		$graph->legend->Pos(0.1,0.2);

		// Create pie plot
		$p1 = new PiePlot($data);
		$p1->SetCenter(0.5,0.5);
		$p1->SetSize(0.3);

		$p1->SetGuideLines();
		$p1->SetGuideLinesAdjust(1.4);

		// Setup the labels to be displayed
		$p1->SetLabels($labels);

		// This method adjust the position of the labels. This is given as fractions
		// of the radius of the Pie. A value < 1 will put the center of the label
		// inside the Pie and a value >= 1 will pout the center of the label outside the
		// Pie. By default the label is positioned at 0.5, in the middle of each slice.
		$p1->SetLabelPos(1);

		// Setup the label formats and what value we want to be shown (The absolute)
		// or the percentage.
		$p1->SetLabelType(PIE_VALUE_ABS);
		$p1->value->Show();
		$p1->value->SetFont(FF_ARIAL,FS_NORMAL,10);
		$p1->value->SetColor('black');



		// Add and stroke
		$graph->Add($p1);

		if($pdf === true) {
				
			$path =  dirname(APPLICATION_PATH).'/public/_graphs_pdf';
			$tmpfname = tempnam($path, 'PDF_');
			
			$graph->Stroke($tmpfname);
			
			return basename($tmpfname);
				
		} else {
			$graph->Stroke();
			exit;
		}
	}

}

?>