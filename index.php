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
function logger($msg) {
  file_put_contents('php://stderr', print_r($msg, TRUE) . "\n");
}

$m = new Memcache;
$m->connect('127.0.0.1', 11211) or die ("Could not connect");
if (!$m->get($Cid)) {
	$m->set($Cid, 1);   // Danger: 32-bit int counter... or might be 64?
	$m->set($Cud, 1);   // Danger: 32-bit int counter
}
if ($m->get($Cid) > 1<<30) {
  die ("We are out of ID numbers.");
} else if ($_SERVER['REQUEST_METHOD'] === 'GET' and empty($_GET)) {
  // API:  GET[] - Retrieve a new user
  $user = $m->increment($Cud);
  $id = $m->get($Cid);
  echo json_encode(array('user' => $user, 'id' => $id));
} else if ($_SERVER['REQUEST_METHOD'] === 'GET' and isset($_GET['id']) and isset($_GET['user'])) {
  // API:   GET[user,id] - Polling for Messages since id not sent by user
  $id = intval($_GET['id']);
  $user = $_GET['user'];
  $last_id = $m->get($Cid);
  $messages = array();
  while ($id < $last_id) {
    $id++;
    $msg_user = $m->get(message_user_key($id));
    $msg_data = $m->get(message_data_key($id));
    if ($msg_data and $msg_user and $msg_user != $user) {
      array_push($messages, array('id' => $id, 'user' => $msg_user, 'data' => $msg_data));
    }
  }
  echo json_encode($messages);
} else if ($_SERVER['REQUEST_METHOD'] === 'POST' and isset($_GET['user'])) {
  // API:  PUT[user,data] - Adding Message Data for User
  if ($_SERVER['CONTENT_LENGTH'] > $C_MAX) {
    http_response_code(413);
    die('Too large');
  }
  if (intval($_GET['user']) > $m->get($Cid)) {
    http_response_code(404);
    die('User ID invalid');
  }
  $data = file_get_contents('php://input');
  $json = json_decode($data);
  if ($json === null) {
    http_response_code(400);
    die('not valid json');
  }
  $id = $m->increment($Cid);
  $user = $_GET['user'];
  $m->set(message_user_key($id), $user, 0, $C_TTL_S);
  $m->set(message_data_key($id), $json, 0, $C_TTL_S);
  echo json_encode(array('user' => $user, 'id' => $id)); 
} else {
  http_response_code(404);
  var_dump($_GET);
  die('Unknown API call');
}
?>
