<?php
    set_time_limit(0);
    require '../config.php';
    global $db;

    $ret = array('status' => 'fail', 'message' => '', 'data' => array());

    if (!isset($_REQUEST['user_id']) || $_REQUEST['user_id'] == '') {
        $ret['message'] = 'User id can not be blank!';
    }

    if (!isset($_REQUEST['pin_id']) || $_REQUEST['pin_id'] == '') {
        $ret['message'] = 'Pin id can not be blank!';
    }

    $ret['status'] = 'Fail';
    $ret['data'] = array();

    $delete_ids = $_REQUEST['delete_ids'] != '' ? $_REQUEST['delete_ids'] : '';

    if ($ret['message'] == '') {
        $update = '';
        if (isset($_REQUEST['title']) && $_REQUEST['title'] != '')
            $update = ' title = "'.$_REQUEST['title'].'" ';

        if (isset($_REQUEST['note']))
            $update = $update != '' ? $update . ' , note = "'.$_REQUEST['note'].'" ' : ' note = "'.$_REQUEST['note'].'" '; 

        $db->query('UPDATE pins SET '.$update.' WHERE id = ' . $_REQUEST['pin_id'] . ' AND user_id = ' . $_REQUEST['user_id']);
        $ret['status'] = 'Success';
        $ret['message'] = 'Pin updated!';

        if ($delete_ids != '') {
            $images = $db->query('SELECT image from media WHERE id IN ('.$delete_ids.') AND  pin_id = ' . $_REQUEST['pin_id']);

            if (count($images) > 0) {
                $dels = array();
                foreach ($images as $k => $img) {
                    $image = explode("/", $img['image']);
                    $dels[] = PUBLIC_PATH . 'pins/' . end($image);
                    unlink(PUBLIC_PATH . 'pins/' . end($image));
                }
                $db->query('DELETE FROM media WHERE id IN ('.$delete_ids.') AND pin_id = ' . $_REQUEST['pin_id']);
            }
        }

        if (isset($_FILES) && isset($_FILES['image']) && isset($_FILES['image']['name']) && count($_FILES['image']['name']) > 0) {
            foreach ($_FILES['image']['name'] as $k => $file_name) {
                if ($_FILES['image']['error'][$k] == 0) {
                    $file = pathinfo($file_name);
                    $name = $file['filename'];
                    $ext = $file['extension'];
                    $rand = time();
                    $image = $name . '-' . $rand . '.' . $ext;
                    $upload_to = PUBLIC_PATH . 'pins/' . $image;
                    move_uploaded_file($_FILES['image']['tmp_name'][$k], $upload_to);
                    $db->query('INSERT INTO media (pin_id, image) VALUES(:pin_id, :image)', array('pin_id' => $_REQUEST['pin_id'], 'image' => PUBLIC_URL . 'pins/' . $image) );
                }
            }
        }
    }
    echo json_encode($ret);
    die();
?>