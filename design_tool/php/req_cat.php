<?php

require_once "database.php";

$q = "SELECT requirement FROM reqs";
$ret = $db->q($q);
if(!$ret)
	return;
	
echo "<ol>";
while($r = mysqli_fetch_array($ret))
{
	echo "<li>$r[requirement]</li>";
}
echo "</ol>";

/*$q = "SELECT category.name AS cat_name, reqs.requirement
	FROM category 
       INNER JOIN decision_has_category 
               ON category.id = decision_has_category.category_id 
       INNER JOIN decision_has_code 
               ON decision_has_category.decision_id = 
                  decision_has_code.decision_id 
       INNER JOIN req_has_tag 
               ON decision_has_code.code_id = req_has_tag.tag_id 
       INNER JOIN reqs 
               ON req_has_tag.req_id = reqs.id";
               
$ret = $db->q($q);
if(!$ret)
	return;

while($r = mysqli_fetch_array($ret))
{
	$key = $r["cat_name"];
	array_push($cats[$key], $r["requirement"]);
}

$q = "SELECT category.name AS cat_name, reqs.requirement
	FROM category 
       INNER JOIN decision_has_option 
               ON category.id = decision_has_option.category_id 
       INNER JOIN option_has_code 
               ON decision_has_option .option_id = 
                  option_has_code.option_id 
       INNER JOIN req_has_tag 
               ON option_has_code.code_id = req_has_tag.tag_id 
       INNER JOIN reqs 
               ON req_has_tag.req_id = reqs.id";
               
$ret = $db->q($q);
if(!$ret)
	return;

while($r = mysqli_fetch_array($ret))
{
	$key = $r["cat_name"];
	array_push($cats[$key], $r["requirement"]);
}

$keys = array_keys($cats);
for($i = 0; $i < count($keys); $i++)
{
	$key = $keys[$i];
	$reqs = $cats[$key];
	echo "<h4>$key</h4><ol>";
	
	for($j = 0; $j < count($reqs); $j++)
	{
		echo "<li>$reqs[$j]</li>";
	}
	echo "</ol>";
}*/

?>