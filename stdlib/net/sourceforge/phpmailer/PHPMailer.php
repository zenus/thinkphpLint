<?php

namespace net\sourceforge\phpmailer;

/*.
	require_module 'standard';
	require_module 'spl';
	require_module 'pcre';
	require_module 'hash';
.*/

require_once __DIR__ . "/../../../autoload.php";
require_once __DIR__ . "/../../../errors.php";
//require_once __DIR__ . "/SMTP.php";

use ErrorException;
use RuntimeException;
use InvalidArgumentException;


/*. private .*/ class MIMEPart {
	
	public /*. string .*/ $header;
	
	public /*. string .*/ $body;
	
	function __construct(
			/*. string .*/ $header,
			/*. string .*/ $body)
	{
		$this->header = $header;
		$this->body = $body;
	}
}

/**
 * Email composition and transport. The structure of the message may include:
 * one textual part; one HTML part, possibly with inline related contents
 * (images, sounds, etc.) and several attachments.
 *
 * If both the textual and the HTML readable parts are specified, the
 * remote e-mail client program may display to the user the preferred part
 * (see the multipart/alternative MIME content type).
 *
 * If none of these parts is specified, and empty message is sent.
 *
 * Messages can be sent via PHP mail() function (the default), via SMTP
 * protocol, or via sendmail/qmail process. Building and sending a message
 * requires three steps:
 * 
 * 1. Instantiate a new object of this class:
 * <pre>
 *	$m = new PHPMailer();
 * </pre>
 * 
 * 2. Set the properties you need, in any order:
 * <pre>
 *	$m-&gt;charset = "UTF-8";
 *	$m-&gt;from = "myname@mycompany.it";
 *	$m-&gt;from_name = "My Name";
 *	$m-&gt;subject = "The subject here";
 *	$m-&gt;addAddress("someone@acme.com");
 *	$m-&gt;setHtmlMessage("&lt;html&gt;&lt;body&gt;Your message here.&lt;/body&gt;&lt;/html&gt;");
 *	$m-&gt;addAttachmentFromFile("report.pdf", "application/pdf");
 *	$m-&gt;useSMTP("localhost:25");
 *	$m-&gt;send_message_id = TRUE;
 * </pre>
 * 
 * 3. Send the message:
 * <pre>
 *	if( $m-&gt;send() ){
 *		echo "Message sent with Message-ID: ", $this-&gt;message_id, "\n";
 *	} else {
 *		echo "Message NOT sent.\n";
 *		# Only if the mailer is SMTP, this info may be available:
 *		if( count($m-&gt;bad_rcpt) &gt; 0 ){
 *			echo "These recipients were rejected from the SMTP server:\n";
 *			foreach($m-&gt;bad_rcpt as $email)
 *				echo "$email\n";
 *		}
 *	}
 * </pre>
 * 
 * Once sent, you may change any property of the message and send it again to
 * others recipients.
 * 
 * This version of the PHPMailer class has been converted from PHP 4 to
 * PHP 5 from the original PHPMailer 1.73 version. The result is a class
 * that heavely relies on exceptions to report errors, so the old custom
 * signaling mechanism has been dropped.
 * 
 * Moreover, when the SMTP mailer is used, the client
 * program can know the list of users that were rejected.
 * 
 * Support for error messages in localized languages has been dropped.
 * 
 * @author Brent R. Matzelle
 * @author Umberto Salsi
 * @version $Date: 2014/02/21 17:22:16 $
 * @license LICENSE.txt LGPL
 * @copyright 2001 - 2003 Brent R. Matzelle
 */
class PHPMailer
{

/** PHPMailer-ico version. */
const VERSION = 'ico-$Date: 2014/02/21 17:22:16 $';

/** Priority of the message (1 = High, 3 = Normal, 5 = low). */
public $priority          = 3;

/**
 * Charset of the message (text and HTML).
 * Will use this charset for any component of the message:
 * header (including subject, email addresses and names),
 * and body (text and HTML).
*/
public $charset           = "UTF-8";

/**
	Encoding of the message (text and HTML).
	Options for this are "8bit", "7bit", "binary", "base64", and
	"quoted-printable". See {@link http://tools.ietf.org/html/rfc2045 RFC2045}.
*/
public $encoding          = "8bit";

/**
	"From" email address of the message.
*/
public $from              = "root@localhost";

/** "From" name of the message.  */
public $from_name         = "Root User";

/**
	Sets the sender of the message. The Return-Path field is set to this
	address if not empty, otherwise the "from" address is used instead. If not
	empty, will be sent via -f to sendmail or as 'MAIL FROM' in smtp mode.
*/
public $sender            = "";

/**
	Sets the subject of the message. The string must be encoded accordingly to
	the {@link ::$charset} variable. The "Subject:" header will be encoded
	properly to render the given charset.
*/
public $subject           = "";

/**
	HTML readable part of the message. If HTML then run {@link ::isHTML(TRUE)}.
	The charset of this string must be {@link ::$charset}. See also {@link
	::$text_body} if you want to set an alternate plain text version of the
	message.
	@var string
*/
private $html_msg         = NULL;

/**
	Textual readable part of the message. If both the HTML
	part and the textual part are set, the message becomes
	multipart/alternative.	This body can be read by mail
	clients that do not have HTML email capability such as
	mutt. Clients that can read HTML will view the normal
	{@link ::$html_body}.
	The charset of this string must be {@link ::$charset}.
	@var string
*/
private $text_msg         = NULL;

/**
	Sets word wrapping on the body of the message to a given number
	of characters.
	Lines longer than that are splitted. 0 = no word wrap.
*/
public $word_wrap         = 75;

/** Method to send mail: ("mail", "sendmail", or "smtp"). */
private $mailer           = "mail";

/** Path of the sendmail or qmail program. */
private $sendmail = "";

/**
	Sets the email address for the message disposition notification.
	Ignore if empty.
	See {@link http://tools.ietf.org/html/rfc3798 RFC3798}.
*/
public $confirm_reading_to = "";

/**
	Hostname to use in Message-Id and Received headers and as default HELO
	string. If empty, tries SERVER_NAME, then {@link php_uname("n")} and
	finally uses 'localhost.localdomain'.
	@var string
*/
public $host_name         = NULL;

/**
 * If the Message-ID header field has to be generated. If set to FALSE (default)
 * the Message-ID is generated by the chosen MTA and cannot be retrieved.
 * If set to TRUE the Message-ID is generated by this class for every message
 * sent (with success or not). See the description  of the {@link ::$message_id}
 * property for more details.
 */
public $send_message_id   = FALSE;


/**
 * Message-ID of the last sent message. This field is set only if the
 * {@link ::$send_message_id} is set to TRUE, otherwise it is reset to the
 * NULL value after every message sent. The format of the ID is:
 * <pre>
 *    &lt;ALPHANUM@HOSTNAME&gt;
 * </pre>
 * where ALFANUM is a string generated as MD5 hash of a mix of informations
 * that should be unique of this message: time (in microseconds), from and
 * sender addresses, subject and at least 5 recipient addresses.
 * HOSTNAME is the host name according to the specifications of the
 * {@link ::$host_name} property.
 * @var string
 */
public $message_id        = NULL;

/** SMTP hosts.

	You can list one or several servers separated by semicolon
	and possibly with a port number separated with a colon,
	for example: "host1;host2:25;host3:1025".  The default
	TCP port is 25.

	The listed hosts are tried in the order, the first accepting
	the connection and possibly the authentication login
	is used.
*/
private $hosts       = "localhost";

/** SMTP HELO message (Default is {@link ::$host_name}). */
private $helo        = "";

/** SMTP username. Performs SMTP authentication if this string
	is non-empty. */
private $username     = "";

/** SMTP password. */
private $password     = "";

/** SMTP server timeout in seconds.
	This feature will not work with the win32 version.
*/
private $timeout      = 10;

/** Sets SMTP class debugging on or off. */
private $SMTPDebug    = FALSE;

/** Prevents the SMTP connection from being closed after each mail sent.
*/
public $SMTPKeepAlive = FALSE;

/**
	If the mailer is SMTP and {@link ::send()} failed, this
	property may contain the list of email addresses that were
	rejected by the remote server.
	@var string[int]
*/
public $bad_rcpt = NULL;


/*. private .*/ const   LE = "\r\n";
private $smtp        = /*. (SMTP) .*/ NULL;
private $to          = /*. (string[int][int]) .*/ NULL;
private $cc          = /*. (string[int][int]) .*/ NULL;
private $bcc         = /*. (string[int][int]) .*/ NULL;
private $replyTo     = /*. (string[int][int]) .*/ NULL;
private $attachments = /*. (MIMEPart[int]) .*/ NULL;
private $inlines     = /*. (MIMEPart[int]) .*/ NULL;
private $customFields = /*. (string[int][int]) .*/ NULL;


/**
	Return TRUE if $email is a (formally) valid email address.
	Valid email address are specified by RFC 2822. This method implements
	a sub-set of this syntax that can be described as follows.
	The address has the form user@domain where the user part and the domain
	part are made of one or more "atoms" separated by a dot. Every atom
	is a sequence of one or more letters of the US-ASCII charset,
	digits or any of the following characters:
	<pre>! # $ % &amp; ' * + - / = ? ^ _ ` { | } ~</pre>
	@param string $email
	@return bool
*/
static function isValidEmailAddress($email)
{
	static $re = "";

	if( $re === "" ){
		$atext = "[-!#-'*+/0-9=?A-Z^-~]{1,}";
		$atom = $atext . "+";
		$dot_atom = $atom . "(\\.$atom)*";
		$re = ":^". $dot_atom ."@". $dot_atom ."\$:";
	}
	return preg_match($re, $email) === 1;
}


/** Adds a "To" address.
	@param string $address
	@param string $name
	@return void
*/
function addAddress($address, $name = "")
{
	$cur = count($this->to);
	$this->to[$cur][0] = trim($address);
	$this->to[$cur][1] = $name;
}


/** Adds a "Cc" address.
	FIXME: this function works with the SMTP mailer on win32,
	not with the "mail" mailer.
	@param string $address
	@param string $name
	@return void
*/
function addCC($address, $name = "")
{
	$cur = count($this->cc);
	$this->cc[$cur][0] = trim($address);
	$this->cc[$cur][1] = $name;
}


/** Adds a "Bcc" address.
	FIXME: this function works with the SMTP mailer on win32,
	not with the "mail" mailer.
	@param string $address
	@param string $name
	@return void
*/
function addBCC($address, $name = "")
{
	$cur = count($this->bcc);
	$this->bcc[$cur][0] = trim($address);
	$this->bcc[$cur][1] = $name;
}


/** Adds a "Reply-To" address.
	@param string $address
	@param string $name
	@return void
*/
function addReplyTo($address, $name = "")
{
	$cur = count($this->replyTo);
	$this->replyTo[$cur][0] = trim($address);
	$this->replyTo[$cur][1] = $name;
}


/** Adds a custom header field.
	This method accept only one field; more fields can be added by calling
	this method several times.
	@param string $field Custom field to add to the header. Field name and
	field body must be separated by a colon.
	If the field spans over several lines, lines MUST be separated by
	<code>"\r\n\t"</code> the last tabulator character being the continuation
	marker.
	The field MUST NOT be terminated by a new line.
	Non-ASCII characters are encoded using the default charset
	{@link ::$charset}.
	@return void
*/
function addCustomHeaderField($field)
{
	$this->customFields[] = explode(":", $field, 2);
}


/**
 * Set the textual message. Uses the {@link ::$charset} charset.
 * If set to NULL, no textual message is sent.
 * If set to the empty string, an empty text message is sent.
 * @param string $text_msg
 * @return void
 */
function setTextMessage($text_msg)
{
	$this->text_msg = $text_msg;
}


/**
 * Set the HTML message. Uses the {@link ::$charset} charset.
 * If set to NULL, no HTML message is sent.
 * If set to the empty string, an empty HTML message is sent.
 * @param string $html_msg
 * @return void
 */
function setHtmlMessage($html_msg)
{
	$this->html_msg = $html_msg;
}


/**
	Returns a formatted header line.
	@param string $name
	@param string $value
	@return string
*/
private static function headerLine($name, $value)
{
	return $name . ": " . $value . self::LE;
}


/**
	Returns the proper RFC 822 formatted date. 
	@return string
*/
private static function RFCDate()
{
	$tz = (int) date("Z");
	$tzs = ($tz < 0) ? "-" : "+";
	$tz = (int) abs($tz);
	$tz = (int) ( ($tz/3600)*100 + ($tz%3600)/60 );
	$result = sprintf("%s %s%04d", date("D, j M Y H:i:s"), $tzs, $tz);

	return $result;
}


/**
	Changes every end of line from CR or LF to CRLF.
	@param string $str
	@return string
*/
private static function fixEOL($str)
{
	$str = (string) str_replace("\r\n", "\n", $str);
	$str = (string) str_replace("\r", "\n", $str);
	$str = (string) str_replace("\n", self::LE, $str);
	return $str;
}


/**
	Wraps message for use with mailers that do not
	automatically perform wrapping and for quoted-printable.
	Original written by philippe.
	@param string $message
	@param int $length
	@param bool $qp_mode
	@return string
*/
private static function wrapText($message, $length, $qp_mode = FALSE)
{
	$soft_break = ($qp_mode) ? sprintf(" =%s", self::LE) : self::LE;

	$message = self::fixEOL($message);
	if (substr($message, -1) === self::LE)
		$message = substr($message, 0, -1);

	$line = explode(self::LE, $message);
	$message = "";
	for ($i = 0; $i < count($line); $i++) {
		$line_part = explode(" ", $line[$i]);
		$buf = "";
		for ($e = 0; $e < count($line_part); $e++) {
			$word = $line_part[$e];
			if ($qp_mode and (strlen($word) > $length)) {
				$space_left = $length - strlen($buf) - 1;
				if ($e != 0) {
					if ($space_left > 20) {
						$len = $space_left;
						if (substr($word, $len - 1, 1) === "=")
							$len--;
						elseif (substr($word, $len - 2, 1) === "=")
							$len -= 2;
						$part = substr($word, 0, $len);
						$word = substr($word, $len);
						$buf .= " " . $part;
						$message .= $buf . sprintf("=%s", self::LE);
					}
					else {
						$message .= $buf . $soft_break;
					}
					$buf = "";
				}
				while (strlen($word) > 0) {
					$len = $length;
					if (substr($word, $len - 1, 1) === "=")
						$len--;
					elseif (substr($word, $len - 2, 1) === "=")
						$len -= 2;
					$part = substr($word, 0, $len);
					$word = substr($word, $len);

					if (strlen($word) > 0)
						$message .= $part . sprintf("=%s", self::LE);
					else
						$buf = $part;
				}
			}
			else {
				$buf_o = $buf;
				$buf .= ($e == 0) ? $word : (" " . $word);

				if (strlen($buf) > $length and strlen($buf_o) > 0) {
					$message .= $buf_o . $soft_break;
					$buf = $word;
				}
			}
		}
		$message .= $buf . self::LE;
	}

	return $message;
}


/**
	Encode string to quoted-printable.  
	@param string $str
	@return string
*/
private static function encodeQP($str)
{
	$encoded = self::fixEOL($str);
	if (substr($encoded, -(strlen(self::LE))) !== self::LE)
		$encoded .= self::LE;

	// Replace every high ascii, control and = characters
	$encoded = preg_replace('/([\\000-\\010\\013\\014\\016-\\037\\075\\177-\\377])/e',
			  "'='.sprintf('%02X', ord('\\1'))", $encoded);
	// Replace every spaces and tabs when it's the last character on a line
	$encoded = preg_replace("/([\\011\\040])".self::LE."/e",
			  "'='.sprintf('%02X', ord('\\1')).'".self::LE."'", $encoded);

	// Maximum line length of 76 characters before CRLF (74 + space + '=')
	$encoded = self::wrapText($encoded, 74, TRUE);

	return $encoded;
}


/**
	Encode string to Q encoding.  
	@param string $str
	@param string $position
	@return string
*/
private static function encodeQ($str, $position = "text")
{
	// There should not be any EOL in the string
	$encoded = preg_replace("[\r\n]", " ", $str);

	switch (strtolower($position)) {
	  case "phrase":
		$encoded = preg_replace("@([^A-Za-z0-9!*+/ -])@e", "'='.sprintf('%02X', ord('\\1'))", $encoded);
		break;
	  case "comment":
		$encoded = preg_replace("/([()\"])/e", "'='.sprintf('%02X', ord('\\1'))", $encoded);
			/*. missing_break; .*/
	  case "text":
			/*. missing_break; .*/
	  default:
		// Replace every high ascii, control =, ? and _ characters
		$encoded = preg_replace('/([\\000-\\011\\013\\014\\016-\\037\\075\\077\\137\\177-\\377])/e',
			  "'='.sprintf('%02X', ord('\\1'))", $encoded);
		break;
	}
	
	// Replace every spaces to _ (more readable than =20)
	$encoded = (string) str_replace(" ", "_", $encoded);
	return $encoded;
}


/**
	Encode a header string to best of Q, B, quoted or none.
	@param string $str
	@param string $position
	@return string
*/
private function encodeHeader($str, $position = 'text')
{
	$x = 0;

	switch (strtolower($position)) {
		case 'phrase':
			if (FALSE === preg_match('/[\\200-\\377]/', $str)) {
				$encoded = addcslashes($str, "\0..\37\177\\\"");

				if (($str === $encoded) && FALSE === preg_match('/[^A-Za-z0-9!#$%&\'*+\\/=?^_`{|}~ -]/', $str))
					return ($encoded);
				else
					return ("\"$encoded\"");
			}
			$x = preg_match_all('/[^\\040\\041\\043-\\133\\135-\\176]/', $str, $matches);
			break;
		/*
		case 'comment':
			$x = preg_match_all('/[()"]/', $str, $matches);
		
		*/
		case 'text':
			$x += preg_match_all('/[\\000-\\010\\013\\014\\016-\\037\\177-\\377]/', $str, $matches);
			break;
		/*. missing_default: .*/
	}

	if ($x == 0)
		return ($str);

	$maxlen = 75 - 7 - strlen($this->charset);
	// Try to select the encoding which should produce the shortest output
	if (strlen($str) < 3 * $x) {
		$encoding = 'B';
		$encoded = base64_encode($str);
		$maxlen -= $maxlen % 4;
		$encoded = trim(chunk_split($encoded, $maxlen, "\n"));
	} else {
		$encoding = 'Q';
		$encoded = self::encodeQ($str, $position);
		$encoded = self::wrapText($encoded, $maxlen, TRUE);
		$encoded = (string) str_replace("=" . self::LE, "\n", trim($encoded));
	}

	$encoded = preg_replace('/^(.*)$/m', " =?" . $this->charset . "?$encoding?\\1?=", $encoded);
	$encoded = trim((string) str_replace("\n", self::LE, $encoded));

	return $encoded;
}


/**
	Formats an address correctly. 
	@param string[int] $addr
	@return string
*/
private function addrFormat($addr)
{
	if(empty($addr[1]))
		$formatted = $addr[0];
	else
		$formatted = $this->encodeHeader($addr[1], 'phrase') . " <" . 
			 $addr[0] . ">";

	return $formatted;
}


/**
	Creates recipient headers.
	@param string $type
	@param string[int][int] $addr
	@return string
*/
private function addrAppend($type, $addr)
{
	$addr_str = $type . ": ";
	$addr_str .= $this->addrFormat($addr[0]);
	if(count($addr) > 1)
	{
		for($i = 1; $i < count($addr); $i++)
			$addr_str .= ", " . $this->addrFormat($addr[$i]);
	}
	$addr_str .= self::LE;

	return $addr_str;
}


/**
	Encodes string to the requested format. Returns an
	empty string on failure.
	@param string $str
	@param string $encoding Can be: "base64" or "7bit" or "8bit"
	or "binary" or "quoted-printable".
	@return string Encoded string.
	@throws InvalidArgumentException Unknown encoding.
*/
private function encodeString($str, $encoding = "base64")
{
	$encoded = "";
	switch(strtolower($encoding)) {
	  case "base64":
		  // chunk_split is found in PHP >= 3.0.6
		  $encoded = chunk_split(base64_encode($str), 76, self::LE);
		  break;
	  case "7bit":
	  case "8bit":
		  $encoded = self::fixEOL($str);
		  if (substr($encoded, -(strlen(self::LE))) !== self::LE)
			$encoded .= self::LE;
		  break;
	  case "binary":
		  $encoded = $str;
		  break;
	  case "quoted-printable":
		  $encoded = self::encodeQP($str);
		  break;
	  default:
		  throw new InvalidArgumentException("unknown encoding: $encoding");
	}
	return $encoded;
}


/**
	Encodes attachment in requested format.
	@param string $path
	@param string $encoding
	@return string
	@throws ErrorException
*/
private function encodeFile($path, $encoding = "base64")
{
	$fd = fopen($path, "rb");
	$file_buffer = fread($fd, filesize($path));
	fclose($fd);
	$file_buffer = $this->encodeString($file_buffer, $encoding);
	return $file_buffer;
}


/**
 * Encodes file name to be enclosed between double-quotes.
 * FIXME: not sure it's the right fix, see RFC 2047 (forbids this),
 * RFC 2231 (?) and RFC 5987 (apparently related to HTML only).
 * @param string $name
 * @return string 
 */
private function encodeFileName($name)
{
	return $this->encodeHeader((string)str_replace("\"", "\\\"", $name));
}


/**
	Adds an attachment from a file.
	This example will add an image as an attachment:

	<pre>
	$m-&gt;addAttachmentFromFile("C:\\images\\photo.jpeg", "image/jpeg");
	</pre>

	@param string $path Path where the file is stored.
	@param string $type MIME type of the file. See
	{@link http://www.iana.org/assignments/media-types/index.html} for
	a complete list of the registered media types.
	@param string $name Name proposed to the remote user whenever he decides
	to save this file. If empty, uses the basename of the file.
	@param string $encoding Options for this are "8bit", "7bit", "binary",
	"base64", and "quoted-printable". See
	{@link http://tools.ietf.org/html/rfc2045 RFC2045}.
	@return void
	@throws ErrorException Failed to read the file.
*/
function addAttachmentFromFile($path, $type, $name = "", $encoding = "base64")
{
	$name = empty($name)? basename($path) : $name;
	$name_encoded = $this->encodeFileName($name);
	$part = new MIMEPart(
		self::headerLine("Content-Type", "$type; name=\"$name_encoded\"")
		. self::headerLine("Content-Transfer-Encoding", $encoding)
		. self::headerLine("Content-Disposition", "attachment; filename=\"$name_encoded\""),
		$this->encodeFile($path, $encoding) );
	
	$this->attachments[] = $part;
}


/**
	Adds a string or binary attachment (non-filesystem).
	@param string $content Text or binary data to attach.
	@param string $type MIME type of the data. See
	{@link http://www.iana.org/assignments/media-types/index.html} for
	a complete list of the registered media types.
	@param string $name Name proposed to the remote user whenever he decides
	to save this content.
	@param string $encoding Options for this are "8bit", "7bit", "binary",
	"base64", and "quoted-printable". See
	{@link http://tools.ietf.org/html/rfc2045 RFC2045}.
	@return void
*/
function addAttachmentFromString($content, $type, $name, $encoding = "base64")
{
	$name_encoded = $this->encodeFileName($name);
	$part = new MIMEPart(
		self::headerLine("Content-Type", "$type; name=\"$name_encoded\"")
		. self::headerLine("Content-Transfer-Encoding", $encoding)
		. self::headerLine("Content-Disposition",
				"attachment; filename=\"$name_encoded\""),
		$this->encodeString($content, $encoding) );
	
	$this->attachments[] = $part;
}


/**
	Adds an inline attachment to the HTML message, typically an image.
	Example:
	
	<pre>
	$m = new PHPMailer();
	$cid = "1";
	$m-&gt;setHtmlMessage("&lt;html&gt;&lt;body&gt;Photo: &lt;img src='cid:$cid'&gt; &lt;/body&gt;&lt;/html&gt;");
	$m-&gt;addInlineFromFile("C:\\images\\photo.jpg", "image/jpeg", $cid);
	</pre>

	@param string $path Pathfile of the attachment.
	@param string $type MIME type of the file. See
	{@link http://www.iana.org/assignments/media-types/index.html} for
	a complete list of the registered media types.
	@param string $name Name proposed to the remote user whenever he decides
	to save this content. If empty, uses the basename of the file.
	@param string $cid Content ID of the attachment to be used in a URL
	of the type cid:ID. Every embedded attachment must have a different
	content ID.
	@param string $encoding Options for this are "8bit", "7bit", "binary",
	"base64", and "quoted-printable". See
	{@link http://tools.ietf.org/html/rfc2045 RFC2045}.
	@return void
	@throws ErrorException Failed to read the file.
*/
function addInlineFromFile($path, $type, $cid, $name = "", $encoding = "base64")
{
	$name = empty($name)? basename($path) : $name;
	$name_encoded = $this->encodeFileName($name);
	$part = new MIMEPart(
		self::headerLine("Content-Type", "$type; name=\"$name_encoded\"")
		. self::headerLine("Content-Transfer-Encoding", $encoding)
		. self::headerLine("Content-ID", "<$cid>")
		. self::headerLine("Content-Disposition", "inline; filename=\"$name_encoded\""),
		$this->encodeFile($path, $encoding) );
	
	$this->inlines[] = $part;
}


/**
	Adds an inline related attachment to the HTML message, typically an image.
	Example:
	
	<pre>
	$m = new PHPMailer();
	$cid = "1";
	$m-&gt;setHtmlMessage("&lt;html&gt;&lt;body&gt;Photo: &lt;img src='cid:$cid'&gt; &lt;/body&gt;&lt;/html&gt;");
	$m-&gt;addInlineFromString("photo.jpg", "image/jpeg", $cid);
	</pre>

	@param string $content Binary content of the inline related attachment.
	@param string $type MIME type of the content. See
	{@link http://www.iana.org/assignments/media-types/index.html} for
	a complete list of the registered media types.
	@param string $cid Content ID of the attachment to be used in a URL
	of the type cid:ID. Every embedded attachment must have a different
	content ID.
	@param string $name Name proposed to the remote user whenever he decides
	to save this content.
	@param string $encoding Options for this are "8bit", "7bit", "binary",
	"base64", and "quoted-printable". See
	{@link http://tools.ietf.org/html/rfc2045 RFC2045}.
	@return void
*/
function addInlineFromString($content, $type, $cid, $name, $encoding = "base64")
{
	$name_encoded = $this->encodeFileName($name);
	$part = new MIMEPart(
		self::headerLine("Content-Type", "$type; name=\"$name_encoded\"")
		. self::headerLine("Content-Transfer-Encoding", $encoding)
		. self::headerLine("Content-ID", "<$cid>")
		. self::headerLine("Content-Disposition", "inline; filename=\"$name_encoded\""),
		$this->encodeString($content, $encoding) );
	
	$this->inlines[] = $part;
}


/**
	Returns the server hostname or 'localhost.localdomain' if unknown.
	@return string
*/
private function serverHostname()
{
	if ( ! empty($this->host_name) )
		$result = $this->host_name;
	else if ( isset($_SERVER['SERVER_NAME']) )
		$result = $_SERVER['SERVER_NAME'];
	else if( strlen( php_uname("n") ) > 0 )
		$result = php_uname("n");
	/*
	else if( function_exists("gethostname") and gethostname() !== FALSE )
		# gethostname() exists only since PHP 5.3.0
		$result = gethostname();
	*/
	else
		$result = "localhost.localdomain";

	return $result;
}


/**
 * Generates an unique message ID for this message. Tries its best to avoid
 * collisions with ohter messages that may be sent from this same server
 * and at the same time by mixing informations that are (should be...)
 * specific of this message alone.
 * @return string 
 */
private function messageID()
{
	$base = uniqid("", TRUE) . $this->subject . $this->from . $this->sender;
	# Also appends some of the recipients, max 5 from the To list, and at
	# least one from the Cc and Bcc lists:
	$n = 5;
	if( $this->to !== NULL )
		foreach($this->to as $to){
			$base .= $to[0];
			$n--;
			if( $n <= 0 )
				break;
		}
	if( $this->cc !== NULL )
		foreach($this->cc as $to){
			$base .= $to[0];
			$n--;
			if( $n <= 0 )
				break;
		}
	if( $this->bcc !== NULL )
		foreach($this->bcc as $to){
			$base .= $to[0];
			$n--;
			if( $n <= 0 )
				break;
		}
	return "<" . md5($base) . "@" . $this->serverHostname() . ">";
}


/**
	Assembles message header.
	@param MIMEPart $body
	@return string
*/
private function createHeader($body)
{
	$result = self::headerLine("Date", PHPMailer::RFCDate());
	if( strlen($this->sender) == 0 )
		$result .= self::headerLine("Return-Path", trim($this->from));
	else
		$result .= self::headerLine("Return-Path", trim($this->sender));
	
	// To be created automatically by mail()
	if($this->mailer !== "mail")
	{
		if(count($this->to) > 0)
			$result .= $this->addrAppend("To", $this->to);
		else if (count($this->cc) == 0)
			$result .= self::headerLine("To", "undisclosed-recipients:;");
		if(count($this->cc) > 0)
			$result .= $this->addrAppend("Cc", $this->cc);
	}

	$from = array( array(trim($this->from), $this->from_name) );
	$result .= $this->addrAppend("From", $from); 

	// sendmail and mail() extract Bcc from the header before sending
	if((($this->mailer === "sendmail") || ($this->mailer === "mail")) && (count($this->bcc) > 0))
		$result .= $this->addrAppend("Bcc", $this->bcc);

	if(count($this->replyTo) > 0)
		$result .= $this->addrAppend("Reply-to", $this->replyTo);

	// mail() sets the subject itself
	if($this->mailer !== "mail")
		$result .= self::headerLine("Subject", $this->encodeHeader(trim($this->subject)));

	if( $this->send_message_id ){
		$this->message_id = $this->messageID();
		$result .= self::headerLine("Message-ID", $this->message_id);
	} else {
		$this->message_id = NULL;
	}
	
	$result .= self::headerLine("X-Priority", (string) $this->priority);
	$result .= self::headerLine("X-Mailer", "PHPMailer [version " . self::VERSION . "]");
	
	if( strlen($this->confirm_reading_to) > 0 )
	{
		$result .= self::headerLine("Disposition-Notification-To", 
				   "<" . trim($this->confirm_reading_to) . ">");
	}

	// Add custom headers
	for($index = 0; $index < count($this->customFields); $index++)
	{
		$result .= self::headerLine(trim($this->customFields[$index][0]), 
				   $this->encodeHeader(trim($this->customFields[$index][1])));
	}
	$result .= self::headerLine("MIME-Version", "1.0") . $body->header;

	return $result;
}


/////////////////////////////////////////////////
// MAIL SENDING METHODS
/////////////////////////////////////////////////

/**
	Closes the active SMTP session if one exists.
	@return void
*/
private function smtpClose()
{
	if($this->smtp != NULL)
	{
		try {
			if($this->smtp->connected())
			{
				# Try to close cleanly:
				try { $this->smtp->quit(); } catch(ErrorException $e){}
				# Force close:
				try { $this->smtp->close(); } catch(ErrorException $e){}
			}
		}
		catch(ErrorException $e){}
		$this->smtp = NULL;
	}
}

/**
	Sets mailer to send message using the PHP {@link mail()} function.
	Also closes any pending SMTP, if any. This mailer is the default one.
	@return void
*/
function useMail()
{
	$this->smtpClose();
	$this->mailer = "mail";
}

/**
	Sets Mailer to send message using the SMTP protocol.
	Also closes any pending SMTP, if any.
	@param string $hosts SMTP hosts.
	You can list one or several servers separated by semicolon
	and possibly with a port number separated with a colon,
	for example: "host1;host2:25;host3:1025".  The default
	TCP port is 25.
	The listed hosts are tried in the order, the first accepting
	the connection and possibly the authentication login
	is used.
	@param int $timeout Connection timeout (s).
	@param string $username If non-empty, performs user authentication.
	@param string $password Password for the user authentication.
	@param string $helo "Helo" message, if required.
	@return void
*/
function useSMTP($hosts, $timeout = 10,
	$username = "", $password = "", $helo = "")
{
	$this->smtpClose();
	
	$this->mailer = "smtp";
	$this->hosts = $hosts;
	$this->timeout = $timeout;
	$this->username = $username;
	$this->password = $password;
	$this->helo = $helo;
}


/**
	Sets Mailer to send message using the sendmail,
	qmail or other compatible program.
	Also closes any pending SMTP, if any.
	@param string $sendmail Path to the sendmail program, typically
	"/usr/sbin/sendmail" or "/var/qmail/bin/sendmail".
	@return void
*/
function useSendmail($sendmail = "/usr/sbin/sendmail")
{
	$this->smtpClose();
	
	$this->sendmail = $sendmail;
	$this->mailer = "sendmail";
}


private static /*. string .*/ function joinParts(/*. MIMEPart[int] .*/ $parts, /*. string .*/ $boundary)
{
	$res = "";
	foreach($parts as $part){
		$res .= "--$boundary" . self::LE
			. $part->header
			. self::LE
			. $part->body . self::LE;
	}
	$res .= "--$boundary--" . self::LE;
	return $res;
}


private /*. string .*/ $boundary = NULL;


private /*. string .*/ function getBoundary()
{
	if( $this->boundary === NULL )
		$this->boundary = md5( uniqid("", TRUE) );
	return $this->boundary;
}


/**
 * Assembles the message body. The body may contain:
 * the text part; the HTML part possibly with inline attachments;
 * the attachments. If none of these parts is present, a dummy empty
 * text/plain part is returned.
 * @return MIMEPart The body of the message.
 * @throws ErrorException One of the attached files cannot be accessed.
 */
private function createBody()
{
	# Resets boundary generator:
	$this->boundary = NULL;
	
	# Creates text part:
	$text_part = /*. (MIMEPart) .*/ NULL;
	if( $this->text_msg !== NULL ){
		$text_part = new MIMEPart(
			self::headerLine("Content-Type", "text/plain; charset=" . $this->charset)
			. self::headerLine("Content-Transfer-Encoding", $this->encoding),
			$this->encodeString( self::wrapText($this->text_msg, $this->word_wrap), $this->encoding) );
	}
	
	# Creates HTML + inline part:
	$html_part = /*. (MIMEPart) .*/ NULL;
	if( $this->html_msg !== NULL ){
		$html_part = new MIMEPart(
			self::headerLine("Content-Type", "text/html; charset=" . $this->charset)
			. self::headerLine("Content-Transfer-Encoding", $this->encoding),
			$this->encodeString( self::wrapText($this->html_msg, $this->word_wrap), $this->encoding)
		);
		if( count($this->inlines) > 0 ){
			# Add inline parts:
			$parts = array($html_part);
			foreach($this->inlines as $r){
				$parts[] = $r;
			}
			$boundary = "html_and_related-" . $this->getBoundary();
			$html_part = new MIMEPart(
				self::headerLine("Content-Type", "multipart/related; boundary=\"$boundary\""),
				self::joinParts($parts, $boundary) );
		}
	}
	
	# Create readable part, that may contain the text and the HTML parts:
	$readable_part = /*. (MIMEPart) .*/ NULL;
	if( $text_part === NULL ){
		if( $html_part === NULL ){
			# No readable part.
		} else {
			$readable_part = $html_part;
		}
	} else {
		if( $html_part === NULL ){
			$readable_part = $text_part;
		} else {
			$boundary = "both_text_and_html-" . $this->getBoundary();
			$readable_part = new MIMEPart(
				self::headerLine("Content-Type", "multipart/alternative; boundary=\"$boundary\""),
				self::joinParts(array($text_part, $html_part), $boundary) );
		}
	}
	
	# Assembles the readable part and attachments:
	$body = /*. (MIMEPart) .*/ NULL;
	if( $readable_part === NULL ){
		if( count($this->attachments) == 0 ){
			# Empty body.
			$body = new MIMEPart(
				self::headerLine("Content-Type", "text/plain"), "");
		} else {
			# Only attachments.
			$boundary = "only_attachments-" . $this->getBoundary();
			$body = new MIMEPart(
				self::headerLine("Content-Type", "multipart/mixed; boundary=\"$boundary\""),
				self::joinParts($this->attachments, $boundary) );
		}
	} else {
		if( count($this->attachments) == 0 ){
			# Only readable part.
			$body = $readable_part;
		} else {
			# Readable part + attachments.
			$parts = array($readable_part);
			foreach($this->attachments as $a){
				$parts[] = $a;
			}
			$boundary = "readable_and_attachments-" . $this->getBoundary();
			$body = new MIMEPart(
				self::headerLine("Content-Type", "multipart/mixed; boundary=\"$boundary\""),
				self::joinParts($parts, $boundary));
		}
	}

	return $body;
}


/**
	Sends mail using the sendmail program.  
	@param string $header
	@param string $body
	@return void
	@throws ErrorException Communication with the sendmail process
	failde. Mail delivery failed.
*/
private function sendmailSend($header, $body)
{
	if( strlen($this->sender) > 0 )
		$sendmail = sprintf("%s -oi -f %s -t", $this->sendmail, $this->sender);
	else
		$sendmail = sprintf("%s -oi -t", $this->sendmail);

	$mail = popen($sendmail, "w");

	if(
		fputs($mail, $header) === FALSE
		or fputs($mail, self::LE) === FALSE
		or fputs($mail, $body) === FALSE
	){
		pclose($mail);
		throw new ErrorException("fputs() failed on $sendmail");
	}
	
	if( pclose($mail) != 0 )
		throw new ErrorException("pclose() failed on $sendmail");
}


/**
	Sends mail using the PHP mail() function.
	@param string $header
	@param string $body
	@return bool
	@throws ErrorException mail() failed to deliver the message.
*/
private function mailSend($header, $body)
{
	$to = "";
	for($i = 0; $i < count($this->to); $i++)
	{
		if($i != 0) { $to .= ", "; }
		$to .= $this->to[$i][0];
	}

	if( strlen($this->sender) > 0 && strlen(ini_get("safe_mode"))< 1)
	{
		$old_from = ini_get("sendmail_from");
		ini_set("sendmail_from", $this->sender);
		$params = sprintf("-oi -f %s", $this->sender);
		$rt = mail($to, $this->encodeHeader($this->subject), $body, 
					$header, $params);
	}
	else
		$rt = mail($to, $this->encodeHeader($this->subject), $body, $header);

	if (isset($old_from))
		ini_set("sendmail_from", $old_from);

	if(!$rt)
		throw new ErrorException("mail() failed: $php_errormsg");

	return TRUE;
}


/**
	Initiates a connection to an SMTP server.
	@return void
	@throws ErrorException None of the hosts accepted
	the connection and possibly the user authentication.
*/
private function smtpConnect()
{
	if($this->smtp == NULL) { $this->smtp = new SMTP(); }

	// Connection+authentication already available?
	if( $this->smtp->connected() )
		return;

	// Try all the hosts in turn:
	$this->smtp->debug = $this->SMTPDebug;
	$hosts = explode(";", $this->hosts);
	if ( count($hosts) < 1 )
		throw new ErrorException("empty SMTP hosts list");
	$errs = "";
	for( $i = 0; $i < count($hosts); $i++ ){

		if(strstr($hosts[$i], ":") !== FALSE){
			$a = explode(":", $hosts[$i]);
			$host = $a[0];
			$port = (int) $a[1];

		} else {
			$host = $hosts[$i];
			$port = 25;
		}

		try {

			$this->smtp->connect($host, $port, $this->timeout);

			if ($this->helo !== '')
				$this->smtp->hello($this->helo);
			else
				$this->smtp->hello($this->serverHostname());
	
			if( strlen($this->username) > 0 )
				$this->smtp->authenticate($this->username, $this->password);

			return;
		}
		catch ( ErrorException $e ) {
			$errs .= " $host:$port: " . $e->getMessage();
			if ( $this->smtp->connected() ){
				try {
					$this->smtp->quit();
					$this->smtp->close();
				} catch ( ErrorException $e2 ) { }
			}
			$this->smtp = NULL;
		}
	}

	// All the hosts failed:
	throw new ErrorException($errs);
}


/**
	Sends mail via SMTP using {@link SMTP}.
	@author Chris Ryan
	@param string $header
	@param string $body
	@return string[int] List of recipients that where rejected from
	the remote server. If this array is empty, all the recipients where
	accepted and the mail has been sent, otherwise the mail is not sent.
	@throws RuntimeException No TO/CC/BCC recipients.
	@throws ErrorException SMTP connection failed. SMTP dialogue failed.
*/
private function smtpSend($header, $body)
{
	$rcpt_total = count($this->to) + count($this->cc) + count($this->bcc);
	if( $rcpt_total == 0 )
		throw new RuntimeException("no TO/CC/BCC recipients");

	$bad_rcpt = /*. (string[int]) .*/ array();

	$this->smtpConnect();

	$smtp_from = (strlen($this->sender) == 0) ? $this->from : $this->sender;
	try {
		$this->smtp->mail($smtp_from);
	}
	catch ( ErrorException $e )
	{
		$this->smtp->reset();
		throw $e;
	}

	// Attempt to send all recipients
	for($i = 0; $i < count($this->to); $i++)
	{
		$to = $this->to[$i][0];
		try { $this->smtp->recipient($to); }
		catch ( ErrorException $e ){
			$bad_rcpt[] = $to;
		}
	}
	for($i = 0; $i < count($this->cc); $i++)
	{
		$to = $this->cc[$i][0];
		try { $this->smtp->recipient($to); }
		catch ( ErrorException $e ){
			$bad_rcpt[] = $to;
		}
	}
	for($i = 0; $i < count($this->bcc); $i++)
	{
		$to = $this->bcc[$i][0];
		try { $this->smtp->recipient($to); }
		catch ( ErrorException $e ){
			$bad_rcpt[] = $to;
		}
	}

	if(count($bad_rcpt) == 0){
		$this->smtp->data($header . self::LE . $body);
	}

	if($this->SMTPKeepAlive == TRUE)
		$this->smtp->reset();
	else
		$this->smtpClose();

	return $bad_rcpt;
}


/**
	Sends the message using the chosen mailer.
	@return bool True if the message is sent successfully.
	If it fails, and the mailer is SMTP, then
	{@link ::$bad_rcpt} may contain a list of the recipients
	that were rejected by the remote server causing the failure.
	If no recipients are specified, does nothing and returns true.
	@throws ErrorException Unrecoverable error from the mailer; the message
	has not been sent to any recipient.
*/
function send()
{
	$this->bad_rcpt = /*. (string[int]) .*/ array();
	
	if((count($this->to) + count($this->cc) + count($this->bcc)) < 1)
		return true;

	$body_part = $this->createBody();
	$header = $this->createHeader($body_part);
	
	//echo $header, self::LE, $body_part->body;

	// Choose the mailer
	$result = FALSE;
	$body = $body_part->body;
	switch($this->mailer)
	{
		case "sendmail":
			$this->sendmailSend($header, $body);
			$result = TRUE;
			break;
		case "mail":
			$result = $this->mailSend($header, $body);
			break;
		case "smtp":
			$this->bad_rcpt = $this->smtpSend($header, $body);
			$result = count($this->bad_rcpt) == 0;
			break;
		default:
			throw new RuntimeException("unknown mailer: " . $this->mailer);
	}

	return $result;
}


/////////////////////////////////////////////////
// MESSAGE RESET METHODS
/////////////////////////////////////////////////

/** Clears all recipients assigned in the TO array.
	@return void
*/
function clearAddresses()
{
	$this->to = NULL;
}

/** Clears all recipients assigned in the CC array.
	@return void
*/
function clearCCs()
{
	$this->cc = NULL;
}

/** Clears all recipients assigned in the BCC array.
	@return void
*/
function clearBCCs()
{
	$this->bcc = NULL;
}

/** Clears all recipients assigned in the ReplyTo array.
	@return void
*/
function clearReplyTos()
{
	$this->replyTo = NULL;
}

/** Clears all recipients assigned in the TO, CC and BCC array.
	@return void
*/
function clearAllRecipients()
{
	$this->to = NULL;
	$this->cc = NULL;
	$this->bcc = NULL;
}

/** Clears all previously set attachments.
	@return void
*/
function clearAttachments()
{
	$this->attachments = NULL;
}

/** Clears all previously set inline related attachments.
	@return void
*/
function clearInline()
{
	$this->inlines = NULL;
}

/** Clears all custom headers.
	@return void
*/
function clearCustomHeaders()
{
	$this->customFields = NULL;
}


/**
	Destructs this object. Releases the SMTP connection, if any.
	@return void
*/
function __destruct()
{
	$this->smtpClose();
}


}
