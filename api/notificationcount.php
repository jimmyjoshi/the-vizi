<?php
	
	if(isset($_POST['user_id']))
	{

		$userId = $_REQUEST['user_id'];
		$data 	= array(
			'notificationCount' => 3
		);	
		$ret['data'] 	= $data;
	    $ret['message'] = 'Notifications found';
	    $ret['status'] 	= 'success';
	}
	else 
	{
        $ret['message'] = 'No notifications found';
        $ret['status'] = 'fail';
    }

 	echo json_encode($ret);
    die();
?>