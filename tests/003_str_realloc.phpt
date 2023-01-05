--TEST--
str_realloc() test
--SKIPIF--
<?php if (!extension_loaded("qolfuncs"))  echo "skip"; ?>
--FILE--
<?php
	// Reallocates the buffer associated with a string and returns the previous size.
	// Prototype:  int str_realloc(string &$str, int $size, [ bool $fast = false ])

	// Interned string test.  The original string "Interned string." before the assignment to $str should remain unmodified.
	echo "Interned string test:\n";
	for ($x = 0; $x < 2; $x++)
	{
		$str = "Interned string.";

		var_dump($str);
		var_dump(str_realloc($str, 8));
		var_dump($str);
	}
	echo "\n";

	// Same size test.
	echo "Same size test:\n";
	$str = "AAAAA";
	$str .= "BBBBB";
	var_dump(str_realloc($str, 10));
	var_dump($str);
	echo "\n";

	// Expansion test.
	echo "Expansion test:\n";
	$str = "AAAAA";
	$str .= "BBBBB";
	var_dump(str_realloc($str, 15));
	var_dump($str === "AAAAABBBBB" . str_repeat("\x00", 5));
	echo "\n";

	// Shrink test.
	echo "Shrink test:\n";
	$str = "AAAAA";
	$str .= "BBBBB";
	var_dump(str_realloc($str, 5));
	var_dump($str === "AAAAA");
	echo "\n";

	// Fast shrink test.
	echo "Fast shrink test:\n";
	$str = "AAAAA";
	$str .= "BBBBB";
	var_dump(str_realloc($str, 5, true));
	var_dump($str === "AAAAA");
	echo "\n";
?>
--EXPECT--
Interned string test:
string(16) "Interned string."
int(16)
string(8) "Interned"
string(16) "Interned string."
int(16)
string(8) "Interned"

Same size test:
int(10)
string(10) "AAAAABBBBB"

Expansion test:
int(10)
bool(true)

Shrink test:
int(10)
bool(true)

Fast shrink test:
int(10)
bool(true)
