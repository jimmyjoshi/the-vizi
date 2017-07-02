<?php
	require '../config.php';
	global $db;

	$policy = $db->single('SELECT data_value FROM settings where data_key = "policy"');
    $ret 	= array('status' => 'success', 'message' => 'Terms & Condition', 'data' => $policy);
    echo json_encode($ret);
    die();
?>