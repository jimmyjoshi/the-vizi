<?php
	@session_start();

    ini_set('display_errors', 0);
    set_time_limit(0);
    //error_reporting(E_ALL);

	//define('HOST' , 'http://vizi.intellactsoft.com/');
	//define('DOC_ROOT', $_SERVER['DOCUMENT_ROOT'] . '/');

	define('HOST' , 'http://app.theviziapp.com/');
	define('DOC_ROOT', $_SERVER['DOCUMENT_ROOT'] . '/');
	define('DB_NAME', 'vizi');
	define('DB_HOST', 'localhost');
	define('DB_USER', 'root');
	define('DB_PASSWORD','just$123');

	define ('PUBLIC_PATH', DOC_ROOT . 'public/');
	define ('PUBLIC_URL', HOST . 'public/');

	if (strpos( $_SERVER['REQUEST_URI'], 'api') != true) {
		if (isset($_SESSION['user']) && count($_SESSION['user']) > 0) {
			if (strpos( $_SERVER['REQUEST_URI'], 'login') != false || strpos($_SERVER['REQUEST_URI'], 'logout') != false) {
				header('Location: ' . HOST . 'admin/dashboard.php');
			}
		}
		else {
			if (strpos( $_SERVER['REQUEST_URI'], 'login.php') == false && 
				strpos($_SERVER['REQUEST_URI'], 'forget') == false && 
				strpos($_SERVER['REQUEST_URI'], 'reset') == false )
				header('Location: ' . HOST . 'login.php');
		}
	}

	require DOC_ROOT . 'db.php';
	global $db;
	$db = new DB();

	if (isset($_SESSION['user'])) {
		$db->bind('id', $_SESSION['user']['id']);
		$u = $db->row("SELECT * FROM users WHERE id = :id ");
		unset($u['password']);
		$_SESSION['user'] = $u;
	}

	function pr($obj) {
		echo 'Data: <pre>';
		print_r($obj);
		die();
	}

	function generateRandomString($length = 10) {
	    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	    $charactersLength = strlen($characters);
	    $randomString = '';
	    for ($i = 0; $i < $length; $i++) {
	        $randomString .= $characters[rand(0, $charactersLength - 1)];
	    }
	    return $randomString;
	}

	function getUserInfo($field) {
		if ($field == '')
			return $_SESSION['user'];
		else
			return $_SESSION['user'][$field];
	}

	function getCatName($cid) {
		global $db;
		$db->bind("id", $cid);
		$name = $db->single("SELECT name FROM categories WHERE id = :id");
		return $name;
	}

	function redirectIfnotAdmin() {
		if (!isset($_SESSION['user'])) {
			header('Location: ' . HOST . 'login.php');
		}
	}

	function getAddress($lat, $lon) {
		$geocode = file_get_contents('http://maps.googleapis.com/maps/api/geocode/json?latlng='.$lat.','.$lon.'&sensor=false');
        $output = json_decode($geocode);
        return $output->results[0]->formatted_address;
	}

	function getTimeDiff($lat1, $lat2, $lat3, $lat4) {
		$geocode = file_get_contents('https://maps.googleapis.com/maps/api/distancematrix/json?units=imperial&origins='.$lat1.','.$lat2.'&destinations='.$lat3.','.$lat4.'&key=AIzaSyBjLDZ0By3sH4JAGw-3JutwwY7FeBj-KK8');
        $data = json_decode($geocode);
        return $data->rows[0]->elements[0]->duration->text;
	}

	/**
    *
    * $unit = M - Miles, K - Kilometers, N - Nautical Miles
    *
    **/
	function distance($lat1, $lon1, $lat2, $lon2, $unit) {
		$theta = $lon1 - $lon2;
		$dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
		$dist = acos($dist);
		$dist = rad2deg($dist);
		$miles = $dist * 60 * 1.1515;
		$unit = strtoupper($unit);

		$f = 0;

		if ($unit == "K")
			$f = $miles * 1.609344;
		else if ($unit == "N")
			$f = $miles * 0.8684;
		else
			$f = $miles;

		return number_format($f, 2);
	}

	function time_elapsed_string($datetime, $full = false) {
	    $now = new DateTime;
	    $ago = new DateTime($datetime);
	    $diff = $now->diff($ago);

	    $diff->w = floor($diff->d / 7);
	    $diff->d -= $diff->w * 7;

	    $string = array(
	        'y' => 'year',
	        'm' => 'month',
	        'w' => 'week',
	        'd' => 'day',
	        'h' => 'hour',
	        'i' => 'minute',
	        's' => 'second',
	    );
	    foreach ($string as $k => &$v) {
	        if ($diff->$k) {
	            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
	        } else {
	            unset($string[$k]);
	        }
	    }

	    if (!$full) $string = array_slice($string, 0, 1);
	    return $string ? implode(', ', $string) . ' ago' : 'just now';
	}

	function can_send($user_id) {
		global $db;
		$u = $db->query('SELECT device_id, notification FROM users WHERE id = ' . $user_id);
		if (count($u) > 0)
			return ($u[0]['device_id'] != '' && $u[0]['notification'] != 'off') ? $u[0]['device_id'] : false;
		return false;
	}
?>