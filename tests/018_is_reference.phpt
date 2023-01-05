--TEST--
is_reference() test
--SKIPIF--
<?php if (!extension_loaded("qolfuncs"))  echo "skip"; ?>
--FILE--
<?php
	// Finds whether the type of a variable is a reference.
	// Prototype:  bool is_reference(mixed &$value)

	// Interned string test.  Interned strings are special.
	echo "Interned string test:\n";
	$str = "AAAAA";
	var_dump(is_reference($str));
	$str2 = $str;
	var_dump(is_reference($str));
	var_dump(is_reference($str2));

	$str3 = &$str;
	var_dump(is_reference($str3));
	var_dump(is_reference($str));
	$str4 = $str3;
	var_dump(is_reference($str4));

	var_dump(is_reference($str));
	var_dump(is_reference($str2));
	var_dump(is_reference($str3));
	var_dump(is_reference($str4));
	unset($str);
	unset($str2);
	unset($str3);
	unset($str4);
	echo "\n";

	// Dynamic string test.
	echo "Dynamic string test:\n";
	$str = "AAAAA";
	$str .= "BBBBB";
	var_dump(is_reference($str));
	$str2 = $str;
	var_dump(is_reference($str));
	var_dump(is_reference($str2));

	$str3 = &$str;
	var_dump(is_reference($str3));
	var_dump(is_reference($str));
	$str4 = $str3;
	var_dump(is_reference($str4));

	var_dump(is_reference($str));
	var_dump(is_reference($str2));
	var_dump(is_reference($str3));
	var_dump(is_reference($str4));
	unset($str);
	unset($str2);
	unset($str3);
	unset($str4);
	echo "\n";
?>
--EXPECT--
Interned string test:
bool(false)
bool(false)
bool(false)
bool(true)
bool(true)
bool(false)
bool(true)
bool(false)
bool(true)
bool(false)

Dynamic string test:
bool(false)
bool(false)
bool(false)
bool(true)
bool(true)
bool(false)
bool(true)
bool(false)
bool(true)
bool(false)
