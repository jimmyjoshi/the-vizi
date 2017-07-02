<?php
	require '../config.php';
	global $db;

	$policy = $db->single('SELECT data_value FROM settings where data_key = "policy"');
    /*$ret 	= array('status' => 'success', 'message' => 'Private Policy', 'data' => $policy);
    echo json_encode($ret);
    die();*/
?>

<html>
	<head>
		<title>Privacy Policy</title>
	</head>
	<body>
		<?php echo $policy;?>
	</body>	
</html>