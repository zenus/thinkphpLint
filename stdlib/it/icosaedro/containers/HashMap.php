<?php

/*. require_module 'spl'; .*/

namespace it\icosaedro\containers;

require_once __DIR__ . "/../../../all.php";

use RuntimeException;

/**
	Holds a set of (key,element) pairs and allows fast retrieval of one element
	given its key. Keys are univocal in the map, but elements can be duplicated
	with different keys.

	Keys and elements can be of any type, but performances are much better if
	the keys implement the {@link it\icosaedro\containers\Hashable} interface, so that keys provide the
	methods getHash() and equals() that allows to quickly lookup the requested
	(key,element) pair.

	For keys that do not provide the Hashable interface, the generic functions
	provided by the {@link it\icosaedro\containers\Hash} class are applied to calculate the hash, while
	the function {@link it\icosaedro\containers\Equality::areEqual()} provides a quite weak, surrogate
	concept of equality.

	Example:

	<pre>
	# Example 1: maps (string,int) pairs:
	use it\icosaedro\containers\HashMap;
	$m = new HashMap();
	$m-&gt;put("one", 1);
	$m-&gt;put("two", 2);
	$m-&gt;put("three", 3);
	$m-&gt;put("four", 4);
	$k = "three";
	$e = $m-&gt;get($k);
	if( $e === NULL )
		echo "does not contain $k";
	else
		echo "found $k:", (int) $e;
	# ==&gt; found three: 3

	# Example 2: maps (Date,string) pairs:
	use it\icosaedro\utils\Date;
	$m = new HashMap();
	$m-&gt;put(new Date(2012, 1, 1), "year 2012 begins");
	$m-&gt;put(new Date(2011, 12, 31), "year 2011 ends");
	$m-&gt;put(new Date(2012, 2, 29), "leap day of the 2012");

	# Displays quote of the day:
	$quote = $m-&gt;get( Date::today() );
	if( $quote !== NULL )
		echo "Quote of the day: ", (string) $quote;
	# ==&gt; (depends on the current day :-)

	echo "Next notable dates:\n";
	foreach($m as $k =&gt; $e){
		$d = cast("it\\icosaedro\\utils\\Date", $k);
		if( $d-&gt;compareTo( Date::today() ) &gt; 0 ){
			$quote = (string) $e;
			echo "$d: $quote\n";
		}
	}
	</pre>
	@author Umberto Salsi <salsi@icosaedro.it>
	@version $Date: 2015/02/06 10:04:04 $
*/
class HashMap implements \Countable, \Iterator, Printable, Comparable {

	/* Default initial length of the slots table. */
	/*. private .*/ const INITSIZE = 10;

	/*
		Holds one slot for each distinct hash value. First index is the hash
		shared by all the keys contained in the slot. The second index is an
		array of the key/element pairs in the order key0, elem0, key1, elem1,
		...
	*/
	private /*. mixed[int][int] .*/ $slots;

	/* Number of elements in the slots table. */
	private $load = 0;


	/*
		Creates a slots table of the given length.
	*/
	private static /*. mixed[int][int] .*/ function new_array(/*. int .*/ $size)
	{
		$a = /*. (mixed[int][int]) .*/ array();
		for($i = 0; $i < $size; $i++)
			$a[$i] = NULL;
		return $a;
	}


	/**
		Creates a new empty hash map.
		@return void
	*/
	function __construct()
	{
		$this->slots = self::new_array(self::INITSIZE);
		$this->load = 0;
	}


	/**
		Empty the map.
		@return void
	*/
	function clear()
	{
		$this->slots = self::new_array(self::INITSIZE);
		$this->load = 0;
	}


	/* Calculates the slot index from the key. */
	private /*. int .*/ function indexOf(/*. mixed .*/ $key)
		/*. throws RuntimeException .*/
	{
		return (Hash::hashOf($key) & PHP_INT_MAX) % count($this->slots);
	}


	/**
		Tests if a key does exist in the map.
		@param mixed $key The key.
		@return bool True if the key belongs to this map.
	*/
	function containsKey($key)
	{
		# Check if contains the key's hash entry:
		$i = $this->indexOf($key);

		$slot = $this->slots[$i];
		for($i = count($slot)-2; $i >= 0; $i-=2)
			if( Equality::areEqual($slot[$i], $key) )
				return TRUE;
		return FALSE;
	}


	/**
		Tests if an element does exist in the map. This method performs a
		linear search over the whole map, so it is very inefficient.
		@param mixed $element Element we are looking for.
		@return bool True if at least one (key,element) pair has an element
		equal to the given value; equality is tested according to {@link
		it\icosaedro\containers\Equality::areEqual()}.
	*/
	function containsElement($element)
	{
		for($j = count($this->slots)-1; $j >= 0; $j--){
			$slot = $this->slots[$j];
			for($i = count($slot)-1; $i >= 0; $i-=2)
				if( Equality::areEqual($slot[$i], $element) )
					return TRUE;
		}
		return FALSE;
	}


	/**
		Retrieves an element given its key.
		@param mixed $key Key of the element.
		@return mixed The element, or NULL if not found. Note that the NULL
		value may be a valid element, so in this case you may want to check
		with {@link self::containsKey($k)} if the key is there.
	*/
	function get($key)
	{
		$i = $this->indexOf($key);
		$slot = $this->slots[$i];
		for($i = count($slot)-2; $i >= 0; $i-=2)
			if( Equality::areEqual($slot[$i], $key) )
				return $slot[$i+1];
		return NULL;
	}


	/**
		Inserts a (key,element) pair in the map. If a pair with the same
		key already exists, the new element replaces the old one.
		@param mixed $key The key.
		@param mixed $element The element.
		@return void
		@throws RuntimeException The key cannot be hashed because it is an
		unsupported type float, array or resource.
	*/
	function put($key, $element)
	{
		$i = $this->indexOf($key);
		if( $this->slots[$i] === NULL ){
			$this->slots[$i] = array($key, $element);
			$this->load++;
			return;
		}

		$slot = $this->slots[$i];

		# Search entry $slot[$j] with the same key:
		$j = count($slot)-2;
		for(; $j >= 0; $j-=2)
			if( Equality::areEqual($slot[$j], $key) )
				break;

		if( $j >= 0 ){
			# Key found - replace element:
			$this->slots[$i][$j+1] = $element;
		} else {
			# Key not found - add entry:
			$this->slots[$i][] = $key;
			$this->slots[$i][] = $element;
			$this->load++;
			
			$size = count($this->slots);
			if( $this->load > $size && $size < PHP_INT_MAX ){
				# Expands array of slots to prevent key hash collisions:
				if( $size > (PHP_INT_MAX >> 1) )
					$size = PHP_INT_MAX;
				else
					$size = 2*$size;
				$old = $this->slots;
				$this->slots = self::new_array($size);
				$this->load = 0;
				for($i = count($old)-1; $i >= 0; $i--){
					$slot = $old[$i];
					for($j = count($slot)-2; $j >= 0; $j-=2){
						$this->put($slot[$j], $slot[$j+1]);
					}
				}
			}
		}
	}


	/**
		Add all the (key,element) pairs of another map to this map.
		On key collision, replace the element on this map.
		@param HashMap $m Map to add to this one.
		@return void
	*/
	function putMap($m)
	{
		foreach($m->slots as $slot)
			for($i = count($slot)-2; $i >= 0; $i -= 2)
				$this->put($slot[$i], $slot[$i+1]);
	}


	/**
		Remove a (key,element) pair from the map given its key.
		@param mixed $key Key of the pair to remove.
		@return void
		@throws RuntimeException The key cannot be hashed because it is an
		unsupported type float, resource or array.
	*/
	function remove($key)
	{
		$i = $this->indexOf($key);

		$slot = $this->slots[$i];

		$n = count($slot);
		# Search entry $slot[$j] with the same key:
		$j = $n - 2;
		for(; $j >= 0; $j-=2)
			if( Equality::areEqual($slot[$j], $key) )
				break;
		if( $j >= 0 ){
			# Key found - remove entry:
			if( $n == 2 ){
				# Slot contains only this pair - empty the slot:
				$this->slots[$i] = NULL;
			} else {
				# Slot contains at least another pair.
				# Replace removed entry $j with the last one:
				if( $j+2 < count($slot) ){
					# There is at least one pair next to that to remove.
					# Replace this pair with the last pair of the slot:
					$this->slots[$i][$j] = $this->slots[$i][$n-2];
					$this->slots[$i][$j+1] = $this->slots[$i][$n-1];
				}
				# Remove last pair:
				unset($this->slots[$i][$n-1]);
				unset($this->slots[$i][$n-2]);
			}
			$this->load--;
		}
	}


	/**
		Returns the number of (key,element) pairs in the map.
		@return int Number of (key,element) pairs in the map.
	*/
	function count()
	{
		return $this->load;
	}


	/**
		Returns all the keys as an array.
		@return mixed[int] All the keys.
	*/
	function getKeys()
	{
		$keys = /*. (mixed[int]) .*/ array();
		for($j = count($this->slots)-1; $j >= 0; $j--){
			$slot = $this->slots[$j];
			for($i = count($slot)-2; $i >= 0; $i-=2)
				$keys[] = $slot[$i];
		}
		return $keys;
	}


	/**
		Returns all the elements as an array.
		@return mixed[int] All the elements. Note that there may be duplicates
		of equal elements with different keys.
	*/
	function getElements()
	{
		$elements = /*. (mixed[int]) .*/ array();
		for($j = count($this->slots)-1; $j >= 0; $j--){
			$slot = $this->slots[$j];
			for($i = count($slot)-1; $i >= 0; $i-=2)
				$elements[] = $slot[$i];
		}
		return $elements;
	}


	/**
		Returns all the (key,element) pairs.
		@return mixed[int][int] All the (key,element) pairs. The first index is
		the pair, the second index evaluates to 0 for the key and 1 for the
		element.
	*/
	function getPairs()
	{
		$pairs = /*. (array[int][int]mixed) .*/ array();
		for($j = count($this->slots)-1; $j >= 0; $j--){
			$slot = $this->slots[$j];
			for($i = count($slot)-2; $i >= 0; $i-=2)
				$pairs[] = array($slot[$i], $slot[$i+1]);
		}
		return $pairs;
	}


	/*. string .*/ function __toString()
	{
		$pairs = self::getPairs();
		$s = "";
		for($i = 0; $i < count($pairs); $i++){
			if( $i > 0 )
				$s .= ", ";
			$s .= "(" . \it\icosaedro\utils\TestUnit::dump($pairs[$i][0])
				. \it\icosaedro\utils\TestUnit::dump($pairs[$i][1]) . ")";
		}
		return __CLASS__ . "($s)";
	}

	
	/**
		Compare this map with another map for equality.
		@param object $other The other map.
		@return bool True if the other map is not NULL, belongs to this same
		exact class (not extended) and the two maps contains the same
		(key,element) pairs. Elements are compared according to the {@link
		it\icosaedro\containers\Equality::areEqual()} method.
	*/
	function equals($other)
	{
		if( $other === NULL or get_class($other) !== __CLASS__ )
			return FALSE;
		try {
			$other2 = cast(__CLASS__, $other);
		}
		catch(\CastException $e){
			return FALSE;
		}
		$r = $other2->count() - $this->count();
		if( $other2->count() != $this->count() )
			return FALSE;
		foreach($this as $k => $e){
			if( ! $other2->containsKey($k) )
				return FALSE;
			$e2 = $other2->get($k);
			if( ! Equality::areEqual($e, $e2) )
				return FALSE;
		}
		return TRUE;
	}


	/* Cursor of the iterator: */

	/* - selected slot: */
	private $iter_slots_index = 0;

	/* - selected key in current slot: */
	private $iter_slot_index = 0;


	/**
		Resets the position of the iterator to the first element of the map.
		If valid() returns false, the map is empty.
		@return void
	*/
	function rewind()
	{
		$this->iter_slots_index = 0;
		$this->iter_slot_index = -2;
		$this->next();
	}


	/**
		Checks if the iterator is on a valid (key,element) pair. If valid, use
		key() and current() to retrieve the pair.
		@return bool True if the iterator is currently on a pair of the map.
		Returns false if the map is empty or the internal cursor was already
		moved past the last pair.
	*/
	function valid()
	{
		if( $this->iter_slots_index >= count($this->slots) )
			return FALSE;
		$slot = $this->slots[ $this->iter_slots_index ];
		if( $this->iter_slot_index >= count($slot) )
			return FALSE;
		return TRUE;
	}


	/**
		Returns the key of the pair currently selected by the iterator.
		@return mixed Key or the pair currently under the cursor.
		@throws \RuntimeException No pair selected: missing call to rewind()
		or next() or data changed while accessing the iterator, either adding
		or removing elements.
	*/
	function key()
	{
		if( ! $this->valid() )
			throw new \RuntimeException("no element selected - missing call to rewind()?");
		return $this->slots[ $this->iter_slots_index ][ $this->iter_slot_index];
	}


	/**
		Returns the element currently selected by the iterator.
		@return mixed Element currently selected or NULL if no element
		selected.
		@throws \RuntimeException No element selected: missing call to rewind()
		or next() or data changed while accessing the iterator, either adding
		or removing elements.
	*/
	function current()
	{
		if( ! $this->valid() )
			throw new \RuntimeException("no element selected - missing call to rewind()?");
		return $this->slots[ $this->iter_slots_index ][ $this->iter_slot_index + 1 ];
	}


	/**
		Moves the iterator to the next pair. Does nothing if the map is
		empty or the iterator ran past the last pair.
		@return void
	*/
	function next()
	{
		while( $this->iter_slots_index < count($this->slots) ){
			$this->iter_slot_index += 2;
			$slot = $this->slots[ $this->iter_slots_index ];
			if( $this->iter_slot_index >= count($slot) ){
				$this->iter_slots_index++;
				$this->iter_slot_index = -2;
			} else {
				return;
			}
		}
	}

}
