/* qolfuncs extension for PHP */

#ifndef PHP_QOLFUNCS_H
# define PHP_QOLFUNCS_H

extern zend_module_entry qolfuncs_module_entry;
# define phpext_qolfuncs_ptr &qolfuncs_module_entry

# define PHP_QOLFUNCS_VERSION "0.1.0"

PHP_FUNCTION(str_splice);
PHP_FUNCTION(str_realloc);
PHP_FUNCTION(explode_substr);
PHP_FUNCTION(str_split_substr);
PHP_FUNCTION(fread_mem);
PHP_FUNCTION(fwrite_substr);
PHP_FUNCTION(hash_substr);
PHP_FUNCTION(imageexportpixels);
PHP_FUNCTION(imageimportpixels);
PHP_FUNCTION(mat_add);
PHP_FUNCTION(mat_sub);
PHP_FUNCTION(mat_mult);
PHP_FUNCTION(is_valid_utf8);
PHP_FUNCTION(is_interned_string);
PHP_FUNCTION(refcount);
PHP_FUNCTION(is_reference);
PHP_FUNCTION(is_equal_zval);

# if defined(ZTS) && defined(COMPILE_DL_QOLFUNCS)
ZEND_TSRMLS_CACHE_EXTERN()
# endif

#endif	/* PHP_QOLFUNCS_H */
