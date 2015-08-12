/**
 * Build the table of contents of the current HTML page.
 * Only H2 and H3 elements are considered; skip the element whose ID is the
 * parameter. Example:
 * 
 * <body onload="buildTOC('toc');">
 * <h2 id="toc">Table of Contents</h2>
 * 
 * @param string tocId Identifier of the DOM node whose inner text has to be
 * fillen-in with the TOC.
 */
function buildTOC(tocId)
{
	var loc = document.getElementById(tocId);
	if ( ! loc ){
		alert("Error: TOC element ID=" + tocId + " not found.");
		return;
	}
	var hs;
	if( document.querySelectorAll )
		// FF, Chrome:
		hs = document.querySelectorAll("H2,H3,H4");
	else
		// IE:
		//hs = document.getElementsByName("H2");
		hs = document.getElementsByTagName("H2");

	var toc = '';

	for(i=0; i<hs.length; i++){
		var h = hs[i];
		if ( h.id == tocId )
			continue;
		if( h.textContent )
			title = h.textContent;
		else
			title = h.innerText;
		var href = "H" + i + "_" + title.replace(/ /g, '_');
		var indent = "";
		if( h.tagName == "H3" )
			indent = '<span style="margin: 2em">&nbsp;</span>';
		else if ( h.tagName == "H4" )
			indent = '<span style="margin: 4em">&nbsp;</span>';
		toc += indent + "<a href='#" + href + "'>" + title + "</a><br/>";
		var a = document.createElement("a");
		a.name = href;
		a.id = href; // IE wants an ID (!?)
		h.parentElement.insertBefore(a, h);
	}
	
	var bq = document.createElement("blockquote");
	bq.innerHTML = "<blockquote>" + toc + "</blockquote>";
	loc.parentElement.insertBefore(bq, loc.nextSibling);
	//document.getElementById(tocId).innerHTML = "<blockquote>" + toc + "</blockquote>";
}
