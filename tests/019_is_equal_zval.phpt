--TEST--
is_equal_zval() test
--SKIPIF--
<?php if (!extension_loaded("qolfuncs"))  echo "skip"; ?>
--FILE--
<?php
	// Compares two raw zvals for equality but does not compare data for equality.
	// Prototype:  bool is_equal_zval(mixed &$value, mixed &$value2, [ bool $deref = true ])

	$str = "AAAAA";
	$str .= "BBBBB";
	$str2 = $str;
	var_dump(is_equal_zval($str, $str));
	var_dump(is_equal_zval($str, $str2));

	$str3 = &$str;
	var_dump(is_equal_zval($str, $str2));
	var_dump(is_equal_zval($str3, $str2));
	var_dump(is_equal_zval($str, $str3));
	var_dump(is_equal_zval($str, $str2, false));
	var_dump(is_equal_zval($str3, $str2, false));
	var_dump(is_equal_zval($str, $str3, false));
?>
--EXPECT--
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
bool(false)
bool(false)
bool(true)
