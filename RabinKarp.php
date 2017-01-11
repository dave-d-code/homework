<?php 

/**
* Rabin Karp hashing for PHP
*/
class RabinKarp
{

	private $pattern;
	private $patternHash;
	private $text;
	private $previousHash;

	private $radix;
	private $prime;
	private $position;

	function __construct($pattern)
	{
		$this->pattern = $pattern;
		$this->radix = 256;
		$this->prime = 100007;
		$this->previousHash = "";
		$this->position = 0;
		$this->patternHash = $this->generateHash($pattern);
	}

	private function generateHash($key)
	{
		$characterArray = str_split($key);
		$hash = 0;
		foreach ($characterArray as $character) {
			$hash = ($this->radix * $hash + ord($character)) % $this->prime; //  modulus 100007 
		}

		return $hash;
	}

	public function search($character)
	{
		$this->text .= $character;

		if (strlen($this->text) > strlen($this->pattern)) { // ??
			echo "returning false", "\r\n";
			return false;
		}

		$textHash = 0;
		echo $this->previousHash, "\r\n";

		if (empty($this->previousHash)) {
			$textHash = $this->generateHash($this->text);
			$this->previousHash = $textHash;
			$this->position = 0;
		} else {
			$characterArray = str_split($this->text);

			// main calculation

			$textHash = (($this->previousHash + $this->prime)
				- pow($this->radix, strlen($this->pattern) - 1)
				* ord($characterArray[$this->position]) % $this->prime) % $this->prime;

			$textHash = ($textHash * $this->radix + ord($character)) % $this->prime;

			$this->previousHash = $textHash;
			$this->position++;
		}

		if ($textHash == $this->patternHash) {
			echo "Hash Match Found!";
		}
	} // end of search function

	private function mod_pow($base, $exponent, $modulus)
	{
		$aux = 1;
		while ($exponent > 0) {
			if ($exponent % 2 == 1) {
				$aux = ($aux * $base) % $modulus;
			}

			$base = ($base * $base) % $modulus;
			$exponent /= 2;
		}

		return $aux;
	}

} // end of class


$test = new RabinKarp('ABC');
$test->search('Z');
$test->search('A');

 ?>