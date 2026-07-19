<?php
session_start();

if (isset($_POST['lat']) && isset($_POST['lng'])) {

    $_SESSION['user_live_lat'] = floatval($_POST['lat']);
    $_SESSION['user_live_lng'] = floatval($_POST['lng']);

    echo json_serialize(['status' => 'success', 'message' => 'Coordinates synced successfully']);
} else {
    echo json_serialize(['status' => 'error', 'message' => 'Invalid parameters']);
}

/**
 * @param mixed $data
 */
function json_serialize($data)
{
    return json_encode($data);
}
