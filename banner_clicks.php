<?php
/*** wp-banner clicks ***/

if (!file_exists('../../../wp-config.php')) die ('wp-config.php not found');
require_once('../../../wp-config.php');

if (isset($_POST['banner_id'])) 
{
    banner_clicks($_POST['banner_id']);
    return 1;
}

function banner_clicks($banner_id)
{
    global $wpdb,$table_prefix;
    
    $query = "UPDATE ".$table_prefix."banner SET  banner_clicks=banner_clicks+1 WHERE banner_id=".intval($banner_id);
    $wpdb->query($query);
    
}

?>