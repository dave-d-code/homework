<?php 

// maybe an oversimplistic approach
// 

$graph = array(
	'A' => array('B' => 5, 'D' => 5, 'E' => 7),
	'B' => array('C' => 4),
	'C' => array('D' => 8, 'E' => 2),
	'D' => array('C' => 8, 'E' => 6),
	'E' => array('B' => 3),

	);


// take out the letters and use indexes instead

// A = 0, B = 1, C = 2, D = 3, E = 4;

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


$a = 0;
$b = 4;

$q = array();

foreach (array_keys($graph) as $value) {
	$q[$value] = 999999;
	$q[$a] = 0;
}

var_dump($q);







 ?>