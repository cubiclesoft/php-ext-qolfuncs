/* qolfuncs extension for PHP */

#ifdef HAVE_CONFIG_H
# include "config.h"
#endif

#include "php.h"
#include "ext/standard/info.h"
#include "php_qolfuncs.h"

/* For compatibility with older PHP versions */
#ifndef ZEND_PARSE_PARAMETERS_NONE
#define ZEND_PARSE_PARAMETERS_NONE() \
	ZEND_PARSE_PARAMETERS_START(0, 0) \
	ZEND_PARSE_PARAMETERS_END()
#endif

/* {{{ Removes the string designated by offset and length and replaces it with the optional substring.
  int str_splice(string &$dst, int $dst_offset, [ ?int $dst_length = null, string $src = '', int $src_offset = 0, ?int $src_length = null, int $src_repeat = 1, bool $shrink = true, ?int $dst_lastsize = null ])
 */
PHP_FUNCTION(str_splice)
{
	int numargs = ZEND_NUM_ARGS();
	zval *zdst;
	zend_string *zsdst;
	char *dst, *dst2, *dst_ptr, *dst_ptr2;
	size_t dst_size, dst_size2, copy_limit, dst_diff;
	zend_long dst_offset, dst_length, dst_lastsize;
	zval *zdst_length = NULL, *zdst_lastsize = NULL;

	zval *zsrc;
	char *src = "";
	size_t src_size = 0;
	zend_long src_offset = 0, src_repeat = 1, src_length, src_finallength;
	zval *zsrc_length = NULL;
	zend_bool shrink = 1;
	zend_bool samestr = 0;

	ZEND_PARSE_PARAMETERS_START(2, 9)
		Z_PARAM_ZVAL(zdst)
		Z_PARAM_LONG(dst_offset)
		Z_PARAM_OPTIONAL
		Z_PARAM_ZVAL(zdst_length)
		Z_PARAM_ZVAL(zsrc)
		Z_PARAM_LONG(src_offset)
		Z_PARAM_ZVAL(zsrc_length)
		Z_PARAM_LONG(src_repeat)
		Z_PARAM_BOOL(shrink)
		Z_PARAM_ZVAL(zdst_lastsize)
	ZEND_PARSE_PARAMETERS_END();

	/* Dereference the destination zval as needed. */
//if (Z_TYPE_P(zdst) == IS_REFERENCE)  php_printf("Destination is a reference!\n");
//php_printf("Destination zval!  Before:  %p", zdst);
	ZVAL_DEREF(zdst);

	convert_to_string_ex(zdst);

//php_printf(", After:  %p\n", zdst);
//if (ZSTR_IS_INTERNED(Z_STR_P(zdst)))  php_printf("Destination is interned!\n");

	/* Convert the source to a string as needed. */
	if (numargs >= 4)
	{
		convert_to_string_ex(zsrc);
//if (ZSTR_IS_INTERNED(Z_STR_P(zsrc)))  php_printf("Source is interned!\n");

		if (Z_STR_P(zsrc) == Z_STR_P(zdst))  samestr = 1;
	}

	if (EG(exception))
	{
		return;
	}

	/* Confirm that the destination is indeed a string. */
	if (Z_TYPE_P(zdst) != IS_STRING)
	{
		php_error_docref(NULL, E_ERROR, "Destination must be a string");

		return;
	}

	/* Normalize offsets and lengths. */
	if (numargs < 8)  dst_lastsize = (zend_long)Z_STRLEN_P(zdst);
	else
	{
//if (Z_TYPE_P(zdst_lastsize) == IS_REFERENCE)  php_printf("Last size is a reference!\n");
		ZVAL_DEREF(zdst_lastsize);

		if (Z_TYPE_P(zdst_lastsize) == IS_NULL)  dst_lastsize = (zend_long)Z_STRLEN_P(zdst);
		else
		{
			dst_lastsize = Z_LVAL_P(zdst_lastsize);

			if (dst_lastsize < 0)  dst_lastsize = 0;

			if (dst_lastsize > Z_STRLEN_P(zdst))  dst_lastsize = Z_STRLEN_P(zdst);
		}
	}

	if (dst_offset < 0)
	{
		if (-dst_offset > dst_lastsize)  dst_offset = 0;
		else  dst_offset += dst_lastsize;
	}
	else if (dst_offset > dst_lastsize)
	{
		dst_offset = dst_lastsize;
	}

	if (numargs < 3)  dst_length = dst_lastsize;
	else
	{
//if (Z_TYPE_P(zdst_length) == IS_REFERENCE)  php_printf("Destination length is a reference!\n");
		ZVAL_DEREF(zdst_length);

		if (Z_TYPE_P(zdst_length) == IS_NULL)  dst_length = dst_lastsize;
		else  dst_length = Z_LVAL_P(zdst_length);
	}

	if (dst_length < 0)
	{
		if (-dst_length > dst_lastsize)  dst_length = 0;
		else  dst_length += dst_lastsize - dst_offset;
	}

	if (dst_length > dst_lastsize - dst_offset)  dst_length = dst_lastsize - dst_offset;


	if (numargs >= 4)  src_size = Z_STRLEN_P(zsrc);

	if (src_offset < 0)
	{
		if (-src_offset > (zend_long)src_size)  src_offset = 0;
		else  src_offset += (zend_long)src_size;
	}
	else if (src_offset > (zend_long)src_size)
	{
		src_offset = (zend_long)src_size;
	}

	if (numargs < 6)  src_length = (zend_long)src_size;
	else
	{
//if (Z_TYPE_P(zsrc_length) == IS_REFERENCE)  php_printf("Source length is a reference!\n");
		ZVAL_DEREF(zsrc_length);

		if (Z_TYPE_P(zsrc_length) == IS_NULL)  src_length = (zend_long)src_size;
		else  src_length = Z_LVAL_P(zsrc_length);
	}

	if (src_length < 0)
	{
		if (-src_length > (zend_long)src_size)  src_length = 0;
		else  src_length += (zend_long)src_size - src_offset;
	}

	if (src_length > src_size - src_offset)  src_length = (zend_long)src_size - src_offset;

	if (src_repeat < 1)  src_repeat = 1;

	src_finallength = src_length * src_repeat;


	/* Calculate final buffer size. */
	dst_size = dst_lastsize - dst_length + src_finallength;
	dst_size2 = dst_size;
	if (!shrink && dst_size < Z_STRLEN_P(zdst))  dst_size = Z_STRLEN_P(zdst);

//php_printf("Refcount:  %d\n", zend_string_refcount(Z_STR_P(zdst)));
//php_printf("dst_offset:  %ld, dst_length:  %ld, src_offset:  %ld, src_length:  %ld, src_finallength:  %ld, dst_size2:  %ld, dst_size:  %ld\n", dst_offset, dst_length, src_offset, src_length, src_finallength, dst_size2, dst_size);
//if (samestr)  php_printf("Source and destination strings are the same!\n");

	/* Allocate a new destination string if: */
	/*   The destination is interned/immutable. */
	/*   Has more than one (src and dst zend_string are different) or two (src and dst zend_string are the same) references. */
	/*   Or source and destination are the same string but are different initial sizes and the initial substring overlaps. */
	if (ZSTR_IS_INTERNED(Z_STR_P(zdst)) || zend_string_refcount(Z_STR_P(zdst)) > samestr + 1 || (samestr && src_length != dst_length && ((src_offset < dst_offset && src_offset + src_finallength > dst_offset) || (src_offset >= dst_offset && src_offset < dst_offset + dst_length))))
	{
//php_printf("Allocating new string.\n");
		zsdst = zend_string_alloc(dst_size, 0);
		dst = ZSTR_VAL(zsdst);

		memcpy(dst, Z_STRVAL_P(zdst), dst_size + 1);

		ZEND_TRY_ASSIGN_STR(zdst, zsdst);
	}
	else
	{
		/* The existing string just needs to be larger. */
		if (dst_size > Z_STRLEN_P(zdst))
		{
			if (samestr)  ZEND_TRY_ASSIGN_NULL(zsrc);

//php_printf("Resizing existing string!  Before:  %p", Z_STR_P(zdst));
			ZVAL_STR(zdst, zend_string_realloc(Z_STR_P(zdst), dst_size, 0));
//php_printf(", After:  %p\n", Z_STR_P(zdst));

			if (samestr)  zsrc = zdst;
		}

//php_printf("Using existing string!\n");
		/* Notable optimization for all other scenarios:  Directly alter the string. */
		zsdst = Z_STR_P(zdst);
		dst = ZSTR_VAL(zsdst);
	}

	/* If the source length is different than the destination length, move the data for the final space. */
	if (src_finallength != dst_length)
	{
		memmove(dst + dst_offset + src_finallength, dst + dst_offset + dst_length, dst_lastsize - dst_length - dst_offset);
		dst[dst_size] = '\0';

		/* If the source and destination strings are the same, adjust the source offset now that the data has moved. */
		if (numargs >= 4 && Z_STR_P(zsrc) == zsdst && src_offset > dst_offset)  src_offset += (src_finallength - dst_length);

		/* Update the string size as needed. */
		if (shrink && src_finallength < dst_length)  ZSTR_LEN(zsdst) = dst_size;
	}

	/* Copy the source. */
	if (src_finallength > 0)
	{
		if (numargs >= 4)  src = Z_STRVAL_P(zsrc);
//if (src == dst)  php_printf("Source and destination string pointers are identical!\n");

		if (src_length == 1)
		{
			/* Handle the one byte repeating string fill via memset(). */
			if (src_finallength > 1)  memset(dst + dst_offset, *src, src_finallength);
			else  dst[dst_offset] = *src;
		}
		else
		{
			/* Move/copy the source. */
			if (src == dst)  memmove(dst + dst_offset, src + src_offset, src_length);
			else  memcpy(dst + dst_offset, src + src_offset, src_length);

			if (src_repeat > 1)
			{
				/* Attempt to keep the maximum amount of data duplicated per memcpy() call to a size that hopefully keeps the source string within the onboard CPU cache. */
				copy_limit = 32768 / src_length;
				if (!copy_limit)  copy_limit++;
				copy_limit *= src_length * 2;

				dst2 = dst + dst_offset;

				dst_ptr = dst2 + src_length;
				dst_ptr2 = dst2 + src_finallength;

				while (dst_ptr < dst_ptr2)
				{
					dst_diff = ((dst_ptr - dst2) < (dst_ptr2 - dst_ptr) ? (dst_ptr - dst2) : (dst_ptr2 - dst_ptr));
					if (dst_diff > copy_limit)  dst_diff = copy_limit;

					memcpy(dst_ptr, dst2, dst_diff);

					dst_ptr += dst_diff;
				}
			}
		}
	}

//php_printf("Source:  %s\r\nDestination/Final:  %s\r\n", (numargs >= 4 ? Z_STRVAL_P(zsrc) : "[NONE]"), Z_STRVAL_P(zdst));

	/* Reset zend_string internals. */
	zend_string_forget_hash_val(zsdst);

	RETURN_LONG(dst_size2);
}
/* }}} */

/* {{{ Reallocates the buffer associated with a string and returns the previous size.
  int str_realloc(string &$str, int $size, [ bool $fast = false ])
 */
PHP_FUNCTION(str_realloc)
{
	zval *zstr;
	zend_string *zsstr;
	char *str;
	size_t str_size;
	zend_long newsize;
	zend_bool fast = 0;

	ZEND_PARSE_PARAMETERS_START(2, 3)
		Z_PARAM_ZVAL(zstr)
		Z_PARAM_LONG(newsize)
		Z_PARAM_OPTIONAL
		Z_PARAM_BOOL(fast)
	ZEND_PARSE_PARAMETERS_END();

	/* Dereference the zval as needed. */
//if (Z_TYPE_P(zstr) == IS_REFERENCE)  php_printf("zstr is a reference!\n");
//php_printf("Destination zval!  Before:  %p", zstr);
	ZVAL_DEREF(zstr);

	convert_to_string_ex(zstr);

//php_printf(", After:  %p\n", zstr);
//if (ZSTR_IS_INTERNED(Z_STR_P(zstr)))  php_printf("zstr is interned!\n");

	if (EG(exception))
	{
		return;
	}

	str_size = Z_STRLEN_P(zstr);

	if (str_size != newsize)
	{
		/* Always allocate a new string if: */
		/*   The destination is interned/immutable. */
		/*   Or has more than one reference. */
		if (ZSTR_IS_INTERNED(Z_STR_P(zstr)) || zend_string_refcount(Z_STR_P(zstr)) > 1)
		{
//php_printf("Allocating new string.\n");
			zsstr = zend_string_alloc(newsize, 0);
			str = ZSTR_VAL(zsstr);

			if (str_size < newsize)
			{
				memcpy(str, Z_STRVAL_P(zstr), str_size);
				memset(str + str_size, 0, newsize - str_size);
			}
			else
			{
				memcpy(str, Z_STRVAL_P(zstr), newsize);
			}

			ZEND_TRY_ASSIGN_STR(zstr, zsstr);
		}
		else if (fast && str_size > newsize)
		{
			/* Don't reallocate when shrinking the buffer in fast mode. */
//php_printf("Fast shrink.\n");
			Z_STRLEN_P(zstr) = newsize;

			/* Reset zend_string internals. */
			zend_string_forget_hash_val(Z_STR_P(zstr));
		}
		else
		{
//php_printf("Resizing string!  Before:  %p", Z_STR_P(zstr));
			ZVAL_STR(zstr, zend_string_realloc(Z_STR_P(zstr), newsize, 0));
//php_printf(", After:  %p\n", Z_STR_P(zstr));

			if (str_size < newsize)  memset(Z_STRVAL_P(zstr) + str_size, 0, newsize - str_size);
		}

		Z_STRVAL_P(zstr)[newsize] = '\0';
	}

	RETURN_LONG(str_size);
}
/* }}} */

/* {{{ php_adjust_substr_offset_length
 */
PHPAPI void php_adjust_substr_offset_length(zend_string *str, zend_long *str_offset, zend_long *str_length)
{
	zend_long str_size = ZSTR_LEN(str);

	if ((*str_offset) < 0)
	{
		if (-(*str_offset) > str_size)  (*str_offset) = 0;
		else  (*str_offset) += str_size;
	}
	else if ((*str_offset) > str_size)
	{
		(*str_offset) = str_size;
	}

	if ((*str_length) < 0)
	{
		if (-(*str_length) > str_size)  (*str_length) = 0;
		else  (*str_length) += str_size - (*str_offset);
	}

	if ((*str_length) > str_size - (*str_offset))  (*str_length) = str_size - (*str_offset);
}
/* }}} */


/* Start of code lifted directly from ext/standard/string.c and modified slightly to support substrings. */

/* {{{ php_explode_substr
 */
PHPAPI void php_explode_substr(const zend_string *delim, zend_string *str, zval *return_value, zend_long str_offset, zend_long str_length, zend_long limit)
{
	const char *p1 = ZSTR_VAL(str) + str_offset;
	const char *endp = p1 + str_length;
	const char *p2 = php_memnstr(p1, ZSTR_VAL(delim), ZSTR_LEN(delim), endp);
	zval  tmp;

	if (p2 == NULL) {
		ZVAL_STR_COPY(&tmp, str);
		zend_hash_next_index_insert_new(Z_ARRVAL_P(return_value), &tmp);
	} else {
		do {
			size_t l = p2 - p1;

			if (l == 0) {
				ZVAL_EMPTY_STRING(&tmp);
			} else if (l == 1) {
				ZVAL_INTERNED_STR(&tmp, ZSTR_CHAR((zend_uchar)(*p1)));
			} else {
				ZVAL_STRINGL(&tmp, p1, p2 - p1);
			}
			zend_hash_next_index_insert_new(Z_ARRVAL_P(return_value), &tmp);
			p1 = p2 + ZSTR_LEN(delim);
			p2 = php_memnstr(p1, ZSTR_VAL(delim), ZSTR_LEN(delim), endp);
		} while (p2 != NULL && --limit > 1);

		if (p1 <= endp) {
			ZVAL_STRINGL(&tmp, p1, endp - p1);
			zend_hash_next_index_insert_new(Z_ARRVAL_P(return_value), &tmp);
		}
	}
}
/* }}} */

/* {{{ php_explode_substr_negative_limit
 */
PHPAPI void php_explode_substr_negative_limit(const zend_string *delim, zend_string *str, zval *return_value, zend_long str_offset, zend_long str_length, zend_long limit)
{
#define EXPLODE_ALLOC_STEP 64
	const char *p1 = ZSTR_VAL(str) + str_offset;
	const char *endp = p1 + str_length;
	const char *p2 = php_memnstr(p1, ZSTR_VAL(delim), ZSTR_LEN(delim), endp);
	zval  tmp;

	if (p2 == NULL) {
		/*
		do nothing since limit <= -1, thus if only one chunk - 1 + (limit) <= 0
		by doing nothing we return empty array
		*/
	} else {
		size_t allocated = EXPLODE_ALLOC_STEP, found = 0;
		zend_long i, to_return;
		const char **positions = emalloc(allocated * sizeof(char *));

		positions[found++] = p1;
		do {
			if (found >= allocated) {
				allocated = found + EXPLODE_ALLOC_STEP;/* make sure we have enough memory */
				positions = erealloc(positions, allocated*sizeof(char *));
			}
			positions[found++] = p1 = p2 + ZSTR_LEN(delim);
			p2 = php_memnstr(p1, ZSTR_VAL(delim), ZSTR_LEN(delim), endp);
		} while (p2 != NULL);

		to_return = limit + found;
		/* limit is at least -1 therefore no need of bounds checking : i will be always less than found */
		for (i = 0; i < to_return; i++) { /* this checks also for to_return > 0 */
			ZVAL_STRINGL(&tmp, positions[i], (positions[i+1] - ZSTR_LEN(delim)) - positions[i]);
			zend_hash_next_index_insert_new(Z_ARRVAL_P(return_value), &tmp);
		}
		efree((void *)positions);
	}
#undef EXPLODE_ALLOC_STEP
}
/* }}} */

/* {{{ proto array explode_substr(string separator, string str [, ?int limit = null, ?int str_offset = 0, ?int str_length = null ])
   Splits a string on string separator and return array of components. If limit is positive only limit number of components is returned. If limit is negative all components except the last abs(limit) are returned. */
PHP_FUNCTION(explode_substr)
{
	int numargs = ZEND_NUM_ARGS();
	zend_string *str, *delim;
	zend_long limit = ZEND_LONG_MAX; /* No limit */
	zend_long str_offset = 0, str_length;
	zval tmp, *zlimit = NULL, *zstr_length = NULL;

	ZEND_PARSE_PARAMETERS_START(2, 5)
		Z_PARAM_STR(delim)
		Z_PARAM_STR(str)
		Z_PARAM_OPTIONAL
		Z_PARAM_ZVAL(zlimit)
		Z_PARAM_LONG(str_offset)
		Z_PARAM_ZVAL(zstr_length)
	ZEND_PARSE_PARAMETERS_END();

	if (ZSTR_LEN(delim) == 0) {
		php_error_docref(NULL, E_WARNING, "Empty delimiter");
		RETURN_FALSE;
	}

	array_init(return_value);

	if (ZSTR_LEN(str) == 0) {
		if (limit >= 0) {
			ZVAL_EMPTY_STRING(&tmp);
			zend_hash_index_add_new(Z_ARRVAL_P(return_value), 0, &tmp);
		}
		return;
	}

	if (numargs >= 3 && Z_TYPE_P(zlimit) != IS_NULL) {
		limit = Z_LVAL_P(zlimit);
	}

	if (numargs >= 5 && Z_TYPE_P(zstr_length) != IS_NULL) {
		str_length = Z_LVAL_P(zstr_length);
	} else {
		str_length = ZSTR_LEN(str);
	}

	php_adjust_substr_offset_length(str, &str_offset, &str_length);

	if (limit > 1) {
		php_explode_substr(delim, str, return_value, str_offset, str_length, limit);
	} else if (limit < 0) {
		php_explode_substr_negative_limit(delim, str, return_value, str_offset, str_length, limit);
	} else {
		ZVAL_STR_COPY(&tmp, str);
		zend_hash_index_add_new(Z_ARRVAL_P(return_value), 0, &tmp);
	}
}
/* }}} */

/* {{{ proto array str_split_substr(string str [, ?int split_length = 1, ?int str_offset = 0, ?int str_length = null ])
   Convert a string to an array. If split_length is specified, break the string down into chunks each split_length characters long. */
PHP_FUNCTION(str_split_substr)
{
	int numargs = ZEND_NUM_ARGS();
	zend_string *str;
	zend_long split_length = 1;
	const char *p;
	size_t n_reg_segments;
	zend_long str_offset = 0, str_length;
	zval *zsplit_length = NULL, *zstr_length = NULL;

	ZEND_PARSE_PARAMETERS_START(1, 4)
		Z_PARAM_STR(str)
		Z_PARAM_OPTIONAL
		Z_PARAM_ZVAL(zsplit_length)
		Z_PARAM_LONG(str_offset)
		Z_PARAM_ZVAL(zstr_length)
	ZEND_PARSE_PARAMETERS_END();

	if (numargs >= 2 && Z_TYPE_P(zsplit_length) != IS_NULL) {
		split_length = Z_LVAL_P(zsplit_length);
	}

	if (split_length <= 0) {
		php_error_docref(NULL, E_WARNING, "The length of each segment must be greater than zero");
		RETURN_FALSE;
	}

	if (numargs >= 4 && Z_TYPE_P(zstr_length) != IS_NULL) {
		str_length = Z_LVAL_P(zstr_length);
	} else {
		str_length = ZSTR_LEN(str);
	}

	php_adjust_substr_offset_length(str, &str_offset, &str_length);


	p = ZSTR_VAL(str) + str_offset;

	if (0 == str_length || (size_t)split_length >= str_length) {
		array_init_size(return_value, 1);
		add_next_index_stringl(return_value, p, str_length);
		return;
	}

	array_init_size(return_value, (uint32_t)(((str_length - 1) / split_length) + 1));

	n_reg_segments = str_length / split_length;

	while (n_reg_segments-- > 0) {
		add_next_index_stringl(return_value, p, split_length);
		p += split_length;
	}

	if (p < (ZSTR_VAL(str) + str_offset + str_length)) {
		add_next_index_stringl(return_value, p, (ZSTR_VAL(str) + str_offset + str_length - p));
	}
}
/* }}} */

/* End of code lifted directly from ext/standard/string.c and modified slightly to support substrings. */


/* Start of code lifted directly from ext/standard/file.c and modified slightly to support inline reads and substring writes. */

#define PHP_STREAM_FROM_ZVAL(stream, arg) \
	ZEND_ASSERT(Z_TYPE_P(arg) == IS_RESOURCE); \
	php_stream_from_res(stream, Z_RES_P(arg));

/* {{{ proto int|false fread_mem(resource fp, string &$str, [ int str_offset = 0, ?int length = null ])
   Binary-safe file read */
PHPAPI PHP_FUNCTION(fread_mem)
{
	int numargs = ZEND_NUM_ARGS();
	zval *res;
	php_stream *stream;
	zend_string *str;
	zend_long str_size, str_size2, str_offset = 0, length;
	zval *zstr, *zlength = NULL;
	ssize_t n;

	ZEND_PARSE_PARAMETERS_START(2, 4)
		Z_PARAM_RESOURCE(res)
		Z_PARAM_ZVAL(zstr)
		Z_PARAM_OPTIONAL
		Z_PARAM_LONG(str_offset)
		Z_PARAM_ZVAL(zlength)
	ZEND_PARSE_PARAMETERS_END_EX(RETURN_FALSE);

	PHP_STREAM_FROM_ZVAL(stream, res);

	/* Dereference the destination zval as needed. */
//if (Z_TYPE_P(zstr) == IS_REFERENCE)  php_printf("Destination var is a reference!\n");
//php_printf("Destination zval!  Before:  %p", zstr);
	ZVAL_DEREF(zstr);

	convert_to_string_ex(zstr);

//php_printf(", After:  %p\n", zstr);
//if (ZSTR_IS_INTERNED(Z_STR_P(zstr)))  php_printf("Destination is interned!\n");

	if (EG(exception))
	{
		return;
	}

	/* Confirm that the destination is indeed a string. */
	if (Z_TYPE_P(zstr) != IS_STRING)
	{
		php_error_docref(NULL, E_ERROR, "Destination variable must be a string");

		return;
	}

	str = Z_STR_P(zstr);
	str_size = ZSTR_LEN(str);

	/* Normalize offsets and lengths. */
	if (str_offset < 0)
	{
		if (-str_offset > str_size)  str_offset = 0;
		else  str_offset += str_size;
	}
	else if (str_offset > str_size)
	{
		str_offset = str_size;
	}

	if (numargs >= 4 && Z_TYPE_P(zlength) != IS_NULL) {
		length = Z_LVAL_P(zlength);
	} else {
		length = ZSTR_LEN(str) - str_offset;
	}

	if (length <= 0) {
		php_error_docref(NULL, E_WARNING, "Length parameter or destination variable buffer size must be greater than 0");
		RETURN_FALSE;
	}

	str_size2 = str_offset + length;

	/* Allocate a new destination string if: */
	/*   The destination is interned/immutable. */
	/*   Or has more than one reference. */
	if (ZSTR_IS_INTERNED(str) || zend_string_refcount(str) > 1)
	{
//php_printf("Allocating new string.\n");
		str = zend_string_alloc(str_size2, 0);

		memcpy(ZSTR_VAL(str), Z_STRVAL_P(zstr), str_size + 1);
		ZSTR_VAL(str)[str_size2] = '\0';

		ZEND_TRY_ASSIGN_STR(zstr, str);
	}
	else if (str_size < str_size2)
	{
		/* The existing string just needs to be larger. */
//php_printf("Resizing existing string!  Before:  %p", Z_STR_P(zstr));
		str = zend_string_realloc(str, str_size2, 0);
		ZSTR_VAL(str)[str_size2] = '\0';

		ZVAL_STR(zstr, str);
//php_printf(", After:  %p\n", Z_STR_P(zstr));
	}

	n = php_stream_read(stream, ZSTR_VAL(str) + str_offset, length);
	if (n < 0)
	{
		RETURN_FALSE;
	}

	/* Fill remaining space if the string was resized but the read operation didn't fulfill the original request. */
	if (n < length && str_size != str_size2)
	{
		memset(ZSTR_VAL(str) + str_offset + n, 0, length - n);
	}

	RETURN_LONG(n);
}
/* }}} */

/* {{{ proto int|false fwrite_substr(resource fp, string str [, ?int str_offset = 0, ?int str_length = null ])
   Binary-safe file write */
PHPAPI PHP_FUNCTION(fwrite_substr)
{
	int numargs = ZEND_NUM_ARGS();
	zval *res;
	zend_string *str;
	ssize_t ret;
	zend_long str_offset = 0, str_length;
	zval *zstr_length = NULL;
	php_stream *stream;

	ZEND_PARSE_PARAMETERS_START(2, 4)
		Z_PARAM_RESOURCE(res)
		Z_PARAM_STR(str)
		Z_PARAM_OPTIONAL
		Z_PARAM_LONG(str_offset)
		Z_PARAM_ZVAL(zstr_length)
	ZEND_PARSE_PARAMETERS_END_EX(RETURN_FALSE);

	if (numargs >= 4 && Z_TYPE_P(zstr_length) != IS_NULL) {
		str_length = Z_LVAL_P(zstr_length);
	} else {
		str_length = ZSTR_LEN(str);
	}

	php_adjust_substr_offset_length(str, &str_offset, &str_length);

	if (!str_length) {
		RETURN_LONG(0);
	}

	PHP_STREAM_FROM_ZVAL(stream, res);

	ret = php_stream_write(stream, ZSTR_VAL(str) + str_offset, str_length);
	if (ret < 0) {
		RETURN_FALSE;
	}

	RETURN_LONG(ret);
}
/* }}} */

/* End of code lifted directly from ext/standard/file.c and modified slightly to support inline reads and substring writes. */


/* Start of code lifted directly from ext/hash/hash.c and modified slightly to support substrings. */

#include "ext/hash/php_hash.h"
#include "ext/standard/file.h"

static void php_hash_do_hash_substr(INTERNAL_FUNCTION_PARAMETERS, int isfilename, zend_bool raw_output_default) /* {{{ */
{
	zend_string *digest;
	char *algo, *data;
	zend_string *zdata;
	size_t algo_len, data_len;
	zend_bool raw_output = raw_output_default;
	zend_long data_offset = 0, data_len2;
	zval *zdata_len2;
	const php_hash_ops *ops;
	void *context;
	php_stream *stream = NULL;

	if (isfilename) {
		if (zend_parse_parameters(ZEND_NUM_ARGS(), "ss|b", &algo, &algo_len, &data, &data_len, &raw_output) == FAILURE) {
			return;
		}
	} else {
		if (zend_parse_parameters(ZEND_NUM_ARGS(), "sS|blz", &algo, &algo_len, &zdata, &raw_output, &data_offset, &zdata_len2) == FAILURE) {
			return;
		}

		if (ZEND_NUM_ARGS() >= 5 && Z_TYPE_P(zdata_len2) != IS_NULL) {
			data_len2 = Z_LVAL_P(zdata_len2);
		} else {
			data_len2 = ZSTR_LEN(zdata);
		}

		php_adjust_substr_offset_length(zdata, &data_offset, &data_len2);

		data = ZSTR_VAL(zdata) + data_offset;
		data_len = (size_t)data_len2;
	}

	ops = php_hash_fetch_ops(algo, algo_len);
	if (!ops) {
		php_error_docref(NULL, E_WARNING, "Unknown hashing algorithm: %s", algo);
		RETURN_FALSE;
	}
	if (isfilename) {
		if (CHECK_NULL_PATH(data, data_len)) {
			php_error_docref(NULL, E_WARNING, "Invalid path");
			RETURN_FALSE;
		}
		stream = php_stream_open_wrapper_ex(data, "rb", REPORT_ERRORS, NULL, FG(default_context));
		if (!stream) {
			/* Stream will report errors opening file */
			RETURN_FALSE;
		}
	}

	context = emalloc(ops->context_size);
	ops->hash_init(context);

	if (isfilename) {
		char buf[1024];
		ssize_t n;

		while ((n = php_stream_read(stream, buf, sizeof(buf))) > 0) {
			ops->hash_update(context, (unsigned char *) buf, n);
		}
		php_stream_close(stream);
		if (n < 0) {
			efree(context);
			RETURN_FALSE;
		}
	} else {
		ops->hash_update(context, (unsigned char *) data, data_len);
	}

	digest = zend_string_alloc(ops->digest_size, 0);
	ops->hash_final((unsigned char *) ZSTR_VAL(digest), context);
	efree(context);

	if (raw_output) {
		ZSTR_VAL(digest)[ops->digest_size] = 0;
		RETURN_NEW_STR(digest);
	} else {
		zend_string *hex_digest = zend_string_safe_alloc(ops->digest_size, 2, 0, 0);

		php_hash_bin2hex(ZSTR_VAL(hex_digest), (unsigned char *) ZSTR_VAL(digest), ops->digest_size);
		ZSTR_VAL(hex_digest)[2 * ops->digest_size] = 0;
		zend_string_release_ex(digest, 0);
		RETURN_NEW_STR(hex_digest);
	}
}
/* }}} */

/* {{{ proto string hash_substr(string algo, string data[, bool raw_output = false, ?int data_offset = 0, ?int data_length = null])
Generate a hash of a given input string
Returns lowercase hexits by default */
PHP_FUNCTION(hash_substr)
{
	php_hash_do_hash_substr(INTERNAL_FUNCTION_PARAM_PASSTHRU, 0, 0);
}
/* }}} */

/* Directly copied functions so that php_hash_do_hash_hmac_substr() will compile. */
static inline void php_hash_string_xor_char(unsigned char *out, const unsigned char *in, const unsigned char xor_with, const size_t length) {
	size_t i;
	for (i=0; i < length; i++) {
		out[i] = in[i] ^ xor_with;
	}
}

static inline void php_hash_string_xor(unsigned char *out, const unsigned char *in, const unsigned char *xor_with, const size_t length) {
	size_t i;
	for (i=0; i < length; i++) {
		out[i] = in[i] ^ xor_with[i];
	}
}

static inline void php_hash_hmac_prep_key(unsigned char *K, const php_hash_ops *ops, void *context, const unsigned char *key, const size_t key_len) {
	memset(K, 0, ops->block_size);
	if (key_len > ops->block_size) {
		/* Reduce the key first */
		ops->hash_init(context);
		ops->hash_update(context, key, key_len);
		ops->hash_final(K, context);
	} else {
		memcpy(K, key, key_len);
	}
	/* XOR the key with 0x36 to get the ipad) */
	php_hash_string_xor_char(K, K, 0x36, ops->block_size);
}

static inline void php_hash_hmac_round(unsigned char *final, const php_hash_ops *ops, void *context, const unsigned char *key, const unsigned char *data, const zend_long data_size) {
	ops->hash_init(context);
	ops->hash_update(context, key, ops->block_size);
	ops->hash_update(context, data, data_size);
	ops->hash_final(final, context);
}

static void php_hash_do_hash_hmac_substr(INTERNAL_FUNCTION_PARAMETERS, int isfilename, zend_bool raw_output_default) /* {{{ */
{
	zend_string *digest;
	char *algo, *data, *key;
	zend_string *zdata;
	unsigned char *K;
	size_t algo_len, data_len, key_len;
	zend_bool raw_output = raw_output_default;
	zend_long data_offset = 0, data_len2;
	zval *zdata_len2;
	const php_hash_ops *ops;
	void *context;
	php_stream *stream = NULL;

	if (isfilename) {
		if (zend_parse_parameters(ZEND_NUM_ARGS(), "sss|b", &algo, &algo_len, &data, &data_len,
																	  &key, &key_len, &raw_output) == FAILURE) {
			return;
		}
	} else {
		if (zend_parse_parameters(ZEND_NUM_ARGS(), "sSs|blz", &algo, &algo_len, &zdata, &key, &key_len, &raw_output, &data_offset, &zdata_len2) == FAILURE) {
			return;
		}

		if (ZEND_NUM_ARGS() >= 6 && Z_TYPE_P(zdata_len2) != IS_NULL) {
			data_len2 = Z_LVAL_P(zdata_len2);
		} else {
			data_len2 = ZSTR_LEN(zdata);
		}

		php_adjust_substr_offset_length(zdata, &data_offset, &data_len2);

		data = ZSTR_VAL(zdata) + data_offset;
		data_len = (size_t)data_len2;
	}

	ops = php_hash_fetch_ops(algo, algo_len);
	if (!ops) {
		php_error_docref(NULL, E_WARNING, "Unknown hashing algorithm: %s", algo);
		RETURN_FALSE;
	}
	else if (!ops->is_crypto) {
		php_error_docref(NULL, E_WARNING, "Non-cryptographic hashing algorithm: %s", algo);
		RETURN_FALSE;
	}

	if (isfilename) {
		if (CHECK_NULL_PATH(data, data_len)) {
			php_error_docref(NULL, E_WARNING, "Invalid path");
			RETURN_FALSE;
		}
		stream = php_stream_open_wrapper_ex(data, "rb", REPORT_ERRORS, NULL, FG(default_context));
		if (!stream) {
			/* Stream will report errors opening file */
			RETURN_FALSE;
		}
	}

	context = emalloc(ops->context_size);

	K = emalloc(ops->block_size);
	digest = zend_string_alloc(ops->digest_size, 0);

	php_hash_hmac_prep_key(K, ops, context, (unsigned char *) key, key_len);

	if (isfilename) {
		char buf[1024];
		ssize_t n;
		ops->hash_init(context);
		ops->hash_update(context, K, ops->block_size);
		while ((n = php_stream_read(stream, buf, sizeof(buf))) > 0) {
			ops->hash_update(context, (unsigned char *) buf, n);
		}
		php_stream_close(stream);
		if (n < 0) {
			efree(context);
			efree(K);
			zend_string_release(digest);
			RETURN_FALSE;
		}

		ops->hash_final((unsigned char *) ZSTR_VAL(digest), context);
	} else {
		php_hash_hmac_round((unsigned char *) ZSTR_VAL(digest), ops, context, K, (unsigned char *) data, data_len);
	}

	php_hash_string_xor_char(K, K, 0x6A, ops->block_size);

	php_hash_hmac_round((unsigned char *) ZSTR_VAL(digest), ops, context, K, (unsigned char *) ZSTR_VAL(digest), ops->digest_size);

	/* Zero the key */
	ZEND_SECURE_ZERO(K, ops->block_size);
	efree(K);
	efree(context);

	if (raw_output) {
		ZSTR_VAL(digest)[ops->digest_size] = 0;
		RETURN_NEW_STR(digest);
	} else {
		zend_string *hex_digest = zend_string_safe_alloc(ops->digest_size, 2, 0, 0);

		php_hash_bin2hex(ZSTR_VAL(hex_digest), (unsigned char *) ZSTR_VAL(digest), ops->digest_size);
		ZSTR_VAL(hex_digest)[2 * ops->digest_size] = 0;
		zend_string_release_ex(digest, 0);
		RETURN_NEW_STR(hex_digest);
	}
}
/* }}} */

/* {{{ proto string hash_hmac_substr(string algo, string data, string key[, bool raw_output = false, ?int data_offset = 0, ?int data_length = null])
Generate a hash of a given input string with a key using HMAC
Returns lowercase hexits by default */
PHP_FUNCTION(hash_hmac_substr)
{
	php_hash_do_hash_hmac_substr(INTERNAL_FUNCTION_PARAM_PASSTHRU, 0, 0);
}
/* }}} */

/* End of code lifted directly from ext/hash/hash.c and modified slightly to support substrings. */


#if defined(HAVE_LIBGD) || defined(HAVE_GD_BUNDLED)

#include "ext/gd/php_gd.h"
#ifdef HAVE_GD_BUNDLED
# include "ext/gd/libgd/gd.h"
# include "ext/gd/libgd/gd_errors.h"
#else
# include <gd.h>
# include <gd_errors.h>
#endif

/* {{{ proto array imageexportpixels(resource im, int x, int y, int width, int height)
   Export the colors/color indexes of a range of pixels as an array. */
PHP_FUNCTION(imageexportpixels)
{
	zval *IM, tmparr, tmpval;
	zend_long x, x2, x3, y, y2, y3, width, height;
	gdImagePtr im;

	ZEND_PARSE_PARAMETERS_START(5, 5)
#if PHP_MAJOR_VERSION >= 8
		Z_PARAM_OBJECT_OF_CLASS(IM, gd_image_ce)
#else
		Z_PARAM_RESOURCE(IM)
#endif
		Z_PARAM_LONG(x)
		Z_PARAM_LONG(y)
		Z_PARAM_LONG(width)
		Z_PARAM_LONG(height)
	ZEND_PARSE_PARAMETERS_END();

#if PHP_MAJOR_VERSION >= 8
	im = php_gd_libgdimageptr_from_zval_p(IM);
#else
	if ((im = (gdImagePtr)zend_fetch_resource(Z_RES_P(IM), "Image", phpi_get_le_gd())) == NULL) {
		RETURN_FALSE;
	}
#endif

	if (width < 1) {
		php_error_docref(NULL, E_NOTICE, "Expected width to be a positive integer");
		RETURN_FALSE;
	}

	if (height < 1) {
		php_error_docref(NULL, E_NOTICE, "Expected height to be a positive integer");
		RETURN_FALSE;
	}

	x3 = x + width;
	y3 = y + height;

	ZVAL_LONG(&tmpval, 0);

	if (gdImageTrueColor(im)) {
		if (im->tpixels && gdImageBoundsSafe(im, x, y) && gdImageBoundsSafe(im, x3 - 1, y3 - 1)) {
			array_init_size(return_value, height);

			for (y2 = y; y2 < y3; y2++)
			{
				array_init_size(&tmparr, width);

				for (x2 = x; x2 < x3; x2++)
				{
					Z_LVAL(tmpval) = (zend_long)gdImageTrueColorPixel(im, x2, y2);

					zend_hash_next_index_insert_new(Z_ARRVAL(tmparr), &tmpval);
				}

				zend_hash_next_index_insert_new(Z_ARRVAL_P(return_value), &tmparr);
			}
		} else {
			php_error_docref(NULL, E_NOTICE, "[" ZEND_LONG_FMT "," ZEND_LONG_FMT " ... " ZEND_LONG_FMT "," ZEND_LONG_FMT "] is out of bounds", x, y, x3 - 1, y3 - 1);
			RETURN_FALSE;
		}
	} else {
		if (im->pixels && gdImageBoundsSafe(im, x, y) && gdImageBoundsSafe(im, x3 - 1, y3 - 1)) {
			array_init_size(return_value, height);

			for (y2 = y; y2 < y3; y2++)
			{
				array_init_size(&tmparr, width);

				for (x2 = x; x2 < x3; x2++)
				{
					Z_LVAL(tmpval) = (zend_long)im->pixels[y2][x2];

					zend_hash_next_index_insert_new(Z_ARRVAL(tmparr), &tmpval);
				}

				zend_hash_next_index_insert_new(Z_ARRVAL_P(return_value), &tmparr);
			}
		} else {
			php_error_docref(NULL, E_NOTICE, "[" ZEND_LONG_FMT "," ZEND_LONG_FMT " ... " ZEND_LONG_FMT "," ZEND_LONG_FMT "] is out of bounds", x, y, x3 - 1, y3 - 1);
			RETURN_FALSE;
		}
	}
}
/* }}} */

/* {{{ proto bool imageimportpixels(resource im, int x, int y, array colors)
   Sets pixels to the specified colors in the 2D array. */
PHP_FUNCTION(imageimportpixels)
{
	zval *IM, *tmparr, *tmpval;
	zend_long x, x2, y;
	HashTable *colors;
	gdImagePtr im;

	ZEND_PARSE_PARAMETERS_START(4, 4)
#if PHP_MAJOR_VERSION >= 8
		Z_PARAM_OBJECT_OF_CLASS(IM, gd_image_ce)
#else
		Z_PARAM_RESOURCE(IM)
#endif
		Z_PARAM_LONG(x)
		Z_PARAM_LONG(y)
		Z_PARAM_ARRAY_HT(colors)
	ZEND_PARSE_PARAMETERS_END();

#if PHP_MAJOR_VERSION >= 8
	im = php_gd_libgdimageptr_from_zval_p(IM);
#else
	if ((im = (gdImagePtr)zend_fetch_resource(Z_RES_P(IM), "Image", phpi_get_le_gd())) == NULL) {
		RETURN_FALSE;
	}
#endif

	ZEND_HASH_FOREACH_VAL_IND(colors, tmparr) {
		if (EXPECTED(Z_TYPE_P(tmparr) == IS_ARRAY)) {
			x2 = x;

			ZEND_HASH_FOREACH_VAL_IND(Z_ARRVAL_P(tmparr), tmpval) {
				gdImageSetPixel(im, x2, y, zval_get_long(tmpval));

				x2++;
			} ZEND_HASH_FOREACH_END();
		}

		y++;
	} ZEND_HASH_FOREACH_END();

	RETURN_TRUE;
}
/* }}} */

#endif


/* Useful functions and macros that should exist in zend_hash.h/zend_types.h for iterating over a hash like ZEND_HASH_FOREACH()/ZEND_HASH_REVERSE_FOREACH(). */
#define HT_NUM_USED(ht) \
	(ht)->nNumUsed

#define HT_BUCKET_VAL(bucket) \
	&((bucket)->val)

#define HT_BUCKET_FIRST(ht) \
	(ht)->arData

#define HT_BUCKET_END(ht) \
	(ht)->arData + (ht)->nNumUsed

static zend_always_inline zval *zend_hash_get_next_bucket_zval(HashTable *ht, Bucket **bucket, zend_bool indirect)
{
	zval *zv;
	Bucket *end;

	if (EXPECTED(*bucket)) {
		(*bucket)++;
	} else {
		*bucket = HT_BUCKET_FIRST(ht);
	}

	end = HT_BUCKET_END(ht);

	for (; *bucket < end; (*bucket)++) {
		zv = HT_BUCKET_VAL(*bucket);

		if (indirect && Z_TYPE_P(zv) == IS_INDIRECT) {
			zv = Z_INDIRECT_P(zv);
		}

		if (EXPECTED(Z_TYPE_P(zv) != IS_UNDEF))  break;
	}

	if (UNEXPECTED(*bucket >= end)) {
		*bucket = NULL;

		zv = NULL;
	}

	return zv;
}

static zend_always_inline zval *zend_hash_get_prev_bucket_zval(HashTable *ht, Bucket **bucket, zend_bool indirect)
{
	zval *zv;
	Bucket *first;

	if (EXPECTED(*bucket)) {
		(*bucket)--;
	} else {
		*bucket = HT_BUCKET_END(ht) - 1;
	}

	first = HT_BUCKET_FIRST(ht) - 1;

	for (; *bucket > first; (*bucket)--) {
		zv = HT_BUCKET_VAL(*bucket);

		if (indirect && Z_TYPE_P(zv) == IS_INDIRECT) {
			zv = Z_INDIRECT_P(zv);
		}

		if (EXPECTED(Z_TYPE_P(zv) != IS_UNDEF))  break;
	}

	if (UNEXPECTED(*bucket <= first)) {
		*bucket = NULL;

		zv = NULL;
	}

	return zv;
}

#define HT_NEXT_BUCKET(ht, bucket, indirect) \
	zend_hash_get_next_bucket_zval((ht), &(bucket), (indirect))

#define HT_NEXT_BUCKET_VAL(ht, bucket) \
	HT_NEXT_BUCKET(ht, bucket, 0)

#define HT_NEXT_BUCKET_VAL_IND(ht, bucket) \
	HT_NEXT_BUCKET(ht, bucket, 1)

#define HT_PREV_BUCKET(ht, bucket, indirect) \
	zend_hash_get_prev_bucket_zval((ht), &(bucket), (indirect))

#define HT_PREV_BUCKET_VAL(ht, bucket) \
	HT_PREV_BUCKET(ht, bucket, 0)

#define HT_PREV_BUCKET_VAL_IND(ht, bucket) \
	HT_PREV_BUCKET(ht, bucket, 1)


/* {{{ proto array mat_add(array $a, array $b)
   Adds the values of two 2D matrices. */
PHP_FUNCTION(mat_add)
{
	HashTable *a, *a2, *b, *b2;
	Bucket *arow = NULL, *acol, *brow = NULL, *bcol;
	zval *aval, *bval, tmprow, tmplval, tmpdval;
	size_t rows, cols, x;

	ZEND_PARSE_PARAMETERS_START(2, 2)
		Z_PARAM_ARRAY_HT(a)
		Z_PARAM_ARRAY_HT(b)
	ZEND_PARSE_PARAMETERS_END();

	rows = zend_array_count(a);
	x = zend_array_count(b);
	if (rows < x)  rows = x;

	array_init_size(return_value, rows);

	ZVAL_LONG(&tmplval, 0);
	ZVAL_DOUBLE(&tmpdval, 0.0);

	aval = HT_NEXT_BUCKET_VAL_IND(a, arow);
	bval = HT_NEXT_BUCKET_VAL_IND(b, brow);
	while (arow || brow) {
		acol = NULL;
		bcol = NULL;

		if (arow && Z_TYPE_P(aval) == IS_ARRAY) {
			cols = zend_array_count(Z_ARRVAL_P(aval));

			a2 = Z_ARRVAL_P(aval);
			aval = HT_NEXT_BUCKET_VAL_IND(a2, acol);
		} else {
			cols = 0;

			a2 = NULL;
		}

		if (brow && Z_TYPE_P(bval) == IS_ARRAY) {
			x = zend_array_count(Z_ARRVAL_P(bval));
			if (cols < x)  cols = x;

			b2 = Z_ARRVAL_P(bval);
			bval = HT_NEXT_BUCKET_VAL_IND(b2, bcol);
		} else {
			b2 = NULL;
		}

		array_init_size(&tmprow, cols);

		while (acol || bcol) {
			if ((acol && Z_TYPE_P(aval) == IS_DOUBLE) || (bcol && Z_TYPE_P(bval) == IS_DOUBLE)) {
				if (acol) {
					Z_DVAL(tmpdval) = zval_get_double(aval);
				} else {
					Z_DVAL(tmpdval) = 0.0;
				}

				if (bcol) {
					Z_DVAL(tmpdval) += zval_get_double(bval);
				}

				zend_hash_next_index_insert_new(Z_ARRVAL(tmprow), &tmpdval);
			} else {
				if (acol) {
					Z_LVAL(tmplval) = zval_get_long(aval);
				} else {
					Z_LVAL(tmplval) = 0;
				}

				if (bcol) {
					Z_LVAL(tmplval) += zval_get_long(bval);
				}

				zend_hash_next_index_insert_new(Z_ARRVAL(tmprow), &tmplval);
			}

			if (acol)  aval = HT_NEXT_BUCKET_VAL_IND(a2, acol);
			if (bcol)  bval = HT_NEXT_BUCKET_VAL_IND(b2, bcol);
		}

		zend_hash_next_index_insert_new(Z_ARRVAL_P(return_value), &tmprow);

		if (arow)  aval = HT_NEXT_BUCKET_VAL_IND(a, arow);
		if (brow)  bval = HT_NEXT_BUCKET_VAL_IND(b, brow);
	}
}
/* }}} */


/* {{{ proto array mat_sub(array $a, array $b)
   Subtracts the values of two 2D matrices. */
PHP_FUNCTION(mat_sub)
{
	HashTable *a, *a2, *b, *b2;
	Bucket *arow = NULL, *acol, *brow = NULL, *bcol;
	zval *aval, *bval, tmprow, tmplval, tmpdval;
	size_t rows, cols, x;

	ZEND_PARSE_PARAMETERS_START(2, 2)
		Z_PARAM_ARRAY_HT(a)
		Z_PARAM_ARRAY_HT(b)
	ZEND_PARSE_PARAMETERS_END();

	rows = zend_array_count(a);
	x = zend_array_count(b);
	if (rows < x)  rows = x;

	array_init_size(return_value, rows);

	ZVAL_LONG(&tmplval, 0);
	ZVAL_DOUBLE(&tmpdval, 0.0);

	aval = HT_NEXT_BUCKET_VAL_IND(a, arow);
	bval = HT_NEXT_BUCKET_VAL_IND(b, brow);
	while (arow || brow) {
		acol = NULL;
		bcol = NULL;

		if (arow && Z_TYPE_P(aval) == IS_ARRAY) {
			cols = zend_array_count(Z_ARRVAL_P(aval));

			a2 = Z_ARRVAL_P(aval);
			aval = HT_NEXT_BUCKET_VAL_IND(a2, acol);
		} else {
			cols = 0;

			a2 = NULL;
		}

		if (brow && Z_TYPE_P(bval) == IS_ARRAY) {
			x = zend_array_count(Z_ARRVAL_P(bval));
			if (cols < x)  cols = x;

			b2 = Z_ARRVAL_P(bval);
			bval = HT_NEXT_BUCKET_VAL_IND(b2, bcol);
		} else {
			b2 = NULL;
		}

		array_init_size(&tmprow, cols);

		while (acol || bcol) {
			if ((acol && Z_TYPE_P(aval) == IS_DOUBLE) || (bcol && Z_TYPE_P(bval) == IS_DOUBLE)) {
				if (acol) {
					Z_DVAL(tmpdval) = zval_get_double(aval);
				} else {
					Z_DVAL(tmpdval) = 0.0;
				}

				if (bcol) {
					Z_DVAL(tmpdval) -= zval_get_double(bval);
				}

				zend_hash_next_index_insert_new(Z_ARRVAL(tmprow), &tmpdval);
			} else {
				if (acol) {
					Z_LVAL(tmplval) = zval_get_long(aval);
				} else {
					Z_LVAL(tmplval) = 0;
				}

				if (bcol) {
					Z_LVAL(tmplval) -= zval_get_long(bval);
				}

				zend_hash_next_index_insert_new(Z_ARRVAL(tmprow), &tmplval);
			}

			if (acol)  aval = HT_NEXT_BUCKET_VAL_IND(a2, acol);
			if (bcol)  bval = HT_NEXT_BUCKET_VAL_IND(b2, bcol);
		}

		zend_hash_next_index_insert_new(Z_ARRVAL_P(return_value), &tmprow);

		if (arow)  aval = HT_NEXT_BUCKET_VAL_IND(a, arow);
		if (brow)  bval = HT_NEXT_BUCKET_VAL_IND(b, brow);
	}
}
/* }}} */


/* {{{ proto array mat_mult(array $a, array|float|int $b, [int $row = null])
   Multiplies the values of two 2D matrices or the values of a 2D matrix or row of the matrix with a scalar value. */
PHP_FUNCTION(mat_mult)
{
	int numargs = ZEND_NUM_ARGS();
	HashTable *a, *b;
	Bucket *arow = NULL, *acol;
	zval *zb, *zrownum, *aval, *aval2, *bval, *bval2, tmprow, tmplval, tmpdval, tmpval;
	size_t Arows, Acols = 0, Bcols, i, j, k, x;
	zend_bool usedoubles = 0;
	double tmpda, *dB, *dBptr, *dC;
	zend_long tmpzla, *zlB, *zlBptr, *zlC, rownum;

	ZEND_PARSE_PARAMETERS_START(2, 3)
		Z_PARAM_ARRAY_HT(a)
		Z_PARAM_ZVAL(zb)
		Z_PARAM_OPTIONAL
		Z_PARAM_ZVAL(zrownum)
	ZEND_PARSE_PARAMETERS_END();

	Arows = zend_array_count(a);

	array_init_size(return_value, Arows);

	if (Z_TYPE_P(zb) == IS_ARRAY) {
		/* Determine the number of columns in A and whether or not to use doubles. */
		ZEND_HASH_FOREACH_VAL_IND(a, aval) {
			if (EXPECTED(Z_TYPE_P(aval) == IS_ARRAY)) {
				x = 0;

				ZEND_HASH_FOREACH_VAL_IND(Z_ARRVAL_P(aval), aval2) {
					if (Z_TYPE_P(aval2) != IS_LONG)  usedoubles = 1;

					x++;
				} ZEND_HASH_FOREACH_END();

				if (Acols < x)  Acols = x;
			}
		} ZEND_HASH_FOREACH_END();

		b = Z_ARRVAL_P(zb);

		x = zend_array_count(b);
		if (Acols < x)  Acols = x;

		/* Determine the number of columns in B and whether or not to use doubles. */
		Bcols = 0;
		ZEND_HASH_FOREACH_VAL_IND(b, bval) {
			if (EXPECTED(Z_TYPE_P(bval) == IS_ARRAY)) {
				x = 0;

				ZEND_HASH_FOREACH_VAL_IND(Z_ARRVAL_P(bval), bval2) {
					if (Z_TYPE_P(bval2) != IS_LONG)  usedoubles = 1;

					x++;
				} ZEND_HASH_FOREACH_END();

				if (Bcols < x)  Bcols = x;
			}
		} ZEND_HASH_FOREACH_END();

		/* If a double value is in either A or B, then multiply/add using doubles.  Otherwise, multiply using zend_long. */
		if (usedoubles) {
			/* Allocate temporary buffers. */
			dB = (double *)emalloc(Acols * Bcols * sizeof(double));
			dC = (double *)emalloc(Bcols * sizeof(double));

			/* Clone matrix B into dB. */
			dBptr = dB;
			ZEND_HASH_FOREACH_VAL_IND(b, bval) {
				x = 0;

				if (EXPECTED(Z_TYPE_P(bval) == IS_ARRAY)) {
					ZEND_HASH_FOREACH_VAL_IND(Z_ARRVAL_P(bval), bval2) {
						*dBptr = zval_get_double(bval2);

						dBptr++;

						x++;
					} ZEND_HASH_FOREACH_END();
				}

				for (; x < Bcols; x++) {
					*dBptr = 0.0;

					dBptr++;
				}
			} ZEND_HASH_FOREACH_END();

			/* Multiply two virtually normalized matrices (A and B) together.  O(N^3). */
			aval = HT_NEXT_BUCKET_VAL_IND(a, arow);
			ZVAL_DOUBLE(&tmpdval, 0.0);
			for (i = 0; i < Arows; i++) {
				dBptr = dB;

				acol = NULL;
				if (arow && EXPECTED(Z_TYPE_P(aval) == IS_ARRAY)) {
					aval2 = HT_NEXT_BUCKET_VAL_IND(Z_ARRVAL_P(aval), acol);
				} else {
					aval2 = NULL;
				}

				if (EXPECTED(acol)) {
					tmpda = zval_get_double(aval2);
				} else {
					tmpda = 0.0;
				}

				for (j = 0; j < Bcols; j++) {
					dC[j] = tmpda * *dBptr;

					dBptr++;
				}

				for (k = 1; k < Acols; k++) {
					if (EXPECTED(acol)) {
						aval2 = HT_NEXT_BUCKET_VAL_IND(Z_ARRVAL_P(aval), acol);
					}

					if (EXPECTED(acol)) {
						tmpda = zval_get_double(aval2);
					} else {
						/* Skip adding 0.0's if current row is short. */
						break;
					}

					for (j = 0; j < Bcols; j++) {
						dC[j] += tmpda * *dBptr;

						dBptr++;
					}
				}

				/* Write next C row. */
				array_init_size(&tmprow, Bcols);

				for (j = 0; j < Bcols; j++) {
					Z_DVAL(tmpdval) = dC[j];

					zend_hash_next_index_insert_new(Z_ARRVAL(tmprow), &tmpdval);
				}

				zend_hash_next_index_insert_new(Z_ARRVAL_P(return_value), &tmprow);

				/* Bail out reasonably quickly if the global PHP runtime timeout has been exceeded. */
				if (EG(timed_out)) {
					arow = NULL;
				}

				if (arow)  aval = HT_NEXT_BUCKET_VAL_IND(a, arow);
			}

			efree(dB);
			efree(dC);
		} else {
			/* zend_long version. */

			/* Allocate temporary buffers. */
			zlB = (zend_long *)emalloc(Acols * Bcols * sizeof(zend_long));
			zlC = (zend_long *)emalloc(Bcols * sizeof(zend_long));

			/* Clone matrix B into zlB. */
			zlBptr = zlB;
			ZEND_HASH_FOREACH_VAL_IND(b, bval) {
				x = 0;

				if (EXPECTED(Z_TYPE_P(bval) == IS_ARRAY)) {
					ZEND_HASH_FOREACH_VAL_IND(Z_ARRVAL_P(bval), bval2) {
						*zlBptr = zval_get_long(bval2);

						zlBptr++;

						x++;
					} ZEND_HASH_FOREACH_END();
				}

				for (; x < Bcols; x++) {
					*zlBptr = 0;

					zlBptr++;
				}
			} ZEND_HASH_FOREACH_END();

			/* Multiply two virtually normalized matrices (A and B) together.  O(N^3). */
			aval = HT_NEXT_BUCKET_VAL_IND(a, arow);
			ZVAL_LONG(&tmplval, 0);
			for (i = 0; i < Arows; i++) {
				zlBptr = zlB;

				acol = NULL;
				if (arow && EXPECTED(Z_TYPE_P(aval) == IS_ARRAY)) {
					aval2 = HT_NEXT_BUCKET_VAL_IND(Z_ARRVAL_P(aval), acol);
				} else {
					aval2 = NULL;
				}

				if (EXPECTED(acol)) {
					tmpzla = zval_get_long(aval2);
				} else {
					tmpzla = 0;
				}

				for (j = 0; j < Bcols; j++) {
					zlC[j] = tmpzla * *zlBptr;

					zlBptr++;
				}

				for (k = 1; k < Acols; k++) {
					if (EXPECTED(acol)) {
						aval2 = HT_NEXT_BUCKET_VAL_IND(Z_ARRVAL_P(aval), acol);
					}

					if (EXPECTED(acol)) {
						tmpzla = zval_get_long(aval2);
					} else {
						/* Skip adding 0's if current row is short. */
						break;
					}

					for (j = 0; j < Bcols; j++) {
						zlC[j] += tmpzla * *zlBptr;

						zlBptr++;
					}
				}

				/* Write next C row. */
				array_init_size(&tmprow, Bcols);

				for (j = 0; j < Bcols; j++) {
					Z_LVAL(tmplval) = zlC[j];

					zend_hash_next_index_insert_new(Z_ARRVAL(tmprow), &tmplval);
				}

				zend_hash_next_index_insert_new(Z_ARRVAL_P(return_value), &tmprow);

				/* Bail out reasonably quickly if the global PHP runtime timeout has been exceeded. */
				if (EG(timed_out)) {
					arow = NULL;
				}

				if (arow)  aval = HT_NEXT_BUCKET_VAL_IND(a, arow);
			}

			efree(zlB);
			efree(zlC);
		}
	} else {
		/* Scalar multiply. */
		if (Z_TYPE_P(zb) != IS_DOUBLE && Z_TYPE_P(zb) != IS_LONG) {
			convert_to_double(zb);
		}

		if (Z_TYPE_P(zb) == IS_DOUBLE) {
			ZVAL_DOUBLE(&tmpdval, 0.0);

			if (numargs >= 3 && Z_TYPE_P(zrownum) != IS_NULL) {
				rownum = Z_LVAL_P(zrownum);
			} else {
				zrownum = NULL;

				rownum = -1;
			}

			ZEND_HASH_FOREACH_VAL_IND(a, aval) {
				if (zrownum && rownum) {
					/* Copy rows except for the specified row.  Keys are ignored. */
					ZVAL_COPY(&tmpval, aval);

					zend_hash_next_index_insert_new(Z_ARRVAL_P(return_value), &tmpval);
				} else if (Z_TYPE_P(aval) == IS_ARRAY) {
					/* Scalar multiply the array values. */
					array_init_size(&tmprow, zend_array_count(Z_ARRVAL_P(aval)));

					ZEND_HASH_FOREACH_VAL_IND(Z_ARRVAL_P(aval), aval2) {
						Z_DVAL(tmpdval) = zval_get_double(aval2);
						Z_DVAL(tmpdval) *= Z_DVAL_P(zb);

						zend_hash_next_index_insert_new(Z_ARRVAL(tmprow), &tmpdval);
					} ZEND_HASH_FOREACH_END();

					zend_hash_next_index_insert_new(Z_ARRVAL_P(return_value), &tmprow);
				} else {
					/* Row is not an array but attempt to multiply anyway. */
					Z_DVAL(tmpdval) = zval_get_double(aval);
					Z_DVAL(tmpdval) *= Z_DVAL_P(zb);

					zend_hash_next_index_insert_new(Z_ARRVAL_P(return_value), &tmpdval);
				}

				rownum--;
			} ZEND_HASH_FOREACH_END();
		} else {
			/* zend_long version. */
			ZVAL_LONG(&tmplval, 0);

			if (numargs >= 3 && Z_TYPE_P(zrownum) != IS_NULL) {
				rownum = Z_LVAL_P(zrownum);
			} else {
				zrownum = NULL;

				rownum = -1;
			}

			ZEND_HASH_FOREACH_VAL_IND(a, aval) {
				if (zrownum && rownum) {
					/* Copy rows except for the specified row.  Keys are ignored. */
					ZVAL_COPY(&tmpval, aval);

					zend_hash_next_index_insert_new(Z_ARRVAL_P(return_value), &tmpval);
				} else if (Z_TYPE_P(aval) == IS_ARRAY) {
					/* Scalar multiply the array values. */
					array_init_size(&tmprow, zend_array_count(Z_ARRVAL_P(aval)));

					ZEND_HASH_FOREACH_VAL_IND(Z_ARRVAL_P(aval), aval2) {
						Z_LVAL(tmplval) = zval_get_long(aval2);
						Z_LVAL(tmplval) *= Z_LVAL_P(zb);

						zend_hash_next_index_insert_new(Z_ARRVAL(tmprow), &tmplval);
					} ZEND_HASH_FOREACH_END();

					zend_hash_next_index_insert_new(Z_ARRVAL_P(return_value), &tmprow);
				} else {
					/* Row is not an array but attempt to multiply anyway. */
					Z_LVAL(tmplval) = zval_get_long(aval);
					Z_LVAL(tmplval) *= Z_LVAL_P(zb);

					zend_hash_next_index_insert_new(Z_ARRVAL_P(return_value), &tmplval);
				}

				rownum--;
			} ZEND_HASH_FOREACH_END();
		}
	}
}
/* }}} */

/* {{{ proto bool is_valid_utf8(string $value, [ bool $standard = false, int $combinelimit = 16 ])
   Finds whether the given value is a valid UTF-8 string. */
PHP_FUNCTION(is_valid_utf8)
{
	zval *value;
	unsigned char *strp;
	size_t y;
	uint32_t cp;
	zend_bool standard = 0, first = 1;
	zend_long numcombine = 0, combinelimit = 16;

	ZEND_PARSE_PARAMETERS_START(1, 3)
		Z_PARAM_ZVAL(value)
		Z_PARAM_OPTIONAL
		Z_PARAM_BOOL(standard)
		Z_PARAM_LONG(combinelimit)
	ZEND_PARSE_PARAMETERS_END();

	if (Z_TYPE_P(value) != IS_STRING) {
		RETURN_FALSE;
	}

	if (GC_FLAGS(Z_STR_P(value)) & IS_STR_VALID_UTF8) {
		RETURN_TRUE;
	}

	strp = (unsigned char *)Z_STRVAL_P(value);
	y = Z_STRLEN_P(value);

	while (y) {
		/* The official UTF-8 specification says 0x0000-0x007F are valid UTF-8. */
		/* However, given the nature of PHP being web-oriented, commonly displayed, non-control characters should not be allowed to be declared to be valid UTF-8 by default. */
		/* Too much is at stake to take on the risk.  Also only maliciously malformed strings or binary data are likely to contain control characters in the first place. */
		if ((!standard && ((*strp >= 0x20 && *strp <= 0x7E) || *strp == 0x09 || *strp == 0x0A || *strp == 0x0D)) || (standard && *strp <= 0x7F)) {
			strp++;
			y--;

			numcombine = 0;
		} else if (*strp < 0xC2 || *strp > 0xF4) {
			RETURN_FALSE;
		} else {
			if (y < 2 || (strp[1] & 0xC0) != 0x80) {
				RETURN_FALSE;
			}

			if (((*strp) & 0xE0) == 0xC0) {
				cp = ((((uint32_t)(*strp) & 0x1F) << 6) | ((uint32_t)strp[1] & 0x3F));

				/* 0x*FFFE and 0x*FFFF are reserved. */
				if ((cp & 0xFFFE) == 0xFFFE) {
					RETURN_FALSE;
				}

				/* First code point can't be a combining code point. */
				if (cp >= 0x0300 && cp <= 0x036F) {
					numcombine++;

					if (first || numcombine >= combinelimit) {
						RETURN_FALSE;
					}
				} else {
					numcombine = 0;
				}

				strp += 2;
				y -= 2;
			} else {
				if (y < 3 || (strp[2] & 0xC0) != 0x80) {
					RETURN_FALSE;
				}

				if (((*strp) & 0xF0) == 0xE0) {
					cp = ((((uint32_t)(*strp) & 0x0F) << 12) | (((uint32_t)strp[1] & 0x3F) << 6) | ((uint32_t)strp[2] & 0x3F));

					/* Handle overlong. */
					/* 0xD800-0xDFFF are for UTF-16 surrogate pairs.  Invalid code points. */
					/* 0xFDD0-0xFDEF are non-characters. */
					/* 0x*FFFE and 0x*FFFF are reserved. */
					if (cp <= 0x07FF || (cp >= 0xD800 && cp <= 0xDFFF) || (cp >= 0xFDD0 && cp <= 0xFDEF) || (cp & 0xFFFE) == 0xFFFE) {
						RETURN_FALSE;
					}

					/* First code point can't be a combining code point. */
					if ((cp >= 0x1DC0 && cp <= 0x1DFF) || (cp >= 0x20D0 && cp <= 0x20FF) || (cp >= 0xFE20 && cp <= 0xFE2F)) {
						numcombine++;

						if (first || numcombine >= combinelimit) {
							RETURN_FALSE;
						}
					} else {
						numcombine = 0;
					}

					strp += 3;
					y -= 3;
				} else {
					if (y < 4 || (strp[3] & 0xC0) != 0x80 || ((*strp) & 0xF8) != 0xF0) {
						RETURN_FALSE;
					}

					cp = ((((uint32_t)(*strp) & 0x07) << 18) | (((uint32_t)strp[1] & 0x3F) << 12) | (((uint32_t)strp[2] & 0x3F) << 6) | ((uint32_t)strp[3] & 0x3F));

					/* Handle overlong and max value. */
					/* 0x*FFFE and 0x*FFFF are reserved. */
					if (cp <= 0xFFFF || cp > 0x10FFFF || (cp & 0xFFFE) == 0xFFFE) {
						RETURN_FALSE;
					}

					strp += 4;
					y -= 4;

					numcombine = 0;
				}
			}
		}

		first = 0;
	}

	/* Cache the result using the awkward garbage collection flag that exists for PCRE. */
	if (!ZSTR_IS_INTERNED(Z_STR_P(value))) {
		GC_ADD_FLAGS(Z_STR_P(value), IS_STR_VALID_UTF8);
	}

	RETURN_TRUE;
}
/* }}} */

/* {{{ proto bool is_interned_string(mixed $value)
   Finds whether the given variable is an interned (immutable) string. */
PHP_FUNCTION(is_interned_string)
{
	zval *value;

	ZEND_PARSE_PARAMETERS_START(1, 1)
		Z_PARAM_ZVAL(value)
	ZEND_PARSE_PARAMETERS_END();

	if (Z_TYPE_P(value) == IS_STRING && ZSTR_IS_INTERNED(Z_STR_P(value))) {
		RETURN_TRUE;
	}

	RETURN_FALSE;
}
/* }}} */

/* {{{ proto int refcount(mixed &$value)
   Returns the userland internal reference count of a zval. */
PHP_FUNCTION(refcount)
{
	zval *value;
	zend_long num = 0;

	ZEND_PARSE_PARAMETERS_START(1, 1)
		Z_PARAM_ZVAL(value)
	ZEND_PARSE_PARAMETERS_END();

	/* Value is passed in by reference via reflection. */
	if (Z_ISREF_P(value))
	{
		/* Ignore both Zend engine's increment of the refcount and the temporary conversion to a reference. */
		num = GC_REFCOUNT(Z_REF_P(value)) - 2;
//php_printf("Ref:  %ld\n", num);

		value = Z_REFVAL_P(value);
	}

	/* Interned strings are a special case.  See zend_string_refcount(). */
	if (Z_TYPE_P(value) == IS_STRING && ZSTR_IS_INTERNED(Z_STR_P(value))) {
		RETURN_LONG(1);
	} else if (Z_REFCOUNTED_P(value) || Z_TYPE_P(value) == IS_ARRAY) {
		/* Other refcounted values. */
//php_printf("Value:  %ld\n", (zend_long)GC_REFCOUNT(Z_COUNTED_P(value)));
		num += GC_REFCOUNT(Z_COUNTED_P(value));
	} else {
		/* Non-refcounted values. */
		num++;
	}

	RETURN_LONG(num);
}
/* }}} */

/* {{{ proto bool is_reference(mixed &$value)
   Finds whether the type of a variable is a reference. */
PHP_FUNCTION(is_reference)
{
	zval *value;

	ZEND_PARSE_PARAMETERS_START(1, 1)
		Z_PARAM_ZVAL(value)
	ZEND_PARSE_PARAMETERS_END();

	/* Value is passed in by reference via reflection. */
	if (Z_ISREF_P(value) && GC_REFCOUNT(Z_REF_P(value)) > 2) {
		RETURN_TRUE;
	}

	RETURN_FALSE;
}
/* }}} */

/* {{{ proto bool is_equal_zval(mixed &$value, mixed &$value2, [ bool $deref = true ])
   Compares two raw zvals for equality but does not compare data for equality. */
PHP_FUNCTION(is_equal_zval)
{
	zval *value, *value2;
	zend_bool deref = 1;

	ZEND_PARSE_PARAMETERS_START(2, 3)
		Z_PARAM_ZVAL(value)
		Z_PARAM_ZVAL(value2)
		Z_PARAM_OPTIONAL
		Z_PARAM_BOOL(deref)
	ZEND_PARSE_PARAMETERS_END();

	/* Values are passed in by reference via reflection. */
	ZVAL_DEREF(value);
	ZVAL_DEREF(value2);

	if (value == value2) {
		RETURN_TRUE;
	}

	/* Dereferences and compares the data pointer. */
	if (deref) {
		if (Z_TYPE_P(value) != IS_LONG && Z_TYPE_P(value) != IS_DOUBLE && Z_TYPE_P(value2) != IS_LONG && Z_TYPE_P(value2) != IS_DOUBLE && Z_PTR_P(value) == Z_PTR_P(value2)) {
			RETURN_TRUE;
		}
	}

	RETURN_FALSE;
}
/* }}} */


/* {{{ PHP_RINIT_FUNCTION
 */
PHP_RINIT_FUNCTION(qolfuncs)
{
#if defined(ZTS) && defined(COMPILE_DL_QOLFUNCS)
	ZEND_TSRMLS_CACHE_UPDATE();
#endif

	return SUCCESS;
}
/* }}} */

/* {{{ PHP_MINFO_FUNCTION
 */
PHP_MINFO_FUNCTION(qolfuncs)
{
	php_info_print_table_start();
	php_info_print_table_header(2, "qolfuncs (quality of life improvement functions) support", "enabled");
	php_info_print_table_end();
}
/* }}} */

/* {{{ arginfo
 */
ZEND_BEGIN_ARG_INFO_EX(arginfo_str_splice, 0, 0, 2)
	ZEND_ARG_TYPE_INFO(1, dst, IS_STRING, 0)
	ZEND_ARG_INFO(0, dst_offset)
	ZEND_ARG_TYPE_INFO(0, dst_length, IS_LONG, 1)
	ZEND_ARG_INFO(0, src)
	ZEND_ARG_INFO(0, src_offset)
	ZEND_ARG_TYPE_INFO(0, src_length, IS_LONG, 1)
	ZEND_ARG_INFO(0, src_repeat)
	ZEND_ARG_INFO(0, shrink)
	ZEND_ARG_TYPE_INFO(0, dst_lastsize, IS_LONG, 1)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_str_realloc, 0, 0, 2)
	ZEND_ARG_TYPE_INFO(1, str, IS_STRING, 0)
	ZEND_ARG_INFO(0, size)
	ZEND_ARG_INFO(0, fast)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_explode_substr, 0, 0, 2)
	ZEND_ARG_INFO(0, separator)
	ZEND_ARG_INFO(0, str)
	ZEND_ARG_TYPE_INFO(0, limit, IS_LONG, 1)
	ZEND_ARG_INFO(0, str_offset)
	ZEND_ARG_TYPE_INFO(0, str_length, IS_LONG, 1)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_str_split_substr, 0, 0, 1)
	ZEND_ARG_INFO(0, str)
	ZEND_ARG_TYPE_INFO(0, split_length, IS_LONG, 1)
	ZEND_ARG_INFO(0, str_offset)
	ZEND_ARG_TYPE_INFO(0, str_length, IS_LONG, 1)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_fread_mem, 0, 0, 2)
	ZEND_ARG_INFO(0, fp)
	ZEND_ARG_TYPE_INFO(1, str, IS_STRING, 0)
	ZEND_ARG_INFO(0, str_offset)
	ZEND_ARG_TYPE_INFO(0, length, IS_LONG, 1)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_fwrite_substr, 0, 0, 2)
	ZEND_ARG_INFO(0, fp)
	ZEND_ARG_INFO(0, str)
	ZEND_ARG_INFO(0, str_offset)
	ZEND_ARG_TYPE_INFO(0, str_length, IS_LONG, 1)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_hash_substr, 0, 0, 2)
	ZEND_ARG_INFO(0, algo)
	ZEND_ARG_INFO(0, data)
	ZEND_ARG_INFO(0, raw_output)
	ZEND_ARG_INFO(0, data_offset)
	ZEND_ARG_TYPE_INFO(0, data_length, IS_LONG, 1)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_hash_hmac_substr, 0, 0, 3)
	ZEND_ARG_INFO(0, algo)
	ZEND_ARG_INFO(0, data)
	ZEND_ARG_INFO(0, key)
	ZEND_ARG_INFO(0, raw_output)
	ZEND_ARG_INFO(0, data_offset)
	ZEND_ARG_TYPE_INFO(0, data_length, IS_LONG, 1)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO(arginfo_imageexportpixels, 0)
	ZEND_ARG_INFO(0, im)
	ZEND_ARG_INFO(0, x)
	ZEND_ARG_INFO(0, y)
	ZEND_ARG_INFO(0, width)
	ZEND_ARG_INFO(0, height)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO(arginfo_imageimportpixels, 0)
	ZEND_ARG_INFO(0, im)
	ZEND_ARG_INFO(0, x)
	ZEND_ARG_INFO(0, y)
	ZEND_ARG_INFO(0, colors)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO(arginfo_mat_add, 0)
	ZEND_ARG_INFO(0, a)
	ZEND_ARG_INFO(0, b)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO(arginfo_mat_sub, 0)
	ZEND_ARG_INFO(0, a)
	ZEND_ARG_INFO(0, b)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_mat_mult, 0, 0, 2)
	ZEND_ARG_INFO(0, a)
	ZEND_ARG_INFO(0, b)
	ZEND_ARG_TYPE_INFO(0, row, IS_LONG, 1)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_is_valid_utf8, 0, 0, 1)
	ZEND_ARG_INFO(0, value)
	ZEND_ARG_INFO(0, standard)
	ZEND_ARG_INFO(0, combinelimit)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO(arginfo_is_interned_string, 0)
	ZEND_ARG_INFO(0, value)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO(arginfo_refcount, 0)
	ZEND_ARG_INFO(1, value)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO(arginfo_is_reference, 0)
	ZEND_ARG_INFO(1, value)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_is_equal_zval, 0, 0, 2)
	ZEND_ARG_INFO(1, value)
	ZEND_ARG_INFO(1, value2)
	ZEND_ARG_INFO(0, deref)
ZEND_END_ARG_INFO()
/* }}} */

/* {{{ qolfuncs_functions[]
 */
static const zend_function_entry qolfuncs_functions[] = {
	PHP_FE(str_splice, arginfo_str_splice)
	PHP_FE(str_realloc, arginfo_str_realloc)
	PHP_FE(explode_substr, arginfo_explode_substr)
	PHP_FE(str_split_substr, arginfo_str_split_substr)
	PHP_FE(fread_mem, arginfo_fread_mem)
	PHP_FE(fwrite_substr, arginfo_fwrite_substr)
	PHP_FE(hash_substr, arginfo_hash_substr)
	PHP_FE(hash_hmac_substr, arginfo_hash_hmac_substr)

#if defined(HAVE_LIBGD) || defined(HAVE_GD_BUNDLED)
	PHP_FE(imageexportpixels, arginfo_imageexportpixels)
	PHP_FE(imageimportpixels, arginfo_imageimportpixels)
#endif

	PHP_FE(mat_add, arginfo_mat_add)
	PHP_FE(mat_sub, arginfo_mat_sub)
	PHP_FE(mat_mult, arginfo_mat_mult)
	PHP_FE(is_valid_utf8, arginfo_is_valid_utf8)
	PHP_FE(is_interned_string, arginfo_is_interned_string)
	PHP_FE(refcount, arginfo_refcount)
	PHP_FE(is_reference, arginfo_is_reference)
	PHP_FE(is_equal_zval, arginfo_is_equal_zval)
	PHP_FE_END
};
/* }}} */

/* {{{ qolfuncs_module_entry
 */
zend_module_entry qolfuncs_module_entry = {
	STANDARD_MODULE_HEADER,
	"qolfuncs",					/* Extension name */
	qolfuncs_functions,			/* zend_function_entry */
	NULL,							/* PHP_MINIT - Module initialization */
	NULL,							/* PHP_MSHUTDOWN - Module shutdown */
	PHP_RINIT(qolfuncs),			/* PHP_RINIT - Request initialization */
	NULL,							/* PHP_RSHUTDOWN - Request shutdown */
	PHP_MINFO(qolfuncs),			/* PHP_MINFO - Module info */
	PHP_QOLFUNCS_VERSION,		/* Version */
	STANDARD_MODULE_PROPERTIES
};
/* }}} */

#ifdef COMPILE_DL_QOLFUNCS
# ifdef ZTS
ZEND_TSRMLS_CACHE_DEFINE()
# endif
ZEND_GET_MODULE(qolfuncs)
#endif
