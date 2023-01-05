--TEST--
str_splice() test
--SKIPIF--
<?php if (!extension_loaded("qolfuncs"))  echo "skip"; ?>
--FILE--
<?php
	// Removes the string designated by offset and length and replaces it with the optional substring.
	// Prototype:  int str_splice(string &$dst, int $dst_offset, [ ?int $dst_length = null, string $src = '', int $src_offset = 0, ?int $src_length = null, int $src_repeat = 1, bool $shrink = true, ?int $dst_lastsize = null ])

	// Interned string test.  The original string "Interned string." before the assignment to $str should remain unmodified.
	echo "Interned string test:\n";
	for ($x = 0; $x < 2; $x++)
	{
		$str = "Interned string.";

		var_dump($str);
		var_dump(str_splice($str, 0, 8, "Dynamic"));
		var_dump($str);
	}
	echo "\n";

	// Referenced parameter test.  Make sure zval references are derefenced properly.
	echo "Referenced parameter test:\n";
	$str = "Basic string.";
	$str2 = &$str;
	$offset = 0;
	$offset2 = &$offset;
	$length = 5;
	$length2 = &$length;
	$str3 = "Modified";
	$str4 = &$str3;
	var_dump(str_splice($str2, $offset2, $length2, $str4));
	var_dump($str2);
	var_dump($str);
	unset($str);
	unset($str2);
	echo "\n";

	// Bounds test.  Make sure offsets and lengths are properly restricted to string boundaries.
	echo "Bounds test 1 (str_splice(\"0123456789\", -12, -12, \"ABCDE\")):\n";
	$str = "0123456789";
	var_dump(str_splice($str, -12, -12, "ABCDE"));
	var_dump($str);
	echo "\n";
	echo "Bounds test 2 (str_splice(\"0123456789\", 12, 12, \"ABCDE\")):\n";
	$str = "0123456789";
	var_dump(str_splice($str, 12, 12, "ABCDE"));
	var_dump($str);
	echo "\n";
	echo "Bounds test 3 (str_splice(\"0123456789\", 5, 12, \"ABCDE\")):\n";
	$str = "0123456789";
	var_dump(str_splice($str, 5, 12, "ABCDE"));
	var_dump($str);
	echo "\n";
	echo "Bounds test 4 (str_splice(\"0123456789\", 5, 12, \"ABCDE\", -12, -12)):\n";
	$str = "0123456789";
	var_dump(str_splice($str, 5, 12, "ABCDE", -12, -12));
	var_dump($str);
	echo "\n";
	echo "Bounds test 5 (str_splice(\"0123456789\", 5, 12, \"ABCDE\", 12, 12)):\n";
	$str = "0123456789";
	var_dump(str_splice($str, 5, 12, "ABCDE", 12, 12));
	var_dump($str);
	echo "\n";
	echo "Bounds test 6 (str_splice(\"0123456789\", 5, 12, \"ABCDE\", 2, 12)):\n";
	$str = "0123456789";
	var_dump(str_splice($str, 5, 12, "ABCDE", 2, 12));
	var_dump($str);
	echo "\n";
	echo "Bounds test 7 (str_splice(\"0123456789\", 5, 12, \"ABCDE\", 2, 12, 3)):\n";
	$str = "0123456789";
	var_dump(str_splice($str, 5, 12, "ABCDE", 2, 12, 3));
	var_dump($str);
	echo "\n";
	echo "Bounds test 8 (str_splice(\"0123456789\", -2, 12, \"ABCDE\", 0, 12, 1, false, 7)):\n";
	$str = "0123456789";
	var_dump(str_splice($str, -2, 12, "ABCDE", 0, 12, 1, false, 7));
	var_dump($str);
	echo "\n";
	echo "Bounds test 9 (str_splice(\"0123456789\", -2, 12, \"ABCDE\", 0, 12, 1, false, 17)):\n";
	$str = "0123456789";
	var_dump(str_splice($str, -2, 12, "ABCDE", 0, 12, 1, false, 17));
	var_dump($str);
	echo "\n";
	echo "Bounds test 10 (str_splice(\"0123456789\", -3, 12, \"ABCDE\", 0, 12, 1, false, 7)):\n";
	$str = "0123456789";
	var_dump(str_splice($str, -3, 12, "ABCDE", 0, 12, 1, false, 7));
	var_dump($str);
	echo "\n";

	// Same string test.  Correctly handle same source and destination.
	echo "Same string test:\n";
	$str = "AAAAA";
	$str .= "BBBBB";
	$str .= "CCCCC";
	// $str should never be modified by str_splice().
	$str2 = $str;
	var_dump(str_splice($str2, 0, 10, $str2, 9, 5, 2));
	var_dump($str2);
	var_dump($str);
	$str2 = $str;
	var_dump(str_splice($str2, 5, 10, $str2, 1, 5, 2));
	var_dump($str2);
	$str2 = $str;
	var_dump(str_splice($str2, 2, 6, $str2, 0, 5));
	var_dump($str2);
	$str2 = $str;
	var_dump(str_splice($str2, 2, 5, $str2, 0, 5));
	var_dump($str2);
	$str2 = "AAAAA";
	$str2 .= "BBBBB";
	$str2 .= "CCCCC";
	var_dump(str_splice($str2, 2, 5, $str2, 0, 5));
	var_dump($str2);
	$str2 = "AAAAA";
	$str2 .= "BBBBB";
	$str2 .= "CCCCC";
	var_dump(str_splice($str2, 8, 5, $str2, 10, 5));
	var_dump($str2);
	echo "\n";

	// Simple truncation test.
	echo "Simple truncation test:\n";
	$str = "AAAAA";
	$str .= "BBBBB";
	$str .= "CCCCC";
	$str2 = $str;
	var_dump(str_splice($str2, 10));
	var_dump($str2);
	var_dump($str);
	echo "\n";

	// Simple insertion test.
	echo "Simple insertion test:\n";
	$str = "AAAAA";
	$str .= "BBBBB";
	$str .= "CCCCC";
	$str2 = $str;
	var_dump(str_splice($str2, 10, 0, "DDDDD"));
	var_dump($str2);
	var_dump($str);
	echo "\n";

	// Buffer control test.  Minimize the number of times the buffer size is adjusted.
	echo "Buffer control test:\n";
	$str = "AAAAA";
	$str .= "BBBBB";
	$str .= "CCCCC";
	$str2 = $str;
	var_dump($lastsize = str_splice($str2, 5, 5, "DDD", 0, null, 1, false, strlen($str2)));
	var_dump($str2);
	var_dump(str_splice($str2, 5, 3, "EEEEE", 0, null, 1, true, $lastsize));
	var_dump($str2);
	var_dump(str_splice($str2, 5, 0, $str2, 5, 10));
	var_dump($str2);
	var_dump($str);
	echo "\n";

	// Silent buffer control test.  Verifies that garbage collection is working properly (var_dump() can mess up results).
	echo "Silent buffer control test:\n";
	$str = "AAAAA";
	$str .= "BBBBB";
	$str .= "CCCCC";
	$str2 = $str;
	$lastsize = str_splice($str2, 5, 5, "DDD", 0, null, 1, false, strlen($str2));
	str_splice($str2, 5, 3, "EEEEE", 0, null, 1, true, $lastsize);
	str_splice($str2, 5, 0, $str2, 5, 10);
	var_dump($str2);
	var_dump($str);
	echo "\n";

	// Repeating bytes test.
	echo "Repeating bytes test:\n";
	$str = "AAAAA";
	$str .= "BBBBB";
	$str .= "CCCCC";
	$str2 = $str;
	var_dump(str_splice($str2, 10, 0, "D", 0, null, 5));
	var_dump($str2);
	var_dump($str);
	$str2 = $str;
	var_dump(str_splice($str2, 10, 0, "DDDDD", 0, null, 5));
	var_dump($str2);
	$str2 = $str;
	var_dump(str_splice($str2, 10, 0, "DEF", 0, null, 500000));
	var_dump($str2 === "AAAAABBBBB" . str_repeat("DEF", 500000) . "CCCCC");
	echo "\n";
?>
--EXPECT--
Interned string test:
string(16) "Interned string."
int(15)
string(15) "Dynamic string."
string(16) "Interned string."
int(15)
string(15) "Dynamic string."

Referenced parameter test:
int(16)
string(16) "Modified string."
string(16) "Modified string."

Bounds test 1 (str_splice("0123456789", -12, -12, "ABCDE")):
int(15)
string(15) "ABCDE0123456789"

Bounds test 2 (str_splice("0123456789", 12, 12, "ABCDE")):
int(15)
string(15) "0123456789ABCDE"

Bounds test 3 (str_splice("0123456789", 5, 12, "ABCDE")):
int(10)
string(10) "01234ABCDE"

Bounds test 4 (str_splice("0123456789", 5, 12, "ABCDE", -12, -12)):
int(5)
string(5) "01234"

Bounds test 5 (str_splice("0123456789", 5, 12, "ABCDE", 12, 12)):
int(5)
string(5) "01234"

Bounds test 6 (str_splice("0123456789", 5, 12, "ABCDE", 2, 12)):
int(8)
string(8) "01234CDE"

Bounds test 7 (str_splice("0123456789", 5, 12, "ABCDE", 2, 12, 3)):
int(14)
string(14) "01234CDECDECDE"

Bounds test 8 (str_splice("0123456789", -2, 12, "ABCDE", 0, 12, 1, false, 7)):
int(10)
string(10) "01234ABCDE"

Bounds test 9 (str_splice("0123456789", -2, 12, "ABCDE", 0, 12, 1, false, 17)):
int(13)
string(13) "01234567ABCDE"

Bounds test 10 (str_splice("0123456789", -3, 12, "ABCDE", 0, 12, 1, false, 7)):
int(9)
string(10) "0123ABCDE9"

Same string test:
int(15)
string(15) "BCCCCBCCCCCCCCC"
string(15) "AAAAABBBBBCCCCC"
int(15)
string(15) "AAAAAAAAABAAAAB"
int(14)
string(14) "AAAAAAABBCCCCC"
int(15)
string(15) "AAAAAAABBBCCCCC"
int(15)
string(15) "AAAAAAABBBCCCCC"
int(15)
string(15) "AAAAABBBCCCCCCC"

Simple truncation test:
int(10)
string(10) "AAAAABBBBB"
string(15) "AAAAABBBBBCCCCC"

Simple insertion test:
int(20)
string(20) "AAAAABBBBBDDDDDCCCCC"
string(15) "AAAAABBBBBCCCCC"

Buffer control test:
int(13)
string(15) "AAAAADDDCCCCCCC"
int(15)
string(15) "AAAAAEEEEECCCCC"
int(25)
string(25) "AAAAAEEEEECCCCCEEEEECCCCC"
string(15) "AAAAABBBBBCCCCC"

Silent buffer control test:
string(25) "AAAAAEEEEECCCCCEEEEECCCCC"
string(15) "AAAAABBBBBCCCCC"

Repeating bytes test:
int(20)
string(20) "AAAAABBBBBDDDDDCCCCC"
string(15) "AAAAABBBBBCCCCC"
int(40)
string(40) "AAAAABBBBBDDDDDDDDDDDDDDDDDDDDDDDDDCCCCC"
int(1500015)
bool(true)
