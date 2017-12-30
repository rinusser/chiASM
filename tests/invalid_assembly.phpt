--TEST--
Invalid Assembly
--SKIPIF--
<?php if(!extension_loaded('chiasm')) print 'skip'; ?>
--FILE--
<?php
/**
 * passing invalid Assembly code should throw an error
 */

echo var_export(asm('invalid statement'),true);
?>
--EXPECT--
/tmp/asm.generated.asm:19: error: parser: instruction expected
false
