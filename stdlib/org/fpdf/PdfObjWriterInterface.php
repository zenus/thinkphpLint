<?php

namespace org\fpdf;

/**
 * Service interface for FPDF that writes binary data to the PDF document.
 * This interface is implemented by the FPDF class itself, and then used by
 * several other service classes to put their specific data (font, images, ...)
 * into the PDF document.
 */
interface PdfObjWriterInterface {
	
	/**
	 * Creates a new PDF object in the document. Subsequent 'put' operations
	 * are appended just below.
	 * @return int Assigned PDF object number.
	 */
	function addObj();
	
	/**
	 * Put binary data in the PDF document.
	 * @param string $bytes
	 * @return void
	 */
	function put($bytes);


	/**
	 * Put a strem section in the PDF document.
	 * @param string $bytes
	 * @return void
	 */
	function putStream($bytes);
	
}
