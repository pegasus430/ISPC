<?php
//
//function pr($var)
//{
//    $template = '<pre>%s</pre>';
//    printf($template, print_r($var, true));
//}
//
//function show_alert($data)
//{
//	$return = '';
//	switch (TEMPLATE) {
//		case 'nd2020':
//			// $alert_msg = array( 'type' => 'error', 'message' => __t('Error saving category name, please try again.') );
//			switch ($data['type']) {
//				case 'success':
//
//					$type = 'information';
//
//					break;
//
//				case 'warning':
//
//					$type = 'notification';
//
//					break;
//
//				default:
//					$type = 'warning';
//
//					break;
//			}
//
//			if (isset($data['message']) && ! empty($data['message'])) {
//				$return = '<div role="alert" class="alert card mb-20 message ' . $type . '"><p>
//				'.(!empty($data['title']) ? '<strong>'.$data['title'].'</strong> - ' : '' ).'
//            	' . $data['message'] . '</p><button type="button" class="close btnClose" style="opacity: 1;" data-dismiss="alert"></button></div>';
//			} else {
//				$return = '';
//			}
//			break;
//
//		case 'melon1':
//			// $alert_msg = array( 'type' => 'error', 'message' => __t('Error saving category name, please try again.') );
//			switch ($data['type']) {
//				case 'success':
//
//					$type = 'success';
//
//					break;
//
//				case 'warning':
//
//					$type = 'warning';
//
//					break;
//
//				default:
//					$type = 'danger';
//
//					break;
//			}
//
//			if (isset($data['message']) && ! empty($data['message'])) {
//				$return = '<div role="alert" class="alert fade in alert-' . $type . '">
//            	<i class="icon-remove close" data-dismiss="alert"></i>
//            	' . $data['message'] . '</div>';
//			} else {
//				$return = '';
//			}
//	}
//	return $return;
//}
//
///* Name: array2sql
// * Desc: Create sql queryes[insert|update|delete] using various input arrays and strings
// * Returns: (string) ready to use sql query
// *
// * @data		=	 (array) input values [option_value] => [option_name]
// * @operation	=	(string) operation [insert|update|delete]
// * @table		=	(string) table name to work with (including PREFIX)
// * @where		=	 (array) data to use as WHERE statement ([field_name]=>[field_value_matching]) <=== @TO DO: find a way to allow to use other matching conditions such as "LIKE..."
// * @exclude_data=	 (array) data to be used to filter the inserted data from the @data array
// *
// */
//
//function array2sql ($data, $operation = false, $table = false, $where = false, $exclude_data = false)
//{
//	if (is_array ($data) && count ($data) > 0)
//	{
//		if ($operation != false)
//		{
//			switch ($operation)
//			{
//				case 'insert': //in case of insert
//					if ($table != false)
//					{
//						$sql_query = "INSERT INTO $table ";
//					}
////					(column1, column2, column3,...) VALUES (value1, value2, value3,...)
//					$columns = '';
//					$values = '';
//
//					foreach ($data as $ins_field => $ins_data)
//					{
//
//						if (count ($exclude_data) > 0 && is_array($exclude_data))
//						{
//							if (!in_array ($ins_field, $exclude_data))
//							{
//							    if(substr($ins_field,0,3) != 'enc') { //for encrypted fields sanitization is done elsewhere
//							        $columns .= string_clean ($ins_field, 'db') . "`, `";
//							        $values .= string_clean ($ins_data, 'db') . "', '";
//							    } else {
//							        $columns .= $ins_field. "`, `";
//							        $values = substr($values, 0, -1);
//							        $values .= $ins_data . ", '";
//							    }
//							}
//						}
//						else
//						{
//						    if(substr($ins_field,0,3) != 'enc') { //for encrypted fields sanitization is done elsewhere
//						        $columns .= string_clean ($ins_field, 'db') . "`, `";
//						        $values .= string_clean ($ins_data, 'db') . "', '";
//						    } else {
//						        $columns .= $ins_field. "`, `";
//						        $values = substr($values, 0, -1);
//						        $values .= $ins_data . ", '";
//						    }
//						}
//					}
//
//					$columns_sql = '(' . "`" . substr ($columns, 0, -3) . ')';
//					$values_sql = '(' . "'" . substr ($values, 0, -3) . ')';
//
//					$final_sql = $sql_query . $columns_sql . ' VALUES ' . $values_sql . ';';
//
//					break;
//
//				case 'update':
//					if ($table != false)
//					{
//						$sql_query = "UPDATE $table SET ";
//					}
////					column1=value, column2=value2,... WHERE some_column=some_value
//					$update_sql = '';
//					foreach ($data as $upd_field => $upd_value)
//					{
//						if (count ($exclude_data) > 0 && is_array($exclude_data))
//						{
//							if (!in_array ($upd_field, $exclude_data))
//							{
//							    if(substr($upd_field,0,3) != 'enc') { //for encrypted fields sanitization is done elsewhere
//							        $update_data .= '`'.$upd_field . '`="' . string_clean ($upd_value, 'db') . '",';
//							    } else {
//							        $update_data .= '`'.$upd_field . '`=' . $upd_value . ',';
//							    }
//							}
//						}
//						else
//						{
//						    if(substr($upd_field,0,3) != 'enc') { //for encrypted fields sanitization is done elsewhere
//						        $update_data .= '`'.$upd_field . '`="' . string_clean ($upd_value, 'db') . '",';
//						    } else {
//						        $update_data .= '`'.$upd_field . '`=' . $upd_value . ',';
//						    }
//						}
//					}
//
//					if ($where != false && count ($where) > 0)
//					{
//						$w_data = '';
//						foreach ($where as $w_field => $w_value)
//						{
//							$w_data .= ' `' . $w_field . '`="' . string_clean ($w_value, 'db') . '" AND';
//						}
//					}
//					$update_sql = substr ($update_data, 0, -1);
//					$w_sql = substr ($w_data, 0, -4);
//
//					$final_sql = $sql_query . $update_sql . ' WHERE ' . $w_sql . ';';
//					;
//					break;
//
//				case 'delete':
//					if ($table != false)
//					{
//						$sql_query = "DELETE FROM $table ";
//					}
//
////					DELETE FROM table_name WHERE some_column = some_value
//					if ($where != false && count ($where) > 0)
//					{
//						$w_data = 'WHERE';
//						foreach ($where as $w_field => $w_value)
//						{
//							$w_data .= ' `' . $w_field . '`=' . string_clean ($w_value, 'db') . ' AND';
//						}
//					}
//					else
//					{
//						$w_data = '';
//					}
//
//					$w_sql = substr ($w_data, 0, -4);
//
//					$final_sql = $sql_query . '  ' . $w_sql . ';';
//					break;
//			}
//		}
//	}
//
//	return $final_sql;
//}
//
//function path_clean ($path)
//{
//	$s = array ('..', '/');
//	$r = array ();
//
//	return str_replace ($s, $r, $path);
//}
//
//function validate_input ($input, $type)
//{
//	if (empty ($input))
//	{
//		return false;
//	}
//
//	switch ($type)
//	{
//
//		case 'numeric':
//			if (!is_numeric ($input))
//			{
//				return false;
//			}
//			else
//			{
//				return true;
//			}
//		break;
//
//		case 'password':
//			//			return false;
//			break;
//
//		case 'email':
//			//			if(preg_match("/^([a-zA-Z0-9])+@([a-zA-Z0-9_-])+(\.[a-zA-Z0-9_-]+)+/", $input)){
//			$pattern = '/^([*+!.&#$¦\'\\%\/0-9a-z^_`{}=?~:-]+)@(([0-9a-z-]+\.)+[0-9a-z]{2,4})$/i';
//			if (preg_match ($pattern, $input))
//			{
//				return true;
//			}
//			else
//			{
//				return false;
//			}
//		break;
//
//		default:
//			return true;
//		break;
//	}
//
//	return true;
//}
//
//function age ($dob)
//{
//	$year_diff = date ("Y") - date ("Y", $dob);
//	$month_diff = date ("m") - date ("m", $dob);
//	$day_diff = date ("d") - date ("d", $dob);
//	if ($month_diff < 0)
//		$year_diff--;
//	elseif (($month_diff == 0) && ($day_diff < 0))
//		$year_diff--;
//	return $year_diff;
//}
//
//function human_filesize( $filesize )
//{
//	if(is_numeric($filesize))
//	{
//		$step = 0;
//		$suffix = array('B','Kb','Mb','Gb','Tb','Pb');
//
//		while(($filesize / 1024) > 0.9)
//		{
//			$filesize = $filesize/1024;
//			$step++;
//		}
//
//		return round($filesize,2).' '.$suffix[$step];
//	}
//	else
//	{
//
//		return 'NaN';
//	}
//}
//
//function time_passed ($from, $to)
//{
//	if (empty ($to))
//	{
//		$to = time ();
//	}
//	$hours = ceil (($to - $from) / 3600);
//
//	if ($hours < 24)
//	{
//		return $hours . ' hours ago';
//	}
//	$days = ceil ($hours / 24);
//
//	if ($days == 1)
//	{
//		return 'yesterday';
//	}
//	elseif ($days > 1 && $days <= 7)
//	{
//		return 'last week';
//	}
//	elseif ($days > 7 && $days <= 30)
//	{
//		return 'last month';
//	}
//	elseif ($days > 30 && $days <= 90)
//	{
//		return 'last 3 months';
//	}
//	elseif ($days > 90 && $days <= 180)
//	{
//		return 'last 6 months';
//	}
//	elseif ($days > 180 && $days <= 365)
//	{
//		return 'last year';
//	}
//	else
//	{
//		return 'more than an year ago';
//	}
//}
//
function string_clean ($str, $mode = '')
{
	switch ($mode)
	{

		case 'db-perm':
			$clean_str = trim (quote_smart ($str));
		break;

		case 'db':
			//$clean_str = htmlspecialchars (strip_tags (trim (quote_smart ($str))));
		    $clean_str = htmlspecialchars ( strip_tags ( trim ( quote_smart ( $str ) ) ), ENT_COMPAT | ENT_HTML401 , 'ISO-8859-1' );
		break;

		case 'html':
			$clean_str = htmlspecialchars (quote_smart ($str));
		break;

		case 'db-html' :
			$clean_str = htmlspecialchars ( trim ( quote_smart ( $str ) ) );
		break;

		case 'numeric':
			$clean_str = preg_replace ('/[^0-9]+/i', '', strtolower (trim ($str)));
		break;

		case 'search':
			$clean_str = preg_replace ('/[^a-zA-Z0-9_\\040\\.\\-]+/i', '', strtolower (trim ($str)));
		break;

		case 'paranoid':
			$clean_str = preg_replace ('/[^a-zA-Z0-9_.\-\@\'`]+/i', '', strtolower (trim ($str)));
		break;

		case 'url':
			$clean_str = preg_replace ('/[^a-zA-Z0-9_]+/i', '-', strtolower (trim ($str)));
		break;

		default:
			$clean_str = preg_replace ('/[^a-zA-Z0-9_]+/i', '-', strtolower (trim ($str)));
		break;
	}

	return $clean_str;
}

function id_encode ($id)
{
	if ( $id < 0 )
	{
		return '';
	}

	return alphaid ($id, 0, 0, USER_PASSWORD_HASH);
}

//function id_decode ($id)
//{
//	$id = str_replace ('/', '', $id);
//	return alphaid ($id, 1, 0, USER_PASSWORD_HASH);
//}
//
function quote_smart ($value)
{
	if (is_array ($value))
	{
		return array_map ("quote_smart", $value);
	}
	else
	{
		if (get_magic_quotes_gpc () == 1)
		{
			$value = stripslashes ($value);
		}
		if (!is_numeric ($value) || $value[0] == '0')
		{
//			$value = mysql_real_escape_string ($value);
			$value = $GLOBALS['db']->escape($value);
		}
		return $value;
	}
}

function alphaid ($in, $to_num = false, $pad_up = false, $passKey = null)
{
	$index = 'bcdfghjklmnpqrstvwxyzBCDFGHJKLMNPQRSTVWXYZ';
	if ($passKey !== null)
	{

		for ($n = 0; $n < strlen ($index); $n++)
		{
			$i[] = substr ($index, $n, 1);
		}

		$passhash = hash ('sha256', $passKey);
		$passhash = (strlen ($passhash) < strlen ($index)) ? hash ('sha512', $passKey) : $passhash;

		for ($n = 0; $n < strlen ($index); $n++)
		{
			$p[] = substr ($passhash, $n, 1);
		}

		array_multisort ($p, SORT_DESC, $i);
		$index = implode ($i);
	}

	$base = strlen ($index);

	if ($to_num)
	{
		// Digital number  <<--  alphabet letter code
		$in = strrev ($in);
		$out = 0;
		$len = strlen ($in) - 1;
		for ($t = 0; $t <= $len; $t++)
		{
			$bcpow = bcpow ($base, $len - $t);
			$out = $out + strpos ($index, substr ($in, $t, 1)) * $bcpow;
		}

		if (is_numeric ($pad_up))
		{
			$pad_up--;
			if ($pad_up > 0)
			{
				$out -= pow ($base, $pad_up);
			}
		}
		$out = sprintf ('%F', $out);
		$out = substr ($out, 0, strpos ($out, '.'));
	}
	else
	{
		// Digital number  -->>  alphabet letter code
		if (is_numeric ($pad_up))
		{
			$pad_up--;
			if ($pad_up > 0)
			{
				$in += pow ($base, $pad_up);
			}
		}

		$out = "";

		for ($t = floor (log ($in, $base)); $t >= 0; $t--)
		{
			$bcp = bcpow ($base, $t);
			$a = floor ($in / $bcp) % $base;
			$out = $out . substr ($index, $a, 1);
			$in = $in - ($a * $bcp);
		}

		$out = strrev ($out); // reverse
	}

	return $out;
}
//
//function replace_tags ($html, $tags = array ())
//{
//	if (sizeof ($tags) > 0)
//	{
//		foreach ($tags as $tag => $data)
//		{
//			$html = str_ireplace ('{' . $tag . '}', $data, $html);
//		}
//	}
//
//	return $html;
//}
//
//function unlink_r ($str)
//{
//	if (is_file ($str))
//	{
//		return @unlink ($str);
//	}
//	elseif (is_dir ($str))
//	{
//		$scan = glob (rtrim ($str, '/') . '/*');
//		foreach ($scan as $index => $path)
//		{
//			unlink_r ($path);
//		}
//		return @rmdir ($str);
//	}
//}
//
//function translate ($str)
//{
//	return __t($str);
//}
//
///* Name: validate
// * Desc: validate form inputs using various methods
// * Returns: (array) error messages [field(translated)] => [error_message(translated)]
// *
// * @data		=	 (array) input values [field_name] => [field_value]
// * @type		=	 (array) validation rules $validation['form_name']['field_name']  = $field_validation_method
// * @allow_empty=	  (bool) if empty fields are allowed
// *
// *
// * Uage :
// * 	  $data	=	array('field'=>'value'); // validate field with "field" name using "value" method
// * 	  $type	=	array('field'=>'validationMethod');
// */
//
//function validate ($data = false, $type = false, $allow_empty = false, $prefix = null)
//{
//
//
//	if (count ($data) > 0 && count ($type) > 0)
//	{
//		$error_returned = array ();
//
//		foreach ($type as $field => $method)
//		{
//
//			if (array_key_exists ($field, $data))
//			{
//				$value = $data[$field];
//				if (strlen (translate ($field)) > 0)
//				{
//					$t_field = translate ($field);
//				}
//				else
//				{
//					$t_field = $field;
//				}
//
//				if(!empty($prefix)) {
//				    $t_field = $prefix.$t_field;
//				}
//
//				switch ($method)
//				{
//					case 'email':
//						if(!empty($value))
//						{
//							$pattern = '/^([*+!.&#$¦\'\\%\/0-9a-z^_`{}=?~:-]+)@(([0-9a-z-]+\.)+[0-9a-z]{2,4})$/i';
//							if (!preg_match ($pattern, $value))
//							{
//								$error_returned[$t_field] = translate ('wrongemail');
//							}
//						}
//						else
//						{
//							$error_returned[$t_field] = translate ('noemptyemail');
//						}
//
//					break;
//
//					//PAINPOOL-348 - allowing verify email from patient-form that can be also empty
//					case 'email_empty':
//					    if(!empty($value))
//					    {
//					        $pattern = '/^([*+!.&#$¦\'\\%\/0-9a-z^_`{}=?~:-]+)@(([0-9a-z-]+\.)+[0-9a-z]{2,4})$/i';
//					        if (!preg_match ($pattern, $value))
//					        {
//					            $error_returned[$t_field] = translate ('wrongemail');
//					        }
//					    }
//
//					    break;
//
//					case 'text':
//						if (strlen ($value) < 2)
//						{
//							$error_returned[$t_field] = translate ('wrongtextlenght');
//						}
//
//					break;
//
//					case 'password':
//
//						if($data['user_cp'] == 1)
//						{
//							if(!empty($value) && !empty($data['password_r']))
//							{
//								$match_err = 0;
//
//								if ($value != $data['password_r'])
//								{
//									$error_returned[$t_field] = translate ('passnomatch');
//									$match_err = 1;
//								}
//
//								if (strlen ($value) < 6 && $match_err != 1)
//								{
//									$error_returned[$t_field] = translate ('passwronglenght');
//								}
//							}
//							else
//							{
//								$error_returned[$t_field] = translate ('passempty');
//							}
//						}
//					break;
//
//					case 'numeric' :
//						if (!is_numeric ($value))
//						{
//							$error_returned[$t_field] = translate ('numericonly');
//						}
//					break;
//
//					case 'not-empty-select' :
//						if (empty($value))
//						{
//							$error_returned[$t_field] = translate ('notemptyselect');
//						}
//					break;
//
//
//					case 'date-in-past':
//						if(strlen($value) == 10)
//						{
//							$date_parts = explode('.', $value);
//
//							if(count($date_parts) == 3){
////								(d.m.Y only)
//								if(checkdate($date_parts[1], $date_parts[0], $date_parts[2] ))
//								{
//									//check if is in past
//									if(strtotime($value) <= strtotime(date('d.m.Y')))
//									{
//
//									}
//									else
//									{
////										date must be in past
//										$error_returned[$t_field] = translate ('datemustbeinpast');
//									}
//								}
//								else
//								{
//									$error_returned[$t_field] = translate ('invaliddate');
//								}
//							}
//							else
//							{
//								$error_returned[$t_field] = translate ('invaliddateformat');
//							}
//						}
//						else
//						{
//							//invalid date length 10chars
//							$error_returned[$t_field] = translate ('invaliddatelength');
//						}
//					break;
//
//					case 'date-in-future':
//						if(strlen($value) == 10)
//						{
//							$date_parts = explode('.', $value);
//							if(count($date_parts) == 3){
////								(d.m.Y only)
//								if(checkdate($date_parts[1], $date_parts[0], $date_parts[2] ))
//								{
//									//check if is in past
//									if(strtotime($value) >= strtotime(date('d.m.Y')))
//									{
//
//									}
//									else
//									{
////										date must be in future
//										$error_returned[$t_field] = translate ('datemustbeinfuture');
//									}
//								}
//								else
//								{
//									$error_returned[$t_field] = translate ('invaliddate');
//								}
//							}
//							else
//							{
//								$error_returned[$t_field] = translate ('invaliddateformat');
//							}
//						}
//						else
//						{
//							//invalid date length 10chars
//							$error_returned[$t_field] = translate ('invaliddatelength');
//						}
//					break;
//
//					default: //do not allow empty fields by default
//						if (strlen ($data[$field]) == 0 && $allow_empty == false)
//						{
//							$error_returned[$t_field] = translate ('noemptyfields');
//						}
//					break;
//				}
//			}
//			else if (!$allow_empty) //validate if 'field' is not in rules array, check if is !empty !! Only if allow empty is false
//			{
//				if (strlen ($data[$field]) == 0)
//				{
//					$error_returned[translate ($field)] = translate ('noemptyfields');
//				}
//			}
//		}
//
//		return $error_returned;
//	}
//}
//
//
//function validate_new($required, $data) {
//	$error = 0;
//	foreach ( $required as $variable => $var_details ) {
//		if($var_details['noempty'] === true && empty($data[$variable])){
//			$error = 1;
//			$return['message'][] = ucfirst($var_details['text']).' is required.';
//		}
//
//		if($var_details['validation'] && !validate_input($data[$variable], $var_details['validation'], $var_details['extra'])) {
//			$error = 1;
//			$return['message'][] = ucfirst($var_details['text']).' is not valid.';
//		}
//	}
//	if($error == 1){
//		$return['success'] = false;
//	} else {
//		$return['success'] = true;
//	}
//
//	return $return;
//}
//
//function get_countries ()
//{
//	$sql = 'SELECT * FROM ' . TABLE_PREFIX . '_countries ORDER BY `id` ASC';
//	if ($GLOBALS['db']->query ($sql))
//	{
//		$countryes_data = $GLOBALS['db']->get_results (null, ARRAY_A);
//
//		foreach ($countryes_data as $c_key => $c_values)
//		{
//			$countryes[$c_values['id']] = $c_values['name_en'];
//		}
//	}
//
//	return $countryes;
//}
//
///* Name: createDropdown
// * Desc: Create a dropdown select using an array for values and more various
// * Returns: (string)  ready to use <select>....</select> plain HTML
// *
// * @data		=	 (array) input values [option_value] => [option_name]
// * @selected	=	(string) selected option_value
// * @custom_attr=	 (array) array with custom attr [select_attribute] => [select_attribute_value] <=== @TO DO: make this if we need to add custom attr or javascript handler etc..
// * @name		=	(string) dropdown name
// * @id		=	(string) dropdown id
// * @f_o_name	=	(string) first option name
// * @f_o_value	=	(string) first option value
// * @use_tra..	=	  (bool) wheather to use translation system or not in the option values (except first one)
// * @$incr_val..=	  (bool) wheather to show 1. in selects or not
// *
// */
//
//function create_dropdown ($data = false, $selected = false, $custom_attr = false, $name = false, $id = false, $f_o_name = false, $f_o_value = false, $use_translation = false, $incr_values = false)
//{
////print_r($data);
////print_r($selected);
//	if(is_array($selected) && !empty($selected) && is_array($data) && !empty($data))
//	{
//		$data = sort_array_by_array($data, $selected);
//	}
//
//	if ($f_o_value === false && $f_o_value != '0')
//	{
//		$f_o_value = '';
//	}
//
//	if (!$f_o_name && $f_o_name !== false)
//	{
//		$f_o_name = 'pleaseselectone';
//	}
//
//	$attrs = '';
//	if ($custom_attr)
//	{
//		if (is_array ($custom_attr))
//		{
//			foreach ($custom_attr as $attr => $attr_value)
//			{
//				$attrs .= $attr . '="' . $attr_value . '" ';
//			}
//		}
//	}
//	$drop_down = '';
//	$drop_down .= '<select name="' . $name . '" id="' . $id . '" ' . $attrs . ' >';
//
//
//	if ($custom_attr)
//	{
//		if(!in_array('multiple', $custom_attr))
//		{
//				$drop_down .= '<option value="' . $f_o_value . '">' . __t ($f_o_name) . '</option>';
//		}
//	}
//	elseif($f_o_name != false)
//	{
//		$drop_down .= '<option value="' . $f_o_value . '">' . __t ($f_o_name) . '</option>';
//	}
//
//
//	$z = 1;
//	if(!empty($data))
//	{
//		foreach ($data as $key => $value)
//		{
//			if ($use_translation)
//			{
//				$value = __t ($value);
//			}
//
//			if ($incr_values)
//			{
//				$value = $z . '. ' . $value;
//			}
//			if(is_array($selected))
//			{
//				if ($selected)
//				{
//					$selectedstr = '';
//					if (in_array($key, $selected))
//					{
//						$selectedstr = 'selected="selected"';
//					}
//				}
//			}
//			else
//			{
//				if ($selected)
//				{
//					$selectedstr = '';
//					if ($key == $selected)
//					{
//						$selectedstr = 'selected="selected"';
//					}
//				}
//			}
//
//
//
//			$drop_down .= '<option value="' . $key . '" ' . $selectedstr . '>' . __t($value) . '</option>';
//
//
//			$z++;
//		}
//	}
//	$drop_down .= '</select>';
//
//	return $drop_down;
//}
//
//
//function create_dropdown_real ($data = false, $selected = false, $custom_attr = false, $name = false, $id = false, $f_o_name = false, $f_o_value = false, $use_translation = false, $incr_values = false)
//{
////print_r($data);
////print_r($selected);
//
//	if ($f_o_value === false && $f_o_value != '0')
//	{
//		$f_o_value = '';
//	}
//
//	if (!$f_o_name)
//	{
//		$f_o_name = 'pleaseselectone';
//	}
//
//	$attrs = '';
//	if ($custom_attr)
//	{
//		if (is_array ($custom_attr))
//		{
//			foreach ($custom_attr as $attr => $attr_value)
//			{
//				$attrs .= $attr . '="' . $attr_value . '" ';
//			}
//		}
//	}
//	$drop_down = '';
//	$drop_down .= '<select name="' . $name . '" id="' . $id . '" ' . $attrs . ' >';
//
//
//	if ($custom_attr)
//	{
//		if(!in_array('multiple', $custom_attr))
//		{
//				$drop_down .= '<option value="' . $f_o_value . '">' . __t ($f_o_name) . '</option>';
//		}
//	}
//	else
//	{
//		$drop_down .= '<option value="' . $f_o_value . '">' . __t ($f_o_name) . '</option>';
//	}
//
//
//	$z = 1;
//	if(!empty($data))
//	{
//		foreach ($data as $key => $value)
//		{
//			$selectedstr = '';
//			if ($use_translation)
//			{
//				$value = __t ($value);
//			}
//
//			if ($incr_values)
//			{
//				$value = $z . '. ' . $value;
//			}
//			if(is_array($selected))
//			{
//				if ($selected)
//				{
//					$selectedstr = '';
//					if (in_array(strval($key), $selected))
//					{
//						$selectedstr = 'selected="selected"';
//					}
//				}
//			}
//			else
//			{
//				if ($selected)
//				{
//					$selectedstr = '';
//					if ($key == $selected)
//					{
//						$selectedstr = 'selected="selected"';
//					}
//				}
//			}
//
//
//
//			$drop_down .= '<option value="' . $key . '" ' . $selectedstr . '>' . $value . '</option>';
//
//
//			$z++;
//		}
//	}
//	$drop_down .= '</select>';
//
//	return $drop_down;
//}
//
//function user_levels()
//{
//	$user_levels = array('1'=>'Super Admin', '2'=>'Admin', '3'=>'Practice');
//	return $user_levels;
//}
//
//function sort_array_by_array($array, $orderArray)
//{
//	$ordered = array ();
//	foreach ($orderArray as $key=>$val)
//	{
//		if (array_key_exists($val, $array))
//		{
//			$ordered[$val] = $array[$val];
//			unset($array[$val]);
//		}
//	}
//	return $ordered + $array;
//}
//
//function getCode($length = 6)
//{
//	$no = range(48, 57);
////	$lo = range(97, 122);
//	$up = range(65, 90);
//
////	exclude character I, l, 1, 0, O
////	@TO DO: exclude i,L, o,
////	include 1
//	$eno = array (48, 49);
////	$elo = array (108);
//	$eup = array (73, 79);
//
//	$no = array_diff($no, $eno);
////	$lo = array_diff($lo, $elo);
//	$up = array_diff($up, $eup);
////	$chr = array_merge($no, $lo, $up);
//	$chr = array_merge($no, $up);
//
//	for ($i = 1; $i <= $length; $i++)
//	{
//		$code.= chr($chr[rand(0, count($chr) - 1)]);
//	}
//
//	return $code;
//}
//
function var_dump_pre($var, $exit = 0) {
	echo '<pre>';
	var_dump ( $var );
	echo '</pre>';
	if(!empty($exit)) {
		exit;
	}
}

function quit($url) {
	header ( 'Location: ' . $url );
	exit ();
}
//
//function __t ($string, $language = 'de') {
//	if(array_key_exists($string, $GLOBALS['languages'][$language])){
//		$translated = $GLOBALS['languages'][$language][$string];
//	} else {
//		$translated = $string;
//
//		//file_put_contents( "../_logs/translation-".date('Y-m-d').".log", $string . PHP_EOL, FILE_APPEND );
//	}
//	return $translated;
//}
//
//
//
//
//
///* START IMPORT FROM SURVEY GENERAL FUNCTIONS */
//
//
//
//
//
//
//
//
//
//
//function array2select($array, $selected, $tag_name, $multi = array(), $default = 1, $enabled = 1, $custom = '') {
//	$html = '<select name="' . $tag_name . '" ' . ($enabled == 0 ? 'disabled="disabled"' : '') . '' . $custom . '>';
//	if ($default == 1) {
//		$html .= '<option value="">Select</option>';
//	} elseif (strlen ( $default ) > 1) {
//		$html .= '<option value="">' . $default . '</option>';
//	} else {
//
//	}
//	foreach ( $array as $key => $value ) {
//		if (is_array ( $multi ) && sizeof ( $multi ) > 0) {
//			$html .= '<option value="' . $value [$multi ['key']] . '" ' . ((is_array ( $selected ) && @in_array ( $value [$multi ['key']], $selected )) || $value [$multi ['key']] == $selected ? 'selected="selected"' : '') . '>' . $value [$multi ['value']] . '</option>';
//		} else {
//			$html .= '<option value="' . $key . '" ' . ((is_array ( $selected ) && @in_array ( $key, $selected )) || $key == $selected ? 'selected="selected"' : '') . '>' . $value . '</option>';
//		}
//	}
//	$html .= '</select>';
//
//	return $html;
//}
//
//
//
//function message_display($message, $redirect = false, $custom_class = false) {
//
//	if ($message ['success'] === true) {
//		if ($redirect !== false) {
//			$_SESSION ['message'] = '';
//			$_SESSION ['message'] = array ();
//			$_SESSION ['message'] = $message;
//			quit($redirect);
//			exit (); //kill current script
//		}
//	}
//
//	foreach ( $message ['message'] as $message_str ) {
//		$message_html .= $message_str . '<br />';
//	}
//
//	if ($custom_class !== false) {
//		$class = $custom_class;
//	} else {
//
//		if ($message ['success'] === true) {
//			$class = 'alert-success';
//		} elseif (($message ['success'] === false)) {
//			$class = 'alert-danger';
//		}
//	}
//
//	$_SESSION ['message'] = '';
//	$_SESSION ['message'] = array ();
//	unset ( $_SESSION ['message'] );
//
//	$html = '<div id="message-' . $class . '" class="alert fade in '.$class.'">
// 				<i data-dismiss="alert" class="icon-remove close"></i>
//				' . $message_html . '
//			</div>';
//
//	return $html;
//}
//
//
//
//function array_set_current(&$array, $key) {
//	reset ( $array );
//	foreach ( $array as $arr_key => $value ) {
//		if ($arr_key === $key) {
//			break;
//		}
//		next($array);
//	}
//}
//
//function array_get_next(&$array, $curr_key) {
//	$next = 0;
//	reset ( $array );
//
//	do {
//		$tmp_key = key ( $array );
//		$res = next ( $array );
//	} while ( ($tmp_key != $curr_key) && $res );
//
//	if ($res) {
//		$next = key ( $array );
//	}
//
//	return $next;
//}
//
//function array_get_prev(&$array, $curr_key) {
//	end ( $array );
//	$prev = key ( $array );
//
//	do {
//		$tmp_key = key ( $array );
//		$res = prev ( $array );
//	} while ( ($tmp_key != $curr_key) && $res );
//
//	if ($res) {
//		$prev = key ( $array );
//	}
//
//	return $prev;
//}
//
//
//
//function create_columns_array ($end_column, $first_letters = '')
//{
//	$columns = array ();
//	$length = strlen ($end_column);
//	$letters = range ('A', 'Z');
//
//	// Iterate over 26 letters.
//	foreach ($letters as $letter)
//	{
//		// Paste the $first_letters before the next.
//		$column = $first_letters . $letter;
//
//		// Add the column to the final array.
//		$columns[] = $column;
//
//		// If it was the end column that was added, return the columns.
//		if ($column == $end_column)
//		return $columns;
//	}
//
//	// Add the column children.
//	foreach ($columns as $column)
//	{
//		// Don't itterate if the $end_column was already set in a previous itteration.
//		// Stop iterating if you've reached the maximum character length.
//		if (!in_array ($end_column, $columns) && strlen ($column) < $length)
//		{
//			$new_columns = create_columns_array ($end_column, $column);
//			// Merge the new columns which were created with the final columns array.
//			$columns = array_merge ($columns, $new_columns);
//		}
//	}
//
//	return $columns;
//}
//
//
///* END IMPORT FROM SURVEY GENERAL FUNCTIONS */
//
//
///* START MENU FUNCTIONS */
//
//function menu_parents_get()
//{
//	$query = 'SELECT * FROM '.TABLE_PREFIX.'_menu WHERE `parent` =0 AND `status` =1 ORDER BY `order` ASC';
//	$result = $GLOBALS['db']->get_results($query, ARRAY_A);
//
//	//load allowed menu items
//	$allowed = menu_permissions_get();
//
//	if($result && is_array($allowed) && !empty($allowed))
//	{
//		foreach($result as $k_parent=>$v_parent)
//		{
//			if(in_array($v_parent['id'], $allowed))
//			{
//				$parents[$v_parent['id']] = $v_parent;
//			}
//		}
//
//		return $parents;
//	}
//	else
//	{
//		return false;
//	}
//
//}
//
//function menu_children_get()
//{
//	$query = 'SELECT * FROM '.TABLE_PREFIX.'_menu WHERE `parent` !=0 AND `status` =1 ORDER BY `order` ASC';
//	$result = $GLOBALS['db']->get_results($query, ARRAY_A);
//
//	//load allowed menu items
//	$allowed = menu_permissions_get();
//
//	if($result && is_array($allowed) && !empty($allowed))
//	{
//		foreach($result as $k_child=>$v_child)
//		{
//			if(in_array($v_child['id'], $allowed))
//			{
//				$children[$v_child['parent']][] = $v_child;
//			}
//		}
//		return $children;
//	}
//	else
//	{
//		return false;
//	}
//}
//
//function menu_permissions_get()
//{
//	$query = 'SELECT * FROM '.TABLE_PREFIX.'_menu_permissions WHERE level ="'.$_SESSION['user']['level'].'"';
//	$result = $GLOBALS['db']->get_results($query, ARRAY_A);
//
//	$user = new User();
//	$practice = new Practice();
//
//	if(!$_SESSION['user']['permissions']['menu'] || empty($_SESSION['user']['permissions']['menu'])){
//		$_SESSION['user']['permissions']['menu'] = array();
//	}
//
//	if($result)
//	{
//		if(count($result)>0)
//		{
//			foreach($result as $k_perm=>$v_perm)
//			{
//				if(@in_array($v_perm['id'], $_SESSION['user']['permissions']['menu']) ||  $_SESSION['user']['rights'] !== true || $v_perm['special_perm'] == 'ffa' || $_SESSION['user']['level'] != '3') {
//					switch($v_perm['special_perm']) {
//
//						case 'module_ops':
//							if($practice->module_check($_SESSION['user']['practice'], 'ops')) {
//								$allowed_menu_items[] = $v_perm['item'];
//							}
//							break;
//
//						case 'has_barmer':
//							if($user->has_project(BARMER_ID)) {
//								$allowed_menu_items[] = $v_perm['item'];
//							}
//							break;
//
//						case 'module_share':
//							if($practice->module_check($_SESSION['user']['practice'], 'share')) {
//								$allowed_menu_items[] = $v_perm['item'];
//							}
//							break;
//
//						case 'module_eos':
//							if($practice->module_check($_SESSION['user']['practice'], 'eos')) {
//								$allowed_menu_items[] = $v_perm['item'];
//							}
//							break;
//
//						case 'module_kedoq':
//							if($practice->module_check($_SESSION['user']['practice'], 'kedoq')) {
//								$allowed_menu_items[] = $v_perm['item'];
//							}
//							break;
//
//						case 'module_todohide':
//							if($practice->module_check($_SESSION['user']['practice'],'todohide')) {
//								$allowed_menu_items[] = $v_perm['item'];
//							}
//							break;
//
//						default:
//							$allowed_menu_items[] = $v_perm['item'];
//							break;
//					}
//
//				}
//			}
//			return $allowed_menu_items;
//
//		}
//		else
//		{
//			return false;
//		}
//	}
//	else
//	{
//		return false;
//	}
//}
///* END MENU FUNCTIONS */
//
//
//function datatable_fetchlist($columns = false, $index = 'id', $table = false, $table_request = false, $join_query = false, $where = false, $index_alt = false, $select_sql = null)
//{
//
//	if(!empty($columns) && !empty($index_alt))
//	{
//		$columns[0] = $index_alt;
//	}
//
//	/* Paging */
//	if ($table_request)
//	{
//
//		if(function_exists('mb_strtoupper')) {
//			$table_request['sSearchu'] =  mb_strtoupper($table_request['sSearch'],'utf-8');
//		} else {
//			$table_request['sSearchu'] =  strtoupper($table_request['sSearch']);
//		}
//
//		if(function_exists('mb_strtolower')) {
//			$table_request['sSearchl'] =  mb_strtolower($table_request['sSearch'],'utf-8');
//		} else {
//			$table_request['sSearchl'] =  strtolower($table_request['sSearch']);
//		}
//
//		$s_limit = "";
//		if (isset($table_request['iDisplayStart']) && count($table_request['iDisplayStart']) != '0' && $table_request['iDisplayLength'] != '-1')
//		{
//			$s_limit = "LIMIT " . $table_request['iDisplayStart'] . ", " . $table_request['iDisplayLength'];
//		}
//
//		//user save new length_list
//		if($table_request['iDisplayLength'] > 0) {
//			$_SESSION['user']['list_length'] = $table_request['iDisplayLength'];
//			$GLOBALS['db']->query('UPDATE '.TABLE_PREFIX.'_users_details set list_length="'.$table_request['iDisplayLength'].'" WHERE user = "'.intval($_SESSION ['user'] ['id']).'"');
//		}
//	}
//	else
//	{
//		return false;
//	}
//
//
//	/* Ordering */
//	if ($columns)
//	{
//		$s_order = "";
//		if (isset($table_request['iSortCol_0']) && count($table_request['iSortCol_0']) != '0')
//		{
//			$s_order = "ORDER BY  ";
//			for ($i = 0; $i < intval($table_request['iSortingCols']); $i++)
//			{
//				if ($table_request['bSortable_' . intval($table_request['iSortCol_' . $i])] == "true")
//				{
//					$s_order .= "`" . $columns[intval($table_request['iSortCol_' . $i])] . "` " . $table_request['sSortDir_' . $i] . ", ";
//				}
//			}
//
//			$s_order = substr_replace($s_order, "", -2);
//			if ($s_order == "ORDER BY")
//			{
//				$s_order = "";
//			}
//		}
//		if ($table == TABLE_PREFIX . '_patient p') {
//			$dob_index = array_search('dob', $columns);
//			if ($dob_index !== false) {
//				//uset it here, and add it later when forming SQL
//				unset($columns[$dob_index]);
//			}
//			$first_name_index = array_search('first_name', $columns);
//			if ($first_name_index !== false) {
//				//uset it here, and add it later when forming SQL
//				unset($columns[$first_name_index]);
//			}
//			$last_name_index = array_search('last_name', $columns);
//			if ($last_name_index !== false) {
//				//uset it here, and add it later when forming SQL
//				unset($columns[$last_name_index]);
//			}
//		}
//	}
//	else
//	{
//		return false;
//	}
//
//
//	/* Filtering */
//	if ($columns)
//	{
//		$s_where = "";
//		if (!empty($table_request['sSearch']) && $table_request['sSearch'] != "")
//		{
//			$s_where = "WHERE (";
//			for ($i = 0; $i < count($columns); $i++)
//			{
//				$s_where .= "`" . $columns[$i] . "` LIKE '%" . $GLOBALS['db']->escape($table_request['sSearch']) . "%' OR ";
//				$s_where .= "`" . $columns[$i] . "` LIKE '%" . $GLOBALS['db']->escape($table_request['sSearchu']) . "%' OR ";
//				$s_where .= "`" . $columns[$i] . "` LIKE '%" . $GLOBALS['db']->escape($table_request['sSearchl']) . "%' OR ";
//			}
//			$s_where = substr_replace($s_where, "", -3);
//			$s_where .= ')';
//		}
//
//
//		/* Individual column filtering */
//		for ($i = 0; $i < count($columns); $i++)
//		{
//			if (!empty($table_request['bSearchable_' . $i]) && $table_request['bSearchable_' . $i] == "true" && $table_request['sSearch_' . $i] != '')
//			{
//				if ($s_where == "")
//				{
//					$s_where = "WHERE ";
//				}
//				else
//				{
//					$s_where .= " AND ";
//				}
//				$s_where .= "`" . $columns[$i] . "` LIKE '%" . $GLOBALS['db']->escape($table_request['sSearch_' . $i]) . "%' ";
//			}
//		}
//
//		if($where)
//		{
//			foreach($where as $field=>$value)
//			{
//				$where_arr[] = $value;
//			}
//
//			if(!empty($s_where))
//			{
//				$s_where .="AND ".implode(' AND ' , $where_arr);
//			}
//			else
//			{
//				$s_where ="WHERE ".implode(' AND ' , $where_arr);
//			}
//			//to calculate total numbers of rows we need passed where
//			$passed_where = "WHERE ".implode(' AND ' , $where_arr);
//		}
//	}
//	else
//	{
//		return false;
//	}
//
//
//	/* SQL queries */
//	if ($table && $columns && $table_request)
//	{
//		if($join_query)
//		{
//			$join = $join_query;
//		}
//		else
//		{
//			$join = '';
//		}
//
//		if(!$select_sql) {
//			$select_sql = str_replace(" , ", " ", implode("`, `", $columns));
//			if ($table == TABLE_PREFIX . '_patient p') {
//				if ($dob_index !== false) {
//					$select_sql .= '`, ' . sql_aes_decrypt('dob', true) . ' as `dob';
//				}
//				if ($first_name_index !== false) {
//					$select_sql .= '`, ' . sql_aes_decrypt('first_name', true) . ' as `first_name';
//				}
//				if ($last_name_index !== false) {
//					$select_sql .= '`, ' . sql_aes_decrypt('last_name', true) . ' as `last_name';
//				}
//			}
//		}
//
//		$select_q = "SELECT SQL_CALC_FOUND_ROWS `" . $select_sql . "` FROM   $table $join $s_where $s_order";
//
//		//get filtered results without paging limit
//		$temp_results = $GLOBALS['db']->get_results($select_q, ARRAY_A);
//		$filtered_total = $GLOBALS['db']->num_rows;
//		unset($temp_results);
//
//		$select_q .=  ' '.$s_limit;
//
//		if ($GLOBALS['db']->query($select_q))
//		{
//			$select_results = $GLOBALS['db']->get_results(null, ARRAY_A);
//		}
//		//$GLOBALS['db']->debug();
//		//leave them here to avoid NaN in javascript if we have no results
//		//$filtered_total = $GLOBALS['db']->num_rows;
//		if($index_alt)
//		{
//			$total = $GLOBALS['db']->get_var("SELECT COUNT(`" . $index_alt . "`) FROM $table $join $passed_where");
//		}
//		else
//		{
//			$total = $GLOBALS['db']->get_var("SELECT COUNT(`" . $index . "`) FROM $table $join $passed_where");
//		}
////		var_dump("SELECT COUNT(`" . $index . "`) FROM $table $join $s_where");
//		$output = array (
//		    "sEcho" => intval($table_request['sEcho']),
//		    "iTotalRecords" => $total,
//		    "iTotalDisplayRecords" => $filtered_total,
//		    //"Querys" =>array($select_q), //query for debug
//		    "data" => $select_results
//		);
//
//		return $output;
//	}
//	else
//	{
//		return false;
//	}
//}
//
//
//function datatable_fetchlist_simple($sql)
//{
//
//
//
//
//	/* SQL queries */
//	if ($sql)
//	{
//
//		if ($GLOBALS['db']->query($sql))
//		{
//			$select_results = $GLOBALS['db']->get_results(null, ARRAY_A);
//		}
//
//		//$GLOBALS['db']->debug();
//
//		//leave them here to avoid NaN in javascript if we have no results
//		$filtered_total = $GLOBALS['db']->num_rows;
//
//		$output = array (
//		    "sEcho" => 1,
//		    "iTotalRecords" => $filtered_total,
//		    "iTotalDisplayRecords" => $filtered_total,
//		    //"Querys" =>array($sql), //query for debug
//		    "data" => $select_results
//		);
//
//		return $output;
//	}
//	else
//	{
//		return false;
//	}
//}
//
//function system_log_write($line, $level = 'messages') {
//	switch($level) {
//		case 'sharepool':
//			$file = LOGFILE_SHAREPOOL;
//			break;
//		case 'email':
//			$file = LOGFILE_EMAILS;
//			break;
//		default:
//			$file = LOGFILE_MESSAGES;
//			break;
//	}
//
//	if(!empty($line) && is_writable($file)) {
//		@file_put_contents($file, $line."\n",  FILE_APPEND );
//	}
//}
//
//function array_to_xml( $data, &$xml_data ) {
//	foreach( $data as $key => $value ) {
//		if( is_array($value) ) {
//			if( is_numeric($key) ){
//				$key = 'item'.$key; //dealing with <0/>..<n/> issues
//			}
//			$subnode = $xml_data->addChild($key);
//			array_to_xml($value, $subnode);
//		} else {
//			if($value === 'nullmelater') {
//				$subnode2 = $xml_data->addChild("$key",'');
//				$subnode2->addAttribute("xsi:nil", "true", "http://www.w3.org/2001/XMLSchema-instance");
//			} else {
//				$xml_data->addChild("$key",htmlspecialchars("$value"));
//			}
//		}
//	}
//}
//
//
//function curl_post($url, $post = array(), $auth = null, $proxy = null) {
//	$agent = "painPool.de KEDOQ Poster";
//
//	$post = array_map ( "urlencode", $post );
//
//	$post_str = '';
//
//	foreach ( $post as $key => $value ) {
//		$post_str .= $key . '=' . $value . '&';
//	}
//
//	// echo $auth['user'].':'.$auth['password'];
//	$auth = false;
//	$ch = curl_init ();
//	curl_setopt ( $ch, CURLOPT_URL, $url );
//	if (is_array ( $auth ) && sizeof ( $auth ) > 0) {
//		$auth = array_map ( "urlencode", $auth );
//		curl_setopt ( $ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC );
//		curl_setopt ( $ch, CURLOPT_USERPWD, $auth['user'].':'.$auth['password'] );
//	}
//	if(!empty($proxy)) {
//		curl_setopt($ch, CURLOPT_PROXY, $proxy);
//	}
//	curl_setopt($ch, CURLOPT_HEADER, 1);
//	curl_setopt($ch, CURLOPT_USERAGENT, $agent);
//	//curl_setopt ( $ch, CURLOPT_VERBOSE, 1 );
//	curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
//	curl_setopt ( $ch, CURLOPT_FOLLOWLOCATION, 1 );
//	curl_setopt ( $ch, CURLOPT_POST, 1 );
//	curl_setopt ( $ch, CURLOPT_POSTFIELDS, $post_str );
//	curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, false );
//	curl_setopt($ch, CURLINFO_HEADER_OUT, 1);
//	curl_setopt ( $ch, CURLOPT_REFERER, $url );
//
//	$response = curl_exec ( $ch );
//
//	$return['info'] = curl_getinfo($ch);
//	$return['info']['headers'] = substr($response, 0, $return['info']['header_size']);
//	$return['result'] = substr($response, $return['info']['header_size']);
//
//	// echo curl_error($ch);
//	$return['info']['headerout'] = curl_getinfo($ch, CURLINFO_HEADER_OUT);
//	curl_close ( $ch );
//
//	return $return;
//}
//
//function to_utf8( $string, $from =  'CP1252') {
//	// From http://w3.org/International/questions/qa-forms-utf-8.html
//	if ( preg_match('%^(?:
//	      [\x09\x0A\x0D\x20-\x7E]            # ASCII
//	    | [\xC2-\xDF][\x80-\xBF]             # non-overlong 2-byte
//	    | \xE0[\xA0-\xBF][\x80-\xBF]         # excluding overlongs
//	    | [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}  # straight 3-byte
//	    | \xED[\x80-\x9F][\x80-\xBF]         # excluding surrogates
//	    | \xF0[\x90-\xBF][\x80-\xBF]{2}      # planes 1-3
//	    | [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
//	    | \xF4[\x80-\x8F][\x80-\xBF]{2}      # plane 16
//		)*$%xs', $string) ) {
//		return $string;
//	} else {
//		return iconv( $from , 'UTF-8', $string);
//	}
//}
//
//function umlauts_to_ascii($string) {
//	$s = array('ä', 'ö', 'ü', 'ß', 'Ä', 'Ö','Ü', 'ẞ');
//	$r = array('ae', 'oe', 'ue', 'ss', 'Ae', 'Oe', 'Ue', 'Ss');
//
//	return str_replace($s,$r, $string);
//
//}
//
//function filter_filename($filename, $beautify=true) {
//	// sanitize filename
//	$filename = preg_replace(
//			'~
//        [<>:"/\\|?*]|            # file system reserved https://en.wikipedia.org/wiki/Filename#Reserved_characters_and_words
//        [\x00-\x1F]|             # control characters http://msdn.microsoft.com/en-us/library/windows/desktop/aa365247%28v=vs.85%29.aspx
//        [\x7F\xA0\xAD]|          # non-printing characters DEL, NO-BREAK SPACE, SOFT HYPHEN
//        [#\[\]@!$&\'()+,;=]|     # URI reserved https://tools.ietf.org/html/rfc3986#section-2.2
//        [{}^\~`]                 # URL unsafe characters https://www.ietf.org/rfc/rfc1738.txt
//        ~x',
//			'-', $filename);
//	// avoids ".", ".." or ".hiddenFiles"
//	$filename = ltrim($filename, '.-');
//
//	// optional beautification
//	if ($beautify) $filename = beautify_filename($filename);
//
//	// maximise filename length to 255 bytes http://serverfault.com/a/9548/44086
//	$ext = pathinfo($filename, PATHINFO_EXTENSION);
//	if (($mbdetectencoding = mb_detect_encoding($filename)) === false) {
//		$mbdetectencoding = "UTF-8";
//	}
//	$filename = mb_strcut(pathinfo($filename, PATHINFO_FILENAME), 0, 255 - ($ext ? strlen($ext) + 1 : 0), $mbdetectencoding) . ($ext ? '.' . $ext : '');
//	return $filename;
//}
//
//function beautify_filename($filename) {
//
//	// replace tokens
//	$filename = preg_replace(array(
//			// "file%date%name.zip" becomes "file20170907name.zip"
//			'/%date%/',
//			// "file%time%name.zip" becomes "file075901name.zip"
//			'/%time%/',
//			// "file%%%name.zip" becomes "file-name.zip"
//			'/%+/'
//	), array(
//			date('Ymd'),
//			date('His'),
//			'-',
//	), $filename);
//
//	// reduce consecutive characters
//	$filename = preg_replace(array(
//			// "file   name.zip" becomes "file-name.zip"
//			'/ +/',
//			// "file___name.zip" becomes "file-name.zip"
//			'/_+/',
//			// "file---name.zip" becomes "file-name.zip"
//			'/-+/'
//	), '-', $filename);
//
//	$filename = preg_replace(array(
//			// "file--.--.-.--name.zip" becomes "file.name.zip"
//			'/-*\.-*/',
//			// "file...name..zip" becomes "file.name.zip"
//			'/\.{2,}/'
//	), '.', $filename);
//	// lowercase for windows/unix interoperability http://support.microsoft.com/kb/100625
//	// 			if (($mbdetectencoding = mb_detect_encoding($filename)) === false) {
//	// 				$mbdetectencoding = "UTF-8";
//	// 			}
//	// 			$filename = mb_strtolower($filename, $mbdetectencoding);
//	// ".file-name.-" becomes "file-name"
//	$filename = trim($filename, '.-');
//	return $filename;
//}
//
//
//function sql_aes_encrypt($value = null) {
//    return 'AES_ENCRYPT("'.string_clean($value, 'db').'", UNHEX(SHA2("'.PHASH.'",512)))';
//}
//
function sql_aes_decrypt($field = null, $exclude_as = false) {
    if(empty($field)) {
        $sql_string = 'AES_DECRYPT(`p`.`enc_first_name`, UNHEX(SHA2("'.PHASH.'",512))) AS `first_name`, 
                AES_DECRYPT(`p`.`enc_last_name`, UNHEX(SHA2("'.PHASH.'",512))) AS `last_name`, 
                DATE(AES_DECRYPT(`p`.`enc_dob`, UNHEX(SHA2("'.PHASH.'",512)))) AS `dob` 
                ';
    } else {
        $sql_string = ' AES_DECRYPT(`p`.`enc_'.$field.'`, UNHEX(SHA2("'.PHASH.'",512)))';
        if($exclude_as != true) {
            $sql_string .= ' AS `'.$field.'`';
        }
    }

    return $sql_string;
}
//
//function data_encrypt($data, $key = null) {
//    $ivlen = openssl_cipher_iv_length($cipher="aes-256-ctr");
//    $iv = openssl_random_pseudo_bytes($ivlen);
//    $ciphertext_raw = openssl_encrypt($data, $cipher, $key, $options=OPENSSL_RAW_DATA|OPENSSL_ZERO_PADDING, $iv);
//    $hmac = hash_hmac('sha256', $ciphertext_raw, $key, $as_binary=true);
//    $ciphertext = base64_encode( $iv.$hmac.$ciphertext_raw );
//
//    return $ciphertext;
//}
//
///*
// *
// * decrypt data with local key
// */
//
//function data_decrypt($data, $key = null) {
//    $c = base64_decode($data);
//    $ivlen = openssl_cipher_iv_length($cipher="aes-256-ctr");
//    $iv = substr($c, 0, $ivlen);
//    $hmac = substr($c, $ivlen, $sha2len=32);
//    $ciphertext_raw = substr($c, $ivlen+$sha2len);
//    $decrypted_data = openssl_decrypt($ciphertext_raw, $cipher, $key, $options=OPENSSL_RAW_DATA|OPENSSL_ZERO_PADDING, $iv);
//    $calcmac = hash_hmac('sha256', $ciphertext_raw, $key, $as_binary=true);
//    if (hash_equals($hmac, $calcmac))
//    {
//        return $decrypted_data;
//    }
//}
//
//
//function check_date($date, $strict = true)
//{
//    $dateTime = DateTime::createFromFormat('d.m.Y H:i', $date);
//    if ($strict) {
//        $errors = DateTime::getLastErrors();
//        if (!empty($errors['warning_count'])) {
//            return false;
//        }
//    }
//    if($dateTime !== false) {
//        return $dateTime->format('Y-m-d H:i:s');
//    } else {
//        return false;
//    }
//}
//
//
//function base64url_encode($data) {
//    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
//}
//
//function base64url_decode($data) {
//    return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
//}
//
//function convert_filesize_readable($bytes, $decimals = 2){
//    $size = array('B','kB','MB','GB','TB','PB','EB','ZB','YB');
//    $factor = floor((strlen($bytes) - 1) / 3);
//    return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$size[$factor];
//}
//
//function log_cron_start($function_name, $cron_type, $record_id=null, $record_name=null) {
//    if ((empty($record_name) && !empty($record_id)) || (empty($record_id) && !empty($record_name))) {
//        return false;
//    }
//    $db = $GLOBALS['db'];
//    $sql = 'INSERT INTO `'.TABLE_PREFIX.'_cron_log` (
//                          `id`,
//                          `cron_type`,
//                          `function_name`,
//                          `record_id`,
//                          `record_name`,
//                          `start`,
//                          `end`,
//                          `success`,
//                          `returned_value`)
//                    VALUES (
//                            NULL,
//                            "'.string_clean($cron_type,'db').'",
//                            "'.string_clean($function_name,'db').'",
//                            '.(($record_id === null) ? 'NULL' : '"'.string_clean($record_id, 'db').'"').',
//                            '.(($record_id === null) ? 'NULL' : '"'.string_clean($record_name, 'db').'"').',
//                            NOW(),
//                            NULL,
//                            "no",
//                            NULL);';
//    $result = $db->query($sql);
//    if ($result) {
//        return $db->insert_id;
//    } else {
//        return false;
//    }
//}
//
//function log_cron_end ($cron_log_id, $success, $returned_value) {
//    $db = $GLOBALS['db'];
//    if (!in_array($success, array('yes','no'))) {
//        return false;
//    }
//    $sql = 'UPDATE `'.TABLE_PREFIX.'_cron_log` SET
//                        `end`=NOW(),
//                        `success`="'.$success.'",
//                        `returned_value`="'.string_clean($returned_value,'db').'"
//            WHERE `id` = "'.intval($cron_log_id).'"';
//    $result = $db->query($sql);
//    if ($result) {
//        return true;
//    } else {
//        return false;
//    }
//}
//
//function array_recursive_unset(&$array, $unwanted_key) {
//	if (!is_array($array) || empty($unwanted_key)){
//		return false;
//	}
//	unset($array[$unwanted_key]);
//	foreach ($array as &$value) {
//		if (is_array($value)) {
//			array_recursive_unset($value, $unwanted_key);
//		}
//	}
//}
//
//function SimpleXML2ArrayWithCDATASupport($xml)
//{
//	$array = (array)$xml;
//
//	if (count($array) === 0) {
//		return (string)$xml;
//	}
//
//	foreach ($array as $key => $value) {
//		if (!is_object($value) || strpos(get_class($value), 'SimpleXML') === false) {
//			continue;
//		}
//		$array[$key] = SimpleXML2ArrayWithCDATASupport($value);
//	}
//
//	return $array;
//}

?>
