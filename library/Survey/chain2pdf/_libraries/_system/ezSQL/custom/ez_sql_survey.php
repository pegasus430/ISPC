<?php

class ezSQL_survey extends ezSQL_mysqli {
	//quick & dirty
	function get_insert_id() {
		return mysqli_insert_id($this->dbh);
	}

	function get_results($query=null, $output = OBJECT, $field = false)
	{
		$this->show_errors = true;

		// Log how the function was called
		$this->func_call = "\$db->get_results(\"$query\", $output)";

		// If there is a query then perform it if not then use cached results..
		if ( $query )
		{
			$this->query($query);
		}

		// Send back array of objects. Each row is an object
		if ( $output == OBJECT )
		{
			return $this->last_result;
		}
		elseif ( $output == ARRAY_A || $output == ARRAY_N )
		{
			if ( $this->last_result )
			{
				$i=0;
				foreach( $this->last_result as $row )
				{

					if($field !== false) {
						$i = $row->$field;
					}

					$new_array[$i] = get_object_vars($row);

					if ( $output == ARRAY_N )
					{
						$new_array[$i] = array_values($new_array[$i]);
					}

					if($field === false) {
						$i++;
					}

				}

				return $new_array;
			}
			else
			{
				return null;
			}

		}
	}
}

?>