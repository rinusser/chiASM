--TEST--
CPUID execution
--SKIPIF--
<?php if(!extension_loaded('chiasm')) print 'skip'; ?>
--FILE--
<?php
/**
 * since we're requiring a 64 bit x86 CPU it should support the CPUID instruction and return at least a few flags set
 */

$result=asm(';rbx rcx rdx - need to mark these as volatile: CPUID will change them
             mov rax,1
             cpuid
             mov [rsi],rax');
if(is_int($result) && $result>0)
  echo 'CPUID seems to be working';
else
  echo 'ERROR, got ',var_export($result,true);
?>
--EXPECT--
CPUID seems to be working
