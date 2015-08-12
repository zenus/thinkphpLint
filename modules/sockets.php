<?php
/** Socket Functions.

See: {@link http://www.php.net/manual/en/ref.sockets.php}
@package sockets
*/

# Required for E_WARNING:
/*. require_module 'standard'; .*/


# FIXME: dummy values
define('AF_INET', 1);
define('AF_INET6', 2);
define('AF_UNIX', 3);
define('MSG_DONTROUTE', 4);
define('MSG_EOF', 5);
define('MSG_EOR', 6);
define('MSG_OOB', 7);
define('MSG_PEEK', 8);
define('MSG_WAITALL', 9);
define('PHP_BINARY_READ', 10);
define('PHP_NORMAL_READ', 11);
define('SOCKET_E2BIG', 12);
define('SOCKET_EACCES', 13);
define('SOCKET_EADDRINUSE', 14);
define('SOCKET_EADDRNOTAVAIL', 15);
define('SOCKET_EADV', 16);
define('SOCKET_EAFNOSUPPORT', 17);
define('SOCKET_EAGAIN', 18);
define('SOCKET_EALREADY', 19);
define('SOCKET_EBADE', 20);
define('SOCKET_EBADF', 21);
define('SOCKET_EBADFD', 22);
define('SOCKET_EBADMSG', 23);
define('SOCKET_EBADR', 24);
define('SOCKET_EBADRQC', 25);
define('SOCKET_EBADSLT', 26);
define('SOCKET_EBUSY', 27);
define('SOCKET_ECHRNG', 28);
define('SOCKET_ECOMM', 29);
define('SOCKET_ECONNABORTED', 30);
define('SOCKET_ECONNREFUSED', 31);
define('SOCKET_ECONNRESET', 32);
define('SOCKET_EDESTADDRREQ', 33);
define('SOCKET_EDISCON', 34);
define('SOCKET_EDQUOT', 35);
define('SOCKET_EEXIST', 36);
define('SOCKET_EFAULT', 37);
define('SOCKET_EHOSTDOWN', 38);
define('SOCKET_EHOSTUNREACH', 39);
define('SOCKET_EIDRM', 40);
define('SOCKET_EINPROGRESS', 41);
define('SOCKET_EINTR', 42);
define('SOCKET_EINVAL', 43);
define('SOCKET_EIO', 44);
define('SOCKET_EISCONN', 45);
define('SOCKET_EISDIR', 46);
define('SOCKET_EISNAM', 47);
define('SOCKET_EL2HLT', 48);
define('SOCKET_EL2NSYNC', 49);
define('SOCKET_EL3HLT', 50);
define('SOCKET_EL3RST', 51);
define('SOCKET_ELNRNG', 52);
define('SOCKET_ELOOP', 53);
define('SOCKET_EMEDIUMTYPE', 54);
define('SOCKET_EMFILE', 55);
define('SOCKET_EMLINK', 56);
define('SOCKET_EMSGSIZE', 57);
define('SOCKET_EMULTIHOP', 58);
define('SOCKET_ENAMETOOLONG', 59);
define('SOCKET_ENETDOWN', 60);
define('SOCKET_ENETRESET', 61);
define('SOCKET_ENETUNREACH', 62);
define('SOCKET_ENFILE', 63);
define('SOCKET_ENOANO', 64);
define('SOCKET_ENOBUFS', 65);
define('SOCKET_ENOCSI', 66);
define('SOCKET_ENODATA', 67);
define('SOCKET_ENODEV', 68);
define('SOCKET_ENOENT', 69);
define('SOCKET_ENOLCK', 70);
define('SOCKET_ENOLINK', 71);
define('SOCKET_ENOMEDIUM', 72);
define('SOCKET_ENOMEM', 73);
define('SOCKET_ENOMSG', 74);
define('SOCKET_ENONET', 75);
define('SOCKET_ENOPROTOOPT', 76);
define('SOCKET_ENOSPC', 77);
define('SOCKET_ENOSR', 78);
define('SOCKET_ENOSTR', 79);
define('SOCKET_ENOSYS', 80);
define('SOCKET_ENOTBLK', 81);
define('SOCKET_ENOTCONN', 82);
define('SOCKET_ENOTDIR', 83);
define('SOCKET_ENOTEMPTY', 84);
define('SOCKET_ENOTSOCK', 85);
define('SOCKET_ENOTTY', 86);
define('SOCKET_ENOTUNIQ', 87);
define('SOCKET_ENXIO', 88);
define('SOCKET_EOPNOTSUPP', 89);
define('SOCKET_EPERM', 90);
define('SOCKET_EPFNOSUPPORT', 91);
define('SOCKET_EPIPE', 92);
define('SOCKET_EPROCLIM', 93);
define('SOCKET_EPROTO', 94);
define('SOCKET_EPROTONOSUPPORT', 95);
define('SOCKET_EPROTOTYPE', 96);
define('SOCKET_EREMCHG', 97);
define('SOCKET_EREMOTE', 98);
define('SOCKET_EREMOTEIO', 99);
define('SOCKET_ERESTART', 100);
define('SOCKET_EROFS', 101);
define('SOCKET_ESHUTDOWN', 102);
define('SOCKET_ESOCKTNOSUPPORT', 103);
define('SOCKET_ESPIPE', 104);
define('SOCKET_ESRMNT', 105);
define('SOCKET_ESTALE', 106);
define('SOCKET_ESTRPIPE', 107);
define('SOCKET_ETIME', 108);
define('SOCKET_ETIMEDOUT', 109);
define('SOCKET_ETOOMANYREFS', 110);
define('SOCKET_EUNATCH', 111);
define('SOCKET_EUSERS', 112);
define('SOCKET_EWOULDBLOCK', 113);
define('SOCKET_EXDEV', 114);
define('SOCKET_EXFULL', 115);
define('SOCKET_HOST_NOT_FOUND', 116);
define('SOCKET_NOTINITIALISED', 117);
define('SOCKET_NO_ADDRESS', 118);
define('SOCKET_NO_DATA', 119);
define('SOCKET_NO_RECOVERY', 120);
define('SOCKET_SYSNOTREADY', 121);
define('SOCKET_TRY_AGAIN', 122);
define('SOCKET_VERNOTSUPPORTED', 123);
define('SOCK_DGRAM', 124);
define('SOCK_RAW', 125);
define('SOCK_RDM', 126);
define('SOCK_SEQPACKET', 127);
define('SOCK_STREAM', 128);
define('SOL_SOCKET', 129);
define('SOL_TCP', 130);
define('SOL_UDP', 131);
define('SOMAXCONN', 132);
define('SO_BROADCAST', 133);
define('SO_DEBUG', 134);
define('SO_DONTROUTE', 135);
define('SO_ERROR', 136);
define('SO_KEEPALIVE', 137);
define('SO_LINGER', 138);
define('SO_OOBINLINE', 139);
define('SO_RCVBUF', 140);
define('SO_RCVLOWAT', 141);
define('SO_RCVTIMEO', 142);
define('SO_REUSEADDR', 143);
define('SO_SNDBUF', 144);
define('SO_SNDLOWAT', 145);
define('SO_SNDTIMEO', 146);
define('SO_TYPE', 147);

/*. int .*/ function socket_select(/*. array .*/ &$read_fds, /*. array .*/ &$write_fds, /*. array .*/ &$except_fds, /*. int .*/ $tv_sec /*., args .*/){}
/*. resource .*/ function socket_create_listen(/*. int .*/ $port /*., args .*/){}
/*. resource .*/ function socket_accept(/*. resource .*/ $socket){}
/*. bool .*/ function socket_set_nonblock(/*. resource .*/ $socket){}
/*. bool .*/ function socket_set_block(/*. resource .*/ $socket){}
/*. bool .*/ function socket_listen(/*. resource .*/ $socket /*., args .*/){}
/*. void .*/ function socket_close(/*. resource .*/ $socket){}
/*. int .*/ function socket_write(/*. resource .*/ $socket, /*. string .*/ $buf /*., args .*/){}
/*. string .*/ function socket_read(/*. resource .*/ $socket, /*. int .*/ $length /*., args .*/){}
/*. bool .*/ function socket_getsockname(/*. resource .*/ $socket, /*. return string .*/ &$addr /*., args .*/){}
/*. bool .*/ function socket_getpeername(/*. resource .*/ $socket, /*. return string .*/ &$addr /*., args .*/){}
/*. resource .*/ function socket_create(/*. int .*/ $domain, /*. int .*/ $type, /*. int .*/ $protocol)/*. triggers E_WARNING .*/{}
/*. bool .*/ function socket_connect(/*. resource .*/ $socket, /*. string .*/ $addr /*., args .*/){}
/*. string .*/ function socket_strerror(/*. int .*/ $errno){}
/*. bool .*/ function socket_bind(/*. resource .*/ $socket, /*. string .*/ $addr /*., args .*/){}
/*. int .*/ function socket_recv(/*. resource .*/ $socket, /*. string .*/ &$buf, /*. int .*/ $len, /*. int .*/ $flags){}
/*. int .*/ function socket_send(/*. resource .*/ $socket, /*. string .*/ $buf, /*. int .*/ $len, /*. int .*/ $flags){}
/*. int .*/ function socket_recvfrom(/*. resource .*/ $socket, /*. string .*/ &$buf, /*. int .*/ $len, /*. int .*/ $flags, /*. string .*/ &$name /*., args .*/){}
/*. int .*/ function socket_sendto(/*. resource .*/ $socket, /*. string .*/ $buf, /*. int .*/ $len, /*. int .*/ $flags, /*. string .*/ $addr /*., args .*/){}
/*. mixed .*/ function socket_get_option(/*. resource .*/ $socket, /*. int .*/ $level, /*. int .*/ $optname){}
/*. bool .*/ function socket_set_option(/*. args .*/){}
/*. bool .*/ function socket_create_pair(/*. int .*/ $domain, /*. int .*/ $type, /*. int .*/ $protocol, /*. return array .*/ &$fd){}
/*. bool .*/ function socket_shutdown(/*. resource .*/ $socket /*., args .*/){}
/*. int .*/ function socket_last_error( /*. args .*/){}
/*. void .*/ function socket_clear_error( /*. args .*/){}
