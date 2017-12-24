/**
 * Extension header file for chiASM PHP extension
 *
 * \author Richard Nusser
 * \copyright 2017 Richard Nusser
 * \license GPLv3 (see http://www.gnu.org/licenses/)
 * \link https://github.com/rinusser/chiASM
 */

#ifndef PHP_CHIASM_H
#define PHP_CHIASM_H

extern zend_module_entry chiasm_module_entry;
#define phpext_chiasm_ptr &chiasm_module_entry

#define PHP_CHIASM_VERSION "0.06"

#ifdef PHP_WIN32
#	define PHP_CHIASM_API __declspec(dllexport)
#elif defined(__GNUC__) && __GNUC__ >= 4
#	define PHP_CHIASM_API __attribute__ ((visibility("default")))
#else
#	define PHP_CHIASM_API
#endif

#ifdef ZTS
#include "TSRM.h"
#endif

#define CHIASM_G(v) ZEND_MODULE_GLOBALS_ACCESSOR(chiasm, v)

#if defined(ZTS) && defined(COMPILE_DL_CHIASM)
ZEND_TSRMLS_CACHE_EXTERN()
#endif

#endif	/* PHP_CHIASM_H */
