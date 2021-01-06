<?php
///// CONFIG ////
$C_TTL_S   = 60;             // Time To Live for each key, in seconds
$C_MAX     = 10240;          // Max Size per Key in Bytes. Rejects if larger.
$C_P       = "shortbus-"     //prefix used on all keys
/////////////////
$memcache = new Memcache;
$memcache->connect('127.0.0.1', 11211) or die ("Could not connect");
if (!$memcache->get($C_P . 'id')) {
	$memcache->set($C_P . 'id', 1)    // Danger: 32-bit int counter
	$memcache->set($C_P . 'user', 1); // Danger: 32-bit int counter
}
if ($memcache->get($C_P . 'id') > 1<<30) {
  die ("We are out of ID numbers.");
}
if ($_SERVER['REQUEST_METHOD'] === 'GET' and isset($_GET['id']) and isset($_GET['user'])) {
  // Retrieving keys
  echo "Retrieving keys!";
} else if (!$_GET) {
  echo "Retrieving a new user id!";
} else if ($_SERVER['REQUEST_METHOD'] === 'POST' and isset($_GET['user']) {
  echo "Adding files!";
} else {
  http_response_code(404);
}
 


// if ($_SERVER['REQUEST_METHOD'] === 'POST') {
// 	$entityBody = file_get_contents('php://input');
// 	$memcache->set('bcast-obj', json_decode($entityBody));
// 	$memcache->increment('bcast-count');
// }
// $ret = new stdClass;
// $ret->count = $memcache->get('bcast-count');
// $ret->message = $memcache->get('bcast-obj');
// echo json_encode($ret);
?>
