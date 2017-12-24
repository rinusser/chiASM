/**
 * Extension for PHP: adds inline Assembly support
 *
 * \author Richard Nusser
 * \copyright 2017 Richard Nusser
 * \license GPLv3 (see http://www.gnu.org/licenses/)
 * \link https://github.com/rinusser/chiASM
 */

#ifdef HAVE_CONFIG_H
#include "config.h"
#endif

#include "php.h"
#include "php_ini.h"
#include "ext/standard/info.h"
#include "php_chiasm.h"


const char *register_names[]={"rax","rbx","rcx","rdx","rbp","rsp","rsi","rdi","r8","r9","r10","r11","r12","r13","r14","r15"};


//XXX could move these to INI settings
static char *filename_obj="/tmp/asm.generated.o";
static char *filename_so="/tmp/asm.generated.so";
static char *filename_asm="/tmp/asm.generated.asm";
static char *fn_asm="asm_generated";


//TODO: add xmm/ymm/zmm: would need to check cpuid for amount and width (thus exact names) of registers
static unsigned int parse_registers(const char *str)
{
  unsigned int rv=0;
  int tc;

  for(tc=0;tc<16;tc++)
  {
    if(!strstr(str,register_names[tc]))
      continue;
    rv|=1<<tc;
  }

  return rv;
}


static char *do_register_op(unsigned int field, int start, int end, int delta, const char *op)
{
  int high=start>end?start:end;
  int low=start<end?start:end;
  int tc;
  char *rv=emalloc(165);
  memset(rv,0,165);

  for(tc=start;tc>=low&&tc<=high;tc+=delta)
  {
    if(!(field&(1<<tc)))
      continue;
    strcat(rv,op);
    strcat(rv,register_names[tc]);
    strcat(rv,"\n");
  }

  return rv;
}

static char *push_registers(unsigned int field)
{
  return do_register_op(field,0,15,1,"push ");
}

static char *pop_registers(unsigned int field)
{
  return do_register_op(field,15,0,-1,"pop ");
}


/* {{{ proto mixed asm(string $code, ?array $inputs=NULL, ?array $outputs=NULL)
   Assemble, link and run code, pass inputs and references to outputs
   Returns reason string on error */
PHP_FUNCTION(asm)
{
  char *code=NULL;
  size_t code_len=0;
  HashTable *inputs=NULL;
  HashTable *outputs=NULL;
  void *lib;
  zend_string *str;
  char *err;
  void (*func)(INTERNAL_FUNCTION_PARAMETERS);
  FILE *asmfile;
  char *buf;
  int reg_mask;
  char *inout_movs, *inout_mov;
  int tc;
  int offset;

  if(zend_parse_parameters(ZEND_NUM_ARGS(),"s|hh",&code,&code_len,&inputs,&outputs)!=SUCCESS)
    RETURN_FALSE;

  tc=0;
  if(inputs)
    tc+=inputs->nNumUsed;
  if(outputs)
    tc+=outputs->nNumUsed;
  if(tc>13)
  {
    str=strpprintf(0,"ERROR: cannot handle more than 13 inputs and outputs total, got %d",tc);
    RETURN_STR(str);
  }

  asmfile=fopen(filename_asm,"w");
  if(!asmfile)
    RETURN_FALSE;

  buf=emalloc(strlen(code)+200+165*2+13*30); //200 bytes for template, 2*165 bytes for push/pop ops, up to 13 i/o register movs
  reg_mask=parse_registers(code);

  inout_movs=emalloc(30*13);
  inout_mov=emalloc(30);

  //zero string buffers, just in case of slight offset errors
  memset(inout_movs,0,30*13);
  memset(inout_mov,0,30);

  //copy inputs to registers starting at rax, skip rsp (preserve stack in asm code), rdi and rsi (Zend parameter passthrough)
  //zval type info gets lost: at least NULLs and booleans can't be passed this way
  if(inputs)
  {
    for(tc=0;tc<inputs->nNumUsed;tc++)
    {
      offset=tc;
      if(offset>4)
        offset+=3;
      sprintf(inout_mov,"mov %s,0x%016lx\n",register_names[offset],inputs->arData[tc].val.value);
      strcat(inout_movs,inout_mov);
      reg_mask|=1<<offset;
    }
  }

  //copy output references, start at r15 and skip same registers as with inputs
  if(outputs)
  {
    for(tc=0;tc<outputs->nNumUsed;tc++)
    {
      offset=15-tc;
      if(offset<8)
        offset-=3;
      if(!Z_ISREF(outputs->arData[tc].val))
      {
        str=strpprintf(0,"ERROR: outputs must be references; output #%d isn't",tc+1);
        RETURN_STR(str);
      }
      sprintf(inout_mov,"mov %s,0x%016lx\n",register_names[offset],outputs->arData[tc].val.value.ptr+8);
      strcat(inout_movs,inout_mov);
      reg_mask|=1<<offset;
    }
  }


  sprintf(buf,"bits 64\n\
section .text\n\
  global %s\n\
%s:\n\
  mov qword [rsi],%d\n\
  mov qword [rsi+8],4\n\
%s\n\
%s\n\
  %s\n\
%s\n\
  ret\n",fn_asm,fn_asm,reg_mask,push_registers(reg_mask),inout_movs,code,pop_registers(reg_mask));

  fwrite(buf,1,strlen(buf),asmfile); //TODO: check return value

  fclose(asmfile);

  //assemble to .o
  sprintf(buf,"nasm -f elf64 -o %s %s",filename_obj,filename_asm);
  if(system(buf))
    RETURN_FALSE;

  //link to .so
  sprintf(buf,"ld -shared -S -melf_x86_64 -o %s %s",filename_so,filename_obj);
  if(system(buf))
    RETURN_FALSE;

  //open .so and look up generated function symbol
  if(!(lib=dlopen(filename_so,RTLD_NOW)) || !(func=dlsym(lib,fn_asm)))
  {
    str=strpprintf(0,"ERROR: %s",dlerror());
    RETURN_STR(str);
  }

  //call generated function
  func(INTERNAL_FUNCTION_PARAM_PASSTHRU);

  //remove .so from memory, otherwise next call to asm() would reuse previously generated code
  //if you get an "unknown file or directory" error here, then there's probably a buffer overflow somewhere above
  dlclose(lib);
}
/* }}} */


/* {{{ PHP_MINIT_FUNCTION
 */
PHP_MINIT_FUNCTION(chiasm)
{
	return SUCCESS;
}
/* }}} */

/* {{{ PHP_MSHUTDOWN_FUNCTION
 */
PHP_MSHUTDOWN_FUNCTION(chiasm)
{
	return SUCCESS;
}
/* }}} */

/* {{{ PHP_RINIT_FUNCTION
 */
PHP_RINIT_FUNCTION(chiasm)
{
#if defined(COMPILE_DL_CHIASM) && defined(ZTS)
	ZEND_TSRMLS_CACHE_UPDATE();
#endif
	return SUCCESS;
}
/* }}} */

/* {{{ PHP_RSHUTDOWN_FUNCTION
 */
PHP_RSHUTDOWN_FUNCTION(chiasm)
{
	return SUCCESS;
}
/* }}} */

/* {{{ PHP_MINFO_FUNCTION
 */
PHP_MINFO_FUNCTION(chiasm)
{
	php_info_print_table_start();
	php_info_print_table_header(2, "chiASM support", "enabled");
	php_info_print_table_end();
}
/* }}} */

ZEND_BEGIN_ARG_INFO(arginfo_asm, 0) //XXX this should list 2 more optional arguments, right?
  ZEND_ARG_TYPE_INFO(0,"code",IS_STRING,0)
ZEND_END_ARG_INFO()


/* {{{ chiasm_functions[]
 */
const zend_function_entry chiasm_functions[] = {
  PHP_FE(asm, arginfo_asm)
  PHP_FE_END
};
/* }}} */

/* {{{ chiasm_module_entry
 */
zend_module_entry chiasm_module_entry = {
	STANDARD_MODULE_HEADER,
	"chiASM",
	chiasm_functions,
	PHP_MINIT(chiasm),
	PHP_MSHUTDOWN(chiasm),
	PHP_RINIT(chiasm),
	PHP_RSHUTDOWN(chiasm),
	PHP_MINFO(chiasm),
	PHP_CHIASM_VERSION,
	STANDARD_MODULE_PROPERTIES
};
/* }}} */

#ifdef COMPILE_DL_CHIASM
#ifdef ZTS
ZEND_TSRMLS_CACHE_DEFINE()
#endif
ZEND_GET_MODULE(chiasm)
#endif
