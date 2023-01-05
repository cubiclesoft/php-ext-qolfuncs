--TEST--
fread_mem() test
--SKIPIF--
<?php if (!extension_loaded("qolfuncs"))  echo "skip"; ?>
--FILE--
<?php
	// Binary-safe inline file read.
	// Prototype:  int|false fread_mem(resource fp, string &$str, [ int str_offset = 0, ?int length = null ])

	$filename = __DIR__ . "/006_fread_mem__testfile_001.tmp";
	file_put_contents($filename, str_repeat("0123456789ABCDEF", 256));

	$fp = fopen($filename, "rb");
	$data = "";
	str_realloc($data, 256);
	var_dump(fread_mem($fp, $data));
	var_dump($data);
	var_dump(fread_mem($fp, $data, 256, 256));
	var_dump($data === str_repeat("0123456789ABCDEF", 32));
	fclose($fp);

	unlink($filename);
?>
--EXPECT--
int(256)
string(256) "0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF"
int(256)
bool(true)
