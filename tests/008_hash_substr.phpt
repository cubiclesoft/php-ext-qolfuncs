--TEST--
hash_substr() test
--SKIPIF--
<?php if (!extension_loaded("qolfuncs"))  echo "skip"; ?>
--FILE--
<?php
	// Generate a hash of a given input string.  Returns lowercase hexits by default.
	// Prototype:  string hash_substr(string algo, string data[, bool raw_output = false, ?int data_offset = 0, ?int data_length = null])

	var_dump(hash_substr("sha256", "ABCDE123456789VWXYZ", false, 5, -5) === hash("sha256", "123456789"));
	var_dump(hash_substr("sha256", "ABCDE123456789VWXYZ", false, -14, -5) === hash("sha256", "123456789"));
?>
--EXPECT--
bool(true)
bool(true)
