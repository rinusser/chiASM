--TEST--
Using input/output registers
--SKIPIF--
<?php if(!extension_loaded('chiasm')) print 'skip'; ?>
--FILE--
<?php
/**
 * The Assembly code should be able to read and write PHP variables used as input/output registers.
 */

$in1=-6;
$in2=0.5;
$out1=1.2;
$out2=5;
$out3=false;

asm("mov r8,[r14]
     shl r8,3
     add r8,rax
     mov [r15],r8         ;out1=out2<<3+in1
     mov qword [r15+8],4  ;set out1's type to int/long
     mov [r13],rbx
     movlpd xmm0,[r13]
     movlpd xmm1,[r13]
     mulsd xmm0,xmm1
     movlpd qword [r14],xmm0  ;out2=in2*in2
     mov qword [r14+8],5      ;set out2's type to float/double",
    [$in1,$in2],
    [&$out1,&$out2,&$out3]);

echo 'out1=',var_export($out1,true),"\n"; //5<<3-6=34
echo 'out2=',var_export($out2,true),"\n"; //0.5*0.5=0.25
echo 'out3=',var_export($out3,true),"\n"; //unchanged, false
?>
--EXPECT--
out1=34
out2=0.25
out3=false
