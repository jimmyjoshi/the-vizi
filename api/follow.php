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

        $userId     = $_REQUEST['user_id'];
        $followerId = $_REQUEST['follower_id'];

        $sql = 'DELETE FROM notifications WHERE type LIKE "FOLLOW" AND obj_id IN( SELECT id FROM follow 
                WHERE follower_id = '. $userId .' AND following_id = '. $followerId .' )';


        $db->query($sql);

        //$db->query('DELETE FROM follow WHERE follower_id = :follower_id AND following_id = :follow_id ', $param);
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


            $singleQuery = 'SELECT is_private FROM users WHERE id = ' . $_POST['follower_id'];

            $private = $db->single($singleQuery);
    
            $db->query('INSERT INTO follow (follower_id, following_id, requested) VALUES(:user_id, :following_id, :requested)', array('user_id' => $_REQUEST['user_id'], 'following_id' => $_REQUEST['follower_id'], "requested" => $private));


            $obj_id = $db->lastInsertId();

            $usrId = $_REQUEST['user_id'];


            $nsql = 'INSERT INTO notifications (user_id, type, obj_id)  VALUES (
                        "'.$usrId.'",
                        "follow",
                        "'.$obj_id.'"
                    )'; 

            $db->query($nsql);


            $ret['message'] = $private ? 'Follow request sent!' : 'You are now following!';
            $ret['status'] = 'success';

            //To the user to whome current user is now following
            $token = can_send($_REQUEST['follower_id']);
            if ($token && 1==2) {

                require 'push.php';
                $usrId = $_REQUEST['user_id'];
                $name = $db->single('SELECT user_name FROM users WHERE id = ' . $usrId);
                $msg_payload = array (
                    'mtitle'        => 'Vizi',
                    'mdesc'         => $private ? $name . ' has sent you follow request' : $name . ' is now following you',
                    'badgeCount'    => unread_notification_count($_REQUEST['user_id'])
                );
                PushNotifications::iOS($msg_payload, $token);
            }

            $usrId      = $_REQUEST['user_id'];
            $followerId = $_REQUEST['follower_id'];
                
            $userIdSql = 'SELECT device_id FROM users WHERE id IN ( SELECT follower_id FROM follow 
                        WHERE  following_id = '. $usrId .' AND follower_id != '. $followerId .' ) ';

            //To all those users who are following user_id
            $user_ids = $db->query($userIdSql);

            if (count($user_ids) > 0 && 1==2) 
            {
                require_once 'push.php';
                $sr = 0;
                foreach ($user_ids as $tkn) 
                {
                    if($sr > 4)
                        break;

                    $followingSQL = 'SELECT user_name FROM users WHERE id = ' . $_REQUEST['follower_id'];
                    $following_to = $db->single($followingSQL);

                    $followedBySQL = 'SELECT user_name FROM users WHERE id = ' . $_REQUEST['user_id'];
                    $followed_by = $db->single($followedBySQL);

                    $msg_payload = array (
                        'mtitle' => 'Vizi',
                        'mdesc' => $followed_by . ' is now following to ' . $following_to,
                        'badgeCount'    => unread_notification_count($_REQUEST['user_id'])
                    );
                    PushNotifications::iOS($msg_payload, $tkn);

                    $sr++;
                }
            }
        }
    }

    echo json_encode($ret);
    die();
?>