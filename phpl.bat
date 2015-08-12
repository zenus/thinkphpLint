@echo off
rem Runs the PHPLint program stdlib/it/icosaedro/lint/PHPLint and displays
rem the report on standard output.
rem Syntax of the command:
rem
rem     phpl [OPTIONS] file.php
rem
rem For a complete list of the available options, type
rem
rem     phpl --help
rem

rem Dir. of this file, with trailing '\' added:
set __DIR__=%~dp0

"%__DIR__%php.bat" ^
	"%__DIR__%stdlib\it\icosaedro\lint\PHPLint.php" ^
	--modules-path "%__DIR__%modules" ^
	--php-version 5 ^
	--print-path relative ^
	--print-errors ^
	--print-warnings ^
	--print-notices ^
	--ascii-ext-check ^
	--ctrl-check ^
	--recursive ^
	--no-print-file-name ^
	--parse-phpdoc ^
	--print-context ^
	--print-source ^
	--print-line-numbers ^
	--report-unused ^
	%*



