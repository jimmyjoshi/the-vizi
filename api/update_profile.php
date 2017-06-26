<?php
	require '../config.php';
	global $db;

    $ret = array('status' => 'fail', 'message' => '', 'data' => array());

    if (!isset($_REQUEST['user_id']) || $_REQUEST['user_id'] == '') {
    	$ret['message'] = 'User id can not be blank!';
    }

    if ($ret['message'] == '') {
    	
    	$user = $db->query('SELECT * FROM users WHERE id = :id', array('id' => $_REQUEST['user_id']));
    	if (count($user) > 0) {
    		$user = $user[0];

    		unset($user['password']);

	    	if (isset($_FILES) && count($_FILES) > 0) {
		    	if (isset($_FILES['image'])) {
		    		$file = pathinfo($_FILES['image']['name']);
	                $name = $file['filename'];
	                $ext = $file['extension'];
	                $rand = time();
	                $image = $name . '-' . $rand . '.' . $ext;
	                $upload_to = PUBLIC_PATH . 'img/users/' . $image;
	                move_uploaded_file($_FILES['image']['tmp_name'], $upload_to);
	                $db->query('UPDATE users SET image = :image WHERE id = :id ', array('id' => $_REQUEST['user_id'], 'image' => PUBLIC_URL . 'img/users/' . $image) );
	                $user['image'] = PUBLIC_URL . 'img/users/' . $image;
		    		//unlink(PUBLIC_PATH . 'img/' . $_REQUEST['user_id'] . '');
		    	}
		    }

		    if (isset($_REQUEST['bio'])) {
		    	$db->query('UPDATE users SET bio = :bio WHERE id = :id ', array('id' => $_REQUEST['user_id'], 'bio' => $_REQUEST['bio']));
		    	$user['bio'] = $_REQUEST['bio'];
		    }

		    if (isset($_REQUEST['visibility'])) {
		    	$db->query('UPDATE users SET visibility = :visibility WHERE id = :id ', array('id' => $_REQUEST['user_id'], 'visibility' => $_REQUEST['visibility']));
		    	$user['visibility'] = $_REQUEST['visibility'];
		    }
		    $ret['status'] = 'success';
		    $ret['message'] = 'User updated!';
		    $ret['data'] = $user;
		}
	}

    echo json_encode($ret);
    die();
?>