<?php
	require '../config.php';
	global $db;

    $ret = array('status' => 'fail', 'message' => '', 'data' => array());

    if (!isset($_REQUEST['email']) || $_REQUEST['email'] == '') {
    	$ret['message'] = 'Email can not be blank!';
    }

    if ($ret['message'] == '') {
        $exi = $db->single('SELECT id FROM users WHERE email LIKE :email', array('email' => $_REQUEST['email']) );
        if ($exi != '') {
        	//code for send email and set rest option and create a page where user can reset passwrord!!!s
        	$ret['status'] = 'success';
        	$ret['message'] = 'Email sent!';
        }
        else {
            $ret['message'] = 'No user found with current details!';
        }
	}
	echo json_encode($ret);
    die();
?>