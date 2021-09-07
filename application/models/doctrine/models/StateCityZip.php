<?php

	Doctrine_Manager::getInstance()->bindComponent('StateCityZip', 'SYSDAT');

	class StateCityZip extends BaseStateCityZip {

		
		public static function get_citys_from_zip( $zip, $limit = 150 )
		{
		    if (empty($limit)) {
		        $limit = 150;
		    }
		    
			$zip = trim($zip);
			if ( strlen($zip) < 1 ){
				return false;
			}
			$q = Doctrine_Query::create()
			->select('id, state, city, zipcode')
			->from('StateCityZip')
			->where("zipcode LIKE( ? )" , $zip. "%" )
			->orderBy('zipcode ASC')
			->limit((int)$limit);
			
			$zip_array = $q->fetchArray();
			/* foreach ($zip_array as $k=>$v){
				$zip_array[$k]['city'] = $v['city'];

				//$zip_array[$k]['city'] = htmlentities(utf8_encode($v['city']), ENT_QUOTES, "UTF-8");
				//echo utf8_encode($v['city']) ."\n";
				//$zip_array[$k]['city'] = mb_convert_encoding(utf8_encode($v['city']), 'HTML-ENTITIES', 'UTF-8');
				//$zip_array[$k]['city'] = html_entity_decode($v['city'], ENT_QUOTES, "utf-8");
			} */
			
			return $zip_array;
		}
		
		//table was indexed by city also ... a LIKE( 'A%' ) will be faster that REGEX
		public static function get_zips_from_city( $city, $limit = 150 )
		{
		    if (empty($limit)) {
		        $limit = 150;
		    }
            //first get the ones that begin with
			$regexp_begins_with = trim($city);
			Pms_CommonData::value_patternation($regexp_begins_with, true);

            //then get the rest that match
			$regexp = trim($city);
			Pms_CommonData::value_patternation($regexp);
			
			if ( strlen($regexp) < 1 ){
				return false;
			}			
			$zip_array = Doctrine_Query::create()
			->select('id, state, city, zipcode')
			->from('StateCityZip')
			->where("LOWER(`city`) REGEXP :regexp")		 
			->orderBy('CASE WHEN city REGEXP :regexp_begins_with THEN 1 ELSE city END') //php usort
			->limit((int)$limit) //php limit
            ->fetchArray(array(
			    'regexp' => $regexp,
			    'regexp_begins_with' => $regexp_begins_with,
			));
			
			/* foreach ($zip_array as $k=>$v){ 
				//$zip_array[$k]['city'] = htmlentities(utf8_encode($v['city']) , ENT_QUOTES, "UTF-8");
				$zip_array[$k]['city'] = $v['city'];
			} */
			
			//orderBy
			usort($zip_array, array(new Pms_Sorter('city', $city), "_strnatcmp"));
// 			//limit	
// 			$zip_array = array_slice($zip_array, 0, $limit);
			
// 			$strlen_city =  strlen($city);
// 			$first = $last = array();
// 			foreach ( $zip_array as $k=>$v) {
// 			    if ( substr( strtolower($v['city']), 0 , $strlen_city) == $city) {
// 			        $first[$k] = $v;
// 			    }else{
// 			        $last[$k] = $v;
// 			    }
// 			}
// 			$zip_array = array_merge($first , $last);
				
			return $zip_array;
			
		}
	
	}

?>