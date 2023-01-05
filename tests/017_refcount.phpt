--TEST--
refcount() test
--SKIPIF--
<?php if (!extension_loaded("qolfuncs"))  echo "skip"; ?>
--FILE--
<?php
	// Returns the userland internal reference count of a zval.
	// Prototype:  int refcount(mixed &$value)

	// Interned string test.  Interned strings are special.
	echo "Interned string test:\n";
	$str = "AAAAA";
	var_dump(refcount($str));
	$str2 = $str;
	var_dump(refcount($str));
	var_dump(refcount($str2));

	$str3 = &$str;
	$str4 = $str3;
	var_dump(refcount($str3));
	var_dump(refcount($str));
	var_dump(refcount($str2));
	unset($str);
	unset($str2);
	unset($str3);
	unset($str4);
	echo "\n";

	// Dynamic string test.
	echo "Dynamic string test:\n";
	$str = "AAAAA";
	$str .= "BBBBB";
	// 1 ref - 1 + 1 value = 1 (Value:  $str)
	var_dump(refcount($str));
	$str2 = $str;
	// 1 ref - 1 + 2 value = 2 (Value:  $str, $str2)
	var_dump(refcount($str));
	// 1 ref - 1 + 2 value = 2 (Value:  $str, $str2)
	var_dump(refcount($str2));

	$str3 = &$str;

	// 2 ref - 1 + 2 value = 3 (Value:  $str/$str3, $str2)
	var_dump(refcount($str3));

	$str4 = $str3;  // Actually assigns the value, not the reference.

	// 2 ref - 1 + 3 value = 4 (Value:  $str/$str3, $str2, $str4)
	var_dump(refcount($str3));

	// 1 ref - 1 + 3 value = 3 (Value:  $str/$str3, $str2, $str4)
	var_dump(refcount($str4));

	// 2 ref - 1 + 3 value = 4 (Value:  $str/$str3, $str2, $str4)
	var_dump(refcount($str));

	// 1 ref - 1 + 3 value = 3 (Value:  $str/$str3, $str2, $str4)
	var_dump(refcount($str2));

	$str2 .= "CCCCC";

	// 2 ref - 1 + 2 value = 3 (Value:  $str/$str3, $str4)
	var_dump(refcount($str));

	// 1 ref - 1 + 1 value = 1 (Value:  $str2)
	var_dump(refcount($str2));

	// 2 ref - 1 + 2 value = 3 (Value:  $str/$str3, $str4)
	var_dump(refcount($str3));

	// 1 ref - 1 + 2 value = 2 (Value:  $str/$str3, $str4)
	var_dump(refcount($str4));

	unset($str);
	unset($str2);
	unset($str3);
	unset($str4);
	echo "\n";

	// Non-refcounted type test.
	echo "Non-refcounted type test:\n";
	$val = 15;
	var_dump(refcount($val));
	$val2 = &$val;
	var_dump(refcount($val));
	echo "\n";
?>
--EXPECT--
Interned string test:
int(1)
int(1)
int(1)
int(1)
int(1)
int(1)

Dynamic string test:
int(1)
int(2)
int(2)
int(3)
int(4)
int(3)
int(4)
int(3)
int(3)
int(1)
int(3)
int(2)

Non-refcounted type test:
int(1)
int(2)
