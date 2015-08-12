<?php
/**
CURL, Client URL Library Functions.

See: {@link http://www.php.net/manual/en/ref.curl.php}
@package curl
*/


# FIXME: all these '1' are dummy values
define('CURLOPT_DNS_USE_GLOBAL_CACHE', 1);
define('CURLOPT_DNS_CACHE_TIMEOUT', 2);
define('CURLOPT_PORT', 3);
define('CURLOPT_FILE', 4);
define('CURLOPT_READDATA', 5);
define('CURLOPT_INFILE', 6);
define('CURLOPT_INFILESIZE', 7);
define('CURLOPT_URL', 8);
define('CURLOPT_PRIVATE', 9);
define('CURLOPT_PROXY', 10);
define('CURLOPT_VERBOSE', 11);
define('CURLOPT_HEADER', 12);
define('CURLOPT_HTTPHEADER', 13);
define('CURLOPT_NOPROGRESS', 14);
define('CURLOPT_NOBODY', 15);
define('CURLOPT_FAILONERROR', 16);
define('CURLOPT_UPLOAD', 17);
define('CURLOPT_POST', 18);
define('CURLOPT_FTPLISTONLY', 19);
define('CURLOPT_FTPAPPEND', 20);
define('CURLOPT_NETRC', 21);
define('CURLOPT_FOLLOWLOCATION', 22);
define('CURLOPT_FTPASCII', 23);
define('CURLOPT_PUT', 24);
define('CURLOPT_MUTE', 25);
define('CURLOPT_USERPWD', 26);
define('CURLOPT_PROXYUSERPWD', 27);
define('CURLOPT_RANGE', 28);
define('CURLOPT_TIMEOUT', 29);
define('CURLOPT_POSTFIELDS', 30);
define('CURLOPT_REFERER', 31);
define('CURLOPT_USERAGENT', 32);
define('CURLOPT_FTPPORT', 33);
define('CURLOPT_FTP_USE_EPSV', 34);
define('CURLOPT_LOW_SPEED_LIMIT', 35);
define('CURLOPT_LOW_SPEED_TIME', 36);
define('CURLOPT_RESUME_FROM', 37);
define('CURLOPT_COOKIE', 38);
define('CURLOPT_SSLCERT', 39);
define('CURLOPT_SSLCERTPASSWD', 40);
define('CURLOPT_WRITEHEADER', 41);
define('CURLOPT_SSL_VERIFYHOST', 42);
define('CURLOPT_COOKIEFILE', 43);
define('CURLOPT_SSLVERSION', 44);
define('CURLOPT_TIMECONDITION', 45);
define('CURLOPT_TIMEVALUE', 46);
define('CURLOPT_CUSTOMREQUEST', 47);
define('CURLOPT_STDERR', 48);
define('CURLOPT_TRANSFERTEXT', 49);
define('CURLOPT_RETURNTRANSFER', 50);
define('CURLOPT_QUOTE', 51);
define('CURLOPT_POSTQUOTE', 52);
define('CURLOPT_INTERFACE', 53);
define('CURLOPT_KRB4LEVEL', 54);
define('CURLOPT_HTTPPROXYTUNNEL', 55);
define('CURLOPT_FILETIME', 56);
define('CURLOPT_WRITEFUNCTION', 57);
define('CURLOPT_READFUNCTION', 58);
define('CURLOPT_PASSWDFUNCTION', 59);
define('CURLOPT_HEADERFUNCTION', 60);
define('CURLOPT_MAXREDIRS', 61);
define('CURLOPT_MAXCONNECTS', 62);
define('CURLOPT_CLOSEPOLICY', 63);
define('CURLOPT_FRESH_CONNECT', 64);
define('CURLOPT_FORBID_REUSE', 65);
define('CURLOPT_RANDOM_FILE', 66);
define('CURLOPT_EGDSOCKET', 67);
define('CURLOPT_CONNECTTIMEOUT', 68);
define('CURLOPT_SSL_VERIFYPEER', 69);
define('CURLOPT_CAINFO', 70);
define('CURLOPT_CAPATH', 71);
define('CURLOPT_COOKIEJAR', 72);
define('CURLOPT_SSL_CIPHER_LIST', 73);
define('CURLOPT_BINARYTRANSFER', 74);
define('CURLOPT_NOSIGNAL', 75);
define('CURLOPT_PROXYTYPE', 76);
define('CURLOPT_BUFFERSIZE', 77);
define('CURLOPT_HTTPGET', 78);
define('CURLOPT_HTTP_VERSION', 79);
define('CURLOPT_SSLKEY', 80);
define('CURLOPT_SSLKEYTYPE', 81);
define('CURLOPT_SSLKEYPASSWD', 82);
define('CURLOPT_SSLENGINE', 83);
define('CURLOPT_SSLENGINE_DEFAULT', 84);
define('CURLOPT_SSLCERTTYPE', 85);
define('CURLOPT_CRLF', 86);
define('CURLOPT_ENCODING', 87);
define('CURLOPT_PROXYPORT', 88);
define('CURLOPT_UNRESTRICTED_AUTH', 89);
define('CURLOPT_FTP_USE_EPRT', 90);
define('CURLOPT_HTTP200ALIASES', 91);
define('CURLOPT_TCP_NODELAY', 92);
define('CURLOPT_TIMEOUT_MS', 93);
define('CURLOPT_CONNECTTIMEOUT_MS', 94);
define('CURL_TIMECOND_IFMODSINCE', 95);
define('CURL_TIMECOND_IFUNMODSINCE', 96);
define('CURL_TIMECOND_LASTMOD', 97);
define('CURL_WRAPPERS_ENABLED', 1);
define('CURLOPT_HTTPAUTH', 98);
define('CURLAUTH_BASIC', 99);
define('CURLAUTH_DIGEST', 100);
define('CURLAUTH_GSSNEGOTIATE', 101);
define('CURLAUTH_NTLM', 102);
define('CURLAUTH_ANY', 103);
define('CURLAUTH_ANYSAFE', 104);
define('CURLOPT_PROXYAUTH', 105);
define('CURLCLOSEPOLICY_LEAST_RECENTLY_USED', 106);
define('CURLCLOSEPOLICY_LEAST_TRAFFIC', 107);
define('CURLCLOSEPOLICY_SLOWEST', 108);
define('CURLCLOSEPOLICY_CALLBACK', 109);
define('CURLCLOSEPOLICY_OLDEST', 110);
define('CURLINFO_EFFECTIVE_URL', 111);
define('CURLINFO_HTTP_CODE', 112);
define('CURLINFO_HEADER_SIZE', 113);
define('CURLINFO_REQUEST_SIZE', 114);
define('CURLINFO_TOTAL_TIME', 115);
define('CURLINFO_NAMELOOKUP_TIME', 116);
define('CURLINFO_CONNECT_TIME', 117);
define('CURLINFO_PRETRANSFER_TIME', 118);
define('CURLINFO_SIZE_UPLOAD', 119);
define('CURLINFO_SIZE_DOWNLOAD', 120);
define('CURLINFO_SPEED_DOWNLOAD', 121);
define('CURLINFO_SPEED_UPLOAD', 122);
define('CURLINFO_FILETIME', 123);
define('CURLINFO_SSL_VERIFYRESULT', 124);
define('CURLINFO_CONTENT_LENGTH_DOWNLOAD', 125);
define('CURLINFO_CONTENT_LENGTH_UPLOAD', 126);
define('CURLINFO_STARTTRANSFER_TIME', 127);
define('CURLINFO_CONTENT_TYPE', 128);
define('CURLINFO_REDIRECT_TIME', 129);
define('CURLINFO_REDIRECT_COUNT', 130);
define('CURLINFO_PRIVATE', 131);
define('CURL_VERSION_IPV6', 132);
define('CURL_VERSION_KERBEROS4', 133);
define('CURL_VERSION_SSL', 134);
define('CURL_VERSION_LIBZ', 135);
define('CURLVERSION_NOW', 136);
define('CURLE_OK', 137);
define('CURLE_UNSUPPORTED_PROTOCOL', 138);
define('CURLE_FAILED_INIT', 139);
define('CURLE_URL_MALFORMAT', 140);
define('CURLE_URL_MALFORMAT_USER', 141);
define('CURLE_COULDNT_RESOLVE_PROXY', 142);
define('CURLE_COULDNT_RESOLVE_HOST', 143);
define('CURLE_COULDNT_CONNECT', 144);
define('CURLE_FTP_WEIRD_SERVER_REPLY', 145);
define('CURLE_FTP_ACCESS_DENIED', 146);
define('CURLE_FTP_USER_PASSWORD_INCORRECT', 147);
define('CURLE_FTP_WEIRD_PASS_REPLY', 148);
define('CURLE_FTP_WEIRD_USER_REPLY', 149);
define('CURLE_FTP_WEIRD_PASV_REPLY', 150);
define('CURLE_FTP_WEIRD_227_FORMAT', 151);
define('CURLE_FTP_CANT_GET_HOST', 152);
define('CURLE_FTP_CANT_RECONNECT', 153);
define('CURLE_FTP_COULDNT_SET_BINARY', 154);
define('CURLE_PARTIAL_FILE', 155);
define('CURLE_FTP_COULDNT_RETR_FILE', 156);
define('CURLE_FTP_WRITE_ERROR', 157);
define('CURLE_FTP_QUOTE_ERROR', 158);
define('CURLE_HTTP_NOT_FOUND', 159);
define('CURLE_WRITE_ERROR', 160);
define('CURLE_MALFORMAT_USER', 161);
define('CURLE_FTP_COULDNT_STOR_FILE', 162);
define('CURLE_READ_ERROR', 163);
define('CURLE_OUT_OF_MEMORY', 164);
define('CURLE_OPERATION_TIMEOUTED', 165);
define('CURLE_FTP_COULDNT_SET_ASCII', 166);
define('CURLE_FTP_PORT_FAILED', 167);
define('CURLE_FTP_COULDNT_USE_REST', 168);
define('CURLE_FTP_COULDNT_GET_SIZE', 169);
define('CURLE_HTTP_RANGE_ERROR', 170);
define('CURLE_HTTP_POST_ERROR', 171);
define('CURLE_SSL_CONNECT_ERROR', 172);
define('CURLE_FTP_BAD_DOWNLOAD_RESUME', 173);
define('CURLE_FILE_COULDNT_READ_FILE', 174);
define('CURLE_LDAP_CANNOT_BIND', 175);
define('CURLE_LDAP_SEARCH_FAILED', 176);
define('CURLE_LIBRARY_NOT_FOUND', 177);
define('CURLE_FUNCTION_NOT_FOUND', 178);
define('CURLE_ABORTED_BY_CALLBACK', 179);
define('CURLE_BAD_FUNCTION_ARGUMENT', 180);
define('CURLE_BAD_CALLING_ORDER', 181);
define('CURLE_HTTP_PORT_FAILED', 182);
define('CURLE_BAD_PASSWORD_ENTERED', 183);
define('CURLE_TOO_MANY_REDIRECTS', 184);
define('CURLE_UNKNOWN_TELNET_OPTION', 185);
define('CURLE_TELNET_OPTION_SYNTAX', 186);
define('CURLE_OBSOLETE', 187);
define('CURLE_SSL_PEER_CERTIFICATE', 188);
define('CURLE_GOT_NOTHING', 189);
define('CURLE_SSL_ENGINE_NOTFOUND', 190);
define('CURLE_SSL_ENGINE_SETFAILED', 191);
define('CURLE_SEND_ERROR', 192);
define('CURLE_RECV_ERROR', 193);
define('CURLE_SHARE_IN_USE', 194);
define('CURLE_SSL_CERTPROBLEM', 195);
define('CURLE_SSL_CIPHER', 196);
define('CURLE_SSL_CACERT', 197);
define('CURLE_BAD_CONTENT_ENCODING', 198);
define('CURLE_LDAP_INVALID_URL', 199);
define('CURLE_FILESIZE_EXCEEDED', 200);
define('CURLE_FTP_SSL_FAILED', 201);
define('CURLPROXY_HTTP', 202);
define('CURLPROXY_SOCKS5', 203);
define('CURL_NETRC_OPTIONAL', 204);
define('CURL_NETRC_IGNORED', 205);
define('CURL_NETRC_REQUIRED', 206);
define('CURL_HTTP_VERSION_NONE', 207);
define('CURL_HTTP_VERSION_1_0', 208);
define('CURL_HTTP_VERSION_1_1', 209);
define('CURLM_CALL_MULTI_PERFORM', 210);
define('CURLM_OK', 211);
define('CURLM_BAD_HANDLE', 212);
define('CURLM_BAD_EASY_HANDLE', 213);
define('CURLM_OUT_OF_MEMORY', 214);
define('CURLM_INTERNAL_ERROR', 215);
define('CURLMSG_DONE', 216);

/*. array .*/ function curl_version( /*. args .*/){}
/*. resource .*/ function curl_init( /*. args .*/){}
/*. resource .*/ function curl_copy_handle(/*. resource .*/ $ch){}
/*. bool .*/ function curl_setopt(/*. resource .*/ $ch, /*. int .*/ $option, /*. mixed .*/ $value){}
/*. mixed .*/ function curl_exec(/*. resource .*/ $ch){}
/*. mixed .*/ function curl_getinfo(/*. resource .*/ $ch /*. , args .*/){}
/*. string .*/ function curl_error(/*. resource .*/ $ch){}
/*. int .*/ function curl_errno(/*. resource .*/ $ch){}
/*. void .*/ function curl_close(/*. resource .*/ $ch){}
/*. resource .*/ function curl_multi_init(){}
/*. int .*/ function curl_multi_add_handle(/*. resource .*/ $multi, /*. resource .*/ $ch){}
/*. int .*/ function curl_multi_remove_handle(/*. resource .*/ $mh, /*. resource .*/ $ch){}
/*. int .*/ function curl_multi_select(/*. resource .*/ $mh /*., args .*/){}
/*. int .*/ function curl_multi_exec(/*. resource .*/ $mh, /*. int .*/ &$still_running){}
/*. string .*/ function curl_multi_getcontent(/*. resource .*/ $ch){}
/*. array .*/ function curl_multi_info_read(/*. resource .*/ $mh /*. , args .*/){}
/*. void .*/ function curl_multi_close(/*. resource .*/ $mh){}
