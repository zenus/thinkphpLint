<?php
/**
File Alteration Monitor Functions.

See: {@link http://www.php.net/manual/en/ref.fam.php}
@package fam
*/


/*. resource .*/ function fam_open( /*. args .*/){}
/*. void .*/ function fam_close(/*. resource .*/ $id){}
/*. resource .*/ function fam_monitor_directory(/*. resource .*/ $id, /*. string .*/ $dirname){}
/*. resource .*/ function fam_monitor_file(/*. resource .*/ $id, /*. string .*/ $filename){}
/*. resource .*/ function fam_monitor_collection(/*. resource .*/ $id, /*. string .*/ $dirname, /*. int .*/ $depth, /*. string .*/ $mask){}
/*. bool  .*/ function fam_suspend_monitor(/*. resource .*/ $id, /*. resource .*/ $monitor_id){}
/*. bool  .*/ function fam_resume_monitor(/*. resource .*/ $id, /*. resource .*/ $monitor_id){}
/*. bool  .*/ function fam_cancel_monitor(/*. resource .*/ $id, /*. resource .*/ $monitor_id){}
/*. int   .*/ function fam_pending(/*. resource .*/ $id){}
/*. array .*/ function fam_next_event(/*. resource .*/ $id){}
