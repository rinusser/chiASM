--TEST--
Register bitfield
--SKIPIF--
<?php if(!extension_loaded('chiasm')) print 'skip'; ?>
--FILE--
<?php
/**
 * By default the asm() function returns the internal bitfield of registers to preserve on the stack. The list of
 * registers should be determined from the passed Assembly, so by using a select bunch of registers in e.g. a comment
 * line we can check whether the internal bitfield is calculated as expected.
 */

$cases=[';rax,rbx,rcx,rdx,rbp,rsp,rdi,rsi,r8,r9,r10,r11,r12,r13,r14,r15',
        ';rax,        rdx,        rdi,rsi,  ,r9,r10,                   ',
        ';   ,rbx,rcx     rbp,rsp,       ,r8,      ,r11,r12,r13,r14,r15'];

foreach($cases as $case)
{
  $result=asm($case);
  if(is_int($result))
    printf("0x%04x\n",$result);
  else
    echo var_export($result,true),"\n";
}
?>
--EXPECT--
0xffff
0x06c9
0xf936
