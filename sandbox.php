<?php 


$result = calculateMinValue()




function calculateMinValue($destinationValue, $marked, $edge)
{
	return min($destinationValue, ($marked + $edge));
}



function returnMarked($row)
{
	$key = min(array_keys($row, min($row)));
	return array($key => $row[$key]);
}






 ?>