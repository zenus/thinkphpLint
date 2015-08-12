<?php

/*. require_module 'standard'; .*/

namespace it\icosaedro\io;

/**
 *
 * Signals a generic I/O exception. Includes: invalid path; invalid file
 * name; access denied to the file or to some part of the path.
 *
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2012/03/17 17:10:42 $
 */
class IOException extends \Exception {}
