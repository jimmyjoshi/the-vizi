<?php
	require '../config.php';
	global $db;

    $ret = array('status' => 'fail', 'message' => '', 'data' => array());

    if ($ret['message'] == '') {
        $admin = $pins = array();

        $pins[] = array('title' => 'Test pin 1', 'address' => 'Ahmedabad, India', 'lat' => '23.0225', 'lon' => '72.5714');
        $pins[] = array('title' => 'Test pin 2', 'address' => 'Surat, India', 'lat' => '21.1702', 'lon' => '72.8311');
        $pins[] = array('title' => 'Test pin 3', 'address' => 'Baroda, India', 'lat' => '22.3072', 'lon' => '73.1812');
        $pins[] = array('title' => 'Test pin 4', 'address' => 'Gandhinagar, India', 'lat' => '23.2156', 'lon' => '72.6369');
        $pins[] = array('title' => 'Test pin 5', 'address' => 'Rajkot, India', 'lat' => '22.3039', 'lon' => '70.8022');

        $admin[] = array('id' => '1', 'title' => 'Trending Places', 'image' => 'https://static.strollup.in/image/uploads/2015/03/Bada_Gumbad_Lodi_Gardens.jpg', 'pins' => $pins);
    	/*$all_pins = $db->query('SELECT * FROM pins WHERE category_id = '.$_REQUEST['category_id']);

        $pins = array();
        if (count($all_pins) > 0) {
            foreach ($all_pins as $p) {
                $u = $db->query('SELECT user_name, image FROM users WHERE id = ' . $p['user_id']);
                $pins[] = array('user' => $u[0]['user_name'], 'image' => $u[0]['image'], 'title' => $p['title'], 'lat' => $p['lat'], 'lon' => $p['lon'], 'address' => $p['address']);
            }
            $ret['status'] = 'success';
            $ret['message'] = 'Pin found!';
        }
        else {
            $ret['status'] = 'fail';
            $ret['message'] = 'No locations found!';
        }*/

        $ret['data'] = $admin;
    }

    echo json_encode($ret);
    die();
?>