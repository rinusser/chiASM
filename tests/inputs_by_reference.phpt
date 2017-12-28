--TEST--
Passing input registers by reference
--SKIPIF--
<?php if(!extension_loaded('chiasm')) print 'skip'; ?>
--FILE--
<?php
/**
 * The input register list should accept and forward references
 */

$in1=246;
$in2=123;

$result=asm("mov r8,[rax]         ;remember in1's value
             mov r9,[rax+8]       ;remember in1's type
             mov qword [rax+8],2  ;set in1's type to false
             add r8,rbx           ;add in2 to in1's value
             mov rbx,999          ;mangle in2's copy
             mov [rsi],r8         ;set return value to in1+in2
             mov [rsi+8],r9       ;set return type to in1's",
            [&$in1,$in2]);

foreach(['result','in1','in2'] as $var)
  echo $var,'=',var_export($$var,true),"\n";
?>
--EXPECT--
result=369
in1=false
in2=123
