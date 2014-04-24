<?php

require_once "database.php";

$old_categories = array();
$old_decisions = array();
$old_options = array();

$q = "SELECT * FROM `category`";

?>