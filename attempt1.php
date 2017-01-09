<?php 

/**
* standard version of Dijkstra's algorithm
*/
class vertex
{

	public $key = null;
	public $visited = 0;
	public $distance = 1000000;
	public $parent = null;
	public $path = null;
	
	function __construct($key)
	{
		$this->key = $key;
	}
}

/**
* using PHP Spl library
*/
class PriorityQueue extends SplPriorityQueue
{
	
	public function compare($a, $b)
	{
		if ($a === $b) {
			return 0;
		}

		return $a > $b ? -1: 1;
	}
}

// define the nodes
$v0 = new vertex(0); // this is A
$v1 = new vertex(1); // this is B
$v2 = new vertex(2); // this is C
$v3 = new vertex(3); // this is D
$v4 = new vertex(4); // this is E

// define the routes...

// A routes
$list0 = new SplDoublyLinkedList();
$list0->push(array('vertex' => $v1, 'distance' => 5)); // AB5
$list0->push(array('vertex' => $v3, 'distance' => 5)); // AD5
$list0->push(array('vertex' => $v4, 'distance' => 7)); // AE7
$list0->rewind();

// B route
$list1 = new SplDoublyLinkedList();
$list1->push(array('vertex' => $v2, 'distance' => 4)); // BC4
$list1->rewind();

// C Route
$list2 = new SplDoublyLinkedList();
$list2->push(array('vertex' => $v3, 'distance' => 8)); // CD8
$list2->push(array('vertex' => $v4, 'distance' => 2)); // CE2
$list2->rewind();

// D Route
$list3 = new SplDoublyLinkedList();
$list3->push(array('vertex' => $v2, 'distance' => 8)); // DC8
$list3->push(array('vertex' => $v4, 'distance' => 6)); // DE6
$list3->rewind();


// E Route
$list4 = new SplDoublyLinkedList();
$list4->push(array('vertex' => $v1, 'distance' => 3)); // EB3
$list4->rewind();

$adjacencyList = array(
    $list0,
    $list1,
    $list2,
    $list3,
    $list4,
);

function calcShortestPaths(vertex $start, &$adjLists)
{
    // define an empty queue
    $q = new PriorityQueue();
 
    // push the starting vertex into the queue
    $q->insert($start, 0);
    $q->rewind();
 
    // mark the distance to it 0
    $start->distance = 0;
 
    // the path to the starting vertex
    $start->path = array($start->key);
 
    while ($q->valid()) {
        $t = $q->extract();
        $t->visited = 1;
 
        $l = $adjLists[$t->key];
        while ($l->valid()) {
            $item = $l->current();
 
            if (!$item['vertex']->visited) {
                if ($item['vertex']->distance > $t->distance + $item['distance']) {
                    $item['vertex']->distance = $t->distance + $item['distance'];
                    $item['vertex']->parent = $t;
                }
 
                $item['vertex']->path = array_merge($t->path, array($item['vertex']->key));
 
                $q->insert($item["vertex"], $item["vertex"]->distance);
            }
            $l->next();
        }
        $q->recoverFromCorruption();
        $q->rewind();
    }
}

calcShortestPaths($v0, $adjacencyList);
 
// The path from node 0 to node 5
// [0, 1, 2, 4, 5]
echo '[' . implode(', ', $v4->path) . ']' . "\r\n";







 ?>