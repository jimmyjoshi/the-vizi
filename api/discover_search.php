<?php
	require '../config.php';
	global $db;

    $ret = array('status' => 'fail', 'message' => '', 'data' => array());

    if (!isset($_REQUEST['lat']) || $_REQUEST['lat'] == '') {
        $ret['message'] = 'Latitude can not be blank!';
    }

    if (!isset($_REQUEST['lon']) || $_REQUEST['lon'] == '') {
        $ret['message'] = 'Longitude can not be blank!';
    }

    if (!isset($_REQUEST['user_id']) || $_REQUEST['user_id'] == '') {
        $ret['message'] = 'User id can not be blank!';
    }

    if ($ret['message'] == '') {
        /*$ids = $db->query(' SELECT following_id FROM follow WHERE follower_id IN (SELECT following_id FROM follow WHERE follower_id = '.$_REQUEST['user_id'].' ) ');
        $tmps = array();
        if (count($ids) > 0) {
            foreach ($ids as $i) {
                $tmps[] = $i['following_id'];
            }
        }*/
        if (!isset($_REQUEST['name']))
    	   $users = $db->query('SELECT id, user_name, image, lat, lon, address FROM users WHERE id != ' . $_REQUEST['user_id'] . ' ORDER BY rand() LIMIT 10 ');
        else {
            $users = $db->query('SELECT id, user_name, image, lat, lon, address FROM users WHERE user_name LIKE "%'.$_REQUEST['name'].'%" AND id != ' . $_REQUEST['user_id']);
        }
        $usr = array();
        if (count($users) > 0) {
            foreach ($users as $u) {
                $u['distance'] = getTimeDiff($u['lat'], $u['lon'], $_REQUEST['lat'], $_REQUEST['lon']);
                $if_follow = $db->single('SELECT id FROM follow WHERE follower_id = ' . $_REQUEST['user_id'] . ' AND following_id = ' . $u['id']);
                $u['following'] = $if_follow != '' ? 1 : 0;
                $usr[] = $u;
            }
        }

        $ret['status'] = 'success';
        $ret['message'] = 'Followers found!';
        $ret['data'] = $usr;
    }

    echo json_encode($ret);
    die();
?>