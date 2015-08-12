@echo off
rem Runs the PHP interpreter with arguments.
rem
rem      YOU MUST EDIT THIS FILE TO ENTER THE EXACT PATH
rem      OF THE PHP EXECUTABLE INSTALLED IN YOUR SYSTEM.
rem
rem Syntax of the command:
rem
rem     php [OPTIONS] file.php
rem
rem For a complete list of the available options, type
rem
rem     php -h
rem

rem Dir. of this file, with trailing '/' added:
set __DIR__=%~dp0

rem PHP executable file
rem ===================
rem Under Windows its name is typically "php.exe", the CLI executable.
rem There are also two more versions, "php-cgi.exe" and "php-win.exe"
rem but these are not intended to work in "batch" mode on a terminal.
rem set PHP=C:\Program Files\php-5.3.10-nts-Win32-VC9-x86\php.exe
rem 
set PHP=F:\wamp\php\php.exe

rem Directory of the php.ini file:
rem ==============================
rem ATTENTION! stdlib/autoload.php pretends this file be located
rem in the stdlib/ directory, or it will complain.
set INI="%__DIR__%stdlib"

"%PHP%" "-c%INI%" %*
