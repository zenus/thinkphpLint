<?php

namespace net\sourceforge\phpmailer;
/*.
	require_module 'standard';
	require_module 'streams';
.*/

require_once __DIR__ . "/../../../errors.php";

use ErrorException;

/**
	Allows to connect and communicate with any SMTP server. It implements
	all the SMTP functions defined in RFC 821 except TURN.

	From: {@link http://phpmailer.sourceforge.net}
	
	SMTP is RFC 821 compliant and implements all the RFC 821 SMTP
	commands except TURN which will always return a not implemented
	error. SMTP also provides some utility methods for sending mail
	to an SMTP server.

	Some methods can return exceptions of the class ErrorException
	indicating something goes wrong talking to the server, or the server
	did not understand the command.

	2012-02-05 Umberto Salsi:
	Converted from PHP4 to PHP5.
	Added PHPLint meta-code.
	Exceptions support added.

	@version 1.02
	@author Chris Ryan
	@license LICENSE.txt LGPL
*/
class SMTP
{
	/** Enable debugging messages
	@var bool */
	public $debug = FALSE;

	/** Send debugging messages to stderr rather than to stdout.
	@var bool */
	public $debug_stderr = FALSE;

	/*. private .*/ const ERR_STILL_NOT_CONNECTED = "still not connected";

	private /*. resource .*/  $smtp_conn;  # the socket to the server
	private /*. string .*/	$helo_rply;	 # the reply the server sent to us for HELO

	private /*. void .*/ function log(/*. string .*/ $s)
	{
		if($this->debug_stderr)
			error_log($s);
		else
			echo $s;
	}


	/**
		Initialize the class so that the data is in a known state.
		@return void
	*/
	function __construct()
	{
		$this->smtp_conn = NULL;
		$this->helo_rply = NULL;

		$this->debug = FALSE;
	}

	/* **********************************************************
						  CONNECTION FUNCTIONS					*
	  **********************************************************/

	/**
		Closes the socket and cleans up the state of the class.
		It is not considered good to use this function without
		first trying to use QUIT.
		@return void
		@throws ErrorException Failed to close the socket.
	*/
	function close()
	{
		$this->helo_rply = NULL;
		if( $this->smtp_conn !== NULL ) {
			# close the connection and cleanup
			fclose($this->smtp_conn);
			$this->smtp_conn = NULL;
		}
	}


	/**
		Tells if we are connected to a server.
		@return bool TRUE if connected to a server otherwise FALSE.
		@throws ErrorException Failed to retrieve the status of the socket.
	*/
	function connected()
	{
		if($this->smtp_conn === NULL)
			return FALSE;

		$sock_status = socket_get_status($this->smtp_conn);
		if((bool) $sock_status["eof"]) {
			# hmm this is an odd situation... the socket is
			# valid but we aren't connected anymore
			throw new ErrorException("EOF caught while checking if connected");
		}
		return TRUE; # everything looks good
	}

	
	private /*. void .*/ function put_line(/*. string .*/ $line)
		/*. throws ErrorException .*/
	{
		if($this->debug) {
			$this->log("SMTP Client: $line\n");
		}
		fputs($this->smtp_conn, $line);
		fputs($this->smtp_conn, "\r\n");
	}

	
	/**
		Read in as many lines as possible
		either before eof or socket timeout occurs on the operation.
		With SMTP we can tell if we have more lines to read if the
		4th character is '-' symbol. If it is a space then we don't
		need to read anything else.
		@return string
		@throws ErrorException
	*/
	private function get_lines()
	{
		$data = "";
		while(FALSE !== ($str = fgets($this->smtp_conn,515))) {
			if($this->debug) {
				$this->log("SMTP Server: $str\n");
			}
			$data .= $str;
			if(substr($str,3,1) === " ") { break; }
		}
		return $data;
	}

	
	/**
		Connect to the server specified on the port specified.
		<pre>
		SMTP CODE SUCCESS: 220
		SMTP CODE FAILURE: 421
		</pre>

		@param string $host
		@param int $port
		@param int $tval Max time to wait for the connection be established
		with the server, then an exception is raised (seconds).
		@return void
		@throws ErrorException Already connected. Connection failed.
		Timout waiting for the connection.
	*/
	function connect($host, $port=25, $tval=30)
	{
		if($this->connected())
			throw new ErrorException("Already connected to a server");

		$this->smtp_conn = fsockopen($host, $port,
			 $errno, $errstr,
			 $tval);
		# verify we connected properly
		if( $this->smtp_conn === FALSE ) {
			$this->smtp_conn = NULL;
			throw new ErrorException("Failed to connect to server: $errstr ($errno)");
		}

		# sometimes the SMTP server takes a little longer to respond
		# so we will give it a longer timeout for the first read
		// Windows still does not have support for this timeout function
		if(substr(PHP_OS, 0, 3) !== "WIN")
		   socket_set_timeout($this->smtp_conn, $tval, 0);

		# get any announcement stuff
		/* $ignore_announce = */ $this->get_lines();

		# set the timeout  of any socket functions at 1/10 of a second
		//if(function_exists("socket_set_timeout"))
		//	 socket_set_timeout($this->smtp_conn, 0, 100000);
	}

	
	/**
		Performs SMTP authentication.  Must be run after running the
		{@link ::hello()} method.
		@param string $username
		@param string $password
		@return void
		@throws ErrorException Still not connected. Login failed.
	*/
	function authenticate($username, $password)
	{
		if(!$this->connected())
			throw new ErrorException(self::ERR_STILL_NOT_CONNECTED);

		// Start authentication
		$this->put_line("AUTH LOGIN");

		$rply = $this->get_lines();
		$code = (int) substr($rply,0,3);

		if($code != 334)
			throw new ErrorException("AUTH not accepted from server: "
			. substr($rply,4) . " ($code)");

		// Send encoded username
		$this->put_line(base64_encode($username));

		$rply = $this->get_lines();
		$code = (int) substr($rply,0,3);

		if($code != 334)
			throw new ErrorException("Username not accepted from server: "
			. substr($rply,4) . " ($code)");

		// Send encoded password
		$this->put_line(base64_encode($password));

		$rply = $this->get_lines();
		$code = (int) substr($rply,0,3);

		if($code != 235)
			throw new ErrorException("Password not accepted from server: "
			. substr($rply,4) . " ($code)");
	}

	/* *************************************************************
							  SMTP COMMANDS						  *
	  ************************************************************/

	/**
		Issues a data command and sends the msg_data to the server
		finializing the mail transaction. $msg_data is the message
		that is to be send with the headers. Each header needs to be
		on a single line followed by a &lt;CRLF&gt; with the message headers
		and the message body being seperated by and additional &lt;CRLF&gt;.

		Implements RFC 821: DATA &lt;CRLF&gt;

		<pre>
		SMTP CODE INTERMEDIATE: 354
		[data] &lt;CRLF&gt;
		.&lt;CRLF&gt;
		SMTP CODE SUCCESS: 250
		SMTP CODE FAILURE: 552,554,451,452
		SMTP CODE FAILURE: 451,554
		SMTP CODE ERROR	: 500,501,503,421
		</pre>
		@param string $msg_data
		@return void
		@throws ErrorException Not connected. Dialogue failed with the
		remote server.
	*/
	function data($msg_data)
	{
		if(!$this->connected()) {
			throw new ErrorException(self::ERR_STILL_NOT_CONNECTED);
		}

		$this->put_line("DATA");

		$rply = $this->get_lines();
		$code = (int) substr($rply,0,3);

		if($code != 354)
			throw new ErrorException("DATA command not accepted from server: "
			. substr($rply,4) . " ($code)");

		# the server is ready to accept data!
		# according to RFC 821 we should not send more than 1000
		# including the CRLF
		# characters on a single line so we will break the data up
		# into lines by \r and/or \n then if needed we will break
		# each of those into smaller lines to fit within the limit.
		# in addition we will be looking for lines that start with
		# a period '.' and append and additional period '.' to that
		# line. NOTE: this does not count towards are limit.

		# normalize the line breaks so we know the explode works
		$msg_data = (string) str_replace("\r\n","\n",$msg_data);
		$msg_data = (string) str_replace("\r","\n",$msg_data);
		$lines = explode("\n",$msg_data);

		# we need to find a good way to determine is headers are
		# in the msg_data or if it is a straight msg body
		# currently I'm assuming RFC 822 definitions of msg headers
		# and if the first field of the first line (':' sperated)
		# does not contain a space then it _should_ be a header
		# and we can process all lines before a blank "" line as
		# headers.
		$field = substr($lines[0],0,strpos($lines[0],":"));
		$in_headers = FALSE;
		if(!empty($field) && FALSE===strstr($field," ")) {
			$in_headers = TRUE;
		}

		$max_line_length = 998; # used below; set here for ease in change

		foreach($lines as $line) {
			$lines_out = /*. (array[int]string) .*/ NULL;
			if($line === "" && $in_headers) {
				$in_headers = FALSE;
			}
			# ok we need to break this line up into several
			# smaller lines
			while(strlen($line) > $max_line_length) {
				$pos = strrpos(substr($line,0,$max_line_length)," ");

				# Patch to fix DOS attack
				if($pos===FALSE) {
					$pos = $max_line_length - 1;
				}

				$lines_out[] = substr($line,0,$pos);
				$line = substr($line,$pos + 1);
				# if we are processing headers we need to
				# add a LWSP-char to the front of the new line
				# RFC 822 on long msg headers
				if($in_headers) {
					$line = "\t" . $line;
				}
			}
			$lines_out[] = $line;

			# now send the lines to the server
			# FIXED
			#while(list(,$line_out) = @each($lines_out)) {
			foreach($lines_out as $line_out) {
				if(strlen($line_out) > 0)
				{
					if(substr($line_out, 0, 1) === ".") {
						$line_out = "." . $line_out;
					}
				}
				$this->put_line($line_out);
			}
		}

		# ok all the message data has been sent so lets get this
		# over with aleady
		$this->put_line(".");

		$rply = $this->get_lines();
		$code = (int) substr($rply,0,3);

		if($code != 250) {
			throw new ErrorException("DATA not accepted from server: "
			. substr($rply,4) . " ($code)");
		}
	}

	
	/**
		Expand takes the name and asks the server to list all the
		people who are members of the _list_. Expand will return
		back and array of the result or FALSE if an error occurs.
		Each value in the array returned has the format of:
		[ &lt;full-name&gt; &lt;SP&gt; ] &lt;path&gt;
		&lt;path&gt; is defined in RFC 821.

		Implements RFC 821: EXPN &lt;SP&gt; &lt;string&gt; &lt;CRLF&gt;

		<pre>
		SMTP CODE SUCCESS: 250
		SMTP CODE FAILURE: 550
		SMTP CODE ERROR	: 500,501,502,504,421
		</pre>
		@param string $name
		@return string[int]
		@throws ErrorException Not connected. EXPN command rejected by
		server. Dialogue failed.
	*/
	function expand($name)
	{
		if(!$this->connected())
			throw new ErrorException(self::ERR_STILL_NOT_CONNECTED);

		$this->put_line("EXPN " . $name);

		$rply = $this->get_lines();
		$code = (int) substr($rply,0,3);

		if($code != 250)
			throw new ErrorException("EXPN not accepted from server: "
			. substr($rply,4) . " ($code)");

		# parse the reply and place in our array to return to user
		$entries = explode("\r\n", $rply);
		$list_ = /*. (string[int]) .*/ array();
		foreach($entries as $l) {
			$list_[] = substr($l,4);
		}

		return $list_;
	}

	
	/**
		Sends a HELO/EHLO command.
		@param string $hello
		@param string $host
		@return void
		@throws ErrorException Not connected. Dialogue failed.
	*/
	function sendHello($hello, $host)
	{
		if(!$this->connected()) {
			throw new ErrorException(self::ERR_STILL_NOT_CONNECTED);
		}

		$this->put_line($hello . " " . $host);

		$rply = $this->get_lines();
		$code = (int) substr($rply,0,3);

		if($code != 250)
			throw new ErrorException($hello . " not accepted from server: "
			. substr($rply,4) . " ($code)");

		$this->helo_rply = $rply;
	}

	
	/**
		Sends the HELO command to the smtp server.
		This makes sure that we and the server are in
		the same known state.

		Implements from RFC 821: HELO &lt;SP&gt; &lt;domain&gt; &lt;CRLF&gt;

		<pre>
		SMTP CODE SUCCESS: 250
		SMTP CODE ERROR	: 500, 501, 504, 421
		</pre>
		@param string $host
		@return void
		@throws ErrorException Not connected. Dialogue failed.
	*/
	function hello($host="")
	{
		if(!$this->connected())
			throw new ErrorException(self::ERR_STILL_NOT_CONNECTED);

		# if a hostname for the HELO wasn't specified determine
		# a suitable one to send
		if(empty($host)) {
			# we need to determine some sort of appropriate default
			# to send to the server
			$host = "localhost";
		}

		// Send extended hello first (RFC 2821)
		try {
			$this->sendHello("EHLO", $host);
		}
		catch ( ErrorException $e ) {
			$this->sendHello("HELO", $host);
		}
	}

	
	/**
		Gets help information on the keyword specified.
		If the keyword
		is not specified then returns generic help, ussually containing
		a list of keywords that help is available on. This function
		returns the results back to the user. It is up to the user to
		handle the returned data.

		Implements RFC 821: HELP [ &lt;SP&gt; &lt;string&gt; ] &lt;CRLF&gt;

		<pre>
		SMTP CODE SUCCESS: 211,214
		SMTP CODE ERROR	: 500,501,502,504,421
		</pre>
		@param string $keyword
		@return string
		@throws ErrorException Not connected. HELP command rejected.
	*/
	function help($keyword="")
	{
		if(!$this->connected())
			throw new ErrorException(self::ERR_STILL_NOT_CONNECTED);

		$extra = "";
		if(!empty($keyword)) {
			$extra = " " . $keyword;
		}

		$this->put_line("HELP" . $extra);

		$rply = $this->get_lines();
		$code = (int) substr($rply,0,3);

		if($code != 211 && $code != 214)
			throw new ErrorException("HELP not accepted from server: "
			. substr($rply,4) . " ($code)");

		return $rply;
	}

	
	/**
		Starts a mail transaction from the email address specified in
		$from and then one or more {@link ::recipient()} commands may be
		called followed by a {@link ::data()} command.
		<p>

		Implements RFC 821: MAIL &lt;SP&gt; FROM:&lt;reverse-path&gt; &lt;CRLF&gt;

		<pre>
		SMTP CODE SUCCESS: 250
		SMTP CODE SUCCESS: 552,451,452
		SMTP CODE SUCCESS: 500,501,421
		</pre>
		@param string $from
		@return void
		@throws ErrorException Not connected. MAIL command rejected.
	*/
	function mail($from)
	{
		if(!$this->connected())
			throw new ErrorException(self::ERR_STILL_NOT_CONNECTED);

		$this->put_line("MAIL FROM: <$from>");

		$rply = $this->get_lines();
		$code = (int) substr($rply,0,3);

		if($code != 250)
			throw new ErrorException("MAIL not accepted from server: "
			. substr($rply,4) . " ($code)");
	}

	
	/**
		Sends the command NOOP to the SMTP server.

		Implements from RFC 821: NOOP &lt;CRLF&gt;

		<pre>
		SMTP CODE SUCCESS: 250
		SMTP CODE ERROR	: 500, 421
		</pre>
		@return void
		@throws ErrorException Not connected. NOOP command rejected.
	*/
	function noop()
	{
		if(!$this->connected())
			throw new ErrorException(self::ERR_STILL_NOT_CONNECTED);

		$this->put_line("NOOP");

		$rply = $this->get_lines();
		$code = (int) substr($rply,0,3);

		if($code != 250)
			throw new ErrorException("NOOP not accepted from server: "
			. substr($rply,4) . " ($code)");
	}

	
	/**
		Sends the quit command to the server and then closes the socket.

		Implements from RFC 821: QUIT &lt;CRLF&gt;

		<pre>
		SMTP CODE SUCCESS: 221
		SMTP CODE ERROR	: 500
		</pre>
		@return void
		@throws ErrorException QUIT command rejected.
	*/
	function quit()
	{
		if( $this->smtp_conn === NULL )
			return;

		# send the quit command to the server
		$this->put_line("QUIT");

		# get any good-bye messages
		$byemsg = $this->get_lines();

		$this->close();

		$code = (int) substr($byemsg,0,3);
		if($code != 221) {
			throw new ErrorException("SMTP server rejected quit command: "
			. substr($byemsg,4) . " ($code)");
		}
	}

	
	/**
		Sends the command RCPT to the SMTP server with the TO: argument of $to.
		Returns TRUE if the recipient was accepted FALSE if it was rejected.

		Implements from RFC 821: RCPT &lt;SP&gt; TO:&lt;forward-path&gt; &lt;CRLF&gt;

		<pre>
		SMTP CODE SUCCESS: 250,251
		SMTP CODE FAILURE: 550,551,552,553,450,451,452
		SMTP CODE ERROR	: 500,501,503,421
		</pre>
		@param string $to
		@return void
		@throws ErrorException Not connected. RCPT command rejected.
	*/
	function recipient($to)
	{
		if(!$this->connected())
			throw new ErrorException(self::ERR_STILL_NOT_CONNECTED);

		$this->put_line("RCPT TO: <$to>");

		$rply = $this->get_lines();
		$code = (int) substr($rply,0,3);

		if($code != 250 && $code != 251)
			throw new ErrorException("RCPT not accepted from server: "
			. substr($rply,4) . " ($code)");
	}

	
	/**
		Sends the RSET command to abort and transaction that is
		currently in progress.

		Implements RFC 821: RSET &lt;CRLF&gt;

		<pre>
		SMTP CODE SUCCESS: 250
		SMTP CODE ERROR	: 500,501,504,421
		</pre>
		@return void
		@throws ErrorException Not connected. RSET command rejected.
	*/
	function reset()
	{
		if(!$this->connected()) {
			throw new ErrorException(self::ERR_STILL_NOT_CONNECTED);
		}

		$this->put_line("RSET");

		$rply = $this->get_lines();
		$code = (int) substr($rply,0,3);

		if($code != 250)
			throw new ErrorException("RSET failed: $rply");
	}

	
	/**
		Starts a mail transaction from the email address specified in
		$from. Returns TRUE if successful or FALSE otherwise. If TRUE
		the mail transaction is started and then one or more {@link
		::recipient()} commands may be called followed by a {@link
		::data()} command. This command will send the message to the
		users terminal if they are logged in.

		Implements RFC 821: SEND &lt;SP&gt; FROM:&lt;reverse-path&gt; &lt;CRLF&gt;

		<pre>
		SMTP CODE SUCCESS: 250
		SMTP CODE SUCCESS: 552,451,452
		SMTP CODE SUCCESS: 500,501,502,421
		</pre>
		@param string $from
		@return void
		@throws ErrorException Not connected. SEND command rejected.
	*/
	function send($from)
	{
		if(!$this->connected())
			throw new ErrorException(self::ERR_STILL_NOT_CONNECTED);

		$this->put_line("SEND FROM: $from");

		$rply = $this->get_lines();
		$code = (int) substr($rply,0,3);

		if($code != 250)
			throw new ErrorException("SEND not accepted from server: $rply");
	}

	
	/**
		Starts a mail transaction from the email address specified in $from.
		Returns TRUE if successful or FALSE otherwise. If TRUE the mail
		transaction is started and then one or more {@link ::recipient()}
		commands may be called followed by a {@link ::data()} command. This
		command will send the message to the users terminal if they are
		logged in and send them an email.

		Implements RFC 821: SAML &lt;SP&gt; FROM:&lt;reverse-path&gt; &lt;CRLF&gt;

		<pre>
		SMTP CODE SUCCESS: 250
		SMTP CODE SUCCESS: 552,451,452
		SMTP CODE SUCCESS: 500,501,502,421
		</pre>
		@param string $from
		@return void
		@throws ErrorException Not connected. SAML command rejected.
	*/
	function sendAndMail($from)
	{
		if(!$this->connected())
			throw new ErrorException(self::ERR_STILL_NOT_CONNECTED);

		$this->put_line("SAML FROM: $from ");

		$rply = $this->get_lines();
		$code = (int) substr($rply,0,3);

		if($code != 250)
			throw new ErrorException("SAML not accepted from server: $rply");
	}

	
	/**
		Starts a mail transaction from the email address specified in
		$from. Returns TRUE if successful or FALSE otherwise. If TRUE
		the mail transaction is started and then one or more {@link
		::recipient()} commands may be called followed by a {@link
		::data()} command. This command will send the message to the users
		terminal if they are logged in or mail it to them if they are not.

		Implements RFC 821: SOML &lt;SP&gt; FROM:&lt;reverse-path&gt; &lt;CRLF&gt;

		<pre>
		SMTP CODE SUCCESS: 250
		SMTP CODE SUCCESS: 552,451,452
		SMTP CODE SUCCESS: 500,501,502,421
		</pre>
		@param string $from
		@return void
		@throws ErrorException Not connected. SOML command rejected.
	*/
	function sendOrMail($from)
	{
		if(!$this->connected())
			throw new ErrorException(self::ERR_STILL_NOT_CONNECTED);

		$this->put_line("SOML FROM: $from");

		$rply = $this->get_lines();
		$code = (int) substr($rply,0,3);

		if($code != 250)
			throw new ErrorException("SOML not accepted from server: $rply");
	}

	
	/**
		This is an optional command for SMTP that this class does not
		support. This method is here to make the RFC 821 Definition
		complete for this class and __may__ be implemented in the future.

		Implements from RFC 821: TURN &lt;CRLF&gt;

		<pre>
		SMTP CODE SUCCESS: 250
		SMTP CODE FAILURE: 502
		SMTP CODE ERROR	: 500, 503
		</pre>
		@return void
		@throws ErrorException Method not implemented; always throws this.
	*/
	function turn()
	{
		throw new ErrorException("method not implemented");
	}

	
	/**
		Verifies that the name is recognized by the server.
		Returns the result code from the server (see below).

		Implements RFC 821: VRFY &lt;SP&gt; &lt;string&gt; &lt;CRLF&gt;

		<pre>
		SMTP CODE SUCCESS: 250,251
		SMTP CODE FAILURE: 550,551,553
		SMTP CODE ERROR	: 500,501,502,421
		</pre>

		NOTE. See RFC 2821 for even more result codes.
		@param string $name
		@return int 250 and 251 means the user is recognized. Anything else
		means error.
		@throws ErrorException Not connected.
	*/
	function verify($name)
	 {
		if(!$this->connected())
			throw new ErrorException(self::ERR_STILL_NOT_CONNECTED);

		$this->put_line("VRFY $name");

		$rply = $this->get_lines();
		$code = (int) substr($rply,0,3);

		if($code != 250 && $code != 251) {
			#throw new ErrorException("VRFY failed on name '$name': $rply");
		}
		return $code;
	}

}
