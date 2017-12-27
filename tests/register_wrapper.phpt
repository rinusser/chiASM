--TEST--
Register save/restore wrapper
--SKIPIF--
<?php if(!extension_loaded('chiasm')) print 'skip'; ?>
--FILE--
<?php
/**
 * Used registers should be pushed and popped.
 * Usage is either direct mentioning, or assignment for input/output purposes
 */

$output=NULL;
asm(";r9 r10
     mov rdx,123
     mov rcx,0x8080",
     [1],
     [&$output]);
$generated_code=file_get_contents('/tmp/asm.generated.asm');
$matches=NULL;
preg_match_all('/push ([a-z0-9]+)/',$generated_code,$matches);
echo 'pushes: ',implode(',',$matches[1]),"\n";
preg_match_all('/pop ([a-z0-9]+)/',$generated_code,$matches);
echo 'pops: ',implode(',',$matches[1]),"\n";
?>
--EXPECT--
pushes: rax,rcx,rdx,r9,r10,r15
pops: r15,r10,r9,rdx,rcx,rax
