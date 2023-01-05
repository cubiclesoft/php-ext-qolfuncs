--TEST--
is_interned_string() test
--SKIPIF--
<?php if (!extension_loaded("qolfuncs"))  echo "skip"; ?>
--FILE--
<?php
	// Finds whether the given variable is an interned (immutable) string.
	// Prototype:  bool is_interned_string(mixed $value)

	var_dump(is_interned_string("Unpaid intern!"));

	$str = "Unpaid intern!";
	var_dump(is_interned_string($str));

	$str .= "  New employee!";
	var_dump(is_interned_string($str));
?>
--EXPECT--
bool(true)
bool(true)
bool(false)
