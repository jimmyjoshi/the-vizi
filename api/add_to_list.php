<?php
	require '../config.php';
	global $db;

    $ret = array('status' => 'fail', 'message' => '', 'data' => array());

    if (!isset($_REQUEST['user_id']) || $_REQUEST['user_id'] == '') {
        $ret['message'] = 'User id can not be blank!';
    }

    if (!isset($_REQUEST['category_id']) || $_REQUEST['category_id'] == '') {
        $ret['message'] = 'Catagory id can not be blank!';
    }

    if (!isset($_REQUEST['pin_ids']) || $_REQUEST['pin_ids'] == '') {
        $ret['message'] = 'Pin ids can not be blank!';
    }

    if ($ret['message'] == '') {
    	$pins = $db->query('SELECT * FROM pins WHERE id IN ('.$_REQUEST['pin_ids'].') ');
        if (count($pins) > 0) {
            foreach ($pins as $pin) {
                $ins = array(
                    'title' => $pin['title'],
                    'user_id' => $_REQUEST['user_id'],
                    'category_id' => $_REQUEST['category_id'],
                    'lat' => $pin['lat'],
                    'lon' => $pin['lon'],
                    'address' => $pin['address'],
                    'note' => $pin['note']
                );
                $db->query('INSERT INTO pins(title, user_id, category_id, lat, lon, address, note) VALUES("'.$ins['title'].'", '.$ins['user_id'].', '.$ins['category_id'].', "'.$ins['lat'].'", "'.$ins['lon'].'", "'.$ins['address'].'", "'.$ins['note'].'") ');

                $new_pin_id = $db->lastInsertId();
                $ret['message'] = 'Pins are added successfully';
            }
        }
        else {
            $ret['message'] = 'No pins found!';
        }
    }

    echo json_encode($ret);
    die();
?>