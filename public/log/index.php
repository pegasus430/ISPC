
<?php
$realm = 'Restricted area';

//user => password
$users = array('sadmin_log' => 'sadmin_log');


if (empty($_SERVER['PHP_AUTH_DIGEST'])) {
    header('HTTP/1.1 401 Unauthorized');
    header('WWW-Authenticate: Digest realm="'.$realm.
           '",qop="auth",nonce="'.uniqid().'",opaque="'.md5($realm).'"');

    die('Text to send if user hits Cancel button');
}


// analyze the PHP_AUTH_DIGEST variable
if (!($data = http_digest_parse($_SERVER['PHP_AUTH_DIGEST'])) ||
    !isset($users[$data['username']]))
    die('Wrong Credentials!');


// generate the valid response
$A1 = md5($data['username'] . ':' . $realm . ':' . $users[$data['username']]);
$A2 = md5($_SERVER['REQUEST_METHOD'].':'.$data['uri']);
$valid_response = md5($A1.':'.$data['nonce'].':'.$data['nc'].':'.$data['cnonce'].':'.$data['qop'].':'.$A2);

if ($data['response'] != $valid_response)
    die('Wrong Credentials!');

// ok, valid username & password
//echo 'You are logged in as: ' . $data['username'];


// function to parse the http auth header
function http_digest_parse($txt)
{
    // protect against missing data
    $needed_parts = array('nonce'=>1, 'nc'=>1, 'cnonce'=>1, 'qop'=>1, 'username'=>1, 'uri'=>1, 'response'=>1);
    $data = array();
    $keys = implode('|', array_keys($needed_parts));

    preg_match_all('@(' . $keys . ')=(?:([\'"])([^\2]+?)\2|([^\s,]+))@', $txt, $matches, PREG_SET_ORDER);

    foreach ($matches as $m) {
        $data[$m[1]] = $m[3] ? $m[3] : $m[4];
        unset($needed_parts[$m[1]]);
    }

    return $needed_parts ? false : $data;
}







// Create recursive dir iterator which skips dot folders
$dir = new RecursiveDirectoryIterator('.',
    FilesystemIterator::SKIP_DOTS);

// Flatten the recursive iterator, folders come before their files
$it  = new RecursiveIteratorIterator($dir,
    RecursiveIteratorIterator::SELF_FIRST);

// Maximum depth is 1 level deeper than the base folder
$it->setMaxDepth(1);

$top_files = [];
$log_files = [];
// Basic loop displaying different messages based on file or folder
foreach ($it as $fileinfo) {
    if ($fileinfo->isDir()) {
        $log_files[$fileinfo->getFilename()]["__folder"] = sprintf("<hr><strong>Folder - %s</strong>\n <br/>", $fileinfo->getFilename());
    } elseif ($fileinfo->isFile()) {
        $getSize = $fileinfo->getSize();
        $getSubPath = $it->getSubPath();
        if (empty($getSubPath)) {
            $top_files[] = sprintf("<a href ='%s%s' target='_blank'>%s - %s</a>\n<br/>", '', $fileinfo->getFilename(), $fileinfo->getFilename(), $fileinfo->getSize()) ;
        } elseif ($getSize > 0) {
            $log_files[$getSubPath][] = sprintf("<a href ='%s%s' target='_blank'>%s</a>\n<br/>", $getSubPath . "/", $fileinfo->getFilename(), $fileinfo->getFilename(), $fileinfo->getSize()) ;
        }
    }
}

echo implode("", $top_files);
ksort($log_files);
foreach ($log_files as $dir) {
    
    $folder = $dir['__folder'];
    unset($dir['__folder']);
    asort($dir);
    echo $folder . implode("", $dir);
}

