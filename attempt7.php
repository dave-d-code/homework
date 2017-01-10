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

// set first row of table ROW 1
array_push($tableArray, setInfinity($start, $graph));

// TODO.. each code line written out like pseudocode.
// Code review and refactor all of this. needs obvious iteration.
// solution for shortest route A - C will be $tableArray[2][2].

$marked = returnMarked($tableArray[0]); // TODOneed to iterate start i at count
$markedArray = array_merge($markedArray, $marked); // push values of marked for next row 


// start a new row ROW 2
$newRow = [];

$newRow = array_merge($newRow, $markedArray);


$nextLine = calculateRow($graph, $tableArray, $marked, $newRow);
$newRow = array_merge($newRow, $nextLine);

// next mark to be added // first take out the zero

$temp = $newRow;
unset($temp[key($marked)]);

//$newRow['marked'] = returnMarked($temp);
$tableArray[] = $newRow;
/////////////////////////////  EVERYTHING FINE NEXT CYCLE /////////////////////////////////

// new marked variable
$marked = returnMarked($temp);

$markedArray = array_merge($markedArray, $marked);

////////////// start a new row ROW 3
$newRow = [];
$newRow = array_merge($newRow, $markedArray);


$nextLine = calculateRow($graph, $tableArray, $marked, $newRow);

$newRow = array_merge($newRow, $nextLine);

$tableArray[] = $newRow;

var_dump($tableArray);

// At this time, the solution is complete, as A - C is $tableArray[2][2];
// Code needs factoring to 

echo $tableArray[2][2] . "\r\n";






function calculateRow($graph, $tableArray, $marked, $newRow)
{
	$previousRow = count($tableArray) -1;
	$row = [];
	$mykey = key($marked);
	echo $marked[$mykey];


	for ($i=count($newRow); $i < count($tableArray[0]); $i++) { 

		$destinationValue = $tableArray[$previousRow][$i];
		$row[$i] = $tableArray[$previousRow][$i];

		if (array_key_exists($i, $graph[$mykey])) { 
			$row[$i] = calculateMinValue($destinationValue, $marked[$mykey], $graph[$mykey][$i]);
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
	$key = min(array_keys($row, min($row)));
	return array($key => $row[$key]);
}


function calculateMinValue($destinationValue, $marked, $edge)
{
	return min($destinationValue, ((int) $marked + $edge));
}


 ?>