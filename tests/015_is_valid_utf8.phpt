--TEST--
is_valid_utf8() test
--SKIPIF--
<?php if (!extension_loaded("qolfuncs"))  echo "skip"; ?>
--FILE--
<?php
	// Finds whether the given value is a valid UTF-8 string.
	// Prototype:  bool is_valid_utf8(mixed $value, [ bool $standard = false, int $combinelimit = 16 ])

	// Basic check.
	echo "Basic test:\n";
	var_dump(is_valid_utf8("ABCDE\t\r\n\xF0\x9F\x98\x8A\n"));
	$str = "ABCDE\t\r\n";
	$str .= "\xF0\x9F\x98\x8A\n";
	var_dump(is_valid_utf8($str));
	var_dump(is_valid_utf8($str));
	echo "\n";

	//    Code Points       First Byte Second Byte Third Byte Fourth Byte
	//  U+0000 -   U+007F   00 - 7F
	//  U+0080 -   U+07FF   C2 - DF    80 - BF
	//  U+0800 -   U+0FFF   E0         A0 - BF     80 - BF
	//  U+1000 -   U+CFFF   E1 - EC    80 - BF     80 - BF
	//  U+D000 -   U+D7FF   ED         80 - 9F     80 - BF
	//  U+E000 -   U+FFFF   EE - EF    80 - BF     80 - BF
	// U+10000 -  U+3FFFF   F0         90 - BF     80 - BF    80 - BF
	// U+40000 -  U+FFFFF   F1 - F3    80 - BF     80 - BF    80 - BF
	//U+100000 - U+10FFFF   F4         80 - 8F     80 - BF    80 - BF

	// Verify restricted ranges:  0x00-0x1F and 0x7F are invalid with the exceptions of \t, \r, and \n by default.
	echo "ASCII range test:\n";
	var_dump(is_valid_utf8("\x00"));
	var_dump(is_valid_utf8("\t"));
	var_dump(is_valid_utf8("\r"));
	var_dump(is_valid_utf8("\n"));
	var_dump(is_valid_utf8("\x1F"));
	var_dump(is_valid_utf8(" \x20"));
	var_dump(is_valid_utf8("\x7E"));
	var_dump(is_valid_utf8("\x7F"));
	echo "\n";
	var_dump(is_valid_utf8("\x00", true));
	var_dump(is_valid_utf8("\t", true));
	var_dump(is_valid_utf8("\r", true));
	var_dump(is_valid_utf8("\n", true));
	var_dump(is_valid_utf8("\x1F", true));
	var_dump(is_valid_utf8(" \x20", true));
	var_dump(is_valid_utf8("\x7E", true));
	var_dump(is_valid_utf8("\x7F", true));
	echo "\n";

	// Verify invalid first byte.
	echo "Invalid first byte test:\n";
	var_dump(is_valid_utf8("\x80\x80"));
	var_dump(is_valid_utf8("\xC1\x80"));
	var_dump(is_valid_utf8("\xF5\x80\x80\x80\x80"));
	echo "\n";

	// Verify two byte ranges.
	//    Code Points       First Byte Second Byte Third Byte Fourth Byte
	//  U+0080 -   U+07FF   C2 - DF    80 - BF
	echo "Two byte range test:\n";
	var_dump(is_valid_utf8("\xC2\x7F"));
	var_dump(is_valid_utf8("\xC2\x80"));
	var_dump(is_valid_utf8("\xC2\xBF"));
	var_dump(is_valid_utf8("\xC2\xC0"));
	echo "\n";
	var_dump(is_valid_utf8("\xDF\x7F"));
	var_dump(is_valid_utf8("\xDF\x80"));
	var_dump(is_valid_utf8("\xDF\xBF"));
	var_dump(is_valid_utf8("\xDF\xC0"));
	echo "\n";
	var_dump(is_valid_utf8("\xC2"));  // Not long enough.
	echo "\n";

	// Verify three byte ranges.
	//    Code Points       First Byte Second Byte Third Byte Fourth Byte
	//  U+0800 -   U+0FFF   E0         A0 - BF     80 - BF
	echo "Three byte E0 range test:\n";
	var_dump(is_valid_utf8("\xE0\x7F\x80"));
	var_dump(is_valid_utf8("\xE0\x80\x80"));
	var_dump(is_valid_utf8("\xE0\x9F\x80"));
	var_dump(is_valid_utf8("\xE0\xA0\x80"));
	var_dump(is_valid_utf8("\xE0\xBF\x80"));
	var_dump(is_valid_utf8("\xE0\xC0\x80"));
	echo "\n";
	var_dump(is_valid_utf8("\xE0\x7F\xBF"));
	var_dump(is_valid_utf8("\xE0\x80\xBF"));
	var_dump(is_valid_utf8("\xE0\x9F\xBF"));
	var_dump(is_valid_utf8("\xE0\xA0\xBF"));
	var_dump(is_valid_utf8("\xE0\xBF\xBF"));
	var_dump(is_valid_utf8("\xE0\xC0\xBF"));
	echo "\n";
	var_dump(is_valid_utf8("\xE0\xA0\x7F"));
	var_dump(is_valid_utf8("\xE0\xA0\x80"));
	var_dump(is_valid_utf8("\xE0\xA0\xBF"));
	var_dump(is_valid_utf8("\xE0\xA0\xC0"));
	echo "\n";
	var_dump(is_valid_utf8("\xE0\xBF\x7F"));
	var_dump(is_valid_utf8("\xE0\xBF\x80"));
	var_dump(is_valid_utf8("\xE0\xBF\xBF"));
	var_dump(is_valid_utf8("\xE0\xBF\xC0"));
	echo "\n";
	var_dump(is_valid_utf8("\xE0"));  // Not long enough.
	var_dump(is_valid_utf8("\xE0\xA0"));  // Not long enough.
	echo "\n";

	//    Code Points       First Byte Second Byte Third Byte Fourth Byte
	//  U+1000 -   U+CFFF   E1 - EC    80 - BF     80 - BF
	echo "Three byte E1-EC range test:\n";
	var_dump(is_valid_utf8("\xE1\x7F\x80"));
	var_dump(is_valid_utf8("\xE1\x80\x80"));
	var_dump(is_valid_utf8("\xE1\xBF\x80"));
	var_dump(is_valid_utf8("\xE1\xC0\x80"));
	echo "\n";
	var_dump(is_valid_utf8("\xE1\x7F\xBF"));
	var_dump(is_valid_utf8("\xE1\x80\xBF"));
	var_dump(is_valid_utf8("\xE1\xBF\xBF"));
	var_dump(is_valid_utf8("\xE1\xC0\xBF"));
	echo "\n";
	var_dump(is_valid_utf8("\xE1\x80\x7F"));
	var_dump(is_valid_utf8("\xE1\x80\x80"));
	var_dump(is_valid_utf8("\xE1\x80\xBF"));
	var_dump(is_valid_utf8("\xE1\x80\xC0"));
	echo "\n";
	var_dump(is_valid_utf8("\xE1\xBF\x7F"));
	var_dump(is_valid_utf8("\xE1\xBF\x80"));
	var_dump(is_valid_utf8("\xE1\xBF\xBF"));
	var_dump(is_valid_utf8("\xE1\xBF\xC0"));
	echo "\n";
	var_dump(is_valid_utf8("\xEC\x7F\x80"));
	var_dump(is_valid_utf8("\xEC\x80\x80"));
	var_dump(is_valid_utf8("\xEC\xBF\x80"));
	var_dump(is_valid_utf8("\xEC\xC0\x80"));
	echo "\n";
	var_dump(is_valid_utf8("\xEC\x7F\xBF"));
	var_dump(is_valid_utf8("\xEC\x80\xBF"));
	var_dump(is_valid_utf8("\xEC\xBF\xBF"));
	var_dump(is_valid_utf8("\xEC\xC0\xBF"));
	echo "\n";
	var_dump(is_valid_utf8("\xEC\x80\x7F"));
	var_dump(is_valid_utf8("\xEC\x80\x80"));
	var_dump(is_valid_utf8("\xEC\x80\xBF"));
	var_dump(is_valid_utf8("\xEC\x80\xC0"));
	echo "\n";
	var_dump(is_valid_utf8("\xEC\xBF\x7F"));
	var_dump(is_valid_utf8("\xEC\xBF\x80"));
	var_dump(is_valid_utf8("\xEC\xBF\xBF"));
	var_dump(is_valid_utf8("\xEC\xBF\xC0"));
	echo "\n";

	//    Code Points       First Byte Second Byte Third Byte Fourth Byte
	//  U+D000 -   U+D7FF   ED         80 - 9F     80 - BF
	echo "Three byte ED range test:\n";
	var_dump(is_valid_utf8("\xED\x7F\x80"));
	var_dump(is_valid_utf8("\xED\x80\x80"));
	var_dump(is_valid_utf8("\xED\x9F\x80"));
	var_dump(is_valid_utf8("\xED\xA0\x80"));
	var_dump(is_valid_utf8("\xED\xBF\x80"));
	var_dump(is_valid_utf8("\xED\xC0\x80"));
	echo "\n";
	var_dump(is_valid_utf8("\xED\x7F\xBF"));
	var_dump(is_valid_utf8("\xED\x80\xBF"));
	var_dump(is_valid_utf8("\xED\x9F\xBF"));
	var_dump(is_valid_utf8("\xED\xA0\xBF"));
	var_dump(is_valid_utf8("\xED\xBF\xBF"));
	var_dump(is_valid_utf8("\xED\xC0\xBF"));
	echo "\n";
	var_dump(is_valid_utf8("\xED\x80\x7F"));
	var_dump(is_valid_utf8("\xED\x80\x80"));
	var_dump(is_valid_utf8("\xED\x80\xBF"));
	var_dump(is_valid_utf8("\xED\x80\xC0"));
	echo "\n";
	var_dump(is_valid_utf8("\xED\x9F\x7F"));
	var_dump(is_valid_utf8("\xED\x9F\x80"));
	var_dump(is_valid_utf8("\xED\x9F\xBF"));
	var_dump(is_valid_utf8("\xED\x9F\xC0"));
	echo "\n";

	//    Code Points       First Byte Second Byte Third Byte Fourth Byte
	//  U+E000 -   U+FFFF   EE - EF    80 - BF     80 - BF
	echo "Three byte EE-EF range test:\n";
	var_dump(is_valid_utf8("\xEE\x7F\x80"));
	var_dump(is_valid_utf8("\xEE\x80\x80"));
	var_dump(is_valid_utf8("\xEE\xBF\x80"));
	var_dump(is_valid_utf8("\xEE\xC0\x80"));
	echo "\n";
	var_dump(is_valid_utf8("\xEE\x7F\xBF"));
	var_dump(is_valid_utf8("\xEE\x80\xBF"));
	var_dump(is_valid_utf8("\xEE\xBF\xBF"));
	var_dump(is_valid_utf8("\xEE\xC0\xBF"));
	echo "\n";
	var_dump(is_valid_utf8("\xEE\x80\x7F"));
	var_dump(is_valid_utf8("\xEE\x80\x80"));
	var_dump(is_valid_utf8("\xEE\x80\xBF"));
	var_dump(is_valid_utf8("\xEE\x80\xC0"));
	echo "\n";
	var_dump(is_valid_utf8("\xEE\xBF\x7F"));
	var_dump(is_valid_utf8("\xEE\xBF\x80"));
	var_dump(is_valid_utf8("\xEE\xBF\xBF"));
	var_dump(is_valid_utf8("\xEE\xBF\xC0"));
	echo "\n";
	var_dump(is_valid_utf8("\xEF\x7F\x80"));
	var_dump(is_valid_utf8("\xEF\x80\x80"));
	var_dump(is_valid_utf8("\xEF\xBF\x80"));
	var_dump(is_valid_utf8("\xEF\xC0\x80"));
	echo "\n";
	var_dump(is_valid_utf8("\xEF\x7F\xBF"));
	var_dump(is_valid_utf8("\xEF\x80\xBF"));
	var_dump(is_valid_utf8("\xEF\xBF\xBF"));  // 0xFFFF (reserved)
	var_dump(is_valid_utf8("\xEF\xC0\xBF"));
	echo "\n";
	var_dump(is_valid_utf8("\xEF\x80\x7F"));
	var_dump(is_valid_utf8("\xEF\x80\x80"));
	var_dump(is_valid_utf8("\xEF\x80\xBF"));
	var_dump(is_valid_utf8("\xEF\x80\xC0"));
	echo "\n";
	var_dump(is_valid_utf8("\xEF\xBF\x7F"));
	var_dump(is_valid_utf8("\xEF\xBF\x80"));
	var_dump(is_valid_utf8("\xEF\xBF\xBF"));  // 0xFFFF (reserved)
	var_dump(is_valid_utf8("\xEF\xBF\xC0"));
	echo "\n";
	var_dump(is_valid_utf8("\xEF\xBF\xBE"));  // 0xFFFE (reserved)
	var_dump(is_valid_utf8("\xEF\xBF\xBF"));  // 0xFFFF (reserved)
	echo "\n";

	// Verify four byte ranges.
	//    Code Points       First Byte Second Byte Third Byte Fourth Byte
	// U+10000 -  U+3FFFF   F0         90 - BF     80 - BF    80 - BF
	echo "Four byte F0 range test:\n";
	var_dump(is_valid_utf8("\xF0\x7F\x80\x80"));
	var_dump(is_valid_utf8("\xF0\x80\x80\x80"));
	var_dump(is_valid_utf8("\xF0\x8F\x80\x80"));
	var_dump(is_valid_utf8("\xF0\x90\x80\x80"));
	var_dump(is_valid_utf8("\xF0\xBF\x80\x80"));
	var_dump(is_valid_utf8("\xF0\xC0\x80\x80"));
	echo "\n";
	var_dump(is_valid_utf8("\xF0\x7F\x80\xBF"));
	var_dump(is_valid_utf8("\xF0\x80\x80\xBF"));
	var_dump(is_valid_utf8("\xF0\x8F\x80\xBF"));
	var_dump(is_valid_utf8("\xF0\x90\x80\xBF"));
	var_dump(is_valid_utf8("\xF0\xBF\x80\xBF"));
	var_dump(is_valid_utf8("\xF0\xC0\x80\xBF"));
	echo "\n";
	var_dump(is_valid_utf8("\xF0\x7F\xBF\x80"));
	var_dump(is_valid_utf8("\xF0\x80\xBF\x80"));
	var_dump(is_valid_utf8("\xF0\x8F\xBF\x80"));
	var_dump(is_valid_utf8("\xF0\x90\xBF\x80"));
	var_dump(is_valid_utf8("\xF0\xBF\xBF\x80"));
	var_dump(is_valid_utf8("\xF0\xC0\xBF\x80"));
	echo "\n";
	var_dump(is_valid_utf8("\xF0\x7F\xBF\xBF"));
	var_dump(is_valid_utf8("\xF0\x80\xBF\xBF"));
	var_dump(is_valid_utf8("\xF0\x8F\xBF\xBF"));
	var_dump(is_valid_utf8("\xF0\x90\xBF\xBF"));
	var_dump(is_valid_utf8("\xF0\xBF\xBF\xBF"));  // 0x3FFFF (reserved)
	var_dump(is_valid_utf8("\xF0\xC0\xBF\xBF"));
	echo "\n";
	var_dump(is_valid_utf8("\xF0\x90\x7F\x80"));
	var_dump(is_valid_utf8("\xF0\x90\x80\x80"));
	var_dump(is_valid_utf8("\xF0\x90\xBF\x80"));
	var_dump(is_valid_utf8("\xF0\x90\xC0\x80"));
	echo "\n";
	var_dump(is_valid_utf8("\xF0\x90\x7F\xBF"));
	var_dump(is_valid_utf8("\xF0\x90\x80\xBF"));
	var_dump(is_valid_utf8("\xF0\x90\xBF\xBF"));
	var_dump(is_valid_utf8("\xF0\x90\xC0\xBF"));
	echo "\n";
	var_dump(is_valid_utf8("\xF0\xBF\x7F\x80"));
	var_dump(is_valid_utf8("\xF0\xBF\x80\x80"));
	var_dump(is_valid_utf8("\xF0\xBF\xBF\x80"));
	var_dump(is_valid_utf8("\xF0\xBF\xC0\x80"));
	echo "\n";
	var_dump(is_valid_utf8("\xF0\xBF\x7F\xBF"));
	var_dump(is_valid_utf8("\xF0\xBF\x80\xBF"));
	var_dump(is_valid_utf8("\xF0\xBF\xBF\xBF"));  // 0x3FFFF (reserved)
	var_dump(is_valid_utf8("\xF0\xBF\xC0\xBF"));
	echo "\n";
	var_dump(is_valid_utf8("\xF0\x90\x80\x7F"));
	var_dump(is_valid_utf8("\xF0\x90\x80\x80"));
	var_dump(is_valid_utf8("\xF0\x90\x80\xBF"));
	var_dump(is_valid_utf8("\xF0\x90\x80\xC0"));
	echo "\n";
	var_dump(is_valid_utf8("\xF0\x90\xBF\x7F"));
	var_dump(is_valid_utf8("\xF0\x90\xBF\x80"));
	var_dump(is_valid_utf8("\xF0\x90\xBF\xBF"));
	var_dump(is_valid_utf8("\xF0\x90\xBF\xC0"));
	echo "\n";
	var_dump(is_valid_utf8("\xF0\xBF\x80\x7F"));
	var_dump(is_valid_utf8("\xF0\xBF\x80\x80"));
	var_dump(is_valid_utf8("\xF0\xBF\x80\xBF"));
	var_dump(is_valid_utf8("\xF0\xBF\x80\xC0"));
	echo "\n";
	var_dump(is_valid_utf8("\xF0\xBF\xBF\x7F"));
	var_dump(is_valid_utf8("\xF0\xBF\xBF\x80"));
	var_dump(is_valid_utf8("\xF0\xBF\xBF\xBF"));  // 0x3FFFF (reserved)
	var_dump(is_valid_utf8("\xF0\xBF\xBF\xC0"));
	echo "\n";
	var_dump(is_valid_utf8("\xF0"));  // Not long enough.
	var_dump(is_valid_utf8("\xF0\x90"));  // Not long enough.
	var_dump(is_valid_utf8("\xF0\x90\x80"));  // Not long enough.
	var_dump(is_valid_utf8("\xF0\x9F\xBF\xBE"));  // 0x1FFFE (reserved)
	var_dump(is_valid_utf8("\xF0\x9F\xBF\xBF"));  // 0x1FFFF (reserved)
	var_dump(is_valid_utf8("\xF0\xAF\xBF\xBE"));  // 0x2FFFE (reserved)
	var_dump(is_valid_utf8("\xF0\xAF\xBF\xBF"));  // 0x2FFFF (reserved)
	var_dump(is_valid_utf8("\xF0\xBF\xBF\xBE"));  // 0x3FFFE (reserved)
	var_dump(is_valid_utf8("\xF0\xBF\xBF\xBF"));  // 0x3FFFF (reserved)
	echo "\n";

	//    Code Points       First Byte Second Byte Third Byte Fourth Byte
	// U+40000 -  U+FFFFF   F1 - F3    80 - BF     80 - BF    80 - BF
	echo "Four byte F1-F3 range test:\n";
	var_dump(is_valid_utf8("\xF1\x7F\x80\x80"));
	var_dump(is_valid_utf8("\xF1\x80\x80\x80"));
	var_dump(is_valid_utf8("\xF1\xBF\x80\x80"));
	var_dump(is_valid_utf8("\xF1\xC0\x80\x80"));
	echo "\n";
	var_dump(is_valid_utf8("\xF1\x7F\x80\xBF"));
	var_dump(is_valid_utf8("\xF1\x80\x80\xBF"));
	var_dump(is_valid_utf8("\xF1\xBF\x80\xBF"));
	var_dump(is_valid_utf8("\xF1\xC0\x80\xBF"));
	echo "\n";
	var_dump(is_valid_utf8("\xF1\x7F\xBF\x80"));
	var_dump(is_valid_utf8("\xF1\x80\xBF\x80"));
	var_dump(is_valid_utf8("\xF1\xBF\xBF\x80"));
	var_dump(is_valid_utf8("\xF1\xC0\xBF\x80"));
	echo "\n";
	var_dump(is_valid_utf8("\xF1\x7F\xBF\xBF"));
	var_dump(is_valid_utf8("\xF1\x80\xBF\xBF"));
	var_dump(is_valid_utf8("\xF1\xBF\xBF\xBF"));  // 0x7FFFF (reserved)
	var_dump(is_valid_utf8("\xF1\xC0\xBF\xBF"));
	echo "\n";
	var_dump(is_valid_utf8("\xF1\x80\x7F\x80"));
	var_dump(is_valid_utf8("\xF1\x80\x80\x80"));
	var_dump(is_valid_utf8("\xF1\x80\xBF\x80"));
	var_dump(is_valid_utf8("\xF1\x80\xC0\x80"));
	echo "\n";
	var_dump(is_valid_utf8("\xF1\x80\x7F\xBF"));
	var_dump(is_valid_utf8("\xF1\x80\x80\xBF"));
	var_dump(is_valid_utf8("\xF1\x80\xBF\xBF"));
	var_dump(is_valid_utf8("\xF1\x80\xC0\xBF"));
	echo "\n";
	var_dump(is_valid_utf8("\xF1\xBF\x7F\x80"));
	var_dump(is_valid_utf8("\xF1\xBF\x80\x80"));
	var_dump(is_valid_utf8("\xF1\xBF\xBF\x80"));
	var_dump(is_valid_utf8("\xF1\xBF\xC0\x80"));
	echo "\n";
	var_dump(is_valid_utf8("\xF1\xBF\x7F\xBF"));
	var_dump(is_valid_utf8("\xF1\xBF\x80\xBF"));
	var_dump(is_valid_utf8("\xF1\xBF\xBF\xBF"));  // 0x7FFFF (reserved)
	var_dump(is_valid_utf8("\xF1\xBF\xC0\xBF"));
	echo "\n";
	var_dump(is_valid_utf8("\xF1\x80\x80\x7F"));
	var_dump(is_valid_utf8("\xF1\x80\x80\x80"));
	var_dump(is_valid_utf8("\xF1\x80\x80\xBF"));
	var_dump(is_valid_utf8("\xF1\x80\x80\xC0"));
	echo "\n";
	var_dump(is_valid_utf8("\xF1\x80\xBF\x7F"));
	var_dump(is_valid_utf8("\xF1\x80\xBF\x80"));
	var_dump(is_valid_utf8("\xF1\x80\xBF\xBF"));
	var_dump(is_valid_utf8("\xF1\x80\xBF\xC0"));
	echo "\n";
	var_dump(is_valid_utf8("\xF1\xBF\x80\x7F"));
	var_dump(is_valid_utf8("\xF1\xBF\x80\x80"));
	var_dump(is_valid_utf8("\xF1\xBF\x80\xBF"));
	var_dump(is_valid_utf8("\xF1\xBF\x80\xC0"));
	echo "\n";
	var_dump(is_valid_utf8("\xF1\xBF\xBF\x7F"));
	var_dump(is_valid_utf8("\xF1\xBF\xBF\x80"));
	var_dump(is_valid_utf8("\xF1\xBF\xBF\xBF"));  // 0x7FFFF (reserved)
	var_dump(is_valid_utf8("\xF1\xBF\xBF\xC0"));
	echo "\n";
	var_dump(is_valid_utf8("\xF3\x7F\x80\x80"));
	var_dump(is_valid_utf8("\xF3\x80\x80\x80"));
	var_dump(is_valid_utf8("\xF3\xBF\x80\x80"));
	var_dump(is_valid_utf8("\xF3\xC0\x80\x80"));
	echo "\n";
	var_dump(is_valid_utf8("\xF3\x7F\x80\xBF"));
	var_dump(is_valid_utf8("\xF3\x80\x80\xBF"));
	var_dump(is_valid_utf8("\xF3\xBF\x80\xBF"));
	var_dump(is_valid_utf8("\xF3\xC0\x80\xBF"));
	echo "\n";
	var_dump(is_valid_utf8("\xF3\x7F\xBF\x80"));
	var_dump(is_valid_utf8("\xF3\x80\xBF\x80"));
	var_dump(is_valid_utf8("\xF3\xBF\xBF\x80"));
	var_dump(is_valid_utf8("\xF3\xC0\xBF\x80"));
	echo "\n";
	var_dump(is_valid_utf8("\xF3\x7F\xBF\xBF"));
	var_dump(is_valid_utf8("\xF3\x80\xBF\xBF"));
	var_dump(is_valid_utf8("\xF3\xBF\xBF\xBF"));  // 0xFFFFF (reserved)
	var_dump(is_valid_utf8("\xF3\xC0\xBF\xBF"));
	echo "\n";
	var_dump(is_valid_utf8("\xF3\x80\x7F\x80"));
	var_dump(is_valid_utf8("\xF3\x80\x80\x80"));
	var_dump(is_valid_utf8("\xF3\x80\xBF\x80"));
	var_dump(is_valid_utf8("\xF3\x80\xC0\x80"));
	echo "\n";
	var_dump(is_valid_utf8("\xF3\x80\x7F\xBF"));
	var_dump(is_valid_utf8("\xF3\x80\x80\xBF"));
	var_dump(is_valid_utf8("\xF3\x80\xBF\xBF"));
	var_dump(is_valid_utf8("\xF3\x80\xC0\xBF"));
	echo "\n";
	var_dump(is_valid_utf8("\xF3\xBF\x7F\x80"));
	var_dump(is_valid_utf8("\xF3\xBF\x80\x80"));
	var_dump(is_valid_utf8("\xF3\xBF\xBF\x80"));
	var_dump(is_valid_utf8("\xF3\xBF\xC0\x80"));
	echo "\n";
	var_dump(is_valid_utf8("\xF3\xBF\x7F\xBF"));
	var_dump(is_valid_utf8("\xF3\xBF\x80\xBF"));
	var_dump(is_valid_utf8("\xF3\xBF\xBF\xBF"));  // 0xFFFFF (reserved)
	var_dump(is_valid_utf8("\xF3\xBF\xC0\xBF"));
	echo "\n";
	var_dump(is_valid_utf8("\xF3\x80\x80\x7F"));
	var_dump(is_valid_utf8("\xF3\x80\x80\x80"));
	var_dump(is_valid_utf8("\xF3\x80\x80\xBF"));
	var_dump(is_valid_utf8("\xF3\x80\x80\xC0"));
	echo "\n";
	var_dump(is_valid_utf8("\xF3\x80\xBF\x7F"));
	var_dump(is_valid_utf8("\xF3\x80\xBF\x80"));
	var_dump(is_valid_utf8("\xF3\x80\xBF\xBF"));
	var_dump(is_valid_utf8("\xF3\x80\xBF\xC0"));
	echo "\n";
	var_dump(is_valid_utf8("\xF3\xBF\x80\x7F"));
	var_dump(is_valid_utf8("\xF3\xBF\x80\x80"));
	var_dump(is_valid_utf8("\xF3\xBF\x80\xBF"));
	var_dump(is_valid_utf8("\xF3\xBF\x80\xC0"));
	echo "\n";
	var_dump(is_valid_utf8("\xF3\xBF\xBF\x7F"));
	var_dump(is_valid_utf8("\xF3\xBF\xBF\x80"));
	var_dump(is_valid_utf8("\xF3\xBF\xBF\xBF"));  // 0xFFFFF (reserved)
	var_dump(is_valid_utf8("\xF3\xBF\xBF\xC0"));
	echo "\n";
	var_dump(is_valid_utf8("\xF1\x8F\xBF\xBE"));  // 0x4FFFE (reserved)
	var_dump(is_valid_utf8("\xF1\x8F\xBF\xBF"));  // 0x4FFFF (reserved)
	var_dump(is_valid_utf8("\xF1\x9F\xBF\xBE"));  // 0x5FFFE (reserved)
	var_dump(is_valid_utf8("\xF1\x9F\xBF\xBF"));  // 0x5FFFF (reserved)
	var_dump(is_valid_utf8("\xF1\xAF\xBF\xBE"));  // 0x6FFFE (reserved)
	var_dump(is_valid_utf8("\xF1\xAF\xBF\xBF"));  // 0x6FFFF (reserved)
	var_dump(is_valid_utf8("\xF1\xBF\xBF\xBE"));  // 0x7FFFE (reserved)
	var_dump(is_valid_utf8("\xF1\xBF\xBF\xBF"));  // 0x7FFFF (reserved)
	var_dump(is_valid_utf8("\xF2\x8F\xBF\xBE"));  // 0x8FFFE (reserved)
	var_dump(is_valid_utf8("\xF2\x8F\xBF\xBF"));  // 0x8FFFF (reserved)
	var_dump(is_valid_utf8("\xF2\x9F\xBF\xBE"));  // 0x9FFFE (reserved)
	var_dump(is_valid_utf8("\xF2\x9F\xBF\xBF"));  // 0x9FFFF (reserved)
	var_dump(is_valid_utf8("\xF2\xAF\xBF\xBE"));  // 0xAFFFE (reserved)
	var_dump(is_valid_utf8("\xF2\xAF\xBF\xBF"));  // 0xAFFFF (reserved)
	var_dump(is_valid_utf8("\xF2\xBF\xBF\xBE"));  // 0xBFFFE (reserved)
	var_dump(is_valid_utf8("\xF2\xBF\xBF\xBF"));  // 0xBFFFF (reserved)
	var_dump(is_valid_utf8("\xF3\x8F\xBF\xBE"));  // 0xCFFFE (reserved)
	var_dump(is_valid_utf8("\xF3\x8F\xBF\xBF"));  // 0xCFFFF (reserved)
	var_dump(is_valid_utf8("\xF3\x9F\xBF\xBE"));  // 0xDFFFE (reserved)
	var_dump(is_valid_utf8("\xF3\x9F\xBF\xBF"));  // 0xDFFFF (reserved)
	var_dump(is_valid_utf8("\xF3\xAF\xBF\xBE"));  // 0xEFFFE (reserved)
	var_dump(is_valid_utf8("\xF3\xAF\xBF\xBF"));  // 0xEFFFF (reserved)
	var_dump(is_valid_utf8("\xF3\xBF\xBF\xBE"));  // 0xFFFFE (reserved)
	var_dump(is_valid_utf8("\xF3\xBF\xBF\xBF"));  // 0xFFFFF (reserved)
	echo "\n";

	//    Code Points       First Byte Second Byte Third Byte Fourth Byte
	//U+100000 - U+10FFFF   F4         80 - 8F     80 - BF    80 - BF
	echo "Four byte F4 range test:\n";
	var_dump(is_valid_utf8("\xF4\x7F\x80\x80"));
	var_dump(is_valid_utf8("\xF4\x80\x80\x80"));
	var_dump(is_valid_utf8("\xF4\x8F\x80\x80"));
	var_dump(is_valid_utf8("\xF4\x90\x80\x80"));
	var_dump(is_valid_utf8("\xF4\xBF\x80\x80"));
	var_dump(is_valid_utf8("\xF4\xC0\x80\x80"));
	echo "\n";
	var_dump(is_valid_utf8("\xF4\x7F\x80\xBF"));
	var_dump(is_valid_utf8("\xF4\x80\x80\xBF"));
	var_dump(is_valid_utf8("\xF4\x8F\x80\xBF"));
	var_dump(is_valid_utf8("\xF4\x90\x80\xBF"));
	var_dump(is_valid_utf8("\xF4\xBF\x80\xBF"));
	var_dump(is_valid_utf8("\xF4\xC0\x80\xBF"));
	echo "\n";
	var_dump(is_valid_utf8("\xF4\x7F\xBF\x80"));
	var_dump(is_valid_utf8("\xF4\x80\xBF\x80"));
	var_dump(is_valid_utf8("\xF4\x8F\xBF\x80"));
	var_dump(is_valid_utf8("\xF4\x90\xBF\x80"));
	var_dump(is_valid_utf8("\xF4\xBF\xBF\x80"));
	var_dump(is_valid_utf8("\xF4\xC0\xBF\x80"));
	echo "\n";
	var_dump(is_valid_utf8("\xF4\x7F\xBF\xBF"));
	var_dump(is_valid_utf8("\xF4\x80\xBF\xBF"));
	var_dump(is_valid_utf8("\xF4\x8F\xBF\xBF"));  // 0x10FFFF (reserved)
	var_dump(is_valid_utf8("\xF4\x90\xBF\xBF"));
	var_dump(is_valid_utf8("\xF4\xBF\xBF\xBF"));
	var_dump(is_valid_utf8("\xF4\xC0\xBF\xBF"));
	echo "\n";
	var_dump(is_valid_utf8("\xF4\x80\x7F\x80"));
	var_dump(is_valid_utf8("\xF4\x80\x80\x80"));
	var_dump(is_valid_utf8("\xF4\x80\xBF\x80"));
	var_dump(is_valid_utf8("\xF4\x80\xC0\x80"));
	echo "\n";
	var_dump(is_valid_utf8("\xF4\x80\x7F\xBF"));
	var_dump(is_valid_utf8("\xF4\x80\x80\xBF"));
	var_dump(is_valid_utf8("\xF4\x80\xBF\xBF"));
	var_dump(is_valid_utf8("\xF4\x80\xC0\xBF"));
	echo "\n";
	var_dump(is_valid_utf8("\xF4\x8F\x7F\x80"));
	var_dump(is_valid_utf8("\xF4\x8F\x80\x80"));
	var_dump(is_valid_utf8("\xF4\x8F\xBF\x80"));
	var_dump(is_valid_utf8("\xF4\x8F\xC0\x80"));
	echo "\n";
	var_dump(is_valid_utf8("\xF4\x8F\x7F\xBF"));
	var_dump(is_valid_utf8("\xF4\x8F\x80\xBF"));
	var_dump(is_valid_utf8("\xF4\x8F\xBF\xBF"));  // 0x10FFFF (reserved)
	var_dump(is_valid_utf8("\xF4\x8F\xC0\xBF"));
	echo "\n";
	var_dump(is_valid_utf8("\xF4\x80\x80\x7F"));
	var_dump(is_valid_utf8("\xF4\x80\x80\x80"));
	var_dump(is_valid_utf8("\xF4\x80\x80\xBF"));
	var_dump(is_valid_utf8("\xF4\x80\x80\xC0"));
	echo "\n";
	var_dump(is_valid_utf8("\xF4\x80\xBF\x7F"));
	var_dump(is_valid_utf8("\xF4\x80\xBF\x80"));
	var_dump(is_valid_utf8("\xF4\x80\xBF\xBF"));
	var_dump(is_valid_utf8("\xF4\x80\xBF\xC0"));
	echo "\n";
	var_dump(is_valid_utf8("\xF4\x8F\x80\x7F"));
	var_dump(is_valid_utf8("\xF4\x8F\x80\x80"));
	var_dump(is_valid_utf8("\xF4\x8F\x80\xBF"));
	var_dump(is_valid_utf8("\xF4\x8F\x80\xC0"));
	echo "\n";
	var_dump(is_valid_utf8("\xF4\x8F\xBF\x7F"));
	var_dump(is_valid_utf8("\xF4\x8F\xBF\x80"));
	var_dump(is_valid_utf8("\xF4\x8F\xBF\xBF"));  // 0x10FFFF (reserved)
	var_dump(is_valid_utf8("\xF4\x8F\xBF\xC0"));
	echo "\n";
	var_dump(is_valid_utf8("\xF4\x8F\xBF\xBE"));  // 0x10FFFE (reserved)
	var_dump(is_valid_utf8("\xF4\x8F\xBF\xBF"));  // 0x10FFFF (reserved)
	echo "\n";

	// Surrogates test.
	echo "Surrogates 0xD800-0xDFFF test:\n";
	var_dump(is_valid_utf8("\xED\xA0\x80"));
	var_dump(is_valid_utf8("\xED\xBF\xBF"));
	echo "\n";

	// Non-characters test.
	echo "Non-characters 0xFDD0-0xFDEF test:\n";
	var_dump(is_valid_utf8("\xEF\xB7\x90"));
	var_dump(is_valid_utf8("\xEF\xB7\xAF"));
	echo "\n";

	// Combining code points test.
	echo "Combining code points test:\n";
	// First code point must not be a combining character.
	var_dump(is_valid_utf8("\xCC\x80"));  // 0x0300
	var_dump(is_valid_utf8("\xCD\xAF"));  // 0x036F
	var_dump(is_valid_utf8("\xE1\xB7\x80"));  // 0x1DC0
	var_dump(is_valid_utf8("\xE1\xB7\xBF"));  // 0x1DFF
	var_dump(is_valid_utf8("\xE2\x83\x90"));  // 0x20D0
	var_dump(is_valid_utf8("\xE2\x83\xBF"));  // 0x20FF
	var_dump(is_valid_utf8("\xEF\xB8\xA0"));  // 0xFE20
	var_dump(is_valid_utf8("\xEF\xB8\xAF"));  // 0xFE2F
	echo "\n";
	var_dump(is_valid_utf8("A\xCC\x80"));  // 0x0300
	var_dump(is_valid_utf8("A\xCD\xAF"));  // 0x036F
	var_dump(is_valid_utf8("A\xE1\xB7\x80"));  // 0x1DC0
	var_dump(is_valid_utf8("A\xE1\xB7\xBF"));  // 0x1DFF
	var_dump(is_valid_utf8("A\xE2\x83\x90"));  // 0x20D0
	var_dump(is_valid_utf8("A\xE2\x83\xBF"));  // 0x20FF
	var_dump(is_valid_utf8("A\xEF\xB8\xA0"));  // 0xFE20
	var_dump(is_valid_utf8("A\xEF\xB8\xAF"));  // 0xFE2F
	echo "\n";
	var_dump(is_valid_utf8("A\xCC\x80Z"));  // 0x0300
	var_dump(is_valid_utf8("A\xCD\xAFZ"));  // 0x036F
	var_dump(is_valid_utf8("A\xE1\xB7\x80Z"));  // 0x1DC0
	var_dump(is_valid_utf8("A\xE1\xB7\xBFZ"));  // 0x1DFF
	var_dump(is_valid_utf8("A\xE2\x83\x90Z"));  // 0x20D0
	var_dump(is_valid_utf8("A\xE2\x83\xBFZ"));  // 0x20FF
	var_dump(is_valid_utf8("A\xEF\xB8\xA0Z"));  // 0xFE20
	var_dump(is_valid_utf8("A\xEF\xB8\xAFZ"));  // 0xFE2F
	echo "\n";
	// Zalgo text.  Strings containing exceptionally long combining code points.
	var_dump(is_valid_utf8("A\xCC\x80\xCD\xAF\xE1\xB7\x80\xCC\x80\xCD\xAF\xE1\xB7\x80\xCC\x80\xCD\xAF\xE1\xB7\x80\xCC\x80\xCD\xAF\xE1\xB7\x80\xCC\x80\xCD\xAF\xE1\xB7\x80"));
	var_dump(is_valid_utf8("A\xCC\x80\xCD\xAF\xE1\xB7\x80\xCC\x80\xCD\xAF\xE1\xB7\x80\xCC\x80\xCD\xAF\xE1\xB7\x80\xCC\x80\xCD\xAF\xE1\xB7\x80\xCC\x80\xCD\xAF\xE1\xB7\x80\xCC\x80"));
	var_dump(is_valid_utf8("A\xCC\x80\xCD\xAF\xE1\xB7\x80\xCC\x80\xCD\xAF\xE1\xB7\x80\xCC\x80\xCD\xAF\xE1\xB7\x80\xCC\x80\xCD\xAF\xE1\xB7\x80\xCC\x80\xCD\xAF\xE1\xB7\x80", false, 8));
	echo "\n";
?>
--EXPECT--
Basic test:
bool(true)
bool(true)
bool(true)

ASCII range test:
bool(false)
bool(true)
bool(true)
bool(true)
bool(false)
bool(true)
bool(true)
bool(false)

bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)

Invalid first byte test:
bool(false)
bool(false)
bool(false)

Two byte range test:
bool(false)
bool(true)
bool(true)
bool(false)

bool(false)
bool(true)
bool(true)
bool(false)

bool(false)

Three byte E0 range test:
bool(false)
bool(false)
bool(false)
bool(true)
bool(true)
bool(false)

bool(false)
bool(false)
bool(false)
bool(true)
bool(true)
bool(false)

bool(false)
bool(true)
bool(true)
bool(false)

bool(false)
bool(true)
bool(true)
bool(false)

bool(false)
bool(false)

Three byte E1-EC range test:
bool(false)
bool(true)
bool(true)
bool(false)

bool(false)
bool(true)
bool(true)
bool(false)

bool(false)
bool(true)
bool(true)
bool(false)

bool(false)
bool(true)
bool(true)
bool(false)

bool(false)
bool(true)
bool(true)
bool(false)

bool(false)
bool(true)
bool(true)
bool(false)

bool(false)
bool(true)
bool(true)
bool(false)

bool(false)
bool(true)
bool(true)
bool(false)

Three byte ED range test:
bool(false)
bool(true)
bool(true)
bool(false)
bool(false)
bool(false)

bool(false)
bool(true)
bool(true)
bool(false)
bool(false)
bool(false)

bool(false)
bool(true)
bool(true)
bool(false)

bool(false)
bool(true)
bool(true)
bool(false)

Three byte EE-EF range test:
bool(false)
bool(true)
bool(true)
bool(false)

bool(false)
bool(true)
bool(true)
bool(false)

bool(false)
bool(true)
bool(true)
bool(false)

bool(false)
bool(true)
bool(true)
bool(false)

bool(false)
bool(true)
bool(true)
bool(false)

bool(false)
bool(true)
bool(false)
bool(false)

bool(false)
bool(true)
bool(true)
bool(false)

bool(false)
bool(true)
bool(false)
bool(false)

bool(false)
bool(false)

Four byte F0 range test:
bool(false)
bool(false)
bool(false)
bool(true)
bool(true)
bool(false)

bool(false)
bool(false)
bool(false)
bool(true)
bool(true)
bool(false)

bool(false)
bool(false)
bool(false)
bool(true)
bool(true)
bool(false)

bool(false)
bool(false)
bool(false)
bool(true)
bool(false)
bool(false)

bool(false)
bool(true)
bool(true)
bool(false)

bool(false)
bool(true)
bool(true)
bool(false)

bool(false)
bool(true)
bool(true)
bool(false)

bool(false)
bool(true)
bool(false)
bool(false)

bool(false)
bool(true)
bool(true)
bool(false)

bool(false)
bool(true)
bool(true)
bool(false)

bool(false)
bool(true)
bool(true)
bool(false)

bool(false)
bool(true)
bool(false)
bool(false)

bool(false)
bool(false)
bool(false)
bool(false)
bool(false)
bool(false)
bool(false)
bool(false)
bool(false)

Four byte F1-F3 range test:
bool(false)
bool(true)
bool(true)
bool(false)

bool(false)
bool(true)
bool(true)
bool(false)

bool(false)
bool(true)
bool(true)
bool(false)

bool(false)
bool(true)
bool(false)
bool(false)

bool(false)
bool(true)
bool(true)
bool(false)

bool(false)
bool(true)
bool(true)
bool(false)

bool(false)
bool(true)
bool(true)
bool(false)

bool(false)
bool(true)
bool(false)
bool(false)

bool(false)
bool(true)
bool(true)
bool(false)

bool(false)
bool(true)
bool(true)
bool(false)

bool(false)
bool(true)
bool(true)
bool(false)

bool(false)
bool(true)
bool(false)
bool(false)

bool(false)
bool(true)
bool(true)
bool(false)

bool(false)
bool(true)
bool(true)
bool(false)

bool(false)
bool(true)
bool(true)
bool(false)

bool(false)
bool(true)
bool(false)
bool(false)

bool(false)
bool(true)
bool(true)
bool(false)

bool(false)
bool(true)
bool(true)
bool(false)

bool(false)
bool(true)
bool(true)
bool(false)

bool(false)
bool(true)
bool(false)
bool(false)

bool(false)
bool(true)
bool(true)
bool(false)

bool(false)
bool(true)
bool(true)
bool(false)

bool(false)
bool(true)
bool(true)
bool(false)

bool(false)
bool(true)
bool(false)
bool(false)

bool(false)
bool(false)
bool(false)
bool(false)
bool(false)
bool(false)
bool(false)
bool(false)
bool(false)
bool(false)
bool(false)
bool(false)
bool(false)
bool(false)
bool(false)
bool(false)
bool(false)
bool(false)
bool(false)
bool(false)
bool(false)
bool(false)
bool(false)
bool(false)

Four byte F4 range test:
bool(false)
bool(true)
bool(true)
bool(false)
bool(false)
bool(false)

bool(false)
bool(true)
bool(true)
bool(false)
bool(false)
bool(false)

bool(false)
bool(true)
bool(true)
bool(false)
bool(false)
bool(false)

bool(false)
bool(true)
bool(false)
bool(false)
bool(false)
bool(false)

bool(false)
bool(true)
bool(true)
bool(false)

bool(false)
bool(true)
bool(true)
bool(false)

bool(false)
bool(true)
bool(true)
bool(false)

bool(false)
bool(true)
bool(false)
bool(false)

bool(false)
bool(true)
bool(true)
bool(false)

bool(false)
bool(true)
bool(true)
bool(false)

bool(false)
bool(true)
bool(true)
bool(false)

bool(false)
bool(true)
bool(false)
bool(false)

bool(false)
bool(false)

Surrogates 0xD800-0xDFFF test:
bool(false)
bool(false)

Non-characters 0xFDD0-0xFDEF test:
bool(false)
bool(false)

Combining code points test:
bool(false)
bool(false)
bool(false)
bool(false)
bool(false)
bool(false)
bool(false)
bool(false)

bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)

bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)

bool(true)
bool(false)
bool(false)
