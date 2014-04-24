<?php 

require_once "../../lib/database.php";

$q = "DELETE FROM `thing_has_thing` WHERE parent_id = $_POST[parent_id] AND child_id = $_POST[child_id]";
// echo $q;
$ret = $db->q($q);
if(!$ret)
{
	echo "Fail: $q<br />";
	return;
}


echo "win";

?>