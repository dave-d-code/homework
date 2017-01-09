<?php 


/**
* dijkstra
*/
class Dijkstra
{
	
	private $graph;
	private $infinity = 100000;
	private $start;
	private $finish;
	private $pathNodes = array();
	private $leftoutNodes = array();
	private $thePath = array();


	function __construct($graph, $start, $finish)
	{
		$this->graph = $graph;
		$this->start = $start;
		$this->finish = $finish;
	}

	public function calculateShortest()
	{ 
		foreach (array_keys($this->graph) as $value) {
			$this->pathNodes[$value] = $this->infinity;
			$this->pathNodes[$this->start] = 0;
		}

		while (!empty($this->pathNodes)) {
			$minimumChoice = array_search(min($this->pathNodes), $this->pathNodes);
			if ($minimumChoice == $this->finish) {
				break;
			}

			foreach ($this->graph[$minimumChoice] as $key => $value) {
				
				if (!empty($this->pathNodes[$key]) && $this->pathNodes[$minimumChoice] + $value < $this->pathNodes[$key]) {
					$this->pathNodes[$key] = $this->pathNodes[$minimumChoice] + $value;
					$this->leftoutNodes = array($minimumChoice, $this->pathNodes[$key]);
				}
			}

			unset($this->pathNodes[$minimumChoice]);
		}

		$position = $this->finish;

		while ($position != $this->start) {
			$this->thePath[] = $position;
			$position = $this->leftoutNodes[$position][0];
		}

		$this->thePath[] = $this->start;
		$this->thePath = array_reverse($this->thePath);

		echo "length is " . $this->leftoutNodes[$this->finish][1] . "\r\n";
		echo "path is " . implode('->', $this->thePath) . "\r\n";



	} // end of function


} // end of class

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
$finish = 4;

$test = new Dijkstra($graph, $start, $finish);
$test->calculateShortest();





 ?>