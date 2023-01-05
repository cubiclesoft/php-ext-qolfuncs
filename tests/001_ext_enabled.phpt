--TEST--
Check if qolfuncs is loaded
--SKIPIF--
<?php if (!extension_loaded("qolfuncs"))  echo "skip"; ?>
--FILE--
<?php
echo "The qolfuncs extension is available";
?>
--EXPECT--
The qolfuncs extension is available
