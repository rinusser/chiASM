--TEST--
Return value passthrough
--SKIPIF--
<?php if(!extension_loaded('chiasm')) print 'skip'; ?>
--FILE--
<?php
declare(strict_types=1);
/**
 * The inline assembly should have access to PHP's return zval via [rsi]
 */

$result=asm('mov rax,0xbfe0000000000000
             mov [rsi],rax
             mov qword [rsi+8],5');
echo 'result: ',var_export($result,true);

?>
--EXPECT--
result: -0.5
