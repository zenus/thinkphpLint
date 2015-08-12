<?php

namespace it\icosaedro\io;
require_once __DIR__ . "/IOException.php";

/**
 * Read data are invalid: unexpected format, incomplete file, transmission
 * issue, partial copy, wrong checksum, or wrong hash.
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2014/11/24 20:51:09 $
 */
class CorruptedException extends IOException {}
