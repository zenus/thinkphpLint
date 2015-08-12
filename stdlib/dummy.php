<?php

/*.
	require_module 'standard';
	require_module 'mysql';
	require_module 'pcre';
.*/

/**

Loads some modules of common use.

<p>
Since most visitors of the PHPLint on-line interface forget (or simply still do
not have realized) to indicate the PHP modules their program needs, they are
confused by so many error messages PHPLint raises on apparently "unknown"
functions as common as <code>count()</code> or <code>printf()</code>. So I
decided to always include this package that, in turn, merely import some common
modules:

<ul>

<li><code>standard</code> provides the most common PHP constants and functions,</li>

<li><code>mysql</code> allows to access the MySQL DB,</li>

<li>and <code>pcre</code> for regular expression parsing.</li>

</ul>

<p>
A minor downside of this approach is that for very simple chunk of code that
does not use any of these resources (for example a bare <code>&lt;?php echo "hello
world"; ?&gt;</code>), PHPLint will complain about this package being imported but
actually not used.

<p>
To summarize, <b>every</b> PHP source of practical use should contain these
PHPLint meta-code instructions that includes the standard module and some
of the most popular extensions:

<blockquote>
<pre>
&lt;?php
/&#42;.
    require_module 'standard';
    require_module 'mysql';
    require_module 'pcre';
.&#42;/
</pre>
</blockquote>

<p>
Please note that the name of the module is a single-quoted string. This
declaration appears very similar to the PHP statement
<code>require_once</code>, but actually it is ignored by PHP since it is only a
comment enclosed between the symbols<code>/&#42;.&nbsp;.&#42;/</code> (please note the
dot after the first asterisk and the dot before the second asterisk: these dots
tell that the comment contains meta-code that needs to be parsed by PHPLint).

<p>
See also the {@link http://www.icosaedro.it/phplint/phplint2/libraries.htm PHPLint
Standard Libraries} for more details about the available modules and packages.

@package dummy
*/

