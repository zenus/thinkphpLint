<?php

/*. require_module 'spl'; .*/

namespace it\icosaedro\containers;

require_once __DIR__ . "/../../../all.php";

/**
	Holds a set of elements that can be quickly retrieved through their hash
	code.

	Elements can be of any type, but performances are much better if these
	elements implement the {@link it\icosaedro\containers\Hashable} interface, so that the getHash()
	and the equals() methods allows to retrive quickly the requested element.
	
	For elements that do not provide the Hashable interface, the generic
	functions provided by the {@link it\icosaedro\containers\Hash} class are applied to calculate the
	hash, while the function {@link it\icosaedro\containers\Equality::areEqual()} provides a quite
	weak surrogate concept of equality.

	Example:

	<pre>
	echo "Example 1: set of strings:\n";
	use it\icosaedro\containers\HashSet;
	$s = new HashSet();
	$s-&gt;put("one");
	$s-&gt;put("two");
	$s-&gt;put("three");
	$s-&gt;put("four");
	if( $s-&gt;contains("three") )
		echo "three belongs to the set";

	echo "Example 2: set of Date objects:\n";
	use it\icosaedro\utils\Date;
	$s = new HashSet();
	$s-&gt;put(new Date(2012, 1, 1));
	$s-&gt;put(new Date(2011, 12, 25));
	$s-&gt;put(new Date(2012, 1, 06));
	# Tells if today is holiday:
	$today = Date::today();
	if( $s-&gt;contains( Date::today() ) )
		echo "today is holiday\n";
	echo "Next holidays:\n";
	foreach($s as $m){
		$d = cast("it\\icosaedro\\utils\\Date", $m);
		if( $d-&gt;compareTo($today) &gt; 0 ){
			echo $d, "\n";
		}
	}
	</pre>

	@author Umberto Salsi <salsi@icosaedro.it>
	@version $Date: 2015/03/03 16:23:56 $
*/
class HashSet implements \Countable, \Iterator, Printable, Comparable {

	/* Default initial length of the slots table. */
	/*. private .*/ const INITSIZE = 10;

	/*
		Holds one slot for each distinct hash value. First index is the hash
		shared by all the elements contained in the slot. The second index is an
		array of the elements.
	*/
	private /*. mixed[int][int] .*/ $slots;


	/* Number of elements in the set. */
	private $load = 0;
	

	/* Cursor of the iterator: */

	/* - selected slot: */
	private $iter_slots_index = 0;

	/* - selected element in current slot: */
	private $iter_slot_index = 0;


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
		@return void
	*/
	public function __clone()
	{
		$this->iter_slots_index = 0;
		$this->iter_slot_index = 0;
		// FIXME: trim array of slots according to actual size
	}


	/**
		Creates a new empty set.
		@return void
	*/
	function __construct()
	{
		$this->slots = self::new_array(self::INITSIZE);
		$this->load = 0;
	}


	/* Calculates the slot index from the hash of the element. */
	private /*. int .*/ function indexOf(/*. mixed .*/ $elem)
	{
		return (Hash::hashOf($elem) & PHP_INT_MAX) % count($this->slots);
	}


	/**
		Empty the set.
		@return void
	*/
	function clear()
	{
		$this->slots = self::new_array(self::INITSIZE);
		$this->load = 0;
	}


	/**
		Tests if the given element belongs to the set.
		@param mixed $element The element.
		@return bool True if the element belongs to the set.
	*/
	function contains($element)
	{
		$i = $this->indexOf($element);

		$slot = $this->slots[$i];
		for($i = count($slot)-1; $i >= 0; $i--)
			if( Equality::areEqual($slot[$i], $element) )
				return TRUE;
		return FALSE;
	}


	/**
		Adds the element to the set. If an equal element is found,
		does nothing.
		@param mixed $element The element to add to the set.
		@return boolean TRUE if the element was not present in the set;
		FALSE if the element is already present in the set.
	*/
	function put($element)
	{
		$i = $this->indexOf($element);
		if( $this->slots[$i] === NULL ){
			$this->slots[$i] = array($element);
			$this->load++;
			return TRUE;
		}

		$slot = $this->slots[$i];

		for($j = count($slot)-1; $j >= 0; $j--)
			if( Equality::areEqual($slot[$j], $element) )
				return FALSE;

		# Element not found - add entry:
		$this->slots[$i][] = $element;
		$this->load++;
		
		$size = count($this->slots);
		if( $this->load > $size && $size < PHP_INT_MAX ){
			# Expands slots table to prevent element hash collisions:
			if( $size > (PHP_INT_MAX >> 1) )
				$size = PHP_INT_MAX;
			else
				$size = 2*$size;
			$old = $this->slots;
			$this->slots = self::new_array($size);
			$this->load = 0;
			for($i = count($old)-1; $i >= 0; $i--){
				$slot = $old[$i];
				for($j = count($slot)-1; $j >= 0; $j--){
					$this->put($slot[$j]);
				}
			}
		}
		
		return TRUE;
	}


	/**
		Adds all the elements of another set to this set.
		@param HashSet $s The set to add to this.
		@return void
	*/
	function putSet($s)
	{
		foreach($s->slots as $slot)
			for($i = count($slot)-1; $i >= 0; $i--)
				$this->put($slot[$i]);
	}


	/**
		Removes an element from the set.
		@param mixed $element  The element to remove.
		@return void
	*/
	function remove($element)
	{
		$i = $this->indexOf($element);

		$slot = $this->slots[$i];

		$n = count($slot);
		$j = $n - 1;
		for(; $j >= 0; $j--)
			if( Equality::areEqual($slot[$j], $element) )
				break;
		if( $j >= 0 ){
			# Element found - remove entry:
			if( $n == 1 ){
				# Slot contains only this element - empty the slot:
				$this->slots[$i] = NULL;
			} else {
				# Slot contains at least another element.
				# Replace removed entry $j with the last one:
				if( $j+1 < count($slot) ){
					# There is at least one element next to that to remove.
					# Replace this element with the last one of the slot:
					$this->slots[$i][$j] = $this->slots[$i][$n-1];
				}
				# Remove last element:
				unset($this->slots[$i][$n-1]);
			}
			$this->load--;
		}
	}


	/**
		Returns the number of elements in the set.
		@return int Number of elements in the set.
	*/
	function count()
	{
		return $this->load;
	}


	/**
		Returns all the elements as an array.
		@return mixed[int] All the elements.
	*/
	function getElements()
	{
		$elements = /*. (mixed[int]) .*/ array();
		for($j = count($this->slots)-1; $j >= 0; $j--){
			$slot = $this->slots[$j];
			for($i = count($slot)-1; $i >= 0; $i--)
				$elements[] = $slot[$i];
		}
		return $elements;
	}


	/*. string .*/ function __toString()
	{
		$e = self::getElements();
		$s = "";
		for($i = 0; $i < count($e); $i++){
			if( $i > 0 )
				$s .= ", ";
			$s .= \it\icosaedro\utils\TestUnit::dump($e[$i]);
		}
		return __CLASS__ . "($s)";
	}

	
	/**
		Compare this set with another set for equality.
		@param object $other The other set.
		@return bool True if $other is not NULL, belongs to this class
		(possibly extended) and the two sets contain the same elements
		according to the {@link it\icosaedro\containers\Equality::areEqual()} method.
	*/
	function equals($other)
	{
		if( ! ($other instanceof self) )
			return FALSE;
		$other2 = cast(__CLASS__, $other);
		if( $other2->count() != $this->count() )
			return FALSE;
		foreach($this as $e){
			if( ! $other2->contains($e) )
				return FALSE;
		}
		return TRUE;
	}


	/**
		Resets the position of the iterator to the first element of the set.
		If valid() returns false, the the set is empty.
		@return void
	*/
	function rewind()
	{
		$this->iter_slots_index = 0;
		$this->iter_slot_index = -1;
		$this->next();
	}


	/**
		Checks if the iterator is on a valid element. If valid, use current()
		to retrieve the element.
		@return bool True if the iterator is currently on an element of the
		set, in which case current() returns that element. Returns false if the
		set is empty or the internal cursor was already moved past the last
		element.
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
		return $this->slots[ $this->iter_slots_index ][ $this->iter_slot_index ];
	}


	/**
		Returns the element currently selected by the iterator. In this
		implementation this method is simply synonym of the {@link
		self::current()} and then it may return any type of data. This violates
		a bit the contract of the interface as stated in the PHP manual, which
		specifies the value returned must be a "scalar".
		@return mixed Element currently under the cursor.
		@throws \RuntimeException No element selected: missing call to rewind()
		or next() or data changed while accessing the iterator, either adding
		or removing elements.
	*/
	function key()
	{
		return $this->current();
	}


	/**
		Moves the iterator to the next element. Does nothing if the set is
		empty or the iterator ran past the last element.
		@return void
	*/
	function next()
	{
		while( $this->iter_slots_index < count($this->slots) ){
			$this->iter_slot_index++;
			$slot = $this->slots[ $this->iter_slots_index ];
			if( $this->iter_slot_index >= count($slot) ){
				$this->iter_slots_index++;
				$this->iter_slot_index = -1;
			} else {
				return;
			}
		}
	}


}
