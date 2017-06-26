<?php
	require '../config.php';
	global $db;

    $ret = array('status' => 'fail', 'message' => '', 'data' => array());

    if (!isset($_REQUEST['user_id']) || $_REQUEST['user_id'] == '') {
    	$ret['message'] = 'User id can not be blank!';
    }

    if (!isset($_REQUEST['follow_id']) || $_REQUEST['follow_id'] == '') {
    	$ret['message'] = 'Follow id can not be blank!';
    }

    if (!isset($_REQUEST['follow']) || $_REQUEST['follow'] == '') {
        $ret['message'] = 'Follow / Unfollow can not be blank!';
    }

    $param = array('follower_id' => $_POST['user_id'], 'follow_id' => $_POST['follow_id'] );

    if ($_REQUEST['follow'] == 0) {
        $db->query('DELETE FROM notifications WHERE type LIKE "FOLLOW" AND obj_id IN ( SELECT id FROM follow WHERE follower_id = '.$_REQUEST['user_id'].' AND following_id = '.$_REQUEST['follow_id'].' )');
        $db->query('DELETE FROM follow WHERE follower_id = :follower_id AND following_id = :follow_id ', $param);
        $ret['message'] = 'User unfollowed!';
        $ret['status'] = 'success';
        //Do we have to remove this from notifications list as well ???
    }
    else {
        $exi = $db->query('SELECT * FROM follow WHERE follower_id = :follower_id AND following_id = :follow_id', $param);
        if (count($exi) > 0) {
            $ret['message'] = 'You are already following!';
            $ret['status'] = 'success';
        }
        else {
            $private = $db->single('SELECT is_private FROM users WHERE id = ' . $_POST['follow_id']);
    
            $db->query('INSERT INTO follow (follower_id, following_id, requested) VALUES(:user_id, :following_id, :requested)', array('user_id' => $_REQUEST['user_id'], 'following_id' => $_REQUEST['follow_id'], "requested" => $private));
            $obj_id = $db->lastInsertId();
            $db->query('INSERT INTO notifications (user_id, type, obj_id) VALUES(:uid, :type, :obj_id) ', array('uid' => $_REQUEST['user_id'], 'type' => 'FOLLOW', 'obj_id' => $obj_id));
            $ret['message'] = $private ? 'Follow request sent!' : 'You are now following!';
            $ret['status'] = 'success';

            //To the user to whome current user is now following
            $token = can_send($_REQUEST['follow_id']);
            if ($token) {
                require 'push.php';
                $name = $db->single('SELECT user_name FROM users WHERE id = ' . $_REQUEST['user_id']);
                $msg_payload = array (
                    'mtitle' => 'Vizi',
                    'mdesc' => $private ? $name . ' has sent you follow request' : $name . ' is now following you'
                );
                PushNotifications::iOS($msg_payload, $token);
            }

            //To all those users who are following user_id
            $user_ids = $db->query('SELECT device_id FROM users WHERE id IN (SELECT follower_id FROM follow WHERE following_id = '.$_REQUEST['user_id'].' AND follower_id != '.$_REQUEST['follow_id'].' ) AND notification LIKE "on" ');
            if (count($user_ids) > 0) {
                require 'push.php';
                foreach ($user_ids as $tkn) {
                    $following_to = $db->single('SELECT user_name FROM users WHERE id = ' . $_REQUEST['follow_id']);
                    $followed_by = $db->single('SELECT user_name FROM users WHERE id = ' . $_REQUEST['user_id']);
                    $msg_payload = array (
                        'mtitle' => 'Vizi',
                        'mdesc' => $followed_by . ' is now following to ' . $following_to
                    );
                    PushNotifications::iOS($msg_payload, $tkn);
                }
            }
        }
    }

    echo json_encode($ret);
    die();
?>