dnl $Id$
dnl config.m4 for extension qolfuncs

PHP_ARG_ENABLE(qolfuncs, [whether to enable quality of life improvement functions support],
  [AS_HELP_STRING([--enable-qolfuncs], [Enable quality of life improvement functions support])], [no])

if test "$PHP_QOLFUNCS" != "no"; then
  dnl # Finish defining basic extension support.
  AC_DEFINE(HAVE_QOLFUNCS, 1, [Whether you have quality of life improvement functions support])
  PHP_NEW_EXTENSION(qolfuncs, qolfuncs.c, $ext_shared)
fi
