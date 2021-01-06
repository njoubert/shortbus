<?php
$memcache = new Memcache;
$memcache->connect('127.0.0.1', 11211) or die ("Could not connect");
if (!$memcache->get('bcast-obj')) {
	$memcache->set('bcast-obj', "");
	$memcache->set('bcast-count', 1);
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$entityBody = file_get_contents('php://input');
	$memcache->set('bcast-obj', json_decode($entityBody));
	$memcache->increment('bcast-count');
}
$ret = new stdClass;
$ret->count = $memcache->get('bcast-count');
$ret->message = $memcache->get('bcast-obj');
echo json_encode($ret);
?>
