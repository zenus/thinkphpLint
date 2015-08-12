<?php
/**
Forms Data Format Functions.

This module now available from PECL.
See: {@link http://www.php.net/manual/en/ref.fdf.php}
@package fdf
*/

# These values are all dummy:
define('FDFValue', 1);
define('FDFStatus', 2);
define('FDFFile', 3);
define('FDFID', 4);
define('FDFFf', 5);
define('FDFSetFf', 6);
define('FDFClearFf', 7);
define('FDFFlags', 8);
define('FDFSetF', 9);
define('FDFClrF', 10);
define('FDFAP', 11);
define('FDFAS', 12);
define('FDFAction', 13);
define('FDFAA', 14);
define('FDFAPRef', 15);
define('FDFIF', 16);
define('FDFEnter', 17);
define('FDFExit', 18);
define('FDFDown', 19);
define('FDFUp', 20);
define('FDFFormat', 21);
define('FDFValidate', 22);
define('FDFKeystroke', 23);
define('FDFCalculate', 24);
define('FDFNormalAP', 25);
define('FDFRolloverAP', 26);
define('FDFDownAP', 27);

/*. resource .*/ function fdf_open(/*. string .*/ $filename){}
/*. resource .*/ function fdf_open_string(/*. string .*/ $fdf_data){}
/*. resource .*/ function fdf_create(){}
/*. void .*/ function fdf_close(/*. resource .*/ $fdfdoc){}
/*. string.*/ function fdf_get_value(/*. resource .*/ $fdfdoc, /*. string .*/ $fieldname /*., args .*/){}
/*. bool  .*/ function fdf_set_value(/*. resource .*/ $fdfdoc, /*. string .*/ $fieldname, /*. mixed .*/ $value /*., args .*/){}
/*. string.*/ function fdf_next_field_name(/*. resource .*/ $fdfdoc /*., args .*/){}
/*. bool  .*/ function fdf_set_ap(/*. resource .*/ $fdfdoc, /*. string .*/ $fieldname, /*. int .*/ $face, /*. string .*/ $filename, /*. int .*/ $pagenr){}
/*. bool  .*/ function fdf_get_ap(/*. resource .*/ $fdfdoc, /*. string .*/ $fieldname, /*. int .*/ $face, /*. string .*/ $filename){}
/*. string.*/ function fdf_get_encoding(/*. resource .*/ $fdf){}
/*. bool  .*/ function fdf_set_status(/*. resource .*/ $fdfdoc, /*. string .*/ $status){}
/*. string.*/ function fdf_get_status(/*. resource .*/ $fdfdoc){}
/*. bool  .*/ function fdf_set_file(/*. resource .*/ $fdfdoc, /*. string .*/ $filename /*., args .*/){}
/*. string.*/ function fdf_get_file(/*. resource .*/ $fdfdoc){}
/*. bool  .*/ function fdf_save(/*. resource .*/ $fdfdoc /*., args .*/){}
/*. string.*/ function fdf_save_string(/*. resource .*/ $fdfdoc){}
/*. bool  .*/ function fdf_add_template(/*. resource .*/ $fdfdoc, /*. int .*/ $newpage, /*. string .*/ $filename, /*. string .*/ $template, /*. int .*/ $rename){}
/*. bool  .*/ function fdf_set_flags(/*. resource .*/ $fdfdoc, /*. string .*/ $fieldname, /*. int .*/ $whichflags, /*. int .*/ $newflags){}
/*. int   .*/ function fdf_get_flags(/*. resource .*/ $fdfdoc, /*. string .*/ $fieldname, /*. int .*/ $whichflags){}
/*. bool  .*/ function fdf_set_opt(/*. resource .*/ $fdfdoc, /*. string .*/ $fieldname, /*. int .*/ $element, /*. string .*/ $value, /*. string .*/ $name){}
/*. mixed .*/ function fdf_get_opt(/*. resource .*/ $fdfdof, /*. string .*/ $fieldname /*., args .*/){}
/*. bool  .*/ function fdf_set_submit_form_action(/*. resource .*/ $fdfdoc, /*. string .*/ $fieldname, /*. int .*/ $whichtrigger, /*. string .*/ $url, /*. int .*/ $flags){}
/*. bool  .*/ function fdf_set_javascript_action(/*. resource .*/ $fdfdoc, /*. string .*/ $fieldname, /*. int .*/ $whichtrigger, /*. string .*/ $script){}
/*. bool  .*/ function fdf_set_encoding(/*. resource .*/ $fdf_document, /*. string .*/ $encoding){}
/*. int   .*/ function fdf_errno(){}
/*. string.*/ function fdf_error( /*. args .*/){}
/*. string.*/ function fdf_get_version( /*. args .*/){}
/*. bool  .*/ function fdf_set_version(/*. resource .*/ $fdfdoc, /*. string .*/ $version){}
/*. bool  .*/ function fdf_add_doc_javascript(/*. resource .*/ $fdfdoc, /*. string .*/ $scriptname, /*. string .*/ $script){}
/*. bool  .*/ function fdf_set_on_import_javascript(/*. resource .*/ $fdfdoc, /*. string .*/ $script /*., args .*/){}
/*. bool  .*/ function fdf_set_target_frame(/*. resource .*/ $fdfdoc, /*. string .*/ $target){}
/*. bool  .*/ function fdf_remove_item(/*. resource .*/ $fdfdoc, /*. string .*/ $fieldname, /*. int .*/ $item){}
/*. array .*/ function fdf_get_attachment(/*. resource .*/ $fdfdoc, /*. string .*/ $fieldname, /*. string .*/ $savepath){}
/*. bool  .*/ function fdf_enum_values(/*. resource .*/ $fdfdoc, /*. string .*/ $function_ /*., args .*/){}
/*. void .*/ function fdf_header(){}
