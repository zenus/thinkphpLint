<?php
/** SimpleXML functions.

See: {@link http://www.php.net/manual/en/ref.simplexml.php}

@deprecated
This module cannot be effectively used under PHPLint because the class
SimpleXMLElement heavily relies on dynamically created properties, which is not
(and never will be) supported by PHPLint. Use the dom module instead along with
the cast() magic function to safely convert generic types.
@package simplexml
*/

/*. if_php_ver_4 .*/
	This_module_is_not_available_under_PHP_4
/*. end_if_php_ver .*/

/*. require_module 'dom'; .*/


class SimpleXMLElement
{
	/*. void .*/ function __construct(
		/*. string .*/ $data,
		$options = 0,
		$data_is_url = FALSE,
		$ns = "",
		$is_prefix = FALSE)
		/*. throws Exception .*/
		/*. triggers E_WARNING .*/{}

	/*. void .*/ function addAttribute(
		/*. string .*/ $name,
		/*. string .*/ $value,
		/*. string .*/ $namespace_ = NULL){}

	/*. void .*/ function addChild(
		/*. string .*/ $name,
		/*. string .*/ $value = NULL,
		/*. string .*/ $namespace_ = NULL){}

	/*. mixed .*/ function asXML(
		/*. string .*/ $filename = NULL)
		/*. triggers E_WARNING .*/{}

	/*. mixed .*/ function saveXML(
		/*. string .*/ $filename = NULL){}

	/*. array .*/ function attributes(
		/*. string .*/ $namespace_ = NULL,
		$is_prefix = FALSE){}
	
	/*. int .*/ function count(){}

	/*. array[string]string .*/ function getDocNamespaces(
		$recursive = FALSE){}

	/*. string .*/ function getName(){}

	/*. array[string]string .*/ function getNamespaces(
		$recursive = FALSE){}

	/*. bool .*/ function registerXPathNamespace(
		/*. string .*/ $prefix,
		/*. string .*/ $namespace_){}

	/*. array[int]SimpleXMLElement .*/ function xpath(
		/*. string .*/ $path)
		/*. triggers E_WARNING .*/{}

	/*. SimpleXMLElement .*/ function children(
		/*. string .*/ $namespace_ = NULL,
		$is_prefix = FALSE){}
}


/*. SimpleXMLElement .*/ function simplexml_import_dom(/*. DOMNode .*/ $node /*., args .*/){}
/*. SimpleXMLElement .*/ function simplexml_load_file(/*. string .*/ $filename /*., args .*/){}
/*. SimpleXMLElement .*/ function simplexml_load_string(/*. string .*/ $data /*., args .*/){}
