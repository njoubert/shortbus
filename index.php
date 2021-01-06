<?php
///// CONFIG ////
$C_TTL_S   = 60;             // Time To Live for each key, in seconds
$C_MAX     = 10240;          // Max Size per Key in Bytes. Rejects if larger. Might be built in...
$C_P       = "shortbus-";    //prefix used on all keys
/////////////////
$Cid      = $C_P . 'id-';
$Cud      = $C_P . 'user-';
$Cmpre    = $C_P . 'msg-';
function message_user_key($id) {
  global $Cmpre;
  return $Cmpre . $id . '-user';
}
function message_data_key($id) {
  global $Cmpre;
  return $Cmpre . $id . '-data';
}
////////////////
$m = new Memcache;
$m->connect('127.0.0.1', 11211) or die ("Could not connect");
if (!$m->get($Cid)) {
	$m->set($Cid, 1);   // Danger: 32-bit int counter... or might be 64?
	$m->set($Cud, 1);   // Danger: 32-bit int counter
}
if ($m->get($Cid) > 1<<30) {
  die ("We are out of ID numbers.");
}
if ($_SERVER['REQUEST_METHOD'] === 'GET' and isset($_GET['id']) and isset($_GET['user'])) {
  $id = intval($_GET['id']);
  $user = $_GET['user'];
  $last_id = $m->get($Cid);
  if ($id >= $last_id) {
    echo '';
  } else {
    // Iterate over all IDs from the provided one to the latest one.
    $messages = array();
    while ($id < $last_id) {
      $msg_user = $m->get(message_user_key($id));
      $msg_data = $m->get(message_data_key($id));
      if ($msg_data and $msg_user and $msg_user != $user) {
        array_push($messages, $msg_data);
      }
      $id++;
    }
    echo json_encode($messages);
  }
} else if ($_SERVER['REQUEST_METHOD'] === 'GET' and empty($_GET)) {
  $user = $m->increment($Cid);
  echo $user;
} else if ($_SERVER['REQUEST_METHOD'] === 'POST' and isset($_GET['user'])) {
  if ($_SERVER['CONTENT_LENGTH'] > $C_MAX) {
    http_response_code(413);
    die('Too large');
  } else if (intval($_GET['user']) > $m->get($Cud)) {
    http_response_code(404);
    die('User ID invalid');
  } else {
    $entityBody = file_get_contents('php://input');
    $id = $m->increment($Cid);
    $m->set(message_user_key($id), $_GET['user'], 0, $C_TTL_S);
    $m->set(message_data_key($id), $entityBody, 0, $C_TTL_S);
    echo message_data_key($id);
  }
} else {
  http_response_code(404);
  var_dump($_GET);
  die('Unknown API call');
}
 

// if ($_SERVER['REQUEST_METHOD'] === 'POST') {
// 	$entityBody = file_get_contents('php://input');
// 	$m->set('bcast-obj', json_decode($entityBody));
// 	$m->increment('bcast-count');
// }
// $ret = new stdClass;
// $ret->count = $m->get('bcast-count');
// $ret->message = $m->get('bcast-obj');
// echo json_encode($ret);
?>
