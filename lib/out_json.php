<?php

header('Content-type: text/javascript');

require_once "build_new_tree.php";

$CATEGORY = "node [color=\"047b35\",fillcolor=\"#8df2b6\",style=filled, shape=house];";
$DECISION = "node[shape=box,color=\"#004a63\",fillcolor=lightblue2,style=filled];";
$OPTION = "node [color=\"#444444\", style=\"rounded,filled\", shape=rect, fontcolor=\"black\", fillcolor=\"#DDDDDD\"];";

$categories = array();
$options = array();
$decisions = array();
$relations = array();

$ds = isset($_GET["ds"]) ? $_GET["ds"] : 1;
$newRoot = buildTree($ds);

setToolInfo($newRoot);

$cat = isset($_GET["c"]) ? $_GET["c"]: 1;
$first = $newRoot->children[$cat - 1];

echo "ds$cat = ";

echo $first->makeString($cat);

?>