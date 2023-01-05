--TEST--
explode_substr() test
--SKIPIF--
<?php if (!extension_loaded("qolfuncs"))  echo "skip"; ?>
--FILE--
<?php
	// Splits a string on string separator and return array of components.  If limit is positive only limit number of components is returned.  If limit is negative all components except the last abs(limit) are returned.
	// Prototype:  array explode_substr(string separator, string str [, ?int limit = null, ?int str_offset = 0, ?int str_length = null ])

	var_dump(explode_substr("|", "abc,1|2|3|4|5,xyz", null, 4, -4));
	var_dump(explode_substr("|", "abc,1|2|3|4|5,xyz", null, -13, -4));
?>
--EXPECT--
array(5) {
  [0]=>
  string(1) "1"
  [1]=>
  string(1) "2"
  [2]=>
  string(1) "3"
  [3]=>
  string(1) "4"
  [4]=>
  string(1) "5"
}
array(5) {
  [0]=>
  string(1) "1"
  [1]=>
  string(1) "2"
  [2]=>
  string(1) "3"
  [3]=>
  string(1) "4"
  [4]=>
  string(1) "5"
}
