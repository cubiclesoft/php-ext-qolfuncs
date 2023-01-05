--TEST--
hash_hmac_substr() test
--SKIPIF--
<?php if (!extension_loaded("qolfuncs"))  echo "skip"; ?>
--FILE--
<?php
	// Generate a hash of a given input string with a key using HMAC.  Returns lowercase hexits by default.
	// Prototype:  string hash_hmac_substr(string algo, string data, string key[, bool raw_output = false, ?int data_offset = 0, ?int data_length = null])

	var_dump(hash_hmac_substr("sha256", "ABCDE123456789VWXYZ", "SUPERSECRET", false, 5, -5) === hash_hmac("sha256", "123456789", "SUPERSECRET"));
	var_dump(hash_hmac_substr("sha256", "ABCDE123456789VWXYZ", "SUPERSECRET", false, -14, -5) === hash_hmac("sha256", "123456789", "SUPERSECRET"));
?>
--EXPECT--
bool(true)
bool(true)
