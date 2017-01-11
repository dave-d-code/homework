<?php 

class TrainsSolution {
	
	protected $graph = [];

	public function __construct($graph) {
		$this->graph = $graph;
	}


	/**
	 * Question 9: length of shortest route from c to c.
	 * meant to be b to b
	 * @param  [type] $start  [description]
	 * @param  [type] $finish [description]
	 * @param  [type] $limit  [description]
	 * @return [type]         [description]
	 */
	public function getTrips($start, $finish, $limit) {
		$trips = 0;
		$q = new SplQueue();

		$q->push(['stop' => $start, 'count' => 1]);

		while ($q->isEmpty() == FALSE) {
			$currentNode = $q->top();
			$q->pop();

			if ($currentNode['count'] > $limit) {
				continue;
			}

			foreach($this->graph[$currentNode['stop']] as $stop => $value) {
				if ($stop == $finish) {
					$trips++;
				}
				$q->push(['stop' => $stop, 'count' => ($currentNode['count'] + 1)]);
			}
		}

		return $trips;
	}

	/**
	 * Question 8 find the shortest path from A to C.
	 * @param  [type] $start  [description]
	 * @param  [type] $finish [description]
	 * @return [type]         [description]
	 */
	public function findShortestPath($start, $finish) {
		$q = new SplQueue();

		$minDistance = [];
		$minDistance[$start] = 0;

		$q->push(['node' => $start, 'distance' => 0]);

		while ($q->isEmpty() == FALSE) {
			$currentNode = $q->top();
			$q->pop();

			if ($currentNode['distance'] > $minDistance[$currentNode['node']]) {
				continue;
			}

			foreach($this->graph[$currentNode['node']] as $neighbour => $distance) {
				$alternativeDistance = $distance + $minDistance[$currentNode['node']];

				if (!isset($minDistance[$neighbour]) || $alternativeDistance < $minDistance[$neighbour]) {
					$minDistance[$neighbour] = $alternativeDistance;
					$q->push(['node' => $neighbour, 'distance' => $alternativeDistance]);
				}
			}
		}

		return $minDistance[$finish];
	}

	/**
	 * Ques
	 * @param  [type] $start  [description]
	 * @param  [type] $finish [description]
	 * @param  [type] $limit  [description]
	 * @return [type]         [description]
	 */
	public function getTripsWithDistanceLimit($start, $finish, $limit) {
		$trips = 0;
		$q = new SplQueue();

		$q->push(['stop' => $start, 'distance' => 0]);



		while ($q->isEmpty() == FALSE) {
			$currentNode = $q->top();
			$q->pop();

			foreach($this->graph[$currentNode['stop']] as $stop => $value) {
				$distance = $currentNode['distance'] + $value;

				if ($distance >= $limit) {
					continue;
				}

				if ($stop == $finish) {
					$trips++;
				}
				$q->push(['stop' => $stop, 'distance' => $distance]);
			}
		}

		return $trips;
	}
}

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

$trainsSol = new TrainsSolution($graph);

//echo "C->*->C: ", $trainsSol->getTrips(2, 2, 3), "\n";
//echo "A->C shortest: ", $trainsSol->findShortestPath(0, 2), "\n";
echo "C->*->C: ", $trainsSol->getTripsWithDistanceLimit(2, 2, 30), "\n";

 ?>