<?php
/**
 * 
 * @author claudiu
 *
 * Jul 5, 20171:08:34 PM
 */
class Zend_View_Helper_Tabulate extends Zend_View_Helper_Abstract 
{
	/**
	 * Jul 5, 2017 @claudiu 
	 * taken from a stackoverflow.com post
	 * 
	 * $data[]['attributes'] = array(id, class) - added this key so you can set tr attributes
	 * $attribs['escaped'] = true|false - set this attrib if you want the raw data
	 * 
	 * returns the array in a html table format
	 * $this->tabulate($this) if you want to test
	 * 
	 * @param array $data
	 * @param array $attribs
	 * @return string
	 * 
	 * ISPC-2609 Ancuta 07.09.2020 - Added Thead 
	 */
	public function tabulate($data, $attribs = array()) 
	{
		$attribString = '';
		$escaped = true;
		foreach ( $attribs as $key => $value ) {
			$attribString .= ' ' . $key . '="' . $value . '"';
			if ($key == "escaped") {
				$escaped = (bool)$value;
			}
		}
		
		$html = "<table $attribString>\n";
		
		
		if( ! isset($attribs['no_header'])) {
		
			//first in array is the table header
			$header = array_shift ( $data );
			
			$header_attrib = "";
			$header_class = "head";
			if ( ! empty($header['attributes']) && is_array($header['attributes'])) {	
				foreach($header['attributes'] as $k_attr => $v_attr) {
					if($k_attr == "class"){	$header_class .= " ".$v_attr; } 
					else { $header_attrib .= ' ' . $k_attr .'="' .$v_attr .'"'; }
				}
				unset($header['attributes']);
			}
			
			$html .= "\t<thead><tr class=\"{$header_class}\" {$header_attrib} >\n";
			foreach ( $header as $cell ) {
				
				$cell_attrib = "";
				$escaped_cell = null;
				if ( is_array($cell) && is_array($cell['attributes']) && ! empty($cell['attributes'])) {
					foreach($cell['attributes'] as $k_attr => $v_attr) {
						if ($k_attr == "escaped") {
							$escaped_cell = (bool)$v_attr;
						}
						$cell_attrib .= " {$k_attr}=\"{$v_attr}\" ";
					}
					unset($cell['attributes']);
					$cell = $cell[0];
				}
	// 			$escapedCell = $this->view->escape ( $cell );
				$escapedCell = ! is_null($escaped_cell) ? ($escaped_cell ? $this->view->escape($cell) : $cell) : ($escaped ? $this->view->escape($cell) : $cell);
					
				$html .= "\t\t<th {$cell_attrib}>$escapedCell</th>\n";
			}
			$html .= "\t</tr></thead>\n";
		}
		
		
		//table body from the rest of the elements
		foreach ( $data as $row ) {
			
			$row_attrib = "";
			$row_attrib_colspan = "";
			if ( ! empty($row['attributes']) && is_array($row['attributes'])) {
				foreach($row['attributes'] as $k_attr => $v_attr) {
					$row_attrib .= " {$k_attr}=\"{$v_attr}\" ";
				}
				$row_attrib_colspan = empty($row['attributes']['colspan']) ? "": " colspan=\"{$row['attributes']['colspan']}\" ";
				unset($row['attributes']);
			}
			
			$html .= "\t<tr {$row_attrib}>\n";
			foreach ( $row as $cell ) {
				$cell_attrib = "";
				$escaped_cell = null;
				if( is_array($cell) && is_array($cell['attributes']) && ! empty($cell['attributes']) ) {	
					foreach($cell['attributes'] as $k_attr => $v_attr) {
						if ($k_attr == "escaped") {
							$escaped_cell = (bool)$v_attr; 
						}
						$cell_attrib .= " {$k_attr}=\"{$v_attr}\" ";
					}
					
					
					unset($cell['attributes']);
					$cell = $cell[0];
				}
				
				$cell = ! is_null($escaped_cell) ? ($escaped_cell ? $this->view->escape($cell) : $cell) : ($escaped ? $this->view->escape($cell) : $cell);
			
				$html .= "\t\t<td {$row_attrib_colspan} {$cell_attrib}>$cell</td>\n";
			}
			$html .= "\t</tr>\n";
		}
		
		$html .= '</table>';
		return $html;
	}
	
	private function _getAttributes(){
		
	}
}