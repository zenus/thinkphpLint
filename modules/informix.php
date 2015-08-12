<?php
/** Informix Functions.

See: {@link http://www.php.net/manual/en/ref.informix.php}

<p>
This extension has moved to PECL since PHP 5.2.1.
@package informix
*/

define('IFX_SCROLL', 1);
define('IFX_HOLD', 1);
define('IFX_LO_RDONLY', 1);
define('IFX_LO_WRONLY', 1);
define('IFX_LO_APPEND', 1);
define('IFX_LO_RDWR', 1);
define('IFX_LO_BUFFER', 1);
define('IFX_LO_NOBUFFER', 1);

/*. resource .*/ function ifx_connect( /*. args .*/){}
/*. resource .*/ function ifx_pconnect( /*. args .*/){}
/*. bool .*/ function ifx_close( /*. args .*/){}
/*. resource .*/ function ifx_query(/*. string .*/ $query, /*. resource .*/ $connid /*., args .*/){}
/*. resource .*/ function ifx_prepare(/*. string .*/ $query, /*. resource .*/ $connid /*., args .*/){}
/*. bool .*/ function ifx_do(/*. resource .*/ $resultid){}
/*. string .*/ function ifx_error( /*. args .*/){}
/*. string .*/ function ifx_errormsg( /*. args .*/){}
/*. int .*/ function ifx_affected_rows(/*. resource .*/ $resultid){}
/*. array .*/ function ifx_fetch_row(/*. resource .*/ $resultid /*., args .*/){}
/*. int .*/ function ifx_htmltbl_result(/*. resource .*/ $resultid /*., args .*/){}
/*. array .*/ function ifx_fieldtypes(/*. resource .*/ $resultid){}
/*. array .*/ function ifx_fieldproperties(/*. resource .*/ $resultid){}
/*. int .*/ function ifx_num_rows(/*. resource .*/ $resultid){}
/*. array .*/ function ifx_getsqlca(/*. resource .*/ $resultid){}
/*. int .*/ function ifx_num_fields(/*. resource .*/ $resultid){}
/*. bool .*/ function ifx_free_result(/*. resource .*/ $resultid){}
/*. int .*/ function ifx_create_blob(/*. int .*/ $type, /*. int .*/ $mode, /*. string .*/ $param){}
/*. int .*/ function ifx_copy_blob(/*. int .*/ $bid){}
/*. int .*/ function ifx_free_blob(/*. int .*/ $bid){}
/*. string .*/ function ifx_get_blob(/*. int .*/ $bid){}
/*. int .*/ function ifx_update_blob(/*. int .*/ $bid, /*. string .*/ $content){}
/*. bool .*/ function ifx_blobinfile_mode(/*. int .*/ $mode){}
/*. bool .*/ function ifx_textasvarchar(/*. int .*/ $mode){}
/*. bool .*/ function ifx_byteasvarchar(/*. int .*/ $mode){}
/*. bool .*/ function ifx_nullformat(/*. int .*/ $mode){}
/*. int .*/ function ifx_create_char(/*. string .*/ $param){}
/*. string .*/ function ifx_get_char(/*. int .*/ $bid){}
/*. bool .*/ function ifx_free_char(/*. int .*/ $bid){}
/*. bool .*/ function ifx_update_char(/*. int .*/ $bid, /*. string .*/ $content){}
/*. int .*/ function ifxus_create_slob(/*. int .*/ $mode){}
/*. bool .*/ function ifxus_free_slob(/*. int .*/ $bid){}
/*. bool .*/ function ifxus_close_slob(/*. int .*/ $bid){}
/*. int .*/ function ifxus_open_slob(/*. int .*/ $bid, /*. int .*/ $mode){}
/*. int .*/ function ifxus_tell_slob(/*. int .*/ $bid){}
/*. int .*/ function ifxus_seek_slob(/*. int .*/ $bid, /*. int .*/ $mode, /*. int .*/ $offset){}
/*. string .*/ function ifxus_read_slob(/*. int .*/ $bid, /*. int .*/ $nbytes){}
/*. int .*/ function ifxus_write_slob(/*. int .*/ $bid, /*. string .*/ $content){}
