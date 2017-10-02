<?php
	require '../config.php';
	global $db;

    $ret = array('status' => 'fail', 'message' => '', 'data' => array());

    if (!isset($_REQUEST['user_id']) || $_REQUEST['user_id'] == '') {
    	$ret['message'] = 'User id can not be blank!';
    }
    
    $notifications = array();

    if ($ret['message'] == '') 
    {
        $notifications = $db->query('SELECT * FROM notifications WHERE type = "FOLLOW" and user_id = ' . $_REQUEST['user_id'].' ORDER BY created_at DESC');

        $userInfo = $db->query('SELECT user_name, image, address FROM users WHERE id = ' . $_REQUEST['user_id']);   
        if($notifications)
        {
            foreach($notifications as $notification)
            {

                if($notification['type'] == 'FOLLOW' && $userInfo)   
                {
                    $followInfo     = $db->query('SELECT * FROM follow WHERE id = ' . $notification['obj_id']);


                    if(! $followInfo)
                        continue;
                    
                    $followInfo     = $followInfo[0];


                    if($followInfo['follower_id'] == $_REQUEST['user_id'])
                    {
                        $otherUserId = $followInfo['following_id'];
                    }
                    else
                    {
                        $otherUserId = $followInfo['follower_id'];
                    }

                    $otherUser = $db->query('SELECT id, user_name, name, image, address FROM users WHERE id  = ' . $otherUserId);
                    $otherUser = $otherUser[0];

                    if($otherUser)
                    {
                        $otherUserName = $otherUser['name'] ? $otherUser['name'] : $otherUser['user_name'];


                        if($followInfo['requested'] == 0)
                        {
                            if($followInfo['following_id'] == $_REQUEST['user_id'])
                            {
                                $statement = $otherUserName . 'is now following You';
                            }
                            else
                            {
                                $statement = 'You are now following '.  $otherUserName;
                            }
                        }
                        else
                        {
                            if($followInfo['following_id'] == $_REQUEST['user_id'])
                            {
                                $statement = 'You have sent follow request to '. $otherUserName;
                            }
                            else
                            {
                                $statement = $otherUserName . ' has sent follow request to You';
                            }
                        }

                        $data[] = array(
                            'id'        => $notification['obj_id'],
                            'toUserId'  => $otherUserId,
                            'text'      => $statement,
                            'image'     => $otherUser['image'] ? $otherUser['image'] : DEFAULT_VIZI_IMAGE,
                            'time'      => isset($notification['created_at']) ? time_elapsed_string($notification['created_at']) : '',
                            'date'      => $notification['created_at'],
                            'requested' => (bool) $followInfo['requested'] 
                        );
                    }

                }
            }
        }

        usort($data, "cmp");

        function cmp($a, $b) {
            if ($a['date'] == $b['date']) {
                return 0;
            }
            return ($a['date'] < $b['date']) ? -1 : 1;
        }

        $ret['data'] = $data;
        $ret['message'] = 'Notifications found';
        $ret['status'] = 'success';
        /*else {
            $ret['message'] = 'No notifications found';
            $ret['status'] = 'fail';
        }*/
    }

    echo json_encode($ret);
    die();
?>