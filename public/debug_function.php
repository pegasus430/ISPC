<?php 
function die_claudiu()
{
    if ( ! defined('APPLICATION_ENV') 
        || (APPLICATION_ENV != 'staging' && APPLICATION_ENV != 'development') ) 
    {
        return;
    }
    
    global $appStartTime;

    // 	error_reporting(E_ALL);

    $text_output = "";
    $debug_all = false;
    $use_the_force = empty(func_get_args()) ? true : false;

    foreach (func_get_args() as $arg) {
        if ($arg === 'all') {
            $debug_all = true;
            break;
        }
    }

    $call = debug_backtrace();
    $text_output .= "<hr>fn:". $call[0]['function'] ." on line:". $call[0]['line'] ." in ".$call[0]['file'] . " ".microtime()."<hr>\n" ;
    $text_console .= "fn:". $call[0]['function'] ." on line:". $call[0]['line'] ." in ".$call[0]['file']."\n" ;

    $tabs = "\t&rdsh;";
    $tabs_console = "\t ";

    //first of all is index.php $application->bootstrap()->run();
    for($i=count($call); $i>0; $i--){
        if (isset($call[$i])) {
            	
            $text_output .= $tabs . $call[$i]['class'] . "::" . $call[$i]['function'] . " (line ".$call[$i-1]['line'] . ")\n";
            $text_console .= $tabs_console."> " . $call[$i]['class'] . "::" . $call[$i]['function'] . " (line ".  $call[$i-1]['line']. ")\n";
            	
            if ($use_the_force) {
                $text_output .= "<span style='color:#666;font-size:10px;'>(". print_r($call[$i]['args'], true) .")</span>\n";
                $text_console .= $tabs_console . print_r($call[$i]['args'], true) .")\n";
            }
            	
            $text_output .="<br>";
            // 			$tabs .= "\t". "&nbsp;&nbsp;&nbsp;";
            $tabs = "&nbsp;".$tabs;

            	

        }
    }

    if ( func_get_arg(0) == 'file' ) {
        file_put_contents('/tmp/xxxxxxxxx.htm', $text_output, FILE_APPEND);
        return;
    }
    
    $text_output_array = array();
    //dump query
    foreach(func_get_args() as $query) {
        if( is_object($query) && $query instanceof Doctrine_Query) {
            $text_output_array[] .= Pms_DoctrineUtil::get_raw_sql($query, false);
        } else {
            $text_output_array[] = print_r($query, true);
        }
    }
    // 	dump class methods
    // 	$text_output .= "\n<hr><pre>\n";
    // 	foreach(func_get_args() as $object) {
    // 		if(!is_object($object)){
    // 			$text_output .= print_r($object, true);
    // 		}
    // 		else {
    // 			if(class_exists(get_class($object), true)) {
    // 				$text_output .=  "CLASS NAME = ".get_class($object);
    // 				$reflection = new ReflectionClass(get_class($object));
    // 				$text_output .= "\n";
    // 				//$text_output .= $reflection->getDocComment();

    // 				$metody = $reflection->getMethods();
    // 				foreach($metody as $key => $value){
    // 					$text_output .=  "\n" . $value;
    // 				}

    // 				$text_output .= "\n";

    // 				$vars = $reflection->getProperties();
    // 				foreach($vars as $key => $value){
    // 					$text_output .= "\n" . $value;
    // 				}


    // 			} else {
    // 				$text_output .= "\ncannot load" . $object;
    // 			}
    // 		}
    // 	}

    $text_output .= "\n<hr><pre>\n";
	$text_output .= print_r($text_output_array, true);
	$text_output .= "</pre><br/>";

	$text_console .= "\n";
	$text_console .= print_r($text_output_array, true);
	$text_console .= "\n";

    echo $text_output;

	$view = Zend_Controller_Action_HelperBroker::getExistingHelper('ViewRenderer')->view;
	// 	echo Zend_Debug::dump($view);

    $reg_array = array();
    $registry = Zend_Registry::getInstance();
    //die(print_r($registry, true));
    foreach ($registry as $index => $value) {
    if( is_string($value) ) {
    $reg_array[] =  array("index"=>$index, "val"=>$value);
    }
    if( is_array($value) ) {
			$reg_array[] =  array("index"=>$index, "val"=>print_r($value, true));
}
	}
	
	//Zend_Registry commented 2018
// 	echo "<div style='border:1px solid aliceBlue'>".$view->tabulate(($reg_array)) ."</div>";




	if ( ($debug_all || func_get_arg(0) == 'dbf' || func_get_arg(1) == 'dbf')  && Zend_Registry::isRegistered('Profiler_table')) {

	$epsilon = 0.00001;
	$time = 0;
		$cnt = 0;
	$longest_t = 0;
	$time_array = array(array("name", "cnt", "total_time"));
	$count_array = array();
	$profiler = Zend_Registry::get('Profiler_table');
	$arr_sysdat = array();
	foreach ($profiler as $event) {

	$event_name = $event->getName();
	$event_getElapsedSecs = $event->getElapsedSecs();

		
	$time += $event->getElapsedSecs();
	$cnt++;
		
	$ccnt = $count_array[$event_name]['cnt'] +1;
	    	
	    $count_array[$event_name] = array(
	"name" => $event_name,
	"cnt" => $ccnt,
	);
		
	$tcnt = $time_array[$event_name]['cnt'] +$event_getElapsedSecs;
	    $time_array[$event_name] = array(
	        "name" => $event_name,
					"cnt" => $ccnt,
					"time" => $tcnt,
					"attributes" =>array("class"=>$event_name)
	);
		
	$arr = array(
					$event_name,

					$event->getQuery(),

					$event_getElapsedSecs,
	);
		
	$params = $event->getParams();
	    if (!empty($params)) {
	//$arr[] = $view->tabulate(array($params));

	$arr[] = "<pre>".print_r($params, true)."</pre>";
	}
		
	if ( ($event_getElapsedSecs - $longest_t ) > $epsilon ) {
	$longest_t = $event_getElapsedSecs;
	$longest_q = $arr;
	}

	$arr['attributes'] = array("class"=>$event_name);
	$arr_sysdat [] = $arr;
		}
		
		echo "<div style='border:2px solid Crimson'><b>Total doctrine queries: " . $cnt . "\n</b><br/></div>";
		echo "<div style='border:2px solid Green'><b>Total time: " . $time . "\n</b><br/></div>";


		echo "<div style='border:2px solid Crimson'>" . $view->tabulate($time_array,  array("class"=>"Profiler_table")). "\n</div><br/>";

	// 		echo "<div id='Profiler_div' style='border:2px solid Navy'>".$view->tabulate($arr_sysdat, array("class"=>"Profiler_table")) ."</div>";
		echo "<div style='border:2px solid Crimson'><b>Longest query: \n</b><br/></div>";
 		echo "<div id='Profiler_div' style='border:2px solid Navy'>".$view->tabulate(array($longest_q), array("class"=>"Profiler_table")) ."</div>";
 			
 		if ($debug_all) {
			echo "<div id='Profiler_div' style='border:2px solid Navy'>".$view->tabulate($arr_sysdat, array("class"=>"Profiler_table", "escaped"=>0)) ."</div>";
	}
		


}

$appTotalTime =   microtime(true) - $appStartTime;
echo '<br style="clear: both"/>microtime: '.$appTotalTime . " | ";
_ob_flush();
	/*
	$text_output

	print_r('"<script>console.groupCollapsed();</script>');
	printConsole($time_array);
	printConsole($arr_sysdat);
	print_r('"<script>console.groupEnd()</script>');
	*/

    if ( func_get_arg(0) == 'file' ) {
        file_put_contents('/tmp/xxxxxxxxx.htm', $text_console, FILE_APPEND);
        return;
    }

	if ($use_the_force || func_get_arg(0) == 'echo') {
	    print_r("<script>var text=`{$text_console}`;\n console.info(text)</script>");
	}

	if ( func_get_arg(0) != 'echo' ) {
	    exit;
	}
}

function dd() { 
    call_user_func_array('die_claudiu', func_get_args());
}
function ddecho() {
    call_user_func_array('die_claudiu', array_merge(array('echo'), func_get_args()));
}


function  _ob_flush()
{
    ob_end_flush();
    ob_flush();
    flush();
    ob_start();
}

function niceVarDump($obj, $ident = 0)
{
    $data = '';
    $data .= str_repeat(' ', $ident);
    $original_ident = $ident;
    $toClose = false;
    switch (gettype($obj)) {
        case 'object':
            $vars = (array) $obj;
            $data .= gettype($obj).' ('.get_class($obj).') ('.count($vars).") {\n";
            $ident += 2;
            foreach ($vars as $key => $var) {
                $type = '';
                $k = bin2hex($key);
                if (strpos($k, '002a00') === 0) {
                    $k = str_replace('002a00', '', $k);
                    $type = ':protected';
                } elseif (strpos($k, bin2hex("\x00".get_class($obj)."\x00")) === 0) {
                    $k = str_replace(bin2hex("\x00".get_class($obj)."\x00"), '', $k);
                    $type = ':private';
                }
                $k = hex2bin($k);
                if (is_subclass_of($obj, 'ProtobufMessage') && $k == 'values') {
                    $r = new ReflectionClass($obj);
                    $constants = $r->getConstants();
                    $newVar = [];
                    foreach ($constants as $ckey => $cval) {
                        if (substr($ckey, 0, 3) != 'PB_') {
                            $newVar[$ckey] = $var[$cval];
                        }
                    }
                    $var = $newVar;
                }
                $data .= str_repeat(' ', $ident)."[$k$type]=>\n".niceVarDump($var, $ident)."\n";
            }
            $toClose = true;
            break;
        case 'array':
            $data .= 'array ('.count($obj).") {\n";
            $ident += 2;
            foreach ($obj as $key => $val) {
                $data .= str_repeat(' ', $ident).'['.(is_int($key) ? $key : "\"$key\"")."]=>\n".niceVarDump($val, $ident)."\n";
            }
            $toClose = true;
            break;
        case 'string':
            $data .= 'string "'.parseText($obj)."\"\n";
            break;
        case 'NULL':
            $data .= gettype($obj);
            break;
        default:
            $data .= gettype($obj).'('.strval($obj).")\n";
            break;
    }
    if ($toClose) {
        $data .= str_repeat(' ', $original_ident)."}\n";
    }
    return $data;
}

function parseText($txt)
{
    for ($x = 0; $x < strlen($txt); $x++) {
        if (ord($txt[$x]) < 20 || ord($txt[$x]) > 230) {
            $txt = 'HEX:'.bin2hex($txt);
            return $txt;
        }
    }
    return $txt;
}


?>