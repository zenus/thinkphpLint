<?php
/** Process Control Functions.

See: {@link http://www.php.net/manual/en/ref.pcntl.php}
@package pcntl
*/


# FIXME: dummy values
define('WNOHANG', 1);
define('WUNTRACED', 1);
define('SIG_IGN', 1);
define('SIG_DFL', 1);
define('SIG_ERR', 1);
define('SIGHUP', 1);
define('SIGINT', 1);
define('SIGQUIT', 1);
define('SIGILL', 1);
define('SIGTRAP', 1);
define('SIGABRT', 1);
define('SIGIOT', 1);
define('SIGBUS', 1);
define('SIGFPE', 1);
define('SIGKILL', 1);
define('SIGUSR1', 1);
define('SIGSEGV', 1);
define('SIGUSR2', 1);
define('SIGPIPE', 1);
define('SIGALRM', 1);
define('SIGTERM', 1);
define('SIGSTKFLT', 1);
define('SIGCLD', 1);
define('SIGCHLD', 1);
define('SIGCONT', 1);
define('SIGSTOP', 1);
define('SIGTSTP', 1);
define('SIGTTIN', 1);
define('SIGTTOU', 1);
define('SIGURG', 1);
define('SIGXCPU', 1);
define('SIGXFSZ', 1);
define('SIGVTALRM', 1);
define('SIGPROF', 1);
define('SIGWINCH', 1);
define('SIGPOLL', 1);
define('SIGIO', 1);
define('SIGPWR', 1);
define('SIGSYS', 1);
define('SIGBABY', 1);
define('PRIO_PGRP', 1);
define('PRIO_USER', 1);
define('PRIO_PROCESS', 1);
define("SIG_BLOCK", 1);
define("SIG_UNBLOCK", 1);
define("SIG_SETMASK", 1);
define("SI_USER", 1);
define("SI_NOINFO", 1);
define("SI_KERNEL", 1);
define("SI_QUEUE", 1);
define("SI_TIMER", 1);
define("SI_MESGQ", 1);
define("SI_ASYNCIO", 1);
define("SI_SIGIO", 1);
define("SI_TKILL", 1);
define("CLD_EXITED", 1);
define("CLD_KILLED", 1);
define("CLD_DUMPED", 1);
define("CLD_TRAPPED", 1);
define("CLD_STOPPED", 1);
define("CLD_CONTINUED", 1);
define("TRAP_BRKPT", 1);
define("TRAP_TRACE", 1);
define("POLL_IN", 1);
define("POLL_OUT", 1);
define("POLL_MSG", 1);
define("POLL_ERR", 1);
define("POLL_PRI", 1);
define("POLL_HUP", 1);
define("ILL_ILLOPC", 1);
define("ILL_ILLOPN", 1);
define("ILL_ILLADR", 1);
define("ILL_ILLTRP", 1);
define("ILL_PRVOPC", 1);
define("ILL_PRVREG", 1);
define("ILL_COPROC", 1);
define("ILL_BADSTK", 1);
define("FPE_INTDIV", 1);
define("FPE_INTOVF", 1);
define("FPE_FLTDIV", 1);
define("FPE_FLTOVF", 1);
define("FPE_FLTUND", 1);
define("FPE_FLTRES", 1);
define("FPE_FLTINV", 1);
define("FPE_FLTSUB", 1);
define("SEGV_MAPERR", 1);
define("SEGV_ACCERR", 1);
define("BUS_ADRALN", 1);
define("BUS_ADRERR", 1);
define("BUS_OBJERR", 1);

/*. int .*/ function pcntl_fork(){}
/*. int .*/ function pcntl_alarm(/*. int .*/ $seconds){}
/*. int .*/ function pcntl_waitpid(/*. int .*/ $pid, /*. return int .*/ &$status, /*. int .*/ $options){}
/*. int .*/ function pcntl_wait(/*. return int .*/ &$status){}
/*. bool .*/ function pcntl_wifexited(/*. int .*/ $status){}
/*. bool .*/ function pcntl_wifstopped(/*. int .*/ $status){}
/*. bool .*/ function pcntl_wifsignaled(/*. int .*/ $status){}
/*. int .*/ function pcntl_wexitstatus(/*. int .*/ $status){}
/*. int .*/ function pcntl_wtermsig(/*. int .*/ $status){}
/*. int .*/ function pcntl_wstopsig(/*. int .*/ $status){}
/*. bool .*/ function pcntl_exec(/*. string .*/ $path /*., args .*/){}
/*. bool .*/ function pcntl_signal(/*. int .*/ $signo, /*. string .*/ $handle, $restart_syscalls = TRUE){}
/*. int .*/ function pcntl_getpriority( /*. args .*/){}
/*. bool .*/ function pcntl_setpriority(/*. int .*/ $priority /*., args .*/){}
