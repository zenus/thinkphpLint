#!/usr/bin/wish
# phplint.tcl
# Syntax:
#
#      phplint.tcl [FILE.PHP]
#
# Simple interface to the PHPLint program. The configuration part allows to define:
# - the PHP CLI executable
# - the PHPLint directory
# - the source to parse and validate.
# Once a PHP source program has been chosen, displays the PHPLint report,
# then periodically check the same source file for changes: it the modification
# time changes, updates the report.
# Version: $Date: 2015/03/03 16:21:03 $
# Copyright 2007-2014 by icosaedro.it di Umberto Salsi
# Info and updates: www.icosaedro.it/phplint

# Time interval to check for source file changes (ms):
set update_report_interval 1000

# Last seen modification time of the target source; if this changes, update report:
set modification_time ""

set padx 8
set pady 4

set butw 10

set font_norm "Helvetica 10"
set font_em "Helvetica 10 bold"
set font_but "Helvetica 10 bold"

array set opts {
# options specific to this program:
	show_cfg 1
	browser ""
	charset ""

# proper PHPLint options:
	php_cli ""
	phplint_dir ""
	php_ver 5
	show_errors 1
	show_warnings 1
	show_notices 1
	check_non_ascii 1
	check_ascii_ctrl 1
	is_module 0
	recursive 1
	print_file_name 1
	print_path absolute
	parse_phpdoc 1
	print_context 1
	print_source 0
	print_line_numbers 1
	report_unused 1
	source_file ""
}


# Adds a button.
# @param path Tk path of the button.
# @param name Name displayed.
# @param cmd Associated action.
proc StdButt { path name cmd } {
	global font_but
	button $path -text $name -width 8 -command $cmd -font $font_but
	#pack $path
}


# Displays error in dialog box.
proc Error { msg } {
	tk_messageBox -title Error -message $msg -icon error -type ok
}


# Handles button press and calls associated action..
# @param button Tk path to the button.
proc PressButton { button } {
	# Visual feedback of the key pressed:
	$button configure -relief sunken
	update
	after 500
	$button configure -relief raised
	
	# Invoke call-back associated:
	$button invoke
}


# Makes absolute the path to an existing file.
# @param file Path to a file.
# @return Absolute path to the file, if it exists. If the file does not exist,
# returns the argument unmodified.
proc abspath { file } {
	if { ! [file exists $file] } {
		return $file
	}
	if { [file pathtype $file] == "relative" } {
		set file [file join [pwd] $file]
	}
	return $file
}


# Draws a short colored line to the left of the vertical scroll bar that
# marks a relative position in the report.
# @param percent Relative position of the line to mark in [0.0-1.0].
# @param color Color of the mark. Errors should be red, warnings orange,
# notices green.
# @param xoffset Horizontal offset to avoid overlapping (red lines
# marking errors, should always be visible).
proc MarkLine { percent color xoffset } {
	set offset 17
	set height [winfo height .report.canvas]
	set height [expr $height - $offset - $offset]
	set y [expr round($offset + $percent * $height)]
	.report.canvas create line $xoffset $y 15 $y -fill $color -width 2
}


# Draws a short colored mark line just to the left of the vertical scroll
# bar for every line of the report containig the pattern.
# @param t       Text of the report.
# @param pattern Lines of the report containing this substring are marked.
# @param color   Color of the markers.
# @param xoffset Horizontal offset of the markers. Red lines, marking
#                errors, should always have zero offset to remain visible.
# @param sel     Name assigned to the style of the selected patterns.
proc MarkLines { t pattern color xoffset sel } {
	set no_lines [$t index end]
	$t tag add $sel 1.0 1.0
	set i "1.0"
	MarkLine 0.0 black 1
	MarkLine 1.0 black 1
	while { true } {
		set i [$t search -count cnt $pattern $i end]
		if { [string length $i] == 0 } {
			break
		}
		$t tag add $sel $i "$i +$cnt chars"
		MarkLine [expr $i / $no_lines] $color $xoffset
		set i "$i + 1 lines"
	}
	$t tag configure $sel -background $color
}


# Updates the colored line markes to the left of the vertical scroll bar
# that indicates relative locations in the report of errors and warnings.
proc updateMarkers { } {
	.report.canvas delete "all"
	.report.text configure -state normal
	MarkLines .report.text " ERROR:"   red    1 sel_errors
	MarkLines .report.text " Warning:" orange 6 sel_warnings
	MarkLines .report.text " notice:"  green  11 sel_notices
	#.report.text configure -state disabled
}


# Runs PHPLint, displays the report and the generated HTML document.
# @param generate_doc Set to true to generate the HTML document.
proc runPhplint { generate_doc } {
	global opts
	
	if { [string length $opts(php_cli)] == 0 } {
		Error "You must specify the location of the PHP CLI executable program."
		return
	}
	
	if { [string length $opts(phplint_dir)] == 0 } {
		Error "You must specify the location of the PHPLint program."
		return
	}

	if { $opts(php_ver) == 4 } {
		set opt "--php-version 4"
	} else {
		set opt "--php-version 5"
	}

	set opt "$opt --version --print-path shortest"

	set opt "$opt --modules-path \"$opts(phplint_dir)/modules\""

	if { $opts(show_errors) } {
		set opt "$opt --print-errors"
	} else {
		set opt "$opt --no-print-errors"
	}

	if { $opts(show_warnings) } {
		set opt "$opt --print-warnings"
	} else {
		set opt "$opt --no-print-warnings"
	}

	if { $opts(show_notices) } {
		set opt "$opt --print-notices"
	} else {
		set opt "$opt --no-print-notices"
	}

	if { $opts(check_non_ascii) } {
		set opt "$opt --ascii-ext-check"
	} else {
		set opt "$opt --no-ascii-ext-check"
	}

	if { $opts(check_ascii_ctrl) } {
		set opt "$opt --ctrl-check"
	} else {
		set opt "$opt --no-ctrl-check"
	}

	if { $opts(is_module) } {
		set opt "$opt --is-module"
	}

	if { $opts(recursive) } {
		set opt "$opt --recursive"
	} else {
		set opt "$opt --no-recursive"
	}

	if { $opts(print_file_name) } {
		set opt "$opt --print-file-name"
	} else {
		set opt "$opt --no-print-file-name"
	}

	if { $opts(print_column_number) } {
		set opt "$opt --print-column-number"
	} else {
		set opt "$opt --no-print-column-number"
	}

	set opt "$opt --print-path $opts(print_path)"

	if { $opts(parse_phpdoc) } {
		set opt "$opt --parse-phpdoc"
	} else {
		set opt "$opt --no-parse-phpdoc"
	}

	if { $opts(print_context) } {
		set opt "$opt --print-context"
	} else {
		set opt "$opt --no-print-context"
	}

	if { $opts(print_source) } {
		set opt "$opt --print-source"
	} else {
		set opt "$opt --no-print-source"
	}

	if { $opts(print_line_numbers) } {
		set opt "$opt --print-line-numbers"
	} else {
		set opt "$opt --no-print-line-numbers"
	}

	if { $opts(report_unused) } {
		set opt "$opt --report-unused"
	} else {
		set opt "$opt --no-report-unused"
	}

	if { [string length $opts(source_file)] > 0 } {
		if { [file exists $opts(source_file)] } {
			if { $generate_doc } {
				set opt "$opt --doc-page-header \" <html><head><meta http-equiv=Content-Type content='text/html; charset=$opts(charset)'></head><body>\" --doc"
			}
			set opt "$opt [list $opts(source_file)]"
			cd [file dirname "$opts(source_file)"]
		} else {
			Error "The specified file `$opts(source_file)' does not exist."
		}
	}

	# Dims data:
	.report.text configure -state normal
	.report.text configure -background gray
	#.report.text configure -state disabled
	.report.canvas delete "all"
	update
	
	set cmd "[list $opts(php_cli) -c$opts(phplint_dir)/stdlib $opts(phplint_dir)/stdlib/it/icosaedro/lint/PHPLint.php] $opt"
	.report.text insert end $cmd\n\n
	#tk_dialog .zzz "Command Line" "$cmd" {} 0 OK
	set code [catch {eval exec $cmd} out]
	
	.report.text configure -state normal
	.report.text delete 0.0 end
	.report.text configure -background white
	.report.text insert end $out
	if { $code != 0 } {
		.report.text insert end " with code $code\n"
	}
	.report.text see end
	#.report.text configure -state disabled
}


# Updates the report preserving the current position of the vertical
# scroll bar.
# @param generate_doc Set to 1 to generate HTML doc, 0 to not generate.
proc runPhplintPreservingPosition { generate_doc } {
	set pos [lindex [.report.text yview] 0]
	runPhplint $generate_doc
	.report.text yview moveto $pos
	updateMarkers
}


proc BrowseDoc { } {
	global opts
	
	if { [string length $opts(browser)] == 0 } {
			Error "You must specify the HTML browser."
			return
	}

	if { [string length $opts(source_file)] == 0 } {
			Error "You must specify the PHP source program from which the documentation has to be extracted."
		return;
	}

	set html [file rootname $opts(source_file)].html

	if { [file exists $html] } {
		#file rename $html $html.bak
		if { [tk_dialog .zzz "Warning" "The file `$html' already exits. Are you sure to overwrite?" warning 1 Overwrite Cancel] == 1 } {
			return
		}
		set html_existent 1
	} else {
		set html_existent 0
	}

	runPhplintPreservingPosition 1
	if { ! [file exists $html] } {
		Error "The expected document file `$html' was not generated."
		return
	}

	set cmd "[list $opts(browser)] [list file://$html]"
	set code [catch {eval "exec $cmd &"} out]
	if { $code != 0 } {
		Error "The command `$cmd' failed with code $code:\n\n$out."
	}


	if { [tk_dialog .zzz "Keep generated HTML file?" "The new document `$html' was generated and the browser was launched.\n\nNow you may choose what to do with this file: keep or delete this document?" warning 1 Keep Delete] == 1 } {
		file delete $html
	}
}


proc EditSource { } {
	global opts

	if { [string length $opts(source_file)] == 0 } {
		return
	}

	if { [string length $opts(editor)] == 0 } {
		Error "You must specify an editor command first."
		return
	}

	set cmd "[list $opts(editor)] [list $opts(source_file)]"
	set code [catch {eval "exec $cmd &"} out]
	if { $code != 0 } {
		Error "The command `$cmd' failed with code $code:\n\n$out."
	}
}


proc SetDefault { } {
	global opts
	
	set opts(php_cli) "MISSING_PHP_CLI_EXECUTABLE"
	set opts(phplint_dir) [pwd]
	set opts(editor) ""
	set opts(browser) ""
	set opts(charset) "UTF-8"
	set opts(php_ver) 5
	set opts(show_errors) 1
	set opts(show_warnings) 1
	set opts(show_notices) 1
	set opts(check_non_ascii) 1
	set opts(check_ascii_ctrl) 1
	set opts(is_module) 0
	set opts(recursive) 1
	set opts(print_file_name) 1
	set opts(print_path) absolute
	set opts(print_column_number) 0
	set opts(parse_phpdoc) 1
	set opts(print_context) 1
	set opts(print_source) 0
	set opts(print_line_numbers) 1
	set opts(report_unused) 1
}


proc save-int { fid name value } {
	puts $fid "set $name [format %d $value]"
}


proc save-str { fid name value } {
	puts $fid "set $name [list $value]"
}


proc Save { } {
	global opts
	global rcfile

	set fid [open $rcfile w]
	puts $fid "# This file was generated by phplint.tcl"
	save-int $fid opts(show_cfg) $opts(show_cfg)
	if { [string length $opts(php_cli)] > 0 } {
		save-str $fid opts(php_cli) $opts(php_cli)
	}
	save-str $fid opts(phplint_dir) $opts(phplint_dir)
	save-str $fid opts(editor) $opts(editor)
	save-str $fid opts(browser) $opts(browser)
	save-str $fid opts(charset) $opts(charset)
	save-int $fid opts(php_ver) $opts(php_ver)
	save-int $fid opts(show_errors) $opts(show_errors)
	save-int $fid opts(show_warnings) $opts(show_warnings)
	save-int $fid opts(show_notices) $opts(show_notices)
	save-int $fid opts(check_non_ascii) $opts(check_non_ascii)
	save-int $fid opts(check_ascii_ctrl) $opts(check_ascii_ctrl)
	save-int $fid opts(is_module) $opts(is_module)
	save-int $fid opts(recursive) $opts(recursive)
	save-int $fid opts(print_file_name) $opts(print_file_name)
	save-str $fid opts(print_path) $opts(print_path)
	save-str $fid opts(print_column_number) $opts(print_column_number)
	save-int $fid opts(parse_phpdoc) $opts(parse_phpdoc)
	save-int $fid opts(print_context) $opts(print_context)
	save-int $fid opts(print_source) $opts(print_source)
	save-int $fid opts(print_line_numbers) $opts(print_line_numbers)
	save-int $fid opts(report_unused) $opts(report_unused)
	save-str $fid opts(source_file) $opts(source_file)
	close $fid
}


proc Config-Panel-Toggle { } {
	global opts

	if { $opts(show_cfg) } {
		set opts(show_cfg) 0
		pack forget .cfg_frame.cfg
	} else {
		set opts(show_cfg) 1
		pack .cfg_frame.cfg -side top -fill x
	}
}



# Updates the report if the source file changed.
# @param update_window Window configuration event occurred, typically resize,
# then line markers repainting required.
proc UpdateReport { update_window } {
	global opts update_report_interval modification_time
	set update_markers 0

	# Need to update the report?
	if { [file exists $opts(source_file)] } {
		set curr_modification_time [file mtime $opts(source_file)]
		if { [string compare $curr_modification_time $modification_time] != 0 } {
			set modification_time $curr_modification_time
			runPhplintPreservingPosition 0
		} else {
			if { $update_window } {
				updateMarkers
			}
		}
		
	} else {
		set modification_time ""
		.report.text configure -state normal
		.report.text delete 0.0 end
		#.report.text configure -state disabled
		.report.canvas delete "all"
	}

	after $update_report_interval {UpdateReport 0}
}


###################
# Set default opts:
###################

SetDefault


#########################
# Load saved preferences:
#########################

set home $env(HOME)
set rcfile "$home/.phplint-prefs.txt"
if { [file exists $rcfile] } {
	source $rcfile
}


##########################
# Parse command line args:
##########################

set f [lindex $argv 0]
if { [string length $f] > 0 } {
	set opts(source_file) [abspath $f]
}


########################
# Window layout summary:
########################

wm title . "PHPLint"
wm protocol . WM_DELETE_WINDOW {Save; exit}

# Check-boxes and buttons:
pack [frame .opt ] -side left -anchor nw -padx 5 -pady 5 -fill y

# Configuration panel:
# The frame .cfg_frame contains the frame .cfg that, in turn, contains
# the actual configuration controls. .cfg gets packed/unpacked accoding
# to the $opts(show_cfg). Unfortunately, for some reason unclear to me,
# when .cfg gets unmapped, the .cfg_frame gets not shrinked as expected.
# The trick I found is not to leave that frame empty adding a dummy
# frame .cfg_frame.dummy.
pack [frame .cfg_frame] -side top -fill x -expand 0
frame .cfg_frame.cfg
pack [frame .cfg_frame.dummy]
if { $opts(show_cfg) } {
	set opts(show_cfg) 0
	Config-Panel-Toggle
}

# Source pathfile entry box:
pack [frame .source] -side top -anchor nw

# Report frame:
pack [frame .report] -side left -fill both -expand true


######################
# Configuration panel:
######################

set cfg_panel ".cfg_frame.cfg"
set f $cfg_panel.php_cli
frame $f
label $f.l -text "PHP CLI:" -font $font_norm
entry $f.v -textvariable opts(php_cli) -background white -width 80
button $f.sel -text "..." -command {
	global opts
	set new $opts(php_cli)
	set new [tk_getOpenFile \
		-initialfile $opts(php_cli) \
		-initialdir [file dirname $opts(php_cli)] \
		-parent . \
		-title "Select the PHP CLI program" ]
	if { [string length $new] > 0 } {
		set opts(php_cli) $new
	}
}
pack $f -side top -anchor nw -fill x
pack $f.l -side left
pack $f.sel -side right
pack $f.v -side left

set f $cfg_panel.packages
frame $f
label $f.l -text "PHPLint directory:" -font $font_norm
entry $f.v -textvariable opts(phplint_dir) -background white -width 80
button $f.sel -text "..." -command {
	global opts
	set new $opts(phplint_dir)
	set new [tk_chooseDirectory \
		-initialdir $opts(phplint_dir) \
		-parent . \
		-title "Select the PHPLint directory" ]
	if { [string length $new] > 0 } {
		if { [string length $opts(phplint_dir)] == 0 } {
			set opts(phplint_dir) $new
		} else {
			set opts(phplint_dir) "$new"
		}
	}
}
pack $f -side top -anchor nw -fill x
pack $f.l -side left
pack $f.sel -side right
pack $f.v -side left

set f $cfg_panel.editor
frame $f
label $f.l -text "Source editor program:" -font $font_norm
entry $f.v -textvariable opts(editor) -background white -width 80
button $f.sel -text "..." -command {
	global opts
	set new $opts(editor)
	set new [tk_getOpenFile \
		-initialfile $opts(editor) \
		-initialdir [file dirname $opts(editor)] \
		-parent . \
		-title "Select the source editor program" ]
	if { [string length $new] > 0 } {
		set opts(editor) $new
	}
}
pack $f -side top -anchor nw -fill x
pack $f.l -side left
pack $f.sel -side right
pack $f.v -side left

set f $cfg_panel.browser
frame $f
label $f.l -text "HTML browser program:" -font $font_norm
entry $f.v -textvariable opts(browser) -background white -width 80
button $f.sel -text "..." -command {
	global opts
	set new $opts(browser)
	set new [tk_getOpenFile \
		-initialfile $opts(browser) \
		-initialdir [file dirname $opts(browser)] \
		-parent . \
		-title "Select the HTML browser program" ]
	if { [string length $new] > 0 } {
		set opts(browser) $new
	}
}
pack $f -side top -anchor nw -fill x
pack $f.l -side left
pack $f.sel -side right
pack $f.v -side left


#########################
# Source entry box frame:
#########################

set f ".source"
label $f.l -text "Source:" -font $font_norm
entry $f.v -textvariable opts(source_file) -background white -width 80
button $f.sel -text "..." -command {
	global opts
	set new $opts(source_file)
	set new [tk_getOpenFile \
		-initialfile $opts(source_file) \
		-initialdir [file dirname $new] \
		-parent . \
		-filetypes {
			{{PHP} {.php}}
			{{PHP Include} {.inc}}
			{{PHP CGI} {.cgi}}
			{{All Files} *} \
		} \
		-title "Select PHP source file" ]
	if { [string length $new] > 0 } {
		set opts(source_file) $new
		set modification_time ""
	}
}
StdButt $f.edit "Edit" EditSource
pack $f.edit -side right
pack $f -side top -anchor nw -fill x
pack $f.l -side left
pack $f.sel -side right
pack $f.v -side left


###############
# Report frame:
###############

text .report.text -width 80 -height 10 \
	-borderwidth 2 -relief sunken -background white \
	-yscrollcommand { .report.yscroll set }
scrollbar .report.yscroll -command {.report.text yview}
canvas .report.canvas -width 15
pack .report.yscroll -side right -fill y
pack .report.canvas -side right -fill y
pack .report.text -side left -fill both -expand true


############################
# Options and buttons frame:
############################

set f ".opt"
frame $f.php_ver
pack $f.php_ver -side top -anchor nw

radiobutton $f.php_ver.4 -text "PHP 4" -variable opts(php_ver) -value 4 \
	-font $font_norm -indicatoron 1
pack $f.php_ver.4 -side left

radiobutton $f.php_ver.5 -text "PHP 5" -variable opts(php_ver) -value 5 \
	-font $font_norm -indicatoron 1
pack $f.php_ver.5 -side left

checkbutton $f.show_errors -text "Show errors" -variable opts(show_errors) -font $font_norm
pack $f.show_errors -anchor nw

checkbutton $f.show_warnings -text "Show warnings" -variable opts(show_warnings) -font $font_norm
pack $f.show_warnings -anchor nw

checkbutton $f.show_notices -text "Show notices" -variable opts(show_notices) -font $font_norm
pack $f.show_notices -anchor nw

checkbutton $f.report_unused -text "Report unused items" -variable opts(report_unused) -font $font_norm
pack $f.report_unused -anchor nw

checkbutton $f.check_non_ascii -text "Check non-ASCII chars" -variable opts(check_non_ascii) -font $font_norm
pack $f.check_non_ascii -anchor nw

checkbutton $f.check_ascii_ctrl -text "Check ASCII ctrl chars" -variable opts(check_ascii_ctrl) -font $font_norm
pack $f.check_ascii_ctrl -anchor nw

checkbutton $f.is_module -text "Is a module" -variable opts(is_module) -font $font_norm
pack $f.is_module -anchor nw

checkbutton $f.parse_phpdoc -text "Parse DocBlocks" -variable opts(parse_phpdoc) -font $font_norm
pack $f.parse_phpdoc -anchor nw

checkbutton $f.recursive -text "Recursive parsing" -variable opts(recursive) -font $font_norm
pack $f.recursive -anchor nw

checkbutton $f.print_file_name -text "Print file name along errors" -variable opts(print_file_name) -font $font_norm
pack $f.print_file_name -anchor nw

set p $f.print_path
frame $p
label $p.lbl -text "Print paths:" -font $font_norm
tk_optionMenu $p.menu opts(print_path) absolute relative shortest
pack $p.lbl $p.menu -side left
pack $p -anchor nw

checkbutton $f.print_column_number -text "Print column number" -variable opts(print_column_number) -font $font_norm
pack $f.print_column_number -anchor nw

checkbutton $f.print_context -text "Print context along errors" -variable opts(print_context) -font $font_norm
pack $f.print_context -anchor nw

checkbutton $f.print_source -text "Print source" -variable opts(print_source) -font $font_norm
pack $f.print_source -anchor nw

checkbutton $f.print_line_numbers -text "...with line numbers" -variable opts(print_line_numbers) -font $font_norm
pack $f.print_line_numbers -anchor nw

set p $f.charset
frame $p
label $p.lbl -text "Doc charset:" -font $font_norm
tk_optionMenu $p.menu opts(charset) UTF-8 ISO-8859-1 ISO-8859-15
pack $p.lbl $p.menu -side left
pack $p -anchor nw


################
# Buttons frame:
################

set buttons ".opt.buttons"
frame $buttons
pack $buttons -side bottom

StdButt $buttons.report Report {runPhplintPreservingPosition 0}
#pack $buttons.report -padx $padx -pady $pady -side bottom
grid $buttons.report -in $buttons -row 1 -column 1

StdButt $buttons.browser "Doc" BrowseDoc
#pack $buttons.browser -padx $padx -pady $pady -side bottom
grid $buttons.browser -in $buttons -row 1 -column 2

###StdButt $buttons.default Default SetDefault
###pack $buttons.default -padx $padx -pady $pady -side bottom

StdButt $buttons.show_cfg Configure Config-Panel-Toggle
#pack $buttons.show_cfg -padx $padx -pady $pady -side bottom
grid $buttons.show_cfg -in $buttons -row 2 -column 1

StdButt $buttons.quit Quit { Save; exit }
#pack $buttons.quit -padx $padx -pady $pady -side bottom
grid $buttons.quit -in $buttons -row 2 -column 2

$buttons.report configure -default active


###############
# Key bindings:
###############

bind . <Down>     {.report.text yview scroll 1 units}
bind . <Up>       {.report.text yview scroll -1 units}
bind . <Prior>    {.report.text yview scroll -1 pages}
bind . <Next>     {.report.text yview scroll 1 pages}
bind . <Home>     {.report.text yview scroll -9999 units}
bind . <End>      {.report.text yview scroll 9999 units}
bind . <Return>   {PressButton .opt.buttons.report}
bind . <KP_Enter> {PressButton .opt.buttons.report}
bind . <Escape>   {PressButton .opt.buttons.quit}
#bind .report.text <<Copy>> [list CanvasCopy .report.text]

bind .report.canvas <Configure> {UpdateReport 1}

update

#after $update_report_interval UpdateReport 0
#UpdateReport 0

##########
# THE END!
##########
