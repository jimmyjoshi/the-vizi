<?php
	require '../config.php';
	global $db;

    $ret = array('status' => 'success', 'message' => 'Terms & Condition', 'data' => 'This is privacy text');
    echo json_encode($ret);
    die();
?>