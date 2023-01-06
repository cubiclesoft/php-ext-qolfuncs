Quality of Life Improvement Functions for PHP Core
==================================================

A set of quality of life improvement functions designed for integration into PHP core.

This is a custom, compile-time PHP extension that is NOT intended for production use as-is.  Instead, it is intended as a conversation starter on the PHP internals mailing list for making notable improvements to the core product.  If approved through the PHP internals gauntlet, some future version of PHP itself could feature these enhancements.  You could be looking at the future!  (Or it could go nowhere fast, bogged down in politics.  Sigh.)  Looking for assistance/support from other folks to help champion the functions found in this extension.

The extension won't compile as-is against PHP 8.x with `gd` enabled because PHP 8.x doesn't appear to have a way to export `gd_image_ce` from `ext/gd/gd.c`, which is required to use the `Z_PARAM_OBJECT_OF_CLASS()` macro.  See the Compiling/Installing section below for options.

The code in this repository may be licensed by the PHP core development team under The PHP License 3.01 when integrating the extension into core.  Users who want to experiment with this extension may license this extension under The PHP License 3.01, MIT, or LGPL - your choice.

Compiling/Installing
--------------------

Fair warning:  This extension is NOT designed to be installed on production systems.  The Compiling/Installing section of this document (i.e. what you are reading) has been tested as the extension was developed inline with a specially prepared PHP 7.4.x for extension development using some flavor of gcc on Ubuntu Linux.  This extension might or might not compile for you but that's not really the point of this code repository.

With that warning/caveat out of the way, running `phpize` from within the directory with the `config.m4`/`config.w32` file should get the party started.

Useful documentation:

https://www.php.net/manual/en/install.pecl.phpize.php

Probably useful SO post:

https://stackoverflow.com/questions/3108937/how-to-install-and-run-phpize

If you run into problems with compiling `imagexportpixels()` and `imageimportpixels()` on PHP 8.x, you can:

* Nuke all code and references to those two functions if you don't want to use them.
* Compile PHP without `gd` support and then compile the extension as-is.  That should disable compilation of those functions.
* Compile against PHP 7.4.x instead.
* Or copy the functions into `ext/gd/gd.c` in a mainline PHP 8.x branch and compile them into PHP directly if you do actually want to try them out in PHP 8.x.

Background
----------

This extension was originally intended to be a series of inline memory manipulation functions for high performance string manipulation.  Zend engine has the policy of performing copy-on-write for every string operation.  This means that memory is regularly allocated and deallocated.  In general, this is fine because freed memory goes back into the allocation pool and therefore reduces the number of overall actual allocation requests to the system.

However, very serious performance issues arise when attempting to process and manipulate strings one byte at a time.  This is because PHP lacks specific facilities for inline string manipulation as well as not efficiently manipulating really large strings (e.g. 1MB or more).  Specifically, when appending one byte at a time, I can get up to 9MB/sec on an Intel Core i7 with [system memory capable of performing transfers up to 23GB/sec](https://ram.userbenchmark.com/SpeedTest/91386/GSKILL-F4-2133C15-8GVR-4x8GB).  Modifying existing strings inline only peaks at 35MB/sec on the same hardware.  Those are not typos.  In short, PHP's 0.2% of maximum system memory performance while churning 100% of one CPU thread to modify strings inline is _very_ unimpressive.  Of course, this won't come as any surprise to the PHP core dev team nor is it a design flaw of the language itself.

The development of the [Incredibly Flexible Data Storage file format](https://github.com/cubiclesoft/ifds) lead to the idea to create an extension to improve upon the situation in certain areas where performance is crucial.  As development of this extension progressed, various ideas for a variety of functions ended up being merged into the `str_splice()` super function and eventually the original concept for the extension went by the wayside.  As such, some functions are performance-oriented while others are simply useful for userland development and/or extension development.  So the extension was renamed to be the "qolfuncs" extension because these functions/ideas/concepts improve overall quality of life in various important ways when writing PHP code.  All of the functions are highly desirable for integration into PHP core.

What follows is a breakdown of each function by prototype and description, who the target audience is, why it should be incorporated into PHP core as soon as possible, and how it should be incorporated into PHP core.  Any showstopper bugs/issues will, of course, be addressed and fixed.

str_splice()
------------

`int str_splice(string &$dst, int $dst_offset, [ ?int $dst_length = null, string $src = '', int $src_offset = 0, ?int $src_length = null, int $src_repeat = 1, bool $shrink = true, ?int $dst_lastsize = null ])`

Removes the string designated by offset and length in the destination and replaces it with the optional source substring.

* $dst - A string passed by reference to splice.
* $dst_offset - The offset into the destination string to start the splice.  Supports negative offsets from the end of the string.
* $dst_length - The length of the destination string to replace from the offset.  Supports negative lengths and is nullable (Default is null).
* $src - A string to insert starting at $dst_offset (Default is '').
* $src_offset - The offset into the source string to begin copying.  Supports negative offsets from the end of the string (Default is 0).
* $src_length - The length of the source string to copy.  Supports negative lengths and is nullable (Default is null).
* $src_repeat - The number of times to repeat the source string (Default is 1).
* $shrink - Whether or not to shrink the destination string (Default is true).  When false and the destination is smaller than the input buffer, then this affects the return value.
* $dst_lastsize - The last size returned from str_splice().  Nullable (Default is null).

Returns:  The size of the destination string.

Unlike `substr_replace()`, `str_splice()` can result in zero memory allocations under carefully controlled conditions.  The function, when possible, performs inline memory manipulation for up to 10x potential performance gains.  That's an untested estimate but a fairly confident metric based on the extensive testing done for IFDS where `substr_replace()` ended up being slightly slower than alternative approaches.

The `$src_repeat` option allows for filling the destination with a repeated substring.  For example, writing 4KB of 0x00 to some part of the string.

This function also supports virtual buffers.  When $shrink is false, the buffer size is not reduced and the return value reflects the size of the virtual buffer.

Target audience:  Users working with large data.  Roughly similar in name/design to `array_splice()` only it doesn't return the removed string.

Why it should be added to PHP core:  Better performance and more features than `substr_replace()`.  Notably better performance for equal sized strings where `$dst_length == $src_length * $src_repeat`.  Fundamentally different design from other string functions in PHP.

How it should be incorporated into PHP core:  As-is into `ext/standard/string.c`.  Maybe remove the commented out `php_printf()` calls to clean up the code though.

str_realloc()
-------------

`int str_realloc(string &$str, int $size, [ bool $fast = false ])`

Reallocates the buffer associated with a string and returns the previous size.

* $str - A string to resize.
* $size - The new size of the string.
* $fast - Whether or not to reallocate the string for the new size when shrinking (Default is false).

Returns:  The previous size of the string.

This function can preallocate a buffer or truncate an existing string.  Inline modifying one byte at a time is approximately 3.8 times faster than appending one byte at a time to the end of a string.

Goes hand-in-hand with str_splice() virtual buffers.  For example, preallocating an estimated 1MB buffer, filling the buffer using str_splice(), and then calling str_realloc() to finalize the string.

Target audience:  Users working with large data.

Why it should be added to PHP core:  Better performance.  Works in concert with str_splice().  Gives indirect access to zend_string_realloc().

How it should be incorporated into PHP core:  As-is into `ext/standard/string.c`.

explode_substr()
----------------

`array explode_substr(string separator, string str [, ?int limit = null, ?int str_offset = 0, ?int str_length = null ])`

Splits a string on string separator and return array of components.  If limit is positive only limit number of components is returned.  If limit is negative all components except the last abs(limit) are returned.

* $separator - A string containing a separator to split on.
* $str - The string to split.
* $limit - The number of components to return.
* $str_offset - The offset into the string to begin splitting.  Supports negative offsets from the end of the string (Default is 0).
* $str_length - The length of the string being split.  Supports negative lengths and is nullable (Default is null).

Returns:  An array containing the split string components.

This proof-of-concept function is nearly identical to explode() but adds string offset and length parameters for the $str.  Useful for extracting substrings that need to be split.

Target audience:  Users that call `explode("...", substr($str))`.

Why it should be added to PHP core:  Saves a call to substr().

How it should be incorporated into PHP core:  Just extend `explode()` with offset and length parameters.  Don't add a new global function.  `explode_substr()` exists solely to provide working code.

str_split_substr()
------------------

`array str_split_substr(string str [, ?int split_length = 1, ?int str_offset = 0, ?int str_length = null ])`

Convert a string to an array.  If split_length is specified, break the string down into chunks each split_length characters long.

* $str - A string to split.
* $split_length - The chunk length of each entry in the array (Default is 1).
* $str_offset - The offset into the string to begin splitting.  Supports negative offsets from the end of the string (Default is 0).
* $str_length - The length of the string being split.  Supports negative lengths and is nullable (Default is null).

Returns:  An array containing the split string components.

This proof-of-concept function is nearly identical to str_split() but adds string offset and length parameters for the $str.  Useful for extracting substrings that need to be split.

Target audience:  Users that call `str_split(substr($str))`.

Why it should be added to PHP core:  Saves a call to substr().

How it should be incorporated into PHP core:  Just extend `str_split()` with offset and length parameters.  Don't add a new global function.  `str_split_substr()` exists solely to provide working code.

fread_mem()
-----------

`int|false fread_mem(resource fp, string &$str, [ int str_offset = 0, ?int length = null ])`

Binary-safe inline file read.

* $fp - A resource to an open file.
* $str - A string to store the read data in.
* $str_offset - The offset into the string to begin reading into.  Supports negative offsets from the end of the string (Default is 0).
* $length - The maximum number of bytes to read.  Nullable (Default is null).

Returns:  An integer containing the number of bytes read on success, false otherwise.

This function reads data from a stream into the destination string starting at the specified offset.

Target audience:  All users.

Why it should be added to PHP core:  It is extremely common to call `fread()`, check for failure, and then append the returned data to another string in a loop.  This eliminates two memory allocations per loop.

How it should be incorporated into PHP core:  It could be incorporated as-is OR extend `fread()` with inline support.

fwrite_substr()
---------------

`int|false fwrite_substr(resource fp, string str [, ?int str_offset = 0, ?int str_length = null ])`

Binary-safe file write.

* $fp - A resource to an open file.
* $str - A string to store the read data in.
* $str_offset - The offset into the string to begin writing from.  Supports negative offsets from the end of the string (Default is 0).
* $str_length - The length of the string being written.  Supports negative lengths and is nullable (Default is null).

Returns:  An integer containing the number of bytes read on success, false otherwise.

This proof-of-concept function is nearly identical to `fwrite()` but adds an offset parameter for the $str.  Useful for efficiently writing partial buffers to non-blocking network streams.

Target audience:  All users.

Why it should be added to PHP core:  It is extremely common to call `fwrite()`, get back a "write succeeded" response BUT only part of the data was written, and then have to chop up the buffer to be able to call fwrite() again to send the rest of the data.  This is extremely inefficient for large buffers.

How it should be incorporated into PHP core:  Just extend `fwrite()` with an offset parameter.  Don't add a new global function.  Unfortunately, there is already a `length` parameter in `fwrite()`, which means `offset` will have to go after `length`.  `fwrite_substr()` just showcases how nice the `fwrite()` prototype could have looked if it had been designed this way from the beginning.

hash_substr()
-------------

`string hash_substr(string algo, string data[, bool raw_output = false, ?int data_offset = 0, ?int data_length = null])`

Generate a hash of a given input string.  Returns lowercase hexits by default.

* $algo - A string containing a hash algorithm.
* $data - The data to hash.
* $raw_output - Output raw data when true, lowercase hexits when false (Default is false).
* $data_offset - The offset into the string to begin hashing from.  Supports negative offsets from the end of the string (Default is 0).
* $data_length - The length of the string to hash.  Supports negative lengths and is nullable (Default is null).

Returns:  A string containing the result of the hash.

This proof-of-concept function is nearly identical to `hash()` but adds data offset and length parameters.  Very useful for handling binary data within a substring such as:

```
4 bytes size
Data
4 byte CRC-32
```

Target audience:  All users who work with binary data.

Why it should be added to PHP core:  Copying data out of a string just to hash it is fairly expensive when a few minor pointer adjustments have the same effect.

How it should be incorporated into PHP core:  Just extend `hash()` with offset and length parameters.  Don't add a new global function.  hash_substr()` exists solely to provide working code.

hash_hmac_substr()
------------------

`string hash_hmac_substr(string algo, string data, string key[, bool raw_output = false, ?int data_offset = 0, ?int data_length = null])`

Generate a hash of a given input string with a key using HMAC.  Returns lowercase hexits by default.

* $algo - A string containing a hash algorithm.
* $data - The data to hash.
* $key - A string containing the HMAC key.
* $raw_output - Output raw data when true, lowercase hexits when false (Default is false).
* $data_offset - The offset into the string to begin hashing from.  Supports negative offsets from the end of the string (Default is 0).
* $data_length - The length of the string to hash.  Supports negative lengths and is nullable (Default is null).

Returns:  A string containing the result of the hash.

This proof-of-concept function is nearly identical to `hash_hmac()` but adds data offset and length parameters.  Very useful for handling binary data within a substring.

Target audience:  Some users who work with binary data.  This function is less useful than `hash_substr()` but exists for completeness and consistency.

Why it should be added to PHP core:  Copying data out of a string just to hash it is fairly expensive when a few minor pointer adjustments have the same effect.

How it should be incorporated into PHP core:  Just extend `hash_hmac()` with offset and length parameters.  Don't add a new global function.  hash_hmac_substr()` exists solely to provide working code.

imagexportpixels()
------------------

`array imageexportpixels(resource im, int x, int y, int width, int height)`

Export the colors/color indexes of a range of pixels as an array.

* $im - A resource/instance of GdImage (depending on PHP version).
* $x - The upper left corner x-coordinate to start from.
* $y - The upper left corner y-coordinate to start from.
* $width - A positive integer width.
* $height - A positive integer height.

Returns:  A 2D array containing the exported pixel colors on success, emits a notice and returns a boolean of false on error.

This long-overdue function massively improves performance when working with gd images at the pixel level.  Calling `imagecolorat()` in a loop is painfully slow.  For example, when processing a 3000x5000 pixel image, that's 15 million function calls to `imagecolorat()` to access every pixel of the image.  This function can rapidly export any portion of the image as a userland 2D array of integers.  Instead of 15 million PHP function calls, even one call per row would be a mere 5,000 function calls.

Target audience:  All users who use gd to do image processing.

Why it should be added to PHP core:  Notably improved performance.  Also, PECL ImageMagick has a similar but more advanced function.

How it should be incorporated into PHP core:  As-is into `ext/gd/gc.c`.  Although PHP 8.x changed things up for gd, so it will need some minor code cleanup.

imageimportpixels()
-------------------

`bool imageimportpixels(resource im, int x, int y, array colors)`

Sets pixels to the specified colors in the 2D array.

* $im - A resource/instance of GdImage (depending on PHP version).
* $x - The upper left corner x-coordinate to start from.
* $y - The upper left corner y-coordinate to start from.
* $colors - A 2D array of integers representing pixel colors.

Returns:  A boolean indicating whether or not the operation was successful.

This long-overdue function massively improves performance when working with gd images at the pixel level.  Calling `imagesetpixel()` in a loop is painfully slow.  For example, when processing a 3000x5000 pixel image, that's 15 million function calls to `imagesetpixel()` to modify every pixel of the image.  This function can rapidly import any portion of the image from a userland 2D array of integers.  Instead of 15 million PHP function calls, even one call per row would be a mere 5,000 function calls.

Target audience:  All users who use gd to do image manipulation.

Why it should be added to PHP core:  Notably improved performance.  Also, PECL ImageMagick has a similar but more advanced function.

How it should be incorporated into PHP core:  As-is into `ext/gd/gc.c`.  Although PHP 8.x changed things up for gd, so it will need some minor code cleanup.

mat_add()
---------

`array mat_add(array $a, array $b)`

Adds the values of two 2D matrices.

* $a - A 2D array.
* $b - A 2D array.

Returns:  A 2D array with the values of each corresponding cell added together.

This function performs a matrix addition operation on two matrices and returns the result.  It also exercises a number of new macros designed to iterate over HashTable structures.

Target audience:  Users working with matrices.

Why it should be added to PHP core:  Performant matrix addition from linear algebra.  See:  https://en.wikipedia.org/wiki/Matrix_(mathematics)

How it should be incorporated into PHP core:  As-is into `ext/standard/math.c`.  The macros (e.g. `HT_NEXT_BUCKET_VAL_IND()`) and relevant inline functions (e.g. `zend_hash_get_next_bucket_zval()`) should be placed as-is into `Zend/zend_hash.h`.

mat_sub()
---------

`array mat_sub(array $a, array $b)`

Subtracts the values of two 2D matrices.

* $a - A 2D array.
* $b - A 2D array.

Returns:  A 2D array with the values of each corresponding cell subtracted together.

This function performs a matrix subtraction operation on two matrices and returns the result.  Basically a copy of mat_add() but for subtraction.

Target audience:  Users working with matrices.

Why it should be added to PHP core:  Performant matrix subtraction from linear algebra.

How it should be incorporated into PHP core:  As-is into `ext/standard/math.c`.

mat_mult()
----------

`mat_mult(array $a, array|float|int $b, [int $row = null])`

Multiplies the values of two 2D matrices or the values of a 2D matrix or row of the matrix with a scalar value.

* $a - A 2D array.
* $b - A 2D array or a numeric scalar value.
* $row - An integer specifying the number of the row to multiply the scalar value on.  Nullable.

Returns:  A 2D array with the resulting values of the multiplication operation.

This function performs a matrix multiplication operation on one or two matrices and returns the result.  When using a scalar value, it can affect just one row with the $row parameter.

Matrix multiplication of two matrices from linear algebra is a traditionally O(N^3) algorithm.  This function is `max_execution_time`-aware to avoid consuming CPU resources beyond the execution time limit on all supported platforms.

Target audience:  Users working with matrices.

Why it should be added to PHP core:  Performant matrix multiplication operations from linear algebra.

How it should be incorporated into PHP core:  As-is into `ext/standard/math.c`.

is_valid_utf8()
---------------

`bool is_valid_utf8(string $value, [ bool $standard = false, int $combinelimit = 16 ])`

Finds whether the given value is a valid UTF-8 string.

* $value - Any input type to validate as a UTF-8 string.
* $standard - Whether or not to strictly adhere to the Unicode standard (Default is false - see Notes).
* $combinelimit - The maximum number of sequential combination code points to allow (Default is 16).

Returns:  A boolean of true if the string is a valid UTF-8 string, false otherwise.

This function determines whether or not a zval is a string and, if it is, whether or not it is valid UTF-8.  It also sets the `IS_STR_VALID_UTF8` garbage collection flag on success for non-interned strings so that future calls against the same string take less time.

The `$standard` option decides whether or not to allow the full 0x0000 through 0x007F character range.  There is significant risk with allowing certain control characters to reach databases, consoles, command-lines, etc.  This function, by default, limits the range to 0x0020 through 0x007E plus `\r`, `\n`, and `\t`.

The `$combinelimit` limits [Zalgo text](https://en.wikipedia.org/wiki/Zalgo_text) to reasonable lengths.  Zalgo text abuses combination code points, which can make a webpage unreadable.  The maximum number of legitimate combination code points to date is 8 code points in Tibetan.  So this function doubles that by default.

Target audience:  All users.

Why it should be added to PHP core:  Validating UTF-8 in userland requires processing one byte at a time, which is slow and better done in PHP core.  `mb_check_encoding($string, "UTF-8")` requires mbstring, which is not compiled in by default and doesn't have options for important protections that PHP should have out of the box.  Recursive regex checking is an even worse option and can crash PCRE.  Unicode is a complex Standard and most people get it wrong.  In addition, passing malformed UTF-8 to databases, processes, or various libraries can trigger security vulnerabilities.  UTF-8 is the most popular character set globally for the web but PHP accepts UTF-8 user input and does not have a built-in UTF-8 validation function.

How it should be incorporated into PHP core:  As a global function, it would perhaps fit into `ext/standard/type.c` or `ext/standard/string.c`?  But maybe it would work equally well as a filter.  Or both?

is_interned_string()
--------------------

`bool is_interned_string(mixed $value)`

Finds whether the given variable is an interned (immutable) string.

$value - Any input type to validate as an interned string.

Returns:  A boolean of true if the string is interned (immutable), false otherwise.

This function differentiates between the two internal types of strings in PHP core.  Interned (immutable) strings have a refcount of 1 and are never garbage collected while other strings are refcounted and garbage collected.

Target audience:  Extension developers.  Maybe some userland library developers too.

Why it should be added to PHP core:  Language completeness.  Could also be useful for some userland devs for performance optimizations.

How it should be incorporated into PHP core:  As-is into `ext/standard/type.c`.

refcount()
----------

`int refcount(mixed &$value)`

Returns the userland internal reference count of a zval.

* $value - Any input variable to retrieve its refcount.

Returns:  An accurate userland perspective reference count of a zval.  Interned strings always return a refcount of 1.

This function returns the accurate userland-oriented reference count of a variable.  Unlike `debug_zval_dump()`, it accomplishes this by passing the variable by reference instead of by value, correctly calculates the true reference count (reference refcount minus the Zend engine increment minus one + value refcount), and returns a straight integer value to the caller instead of a string.

Weak references do not solve all refcounting problems.  When there is a balancing act to maintain, especially in flexible caching scenarios, weak references cannot be used.

Target audience:  All users who need to implement flexible caching mechanisms such as "When 10,000 rows have been loaded and cached, prune the cache down to about 5,000 rows."  Also useful for extension developers.

Why it should be added to PHP core:  No reasonable built-in function exists to get just the refcount.  `debug_zval_dump()` is insufficient and also a tad incorrect.

How it should be incorporated into PHP core:  As-is into `ext/standard/var.c`.

is_reference()
--------------

`bool is_reference(mixed &$value)`

Finds whether the type of a variable is a reference.

* $value - Any input variable to determine if it is a reference.

Returns:  A boolean of true if the variable is a reference, false otherwise.

This function determines whether or not a variable is a reference.

As a side note:  I didn't know if this function was going to even be possible but I've needed it on many occasions for debugging purposes.  The Zend engine VM only has two options for its opcodes for parameters:  Coerce a variable to a reference OR coerce a variable to a value.  Fortunately, coercion to a reference is temporary during the function call and causes the refcount to be a minimum of two and is only higher if it is an actual reference variable.

Target audience:  All users.  Lots of weird application bugs can crop up with references.

Why it should be added to PHP core:  A useful debugging tool.  Also, language completeness.  However, it does rely on a side effect of how the engine functions.

How it should be incorporated into PHP core:  As-is into `ext/standard/type.c`.

is_equal_zval()
---------------

`bool is_equal_zval(mixed &$value, mixed &$value2, [ bool $deref = true ])`

Compares two raw zvals for equality but does not compare data for equality.

* $value - Any input variable to determine if it has equality to $value2.
* $value2 - Any input variable to determine if it has equality to $value.
* $deref - Whether or not to compare data pointers (Default is true).

Returns:  A boolean of true if the zvals are pointing at the same reference variable (for references) or data pointer.

This function performs a low-level pointer zval equality comparison operation between `$value` and `$value2`.  Handy for looking at complex zval mechanisms behind the scenes of PHP but not much else.  Very different from the equality operator (`===`).

Target audience:  Extension developers.  Maybe someone looking to optimize some code?

Why it should be added to PHP core:  Language completeness.  Exposes some inner workings of zvals that are otherwise difficult to surface.

How it should be incorporated into PHP core:  As-is into `ext/standard/var.c`.
