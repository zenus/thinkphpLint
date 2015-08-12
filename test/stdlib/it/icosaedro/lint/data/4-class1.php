<?php

/*. require_module 'standard'; .*/


# Testing visibility attributes:
# ------------------------------

class C1 {

	/*. private .*/ var $priv = 1;
	/*. protected .*/ var $prot = 1;
	/*. public .*/ var $publ = 0;

	/*. private void .*/ function priv(){}
	/*. protected void .*/ function prot(){}
	/*. public void .*/ function publ(){}

}

class C2 extends C1 {

	/*. void .*/ function C2()
	{
		$this->priv = 0;
		$this->prot = 0;
		$this->publ = 0;
		$this->priv();
		$this->prot();
		$this->publ();
	}
}

$o1 = new C1();
$o1->priv = 0;
$o1->prot = 0;
$o1->publ = 0;
$o1->priv();
$o1->prot();
$o1->publ();


# Testing final attribute:
# ------------------------

/*. final .*/ class C3 {
	/*. final void .*/ function f(){}
}

class C4 extends C3 {
	/*. void .*/ function f(){}
}


# Testing static attribute:
# -------------------------

class C5 {

	/*. public static void .*/ function static_func()
	{
		if( $this === NULL ) ;
	}

	/*. public void .*/ function non_static_func()
	{
		if( $this === NULL ) ;
	}

}

$o2 = new C5();
$o2->non_static_func();
C5::non_static_func();
$o2->static_func();
C5::static_func();


# Testing methods overriding:
# ---------------------------

class C6 {
	/*. void .*/ function m1(){}
	/*. int  .*/ function m2(){}
	/*. void .*/ function m3(/*. int .*/ $x){}
	/*. void .*/ function m4(/*. int .*/ $x = 0){}
	/*. void .*/ function m5(/*. args .*/){}
}

class C7 extends C6 {
	# No changes in signatures:
	/*. void .*/ function m1(){}
	/*. int  .*/ function m2(){}
	/*. void .*/ function m3(/*. int .*/ $x){}
	/*. void .*/ function m4(/*. int .*/ $x = 0){}
	/*. void .*/ function m5(/*. args .*/){}
}

class C8 extends C6 {
	# Allowed changes in signatures:
	/*. void .*/ function m1($x = 0){}
	/*. int  .*/ function m2(/*. args .*/){}
	/*. void .*/ function m3(/*. int .*/ $x, $y = ""){}
	/*. void .*/ function m4(/*. int .*/ $x = 0, $y = 0){}
	/*. void .*/ function m5(/*. args .*/){}
}

class C9 extends C6 {
	# Invalid changes in signatures:
	/*. int  .*/ function m1(){}
	/*. void .*/ function m2(){}
	/*. void .*/ function m3(/*. int .*/ $x, /*. int .*/ $y){}
	/*. void .*/ function m4(/*. float .*/ $x = 0.0){}
	/*. void .*/ function m5(){}
}


# Testing abstract classes:
# -------------------------


/*. abstract .*/ class StringContainer {

	/*. abstract void .*/ function set(
		/*. string .*/ $name,
		/*. string .*/ $value){}
	/*. abstract string .*/ function get(/*. string .*/ $name){}

	/*. abstract void .*/ function dispose(){}
}


class StringOnFile extends StringContainer {

	/*. private .*/ var /*. string .*/ $dir;

	/*. void .*/ function StringOnFile()
	{
		do {
			$this->dir = "strings-" . rand();
		} while( file_exists( $this->dir ) );
		mkdir( $this->dir );
	}

	/*. void .*/ function set(
		/*. string .*/ $name,
		/*. string .*/ $value)
	{
		file_put_contents( $this->dir ."/$name", $value);
	}

	/*. string .*/ function get(/*. string .*/ $name)
	{
		$s = file_get_contents( $this->dir ."/$name" );
		return ($s===FALSE)? /*. (string) .*/ NULL : $s;
	}

	/*. void .*/ function dispose()
	{
		system( "rm -r ". $this->dir );
	}
}


class StringOnSession extends StringContainer {

	/*. private .*/ var /*. string .*/$arr;

	/*. void .*/ function StringOnFile()
	{
		do {
			$this->arr = "strings-" . rand();
		} while( isset( $_SESSION[ $this->arr ] ) );
		$_SESSION[ $this->arr ] = array();
	}

	/*. void .*/ function set(
		/*. string .*/ $name,
		/*. string .*/ $value)
	{
		$arr = /*.(array[string]string).*/ & $_SESSION[ $this->arr ];
		$arr[$name] = $value;
	}

	/*. string .*/ function get(/*. string .*/ $name)
	{
		$arr = /*.(array[string]string).*/ & $_SESSION[ $this->arr ];
		if( ! isset( $arr[$name] ) )
			return (string) NULL;
		return $arr[$name];
	}

	/*. void .*/ function dispose()
	{
		unset( $_SESSION[ $this->arr ] );
	}
}


/*. void .*/ function SaveParams(
	/*. array[string]string .*/ & $params,
	/*. StringContainer		.*/ $container)
{
	foreach($params as $k => $v)
		$container->set($k, $v);
}


?>
