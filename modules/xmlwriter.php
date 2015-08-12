<?php
/** xmlwriter Functions.

See: {@link http://www.php.net/manual/en/ref.xmlwriter.php}
@package xmlwriter
*/

/*. bool .*/ function xmlwriter_set_indent(/*. resource .*/ $xmlwriter, /*. bool .*/ $indent){}
/*. bool .*/ function xmlwriter_set_indent_string(/*. resource .*/ $xmlwriter, /*. string .*/ $indentstring){}
/*. bool .*/ function xmlwriter_start_attribute(/*. resource .*/ $xmlwriter, /*. string .*/ $name){}
/*. bool .*/ function xmlwriter_end_attribute(/*. resource .*/ $xmlwriter){}
/*. bool .*/ function xmlwriter_start_attribute_ns(/*. resource .*/ $xmlwriter, /*. string .*/ $prefix, /*. string .*/ $name, /*. string .*/ $uri){}
/*. bool .*/ function xmlwriter_write_attribute(/*. resource .*/ $xmlwriter, /*. string .*/ $name, /*. string .*/ $content){}
/*. bool .*/ function xmlwriter_write_attribute_ns(/*. resource .*/ $xmlwriter, /*. string .*/ $prefix, /*. string .*/ $name, /*. string .*/ $uri, /*. string .*/ $content){}
/*. bool .*/ function xmlwriter_start_element(/*. resource .*/ $xmlwriter, /*. string .*/ $name){}
/*. bool .*/ function xmlwriter_start_element_ns(/*. resource .*/ $xmlwriter, /*. string .*/ $prefix, /*. string .*/ $name, /*. string .*/ $uri){}
/*. bool .*/ function xmlwriter_end_element(/*. resource .*/ $xmlwriter){}
/*. bool .*/ function xmlwriter_full_end_element(/*. resource .*/ $xmlwriter){}
/*. bool .*/ function xmlwriter_write_element(/*. resource .*/ $xmlwriter, /*. string .*/ $name, /*. string .*/ $content){}
/*. bool .*/ function xmlwriter_write_element_ns(/*. resource .*/ $xmlwriter, /*. string .*/ $prefix, /*. string .*/ $name, /*. string .*/ $uri, /*. string .*/ $content){}
/*. bool .*/ function xmlwriter_start_pi(/*. resource .*/ $xmlwriter, /*. string .*/ $target){}
/*. bool .*/ function xmlwriter_end_pi(/*. resource .*/ $xmlwriter){}
/*. bool .*/ function xmlwriter_write_pi(/*. resource .*/ $xmlwriter, /*. string .*/ $target, /*. string .*/ $content){}
/*. bool .*/ function xmlwriter_start_cdata(/*. resource .*/ $xmlwriter){}
/*. bool .*/ function xmlwriter_end_cdata(/*. resource .*/ $xmlwriter){}
/*. bool .*/ function xmlwriter_write_cdata(/*. resource .*/ $xmlwriter, /*. string .*/ $content){}
/*. bool .*/ function xmlwriter_write_raw(/*. resource .*/ $xmlwriter, /*. string .*/ $content){}
/*. bool .*/ function xmlwriter_text(/*. resource .*/ $xmlwriter, /*. string .*/ $content){}
/*. bool .*/ function xmlwriter_start_comment(/*. resource .*/ $xmlwriter){}
/*. bool .*/ function xmlwriter_end_comment(/*. resource .*/ $xmlwriter){}
/*. bool .*/ function xmlwriter_write_comment(/*. resource .*/ $xmlwriter, /*. string .*/ $content){}
/*. bool .*/ function xmlwriter_start_document(/*. resource .*/ $xmlwriter, /*. string .*/ $version, /*. string .*/ $encoding, /*. string .*/ $standalone){}
/*. bool .*/ function xmlwriter_end_document(/*. resource .*/ $xmlwriter){}
/*. bool .*/ function xmlwriter_start_dtd(/*. resource .*/ $xmlwriter, /*. string .*/ $name, /*. string .*/ $pubid, /*. string .*/ $sysid){}
/*. bool .*/ function xmlwriter_end_dtd(/*. resource .*/ $xmlwriter){}
/*. bool .*/ function xmlwriter_write_dtd(/*. resource .*/ $xmlwriter, /*. string .*/ $name, /*. string .*/ $pubid, /*. string .*/ $sysid, /*. string .*/ $subset){}
/*. bool .*/ function xmlwriter_start_dtd_element(/*. resource .*/ $xmlwriter, /*. string .*/ $name){}
/*. bool .*/ function xmlwriter_end_dtd_element(/*. resource .*/ $xmlwriter){}
/*. bool .*/ function xmlwriter_write_dtd_element(/*. resource .*/ $xmlwriter, /*. string .*/ $name, /*. string .*/ $content){}
/*. bool .*/ function xmlwriter_start_dtd_attlist(/*. resource .*/ $xmlwriter, /*. string .*/ $name){}
/*. bool .*/ function xmlwriter_end_dtd_attlist(/*. resource .*/ $xmlwriter){}
/*. bool .*/ function xmlwriter_write_dtd_attlist(/*. resource .*/ $xmlwriter, /*. string .*/ $name, /*. string .*/ $content){}
/*. bool .*/ function xmlwriter_start_dtd_entity(/*. resource .*/ $xmlwriter, /*. string .*/ $name, /*. bool .*/ $isparam){}
/*. bool .*/ function xmlwriter_end_dtd_entity(/*. resource .*/ $xmlwriter){}
/*. bool .*/ function xmlwriter_write_dtd_entity(/*. resource .*/ $xmlwriter, /*. string .*/ $name, /*. string .*/ $content){}
/*. resource .*/ function xmlwriter_open_uri(/*. resource .*/ $xmlwriter, /*. string .*/ $source){}
/*. resource .*/ function xmlwriter_open_memory(){}
/*. string .*/ function xmlwriter_output_memory(/*. resource .*/ $xmlwriter /*. , args .*/){}
/*. mixed .*/ function xmlwriter_flush(/*. resource .*/ $xmlwriter, $flush = TRUE){}

class XMLWriter
{
	/*. bool .*/ function setIndent(/*. bool .*/ $indent){}
	/*. bool .*/ function setIndentString(/*. string .*/ $indentstring){}
	/*. bool .*/ function startAttribute(/*. string .*/ $name){}
	/*. bool .*/ function endAttribute(){}
	/*. bool .*/ function startAttributeNs(/*. string .*/ $prefix, /*. string .*/ $name, /*. string .*/ $uri){}
	/*. bool .*/ function writeAttribute(/*. string .*/ $name, /*. string .*/ $content){}
	/*. bool .*/ function writeAttributeNs(/*. string .*/ $prefix, /*. string .*/ $name, /*. string .*/ $uri, /*. string .*/ $content){}
	/*. bool .*/ function startElement(/*. string .*/ $name){}
	/*. bool .*/ function startElementNs(/*. string .*/ $prefix, /*. string .*/ $name, /*. string .*/ $uri){}
	/*. bool .*/ function endElement(){}
	/*. bool .*/ function fullEndElement(){}
	/*. bool .*/ function writeElement(/*. string .*/ $name, /*. string .*/ $content){}
	/*. bool .*/ function writeElementNs(/*. string .*/ $prefix, /*. string .*/ $name, /*. string .*/ $uri, /*. string .*/ $content){}
	/*. bool .*/ function startPi(/*. string .*/ $target){}
	/*. bool .*/ function endPi(){}
	/*. bool .*/ function writePi(/*. string .*/ $target, /*. string .*/ $content){}
	/*. bool .*/ function startCdata(){}
	/*. bool .*/ function endCdata(){}
	/*. bool .*/ function writeCdata(/*. string .*/ $content){}
	/*. bool .*/ function writeRaw(/*. string .*/ $content){}
	/*. bool .*/ function text(/*. string .*/ $content){}
	/*. bool .*/ function startComment(){}
	/*. bool .*/ function endComment(){}
	/*. bool .*/ function writeComment(/*. string .*/ $content){}
	/*. bool .*/ function startDocument(/*. string .*/ $version /*. , args .*/){}
	/*. bool .*/ function endDocument(){}
	/*. bool .*/ function startDtd(/*. string .*/ $name, /*. string .*/ $pubid, /*. string .*/ $sysid){}
	/*. bool .*/ function endDtd(){}
	/*. bool .*/ function writeDtd(/*. string .*/ $name, /*. string .*/ $pubid, /*. string .*/ $sysid, /*. string .*/ $subset){}
	/*. bool .*/ function startDtdDlement(/*. string .*/ $name){}
	/*. bool .*/ function endDtdElement(){}
	/*. bool .*/ function writeDtdElement(/*. string .*/ $name, /*. string .*/ $content){}
	/*. bool .*/ function startDtdAttlist(/*. string .*/ $name){}
	/*. bool .*/ function endDtdAttlist(){}
	/*. bool .*/ function writeDtdAttlist(/*. string .*/ $name, /*. string .*/ $content){}
	/*. bool .*/ function startDtdEntity(/*. string .*/ $name, /*. bool .*/ $isparam){}
	/*. bool .*/ function endDtdEntity(){}
	/*. bool .*/ function writeDtdEntity(/*. string .*/ $name, /*. string .*/ $content){}
	/*. resource .*/ function openURI(/*. string .*/ $source){}
	/*. resource .*/ function openMemory(){}
	/*. string .*/ function outputMemory($flush = TRUE){}
	/*. mixed .*/ function flush($empty = TRUE){}
}
