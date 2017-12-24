dnl config.m4 for extension chiASM

PHP_ARG_ENABLE(chiasm, whether to enable chiASM support,
[  --enable-chiasm              Enable chiASM support])

if test "$PHP_CHIASM" != "no"; then
  PHP_NEW_EXTENSION(chiasm, chiasm.c, $ext_shared,, -DZEND_ENABLE_STATIC_TSRMLS_CACHE=1)
fi
