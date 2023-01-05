--TEST--
str_split_substr() test
--SKIPIF--
<?php if (!extension_loaded("qolfuncs"))  echo "skip"; ?>
--FILE--
<?php
	// Convert a string to an array. If split_length is specified, break the string down into chunks each split_length characters long.
	// Prototype:  array str_split_substr(string str [, ?int split_length = 1, ?int str_offset = 0, ?int str_length = null ])

	var_dump(str_split_substr("ABCDE123456789VWXYZ", 3, 5, -5));
	var_dump(str_split_substr("ABCDE123456789VWXYZ", 3, -14, -5));
?>
--EXPECT--
array(3) {
  [0]=>
  string(3) "123"
  [1]=>
  string(3) "456"
  [2]=>
  string(3) "789"
}
array(3) {
  [0]=>
  string(3) "123"
  [1]=>
  string(3) "456"
  [2]=>
  string(3) "789"
}
