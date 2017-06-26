<?php
    require '../config.php';
	global $db;

    $ret = array('status' => 'fail', 'message' => '', 'data' => array());

    if (!isset($_REQUEST['pin_id']) || $_REQUEST['pin_id'] == '') {
    	$ret['message'] = 'Pin id can not be blank!';
    }

    if (!isset($_REQUEST['user_id']) || $_REQUEST['user_id'] == '') {
        $ret['message'] = 'User id can not be blank!';
    }    

    if ($ret['message'] == '') {
    	$title = getCatName($db->single('SELECT category_id FROM pins WHERE id = ' . $_REQUEST['pin_id']));
    	$bgimg = $db->single('SELECT image FROM categories WHERE id IN (SELECT category_id FROM pins WHERE id = ' . $_REQUEST['pin_id'] . ')');
    	$pin = $db->query('SELECT id, user_id, title, address, note,lat, lon FROM pins WHERE id = ' . $_REQUEST['pin_id']);
    	$imgs = $db->query('SELECT id, image FROM media WHERE pin_id = ' . $_REQUEST['pin_id']);

        $pin[0]['can_delete'] = $pin[0]['user_id'] == $_REQUEST['user_id'] ? 1 : 0;

    	$ret['status'] = 'success';
    	$ret['message'] = 'Pin found!';
    	$ret['data']['title'] = $title . ' Detail';
    	$ret['data']['bgimage'] = $bgimg;
    	$ret['data']['pin'] = $pin[0];
    	$ret['data']['pin']['images'] = $imgs;
    }

    echo json_encode($ret);
    die();
?>