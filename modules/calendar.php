<?php
/**
Calendar Functions.

See: {@link http://www.php.net/manual/en/ref.calendar.php}
@package calendar
*/


# FIXME: all these '1' are dummy values
define("CAL_GREGORIAN", 1);
define("CAL_JULIAN", 1);
define("CAL_JEWISH", 1);
define("CAL_FRENCH", 1);
define("CAL_NUM_CALS", 1);
define("CAL_DOW_DAYNO", 1);
define("CAL_DOW_SHORT", 1);
define("CAL_DOW_LONG", 1);
define("CAL_MONTH_GREGORIAN_SHORT", 1);
define("CAL_MONTH_GREGORIAN_LONG", 1);
define("CAL_MONTH_JULIAN_SHORT", 1);
define("CAL_MONTH_JULIAN_LONG", 1);
define("CAL_MONTH_JEWISH", 1);
define("CAL_MONTH_FRENCH", 1);
define("CAL_EASTER_DEFAULT", 1);
define("CAL_EASTER_ROMAN", 1);
define("CAL_EASTER_ALWAYS_GREGORIAN", 1);
define("CAL_EASTER_ALWAYS_JULIAN", 1);
define("CAL_JEWISH_ADD_ALAFIM_GERESH", 1);
define("CAL_JEWISH_ADD_ALAFIM", 1);
define("CAL_JEWISH_ADD_GERESHAYIM", 1);

/*. array .*/ function cal_info(/*. int .*/ $calendar){}
/*. int   .*/ function cal_days_in_month(/*. int .*/ $calendar, /*. int .*/ $month, /*. int .*/ $year){}
/*. int   .*/ function cal_to_jd(/*. int .*/ $calendar, /*. int .*/ $month, /*. int .*/ $day, /*. int .*/ $year){}
/*. array .*/ function cal_from_jd(/*. int .*/ $jd, /*. int .*/ $calendar){}
/*. int   .*/ function easter_date( /*. args .*/){}
/*. int   .*/ function easter_days( /*. args .*/){}
/*. string.*/ function jdtogregorian(/*. int .*/ $juliandaycount){}
/*. int   .*/ function gregoriantojd(/*. int .*/ $month, /*. int .*/ $day, /*. int .*/ $year){}
/*. string.*/ function jdtojulian(/*. int .*/ $juliandaycount){}
/*. int   .*/ function juliantojd(/*. int .*/ $month, /*. int .*/ $day, /*. int .*/ $year){}
/*. string.*/ function jdtojewish(/*. int .*/ $juliandaycount /*., args .*/){}
/*. int   .*/ function jewishtojd(/*. int .*/ $month, /*. int .*/ $day, /*. int .*/ $year){}
/*. string.*/ function jdtofrench(/*. int .*/ $juliandaycount){}
/*. int   .*/ function frenchtojd(/*. int .*/ $month, /*. int .*/ $day, /*. int .*/ $year){}
/*. mixed .*/ function jddayofweek(/*. int .*/ $juliandaycount /*., args .*/){}
/*. string.*/ function jdmonthname(/*. int .*/ $juliandaycount, /*. int .*/ $mode){}
/*. int   .*/ function unixtojd( /*. args .*/){}
/*. int   .*/ function jdtounix(/*. int .*/ $jday){}
