<?php
/**
 * Internationalization Functions.
 * See: {@link http://www.php.net/manual/en/book.intl.php}
 * @package intl
 */

# (All dummy values)
define("IDNA_ALLOW_UNASSIGNED", 1);
define("IDNA_CHECK_BIDI", 1);
define("IDNA_CHECK_CONTEXTJ", 1);
define("IDNA_DEFAULT", 1);
define("IDNA_ERROR_BIDI", 1);
define("IDNA_ERROR_CONTEXTJ", 1);
define("IDNA_ERROR_DISALLOWED", 1);
define("IDNA_ERROR_DOMAIN_NAME_TOO_LONG", 1);
define("IDNA_ERROR_EMPTY_LABEL", 1);
define("IDNA_ERROR_HYPHEN_3_4", 1);
define("IDNA_ERROR_INVALID_ACE_LABEL", 1);
define("IDNA_ERROR_LABEL_HAS_DOT", 1);
define("IDNA_ERROR_LABEL_TOO_LONG", 1);
define("IDNA_ERROR_LEADING_COMBINING_MARK", 1);
define("IDNA_ERROR_LEADING_HYPHEN", 1);
define("IDNA_ERROR_PUNYCODE", 1);
define("IDNA_ERROR_TRAILING_HYPHEN", 1);
define("IDNA_NONTRANSITIONAL_TO_ASCII", 1);
define("IDNA_NONTRANSITIONAL_TO_UNICODE", 1);
define("IDNA_USE_STD3_RULES", 1);
define("INTL_IDNA_VARIANT_2003", 1);
define("INTL_IDNA_VARIANT_UTS46", 1);
define("INTL_MAX_LOCALE_LEN", 1);

class Locale {
	# (Dummy values)
	const
		ACTUAL_LOCALE = 1,
		DEFAULT_LOCALE = /*. (string) .*/ NULL,
		EXTLANG_TAG = "?",
		GRANDFATHERED_LANG_TAG = "?",
		LANG_TAG = "?",
		PRIVATE_TAG = "?",
		REGION_TAG = "?",
		SCRIPT_TAG = "?",
		VALID_LOCALE = 1,
		VARIANT_TAG = "?";

	static /*. string .*/ function acceptFromHttp(/*. string .*/ $header){}
	static /*. string .*/ function composeLocale(/*. string[string] .*/ $subtags){}
	static /*. bool .*/ function filterMatches(/*. string .*/ $langtag, /*. string .*/ $locale, /*. bool .*/ $canonicalize = false){}
	static /*. string[int] .*/ function getAllVariants(/*. string .*/ $locale){}
	static /*. string .*/ function getDefault (){}
	static /*. string .*/ function getDisplayLanguage(/*. string .*/ $locale, /*. string .*/ $in_locale = NULL){}
	static /*. string .*/ function getDisplayName(/*. string .*/ $locale, /*. string .*/ $in_locale = NULL){}
	static /*. string .*/ function getDisplayRegion(/*. string .*/ $locale, /*. string .*/ $in_locale = NULL){}
	static /*. string .*/ function getDisplayScript(/*. string .*/ $locale, /*. string .*/ $in_locale = NULL){}
	static /*. string .*/ function getDisplayVariant(/*. string .*/ $locale, /*. string .*/ $in_locale = NULL){}
	static /*. string[string] .*/ function getKeywords(/*. string .*/ $locale){}
	static /*. string .*/ function getPrimaryLanguage(/*. string .*/ $locale){}
	static /*. string .*/ function getRegion(/*. string .*/ $locale){}
	static /*. string .*/ function getScript(/*. string .*/ $locale){}
	static /*. string .*/ function lookup(/*. string[int] .*/ $langtag, /*. string .*/ $locale, /*. bool .*/ $canonicalize = false, /*. string .*/ $default_ = NULL){}
	static /*. string[string] .*/ function parseLocale(/*. string .*/ $locale){}
	static /*. bool .*/ function setDefault(/*. string .*/ $locale){}
}


/*. string .*/ function accept_from_http(/*. string .*/ $header){}
/*. string .*/ function locale_compose(/*. string[string] .*/ $subtags){}
/*. bool .*/ function locale_filter_matches(/*. string .*/ $langtag, /*. string .*/ $locale, /*. bool .*/ $canonicalize = false){}
/*. string[int] .*/ function locale_get_all_variants(/*. string .*/ $locale){}
/*. string .*/ function locale_get_default (){}
/*. string .*/ function locale_get_display_language(/*. string .*/ $locale, /*. string .*/ $in_locale = NULL){}
/*. string .*/ function locale_get_display_name(/*. string .*/ $locale, /*. string .*/ $in_locale = NULL){}
/*. string .*/ function locale_get_display_region(/*. string .*/ $locale, /*. string .*/ $in_locale = NULL){}
/*. string .*/ function locale_get_display_script(/*. string .*/ $locale, /*. string .*/ $in_locale = NULL){}
/*. string .*/ function locale_get_display_variant(/*. string .*/ $locale, /*. string .*/ $in_locale = NULL){}
/*. string[string] .*/ function locale_get_keywords(/*. string .*/ $locale){}
/*. string .*/ function locale_get_primary_language(/*. string .*/ $locale){}
/*. string .*/ function locale_get_region(/*. string .*/ $locale){}
/*. string .*/ function locale_get_script(/*. string .*/ $locale){}
/*. string .*/ function locale_lookup(/*. string[int] .*/ $langtag, /*. string .*/ $locale, /*. bool .*/ $canonicalize = false, /*. string .*/ $default_ = NULL){}
/*. string[string] .*/ function locale_parse(/*. string .*/ $locale){}
/*. bool .*/ function locale_set_default(/*. string .*/ $locale){}


class Collator {

	# (Dummy values)
	const
		ALTERNATE_HANDLING = 1,
		CASE_FIRST = 1,
		CASE_LEVEL = 1,
		DEFAULT_VALUE = 1,
		FRENCH_COLLATION = 1,
		HIRAGANA_QUATERNARY_MODE = 1,
		IDENTICAL = 1,
		LOWER_FIRST = 1,
		NON_IGNORABLE = 1,
		NORMALIZATION_MODE = 1,
		NUMERIC_COLLATION = 1,
		OFF = 1,
		ON = 1,
		PRIMARY = 1,
		QUATERNARY = 1,
		SECONDARY = 1,
		SHIFTED = 1,
		SORT_NUMERIC = 1,
		SORT_REGULAR = 1,
		SORT_STRING = 1,
		STRENGTH = 1,
		TERTIARY = 1,
		UPPER_FIRST = 1;
	
	/*. void .*/ function __construct(/*. string .*/ $locale ){}
	/*. bool .*/ function asort(/*. array .*/ &$arr, $sort_flag = Collator::SORT_REGULAR){}
	/*. int .*/ function compare(/*. string .*/ $str1, /*. string .*/ $str2 ){}
	static /*. Collator .*/ function create(/*. string .*/ $locale ){}
	/*. int .*/ function getAttribute(/*. int .*/ $attr ){}
	/*. int .*/ function getErrorCode(){}
	/*. string .*/ function getErrorMessage(){}
	/*. string .*/ function getLocale(/*. int .*/ $type = Locale::ACTUAL_LOCALE){}
	/*. string .*/ function getSortKey( /*. string .*/ $str ){}
	/*. int .*/ function getStrength(){}
	/*. bool .*/ function setAttribute( /*. int .*/ $attr, /*. int .*/ $val ){}
	/*. bool .*/ function setStrength( /*. int .*/ $strength ){}
	/*. bool .*/ function sortWithSortKeys( /*. array .*/ &$arr ){}
	/*. bool .*/ function sort( /*. array .*/ &$arr, $sort_flag = Collator::SORT_REGULAR){}
}

/*. Collator .*/ function create(/*. string .*/ $locale ){}
/*. bool .*/ function collate_asort(/*. Collator .*/ $coll, /*. array .*/ &$arr, $sort_flag = Collator::SORT_REGULAR){}
/*. int .*/ function collate_compare(/*. Collator .*/ $coll, /*. string .*/ $str1, /*. string .*/ $str2 ){}
/*. int .*/ function collate_get_attribute(/*. Collator .*/ $coll, /*. int .*/ $attr ){}
/*. int .*/ function collate_get_error_code(/*. Collator .*/ $coll){}
/*. string .*/ function collate_get_error_message(/*. Collator .*/ $coll){}
/*. string .*/ function collate_get_locale(/*. Collator .*/ $coll, /*. int .*/ $type = Locale::ACTUAL_LOCALE){}
/*. string .*/ function collate_get_sort_key(/*. Collator .*/ $coll,  /*. string .*/ $str ){}
/*. int .*/ function collate_get_strength(/*. Collator .*/ $coll){}
/*. bool .*/ function collate_set_attribute(/*. Collator .*/ $coll,  /*. int .*/ $attr, /*. int .*/ $val ){}
/*. bool .*/ function collate_set_strength(/*. Collator .*/ $coll,  /*. int .*/ $strength ){}
/*. bool .*/ function collate_sort_with_sort_keys(/*. Collator .*/ $coll,  /*. array .*/ &$arr ){}


class NumberFormatter {

	const
		CURRENCY = 1,
		CURRENCY_CODE = 1,
		CURRENCY_SYMBOL = 1,
		DECIMAL = 1,
		DECIMAL_ALWAYS_SHOWN = 1,
		DECIMAL_SEPARATOR_SYMBOL = 1,
		DEFAULT_RULESET = 1,
		DEFAULT_STYLE = 1,
		DIGIT_SYMBOL = 1,
		DURATION = 1,
		EXPONENTIAL_SYMBOL = 1,
		FORMAT_WIDTH = 1,
		FRACTION_DIGITS = 1,
		GROUPING_SEPARATOR_SYMBOL = 1,
		GROUPING_SIZE = 1,
		GROUPING_USED = 1,
		IGNORE = 1,
		INFINITY_SYMBOL = 1,
		INTEGER_DIGITS = 1,
		INTL_CURRENCY_SYMBOL = 1,
		LENIENT_PARSE = 1,
		MAX_FRACTION_DIGITS = 1,
		MAX_INTEGER_DIGITS = 1,
		MAX_SIGNIFICANT_DIGITS = 1,
		MINUS_SIGN_SYMBOL = 1,
		MIN_FRACTION_DIGITS = 1,
		MIN_INTEGER_DIGITS = 1,
		MIN_SIGNIFICANT_DIGITS = 1,
		MONETARY_GROUPING_SEPARATOR_SYMBOL = 1,
		MONETARY_SEPARATOR_SYMBOL = 1,
		MULTIPLIER = 1,
		NAN_SYMBOL = 1,
		NEGATIVE_PREFIX = 1,
		NEGATIVE_SUFFIX = 1,
		ORDINAL = 1,
		PADDING_CHARACTER = 1,
		PADDING_POSITION = 1,
		PAD_AFTER_PREFIX = 1,
		PAD_AFTER_SUFFIX = 1,
		PAD_BEFORE_PREFIX = 1,
		PAD_BEFORE_SUFFIX = 1,
		PAD_ESCAPE_SYMBOL = 1,
		PARSE_INT_ONLY = 1,
		PATTERN_DECIMAL = 1,
		PATTERN_RULEBASED = 1,
		PATTERN_SEPARATOR_SYMBOL = 1,
		PERCENT = 1,
		PERCENT_SYMBOL = 1,
		PERMILL_SYMBOL = 1,
		PLUS_SIGN_SYMBOL = 1,
		POSITIVE_PREFIX = 1,
		POSITIVE_SUFFIX = 1,
		PUBLIC_RULESETS = 1,
		ROUNDING_INCREMENT = 1,
		ROUNDING_MODE = 1,
		ROUND_CEILING = 1,
		ROUND_DOWN = 1,
		ROUND_FLOOR = 1,
		ROUND_HALFDOWN = 1,
		ROUND_HALFEVEN = 1,
		ROUND_HALFUP = 1,
		ROUND_UP = 1,
		SCIENTIFIC = 1,
		SECONDARY_GROUPING_SIZE = 1,
		SIGNIFICANT_DIGITS_USED = 1,
		SIGNIFICANT_DIGIT_SYMBOL = 1,
		SPELLOUT = 1,
		TYPE_CURRENCY = 1,
		TYPE_DEFAULT = 1,
		TYPE_DOUBLE = 1,
		TYPE_INT32 = 1,
		TYPE_INT64 = 1,
		ZERO_DIGIT_SYMBOL = 1;

	/*. void .*/ function __construct(/*. string .*/ $locale, /*. int .*/ $style, /*. string .*/ $pattern = NULL){}
	static /*. NumberFormatter .*/ function create(/*. string .*/ $locale, /*. int .*/ $style, /*. string .*/ $pattern = NULL){}
	/*. string .*/ function formatCurrency(/*. float .*/ $value, /*. string .*/ $currency){}
	/*. string .*/ function format(/*. float .*/ $value, /*. int .*/ $type = self::TYPE_DEFAULT){}
	/*. int .*/ function getAttribute(/*. int .*/ $attr){}
	/*. int .*/ function getErrorCode(){}
	/*. string .*/ function getErrorMessage(){}
	/*. string .*/ function getLocale(/*. int .*/ $type = Locale::ACTUAL_LOCALE){}
	/*. string .*/ function getPattern(){}
	/*. string .*/ function getSymbol(/*. int .*/ $attr){}
	/*. string .*/ function getTextAttribute(/*. int .*/ $attr){}
	/*. float .*/ function parseCurrency(/*. string .*/ $value, /*. string .*/ &$currency, /*. int .*/ &$position = 0){}
	/*. mixed .*/ function parse(/*. string .*/ $value, /*. int .*/ $type = self::TYPE_DOUBLE, /*. int .*/ &$position = 0){}
	/*. bool .*/ function setAttribute(/*. int .*/ $attr, /*. int .*/ $value){}
	/*. bool .*/ function setPattern(/*. string .*/ $pattern){}
	/*. bool .*/ function setSymbol(/*. int .*/ $attr, /*. string .*/ $value){}
	/*. bool .*/ function setTextAttribute(/*. int .*/ $attr, /*. string .*/ $value){}
}


/*. NumberFormatter .*/ function numfmt_create(/*. string .*/ $locale, /*. int .*/ $style, /*. string .*/ $pattern = NULL){}
/*. string .*/ function numfmt_format_currency(/*. NumberFormatter .*/ $fmt, /*. float .*/ $value, /*. string .*/ $currency){}
/*. string .*/ function numfmt_format(/*. NumberFormatter .*/ $fmt, /*. float .*/ $value, /*. int .*/ $type = NumberFormatter::TYPE_DEFAULT){}
/*. int .*/ function numfmt_get_attribute(/*. NumberFormatter .*/ $fmt, /*. int .*/ $attr){}
/*. int .*/ function numfmt_get_error_code(/*. NumberFormatter .*/ $fmt){}
/*. string .*/ function numfmt_get_error_message(/*. NumberFormatter .*/ $fmt){}
/*. string .*/ function numfmt_get_locale(/*. NumberFormatter .*/ $fmt, /*. int .*/ $type = Locale::ACTUAL_LOCALE){}
/*. string .*/ function numfmt_get_pattern(/*. NumberFormatter .*/ $fmt){}
/*. string .*/ function numfmt_get_symbol(/*. NumberFormatter .*/ $fmt, /*. int .*/ $attr){}
/*. string .*/ function numfmt_get_text_attribute(/*. NumberFormatter .*/ $fmt, /*. int .*/ $attr){}
/*. float .*/ function numfmt_parse_currency(/*. NumberFormatter .*/ $fmt, /*. string .*/ $value, /*. string .*/ &$currency, /*. int .*/ &$position = 0){}
/*. mixed .*/ function numfmt_parse(/*. NumberFormatter .*/ $fmt, /*. string .*/ $value, /*. int .*/ $type = NumberFormatter::TYPE_DOUBLE, /*. int .*/ &$position = 0){}
/*. bool .*/ function numfmt_set_attribute(/*. NumberFormatter .*/ $fmt, /*. int .*/ $attr, /*. int .*/ $value){}
/*. bool .*/ function numfmt_set_pattern(/*. NumberFormatter .*/ $fmt, /*. string .*/ $pattern){}
/*. bool .*/ function numfmt_set_symbol(/*. NumberFormatter .*/ $fmt, /*. int .*/ $attr, /*. string .*/ $value){}
/*. bool .*/ function numfmt_set_text_attribute(/*. NumberFormatter .*/ $fmt, /*. int .*/ $attr, /*. string .*/ $value){}



class Normalizer {

	const
		FORM_C = "",
		FORM_D = "",
		FORM_KC = "",
		FORM_KD = "",
		NONE = "",
		OPTION_DEFAULT = "";

	static /*. bool .*/ function isNormalized(/*. string .*/ $input, $form = Normalizer::FORM_C){}
	static /*. string .*/ function normalize(/*. string .*/ $input, $form = Normalizer::FORM_C){}
}


/*. bool .*/ function normalizer_is_normalized(/*. string .*/ $input, $form = Normalizer::FORM_C){}
/*. string .*/ function normalizer_normalize(/*. string .*/ $input, $form = Normalizer::FORM_C){}


class MessageFormatter {
	/*. void .*/ function __construct(/*. string .*/ $locale, /*. string .*/ $pattern){}
	static /*. MessageFormatter .*/ function create(/*. string .*/ $locale, /*. string .*/ $pattern){}
	static /*. string .*/ function formatMessage(/*. string .*/ $locale, /*. string .*/ $pattern, /*. array .*/ $args_){}
	/*. string .*/ function format(/*. array .*/ $args_){}
	/*. int .*/ function getErrorCode(){}
	/*. string .*/ function getErrorMessage(){}
	/*. string .*/ function getLocale(){}
	/*. string .*/ function getPattern(){}
	static /*. mixed[int] .*/ function parseMessage(/*. string .*/ $locale, /*. string .*/ $pattern, /*. string .*/ $source){}
	/*. mixed[int] .*/ function parse(/*. string .*/ $value){}
	/*. bool .*/ function setPattern(/*. string .*/ $pattern){}
}


/*. MessageFormatter .*/ function msgfmt_create(/*. string .*/ $locale, /*. string .*/ $pattern){}
/*. string .*/ function msgfmt_format_message(/*. string .*/ $locale, /*. string .*/ $pattern, /*. array .*/ $args_){}
/*. string .*/ function msgfmt_format(/*. MessageFormatter .*/ $fmt, /*. array .*/ $args_){}
/*. int .*/ function msgfmt_get_error_code(/*. MessageFormatter .*/ $fmt){}
/*. string .*/ function msgfmt_get_error_message(/*. MessageFormatter .*/ $fmt){}
/*. string .*/ function msgfmt_get_locale(/*. MessageFormatter .*/ $fmt){}
/*. string .*/ function msgfmt_get_pattern(/*. MessageFormatter .*/ $fmt){}
/*. mixed[int] .*/ function msgfmt_parse_message(/*. string .*/ $locale, /*. string .*/ $pattern, /*. string .*/ $source){}
/*. mixed[int] .*/ function msgfmt_parse(/*. MessageFormatter .*/ $fmt, /*. string .*/ $value){}
/*. bool .*/ function msgfmt_set_pattern(/*. MessageFormatter .*/ $fmt, /*. string .*/ $pattern){}


class IntlDateFormatter {

	const
		NONE = 1,
		FULL = 1,
		LONG = 1,
		MEDIUM = 1,
		SHORT = 1,
		TRADITIONAL = 1,
		GREGORIAN = 1;

	/*. void .*/ function __construct(/*. string .*/ $locale, /*. int .*/ $datetype, /*. int .*/ $timetype, $timezone = "", $calendar = IntlDateFormatter::GREGORIAN, /*. string .*/ $pattern = ""){}
	static /*. IntlDateFormatter .*/ function create(/*. string .*/ $locale, /*. int .*/ $datetype, /*. int .*/ $timetype, $timezone = "", $calendar = IntlDateFormatter::GREGORIAN, /*. string .*/ $pattern = ""){}
	/*. string .*/ function format(/*. mixed .*/ $value){}
	/*. int .*/ function getCalendar(){}
	/*. int .*/ function getDateType(){}
	/*. int .*/ function getErrorCode(){}
	/*. string .*/ function getErrorMessage(){}
	/*. string .*/ function getLocale($which = Locale::ACTUAL_LOCALE){}
	/*. string .*/ function getPattern(){}
	/*. int .*/ function getTimeType(){}
	/*. string .*/ function getTimeZoneId(){}
	/*. bool .*/ function isLenient(){}
	/*. int[string] .*/ function localtime(/*. string .*/ $value, & $position = 0){}
	/*. int .*/ function parse(/*. string .*/ $value, & $position = 0){}
	/*. bool .*/ function setCalendar(/*. int .*/ $which){}
	/*. bool .*/ function setLenient(/*. bool .*/ $lenient){}
	/*. bool .*/ function setPattern(/*. string .*/ $pattern){}
	/*. bool .*/ function setTimeZoneId(/*. string .*/ $zone){}
}


class ResourceBundle {
	/* Methods */
	/*. void .*/ function __construct(/*. string .*/ $locale, /*. string .*/ $bundlename, $fallback = FALSE){}
	/*. int .*/ function count(){}
	static /*. ResourceBundle .*/ function create(/*. string .*/ $locale, /*. string .*/ $bundlename, $fallback = FALSE){}
	/*. int .*/ function getErrorCode(){}
	/*. string .*/ function getErrorMessage(){}
	/*. mixed .*/ function get(/*. mixed .*/ $index){}
	/*. string[int] .*/ function getLocales(/*. string .*/ $bundlename){}
}



class Spoofchecker {

	const
		SINGLE_SCRIPT_CONFUSABLE = 1,
		MIXED_SCRIPT_CONFUSABLE = 2,
		WHOLE_SCRIPT_CONFUSABLE = 4,
		ANY_CASE = 8,
		SINGLE_SCRIPT = 16,
		INVISIBLE = 32,
		CHAR_LIMIT = 64;

	/*. bool .*/ function areConfusable(/*. string .*/ $s1, /*. string .*/ $s2, /*. return .*/ & $error = ""){}
	/*. void .*/ function __construct(){}
	/*. bool .*/ function isSuspicious(/*. string .*/ $text, /*. return .*/ & $error = ""){}
	/*. void .*/ function setAllowedLocales(/*. string .*/ $locale_list){}
	/*. void .*/ function setChecks(/*. string .*/ $checks){}
}


class Transliterator {

	const
		FORWARD_FIXME = 0,
		REVERSE = 1 ;

	public /*. string .*/ $id;

	/*. void .*/ function __construct(){}
	static /*. Transliterator .*/ function create(/*. string .*/ $id, $direction = Transliterator::FORWARD_FIXME){}
	static /*. Transliterator .*/ function createFromRules(/*. string .*/ $rules, $direction = Transliterator::FORWARD_FIXME){}
	/*. Transliterator .*/ function createInverse(){}
	/*. int .*/ function getErrorCode(){}
	/*. string .*/ function getErrorMessage(){}
	static /*. string[int] .*/ function listIDs(){}
	/*. string .*/ function transliterate(/*. string .*/ $subject, $start = 0, $end = 999999999){}
}


# Dummy values:
define("GRAPHEME_EXTR_COUNT", 1);
define("GRAPHEME_EXTR_MAXBYTES", 1);
define("GRAPHEME_EXTR_MAXCHARS", 1);


/*. string .*/ function grapheme_extract(/*. string .*/ $haystack, /*. int .*/ $size, $extract_type = GRAPHEME_EXTR_COUNT, $start = 0, &$next = 0){}
/*. string .*/ function grapheme_stripos(/*. string .*/ $haystack, /*. string .*/ $needle, $offset = 0){}
/*. string .*/ function grapheme_stristr(/*. string .*/ $haystack, /*. string .*/ $needle, $before_needle = false){}
/*. string .*/ function grapheme_strlen(/*. string .*/ $input){}
/*. int .*/ function grapheme_strpos(/*. string .*/ $haystack, /*. string .*/ $needle, $offset = 0){}
/*. int .*/ function grapheme_strripos(/*. string .*/ $haystack, /*. string .*/ $needle, $offset = 0){}
/*. int .*/ function grapheme_strrpos(/*. string .*/ $haystack, /*. string .*/ $needle, $offset = 0){}
/*. string .*/ function grapheme_strstr(/*. string .*/ $haystack, /*. string .*/ $needle, $before_needle = false){}
/*. string .*/ function grapheme_substr(/*. string .*/ $string_, /*. int .*/ $start, $lengthi = 0){}


/*. string .*/ function idn_to_ascii ( /*. string .*/ $domain, $options = 0, $variant = INTL_IDNA_VARIANT_2003, /*. array .*/ &$idna_info = NULL){}
/*. string .*/ function idn_to_unicode( /*. string .*/ $domain, $options = 0, $variant = INTL_IDNA_VARIANT_2003, /*. array .*/ &$idna_info = NULL){}
/*. string .*/ function idn_to_utf8 ( /*. string .*/ $domain, $options = 0, $variant = INTL_IDNA_VARIANT_2003, /*. array .*/ &$idna_info = NULL){}


/*. string .*/ function intl_error_name(/*. int .*/ $error_code){}
/*. int .*/ function intl_get_error_code(){}
/*. string .*/ function intl_get_error_message(){}
/*. bool .*/ function intl_is_failure( /*. int .*/ $error_code ){}

