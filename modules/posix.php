<?php
/** POSIX Functions.

See: {@link http://www.php.net/manual/en/ref.posix.php}
@package posix
*/

define('POSIX_F_OK', 1);
define('POSIX_X_OK', 1);
define('POSIX_W_OK', 1);
define('POSIX_R_OK', 1);
define('POSIX_S_IFREG', 1);
define('POSIX_S_IFCHR', 1);
define('POSIX_S_IFBLK', 1);
define('POSIX_S_IFIFO', 1);
define('POSIX_S_IFSOCK', 1);

/*. bool .*/ function posix_kill(/*. int .*/ $pid, /*. int .*/ $sig){}
/*. int .*/ function posix_getpid(){}
/*. int .*/ function posix_getppid(){}
/*. int .*/ function posix_getuid(){}
/*. bool .*/ function posix_setuid(/*. int .*/ $uid){}
/*. int .*/ function posix_geteuid(){}
/*. bool .*/ function posix_seteuid(/*. int .*/ $uid){}
/*. int .*/ function posix_getgid(){}
/*. bool .*/ function posix_setgid(/*. int .*/ $uid){}
/*. int .*/ function posix_getegid(){}
/*. bool .*/ function posix_setegid(/*. int .*/ $uid){}
/*. array .*/ function posix_getgroups(){}
/*. string .*/ function posix_getlogin(){}
/*. int .*/ function posix_getpgrp(){}
/*. int .*/ function posix_setsid(){}
/*. bool .*/ function posix_setpgid(/*. int .*/ $pid, /*. int .*/ $pgid){}
/*. int .*/ function posix_getpgid(){}
/*. int .*/ function posix_getsid(){}
/*. array .*/ function posix_uname(){}
/*. array .*/ function posix_times(){}
/*. string .*/ function posix_ctermid(){}
/*. string .*/ function posix_ttyname(/*. int .*/ $fd){}
/*. bool .*/ function posix_isatty(/*. int .*/ $fd){}
/*. string .*/ function posix_getcwd(){}
/*. bool .*/ function posix_mkfifo(/*. string .*/ $pathname, /*. int .*/ $mode){}
/*. array .*/ function posix_getgrnam(/*. string .*/ $groupname){}
/*. array .*/ function posix_getgrgid(/*. int .*/ $gid){}
/*. array .*/ function posix_getpwnam(/*. string .*/ $groupname){}
/*. array .*/ function posix_getpwuid(/*. int .*/ $uid){}
/*. array .*/ function posix_getrlimit(){}
/*. int .*/ function posix_get_last_error(){}
/*. string .*/ function posix_strerror(/*. int .*/ $errno){}
/*. bool .*/ function posix_initgroups(/*. string .*/ $name, /*. int .*/ $base_group_id){}
