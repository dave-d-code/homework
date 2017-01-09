<?php 

$graph = [];

$graph[0][1] = 5;
$graph[0][3] = 5;
$graph[0][4] = 7;
$graph[1][2] = 4;
$graph[2][3] = 8;
$graph[2][4] = 2;
$graph[3][2] = 8;
$graph[3][4] = 6;
$graph[4][1] = 3;

$start = 0;
$finish = 2;




function count($graph, $nodesArray) 
{
	foreach ($graph as $key => $value) {

		if ($nodesArray[$key] = 100000) {
			$nodesArray[$key] = 0;
		}

		$temp = $nodesArray[$key];
		$nodesArray[$key] += $value;
		// make sure that it adds up
		if (condition) {
			# code...
		}

	}
}


function setInfinity($start, $graph)
{
	$nodesArray = [];

	for ($i=0; $i < count($graph); $i++) { 
		

		$nodesArray[$i] = 100000;

		if ($i == $start) {
			$nodesArray[$i] = 0;
		}	
		
	}

	return $nodesArray;
}








 ?>