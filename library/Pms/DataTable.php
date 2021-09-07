<?

	class Pms_DataTable{
	
		
	
		public static function search($haystack, $needle, $index = null)
		{
			$aIt     = new RecursiveArrayIterator($haystack);
			$it    = new RecursiveIteratorIterator($aIt);
		   
			while($it->valid())
			{       
				if (((isset($index) AND ($it->key() == $index)) OR (!isset($index))) AND ($it->current() == $needle)) {
					return $aIt->key();
				}
			   
				$it->next();
			}
		   
			return false;
		}
		
		
		
		public static function sortArray($arr,$sortby,$order) 
		{
		 	foreach($arr as $key=>$val)
			{
			 	$sortByTemp[$key] = $val[$sortby];
			}
			
			array_multisort($sortByTemp, $order, $arr);
			
			return $arr;
			
		}
	
	
	}

?>