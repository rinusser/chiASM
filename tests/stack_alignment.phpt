--TEST--
Automatic stack alignment
--SKIPIF--
<?php if(!extension_loaded('chiasm')) print 'skip'; ?>
--FILE--
<?php
declare(strict_types=1);
/**
 * The stack pointer should be aligned to 128 bits at the first code line, regardless of how many registers are
 * preserved. This tests works by asserting there is no "Segmentation fault (core dumped)" or similar in the output.
 */

$registers=['r8','r9','r10','r11'];
foreach(range(0,4) as $count)
{
  echo "\n** $count **";
  $comment=implode(' ',array_slice($registers,0,$count));
  if($comment)
    $comment=';'.$comment;
  $code="$comment
         movdqa [rsp-16],xmm0
         mov [rsi],rsp
         mov qword [rsi+8],4";
  asm($code);
}
?>
--EXPECT--
** 0 **
** 1 **
** 2 **
** 3 **
** 4 **
