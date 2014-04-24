<?php

require_once "../database.php";

echo "<table><tr><th>Step</th><th>Category</th><th># Decisions</th><th># Options</th></tr>";

$step = 1;

for($prior = 11; $prior < 22; $prior++)
{
	if($prior == 12) continue;
	
	for($cat = 1; $cat < 5; $cat++)
	{
	
		echo "<tr><td>$prior</td><td>$cat</td>";
	
		$q = "SELECT dd.category_id AS dd_cat, dc.category_id AS dc_cat, d.name AS name 
				FROM decision AS d
				LEFT JOIN decision_has_category AS dc ON d.id = dc.decision_id
				LEFT JOIN decision_has_decision AS dd ON d.id = dd.child_id
				LEFT JOIN decision_has_prior AS dp ON d.id = dp.decision_id
				WHERE d.step = 1 AND dp.prior_id <= $prior";	
		
		
		$ret = $db->q($q);
		$count = 0;
		while($r = mysqli_fetch_array($ret))
		{
			// Move on if we're not looking at the right step
			if($r["dd_cat"] != $cat && $r["dc_cat"] != $cat)
				continue;
		
			$count++;
		}
		
		echo "<td>$count</td>";
		
		$q = "SELECT *
				FROM `option` AS o
				LEFT JOIN decision_has_option AS do ON o.id = do.option_id 
				LEFT JOIN option_has_prior AS op ON o.id = op.option_id
				WHERE o.step = 1 AND op.prior_id <= $prior";
				
		$ret = $db->q($q);
		$count = 0;
		while($r = mysqli_fetch_array($ret))
		{
			if($r["category_id"] != $cat)
				continue;
			
			$count++; 
		}
		
		echo "<td>$count</td></tr>";
	}
}

echo "</table>";
?>