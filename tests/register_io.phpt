--TEST--
Register input/output assignments
--SKIPIF--
<?php if(!extension_loaded('chiasm')) print 'skip'; ?>
--FILE--
<?php
/**
 * Up to 13 inputs/outputs should be assigned to GPRs, skipping rsp, rdi and rsi
 */

asm('',
    range(1,13));
$generated_code=file_get_contents('/tmp/asm.generated.asm');
$matches=NULL;
preg_match_all('/mov ([a-z0-9]+),/',$generated_code,$matches);
echo 'inputs: ',implode(',',$matches[1]),"\n";

$x=NULL;
asm('',
    [],
    [&$x,&$x,&$x,&$x,&$x,&$x,&$x,&$x,&$x,&$x,&$x,&$x,&$x]);
$generated_code=file_get_contents('/tmp/asm.generated.asm');
$matches=NULL;
preg_match_all('/mov ([a-z0-9]+),/',$generated_code,$matches);
echo 'outputs: ',implode(',',$matches[1]),"\n";

asm('',
    [1,2,3],
    [&$x,&$x]);
$generated_code=file_get_contents('/tmp/asm.generated.asm');
$matches=NULL;
preg_match_all('/mov ([a-z0-9]+),/',$generated_code,$matches);
echo 'inputs and outputs: ',implode(',',$matches[1]),"\n";

echo asm('',range(0,13)),"\n";

?>
--EXPECT--
inputs: rax,rbx,rcx,rdx,rbp,r8,r9,r10,r11,r12,r13,r14,r15
outputs: r15,r14,r13,r12,r11,r10,r9,r8,rbp,rdx,rcx,rbx,rax
inputs and outputs: rax,rbx,rcx,r15,r14
ERROR: cannot handle more than 13 inputs and outputs total, got 14
