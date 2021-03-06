<?php
/** Gettext.

See: {@link http://www.php.net/manual/en/ref.gettext.php}
@package gettext
*/


/*. string.*/ function textdomain(/*. string .*/ $domain){}
/*. string.*/ function gettext(/*. string .*/ $msgid){}
/*. string.*/ function dgettext(/*. string .*/ $domain_name, /*. string .*/ $msgid){}
/*. string.*/ function dcgettext(/*. string .*/ $domain_name, /*. string .*/ $msgid, /*. int .*/ $category){}
/*. string.*/ function bindtextdomain(/*. string .*/ $domain_name, /*. string .*/ $dir){}
/*. string.*/ function ngettext(/*. string .*/ $MSGID1, /*. string .*/ $MSGID2, /*. int .*/ $N){}
/*. string.*/ function dngettext(/*. string .*/ $domain, /*. string .*/ $msgid1, /*. string .*/ $msgid2, /*. int .*/ $count){}
/*. string.*/ function dcngettext(/*. string .*/ $domain, /*. string .*/ $msgid1, /*. string .*/ $msgid2, /*. int .*/ $n, /*. int .*/ $category){}
/*. string.*/ function bind_textdomain_codeset(/*. string .*/ $domain, /*. string .*/ $codeset){}
