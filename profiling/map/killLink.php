<?php

require_once "../../php/database.php";

$q = "UPDATE `thing_has_thing` SET dead = 1 WHERE id = $_POST[id]";
$ret = $db->q($q);
if(!$ret)
{
	echo "Fail: $q<br />";
}
else
{
	echo "win";
}

?>