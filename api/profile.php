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
    		unset($user['reset'], $user['created_at'], $user['status'], $user['role'], $user['password'], $user['visibility'], $user['device_id']);
    		
            $followers = $db->single('SELECT count(id) AS followers FROM follow WHERE following_id = ' . $_REQUEST['user_id']);
            $followings = $db->single('SELECT count(id) AS followings FROM follow WHERE follower_id = ' . $_REQUEST['user_id']);
            $pins = $db->single('SELECT count(id) AS pins FROM pins WHERE user_id = ' . $_REQUEST['user_id']);
            //$cats = $db->query('SELECT id, name, image, user_id FROM categories WHERE id IN (SELECT category_id FROM pins WHERE user_id = ' . $_REQUEST['user_id'] . ')');
            //$cats = $db->query('SELECT id, name, image, user_id FROM categories WHERE user_id = ' . $_REQUEST['user_id'] . '  ');
            if (isset($_REQUEST['city']) && $_REQUEST['city'] != '') {
                if($_REQUEST['current_user_id'] != $_REQUEST['user_id'])                
                {
                    $cats = $db->query('SELECT id, name, image, user_id, is_private FROM categories WHERE id IN (SELECT category_id FROM pins WHERE address LIKE "'.$_REQUEST['city'].'" AND is_private = 0 AND user_id = '.$_REQUEST['user_id'].' )');
                }
                else
                {
                    $cats = $db->query('SELECT id, name, image, user_id, is_private FROM categories WHERE id IN (SELECT category_id FROM pins WHERE address LIKE "'.$_REQUEST['city'].'" AND user_id = '.$_REQUEST['user_id'].' )');    
                }
            }
            else {
                if($_REQUEST['current_user_id'] != $_REQUEST['user_id'])                
                {
                    $cats = $db->query('SELECT id, name, image, user_id, is_private FROM categories WHERE is_private = 0 AND user_id IN (SELECT id FROM users WHERE role LIKE "admin" OR id = '.$_REQUEST['user_id'].' )');
                }
                else
                {
                    $cats = $db->query('SELECT id, name, image, user_id, is_private FROM categories WHERE user_id IN (SELECT id FROM users WHERE role LIKE "admin" OR id = '.$_REQUEST['user_id'].' )');    
                }
            }

            $current_id = $_REQUEST['current_user_id'];
            $is_following = "0";
            if ($current_id != '') {
                $ex_following = $db->single('SELECT id FROM follow WHERE follower_id =  ' . $current_id . ' AND following_id = ' . $_REQUEST['user_id']);
                if ($ex_following != '')
                    $is_following = "1";
            }

            if (count($cats) > 0) {
                foreach ($cats as $k => $c) {
                    if ($current_id == $_REQUEST['user_id'])
                        $cats[$k]['show_delete'] = $c['user_id'] == $_REQUEST['user_id'] ? 1 : 0;
                    $city = $db->query('SELECT distinct(address) FROM pins WHERE category_id = ' . $c['id'] . ' AND user_id = ' . $_REQUEST['user_id']);
                    $cats[$k]['city'] = $city;
                }
            }

            $user['notification'] = $user['notification'] == 'on' ? 1 : 0;

            $ret['status'] = 'success';
    		$ret['data']['user'] = $user;
            $ret['data']['is_following'] = $is_following;
            $ret['data']['followers'] = $followers;
            $ret['data']['followings'] = $followings;
            $ret['data']['pins'] = $pins;
            $ret['data']['categories'] = $cats;
    		$ret['message'] = 'User found!';
    	}
    	else {
    		$ret['message'] = 'User not found!';
    	}
    }

    echo json_encode($ret);
    die();
?>