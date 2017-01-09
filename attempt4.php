<?php 

// define graph areas


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

// define start and end nodes

$start = 0;
$finish = 2;

$nodesArray = [];
$visitedNodes = [];
$nodesArray = setInfinity($start, $graph);
$nodesArray = countScores($graph, $start, $nodesArray, true);


array_push($visitedNodes, $start);

usort($nodesArray, function($a, $b) {
	return $a['score'] <=> $b['score'];
});


$nodesArray = countScores($graph, $start, $nodesArray, false);
array_push($visitedNodes, $start);

// go through the graph
for ($i=0; $i <= count($graph); $i++) { 
	# code...
}



function countScores($graph, $node, $nodesArray, $firstloop = false)
{
	foreach ($graph[$node] as $key => $value) {
		
		if ($firstloop) {
			$nodesArray[$key]['score'] = $value;
		}

		if (!$firstloop) {
			$nodesArray[$key]['score'] += $value;
		}


		$nodesArray[$key]['route'] .= "->" . $node . "->" . $key;
	}

	return $nodesArray;
}

/**
 * [setInfinity set all scores to infinity before start of cycle.
 * leave out the start node]
 * @param [int] $start [the start node]
 * @param [array] $graph [the graph array]
 * @return [array] the node array
 */
function setInfinity($start, $graph)
{
	$nodesArray = [];

	for ($i=0; $i < count($graph); $i++) { 
		
		if ($i == $start) {
			continue;
		}
		$nodesArray[$i]['node'] = $i;
		$nodesArray[$i]['score'] = 100000;
		$nodesArray[$i]['route'] = '';
		
	}

	return $nodesArray;
}


 ?>