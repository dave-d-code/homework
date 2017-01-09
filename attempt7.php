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


$tableArray = [];
$markedArray = [];

// set first row of table
array_push($tableArray, setInfinity($start, $graph));

$marked = returnMarked($tableArray[0]); // need to iterate start i at count
$node = 0; // work this out.. last value of marked.. index of


$tableArray[0]['marked'] = $marked; // sort

// start a new row
$newRow = [];

array_push($newRow, $tableArray[0]['marked']); // sort
$nextLine = calculateRow($graph, $tableArray, $marked, $newRow);
$newRow = array_merge($newRow, $nextLine);









function calculateRow($graph, $tableArray, $marked, $newRow)
{
	$previousRow = count($tableArray) -1;
	$row = [];
	$mykey = key($marked);

	for ($i=0; $i < count($graph); $i++) { 
		if (array_key_exists($i, $newRow)) {
			continue;
		}

		$destinationValue = $tableArray[$previousRow][$i];

		if (array_key_exists($i, $graph[$mykey])) {
			$row[$i] = $graph[$mykey][$i];
		} else {
			$row[$i] = $tableArray[$previousRow][$i];
		}
	}

	return $row;
}


function setInfinity($start, $graph)
{
	$row = [];
	$infinity = 10000;

	for ($i=0; $i < count($graph) ; $i++) { 
		if ($i == $start) {
			$row[$i] = 0;
			continue;
		}

		$row[$i] = $infinity;
	}

	return $row;
}

function returnMarked($row)
{
	return array_keys($row, min($row));
}

function calculateMinValue($destinationValue, $marked, $edge)
{
	return min($destinationValue, ($marked + $edge));
}


 ?>