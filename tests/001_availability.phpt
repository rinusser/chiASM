--TEST--
Check for chiasm presence
--SKIPIF--
<?php if (!extension_loaded("chiasm")) print "skip"; ?>
--FILE--
<?php
echo "chiasm extension is available";
?>
--EXPECT--
chiasm extension is available
