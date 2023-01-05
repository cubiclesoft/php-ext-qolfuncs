--TEST--
fwrite_substr() test
--SKIPIF--
<?php if (!extension_loaded("qolfuncs"))  echo "skip"; ?>
--FILE--
<?php
	// Binary-safe file write.
	// Prototype:  int|false fwrite_substr(resource fp, string str [, ?int str_offset = 0, ?int str_length = null ])

	$filename = __DIR__ . "/007_fwrite_substr__testfile_001.tmp";

	$fp = fopen($filename, "wb");
	var_dump(fwrite_substr($fp, "abc,1|2|3|4|5,xyz", 4, -4));
	fclose($fp);

	var_dump(file_get_contents($filename));

	unlink($filename);

	$fp = fopen($filename, "wb");
	var_dump(fwrite_substr($fp, "abc,1|2|3|4|5,xyz", -13, -4));
	fclose($fp);

	var_dump(file_get_contents($filename));

	unlink($filename);
?>
--EXPECT--
int(9)
string(9) "1|2|3|4|5"
int(9)
string(9) "1|2|3|4|5"
