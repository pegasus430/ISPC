<?php 
error_reporting(E_ALL);
session_start();
/*$servers[] = array(
		
			'host' => 'localhost',
			'user' => 'root',
			'password' => '0RWsqlsio315',
			'db' => 'painpool',
			);

$servers[] = array(
		
			'host' => '62.138.248.68',
			'user' => 'u_sysdat',
			'password' => '8FwyAxc25s',
			'db' => 'ispc_sysdat',
			);

$servers[] = array(

		'host' => '62.138.248.69',
		'user' => 'u_mdat',
		'password' => '7mZH2i=87',
		'db' => 'ispc_mdat',
);

$servers[] = array(

		'host' => '62.138.248.70',
		'user' => 'u_idat',
		'password' => '9%nYH2b_47',
		'db' => 'ispc_idat',
);
*/

$servers[] = array(

		'host' => '178.77.88.99',
		'user' => 'ispc_sysdat_user',
		'password' => 'N45wbCYeHw3Lu9C2',
		'db' => 'ispc_sysdat',
);

$servers[] = array(

		'host' => '178.77.88.100',
		'user' => 'ispc_mdat_user',
		'password' => 'dxUw3rHCW2yhFfES',
		'db' => 'ispc_mdat',
);

$servers[] = array(

		'host' => '178.77.88.101',
		'user' => 'ispc_idat_user',
		'password' => 'hAphb9CbmB9xv9bM',
		'db' => 'ispc_idat',
);

$rand = $servers[array_rand($servers)];

$conn = new mysqli($rand['host'], $rand['user'], $rand['password'], $rand['db']);

if ($conn->connect_errno) {
	
	
	$msg = "Errno: " . $conn->connect_errno . "\nError: " . $conn->connect_error . "\n\n";

	
	
} else {
	$sql = 'SELECT NOW()';
	if (!$result = $conn->query($sql)) {
		$msg = "Errno: " . $conn->connect_errno . "\nError: " . $conn->connect_error . "\n\n";
	} else {
		$msg = serialize($result->fetch_assoc())."\n\n";
	}
}

file_put_contents('test.log', $msg, FILE_APPEND);

header('Content-type: text/javascript');
echo '/* */';
?>