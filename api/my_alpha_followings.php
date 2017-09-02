<?php
	require '../config.php';
	global $db;

    $ret = array('status' => 'fail', 'message' => '', 'data' => array());

    if (!isset($_REQUEST['user_id']) || $_REQUEST['user_id'] == '') {
        $ret['message'] = 'User id can not be blank!';
    }

    $usr = array();
    if ($ret['message'] == '') {
    	$users = $db->query('SELECT id, user_name, image, lat, lon, address FROM users WHERE id IN (SELECT following_id FROM follow WHERE follower_id = '.$_REQUEST['user_id'].' ) order by user_name');
    	if (count($users) > 0) {
    		if (count($users) > 0) {
	            foreach ($users as $u) 
	            {
	            	$firstChar = strtolower(substr($u['user_name'], 0, 1));
	            	
	                $u['distance'] = getTimeDiff($u['lat'], $u['lon'], $_REQUEST['lat'], $_REQUEST['lon']);
	                $u['following'] = 1;
	                $u = array_merge($u, ['dataKey' =>$firstChar]);
	                
	                $usr[$firstChar][] = $u;
	            }

	            $response = [];
		
				$sr = 0;
				foreach($usr as $key => $value)	            
				{
					$response[$sr] = [
						'dataKey' => $key
					];
					foreach($value as $subValue)
					{
						$response[$sr]['values'][] = $subValue;
					}
					$sr++;
				}

		    	$ret['status'] = 'success';
		        $ret['message'] = 'Followers found!';
		        $ret['data'] = $response;
		    }
		    else {
		    	$ret['status'] = 'fail';
		        $ret['message'] = 'No followers found!';
		    }
	    }
	    else {
	    	$ret['status'] = 'fail';
	        $ret['message'] = 'No followers found!';
	    }
        $ret['data'] = $response;
	}

	echo json_encode($ret);
    die();
?>