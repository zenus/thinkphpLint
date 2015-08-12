<?php

namespace it\icosaedro\io;

require_once __DIR__ . "/../../../all.php";

/**
 * The file, or some component of the path, or the device are not accessible due
 * to permission constraints.
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2015/02/06 10:05:22 $
 */
class FilePermissionException extends FileException {}
