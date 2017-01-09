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

$nodesArray = [];
$successArray = [];
$visitedNodes = [];


$nodesArray = setInfinity($start, $graph);

for ($i=0; $i < 2; $i++) { 
	$nodesArray = countScores($graph, $i, $nodesArray, $finish);
	
	usort($nodesArray, function($a, $b) {
		return $a['score'] <=> $b['score'];
	});
}

echo "nodesArray is \r\n";
var_dump($nodesArray);
//echo "successArray \r\n";
//var_dump($successArray);


/**
 * [countScores description]
 * @param  [type] $graph      [description]
 * @param  [type] $node       [description]
 * @param  [type] $nodesArray [description]
 * @param  [type] $finish     [description]
 * @return [type]             [description]
 */
function countScores($graph, $node, $nodesArray, $finish)
{
	global $successArray;

	foreach ($graph[$node] as $key => $value) {
		
		$key1 = array_search($key, $nodesArray['node']);

		if ($nodesArray[$key1]['score'] == 100000) {
			$nodesArray[$key1]['score'] = 0;
		}

		$nodesArray[$key1]['score'] += $value; // this wont do in cases of the shorter routes 
		$nodesArray[$key1]['route'] .= "->" . $node . "->" . $key;

		if ($key == $finish) {
			array_push($successArray, $nodesArray[$key1]); // check this
		}
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