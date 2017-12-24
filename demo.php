<?php
declare(strict_types=1);
/**
 * Demonstration file for chiASM extension.
 *
 * requires PHP version 7.0+
 * @author Richard Nusser
 * @copyright 2017 Richard Nusser
 * @license GPLv3 (see http://www.gnu.org/licenses/)
 * @link https://github.com/rinusser/chiASM
 */

$module='chiASM';

if(!extension_loaded($module))
  dl('chiasm.'.PHP_SHLIB_SUFFIX);

if(!extension_loaded($module))
{
  echo "Module $module is not compiled into PHP\n";
  return;
}


$in1=-6;      //passed by value in rax
$in2=0.5;     //       -"-         rbx

$out1=1.2;    //passed by ref in r15
$out2=5;      //       -"-       r14
$out3=false;  //       -"-       r13

$code=<<<EOS
  mov r8,[r14]
  shl r8,3
  add r8,rax
  mov [r15],r8         ;out1=out2<<3+in1
  mov qword [r15+8],4  ;set out1's type to int/long
  mov [r13],rbx        ;abusing out3's unused value field, could use stack instead
  movlpd xmm0,[r13]
  movlpd xmm1,[r13]
  mulsd xmm0,xmm1
  movlpd qword [r14],xmm0  ;out2=in2*in2
  mov qword [r14+8],5      ;set out2's type to float/double
  mov qword [rsi+8],3      ;return true
EOS;

$result=asm($code,[$in1,$in2],[&$out1,&$out2,&$out3]);

foreach(['result'=>'true','out1'=>'5<<3-6=34','out2'=>'0.5*0.5=0.25','out3'=>'false'] as $k=>$v)
  echo $k,' should be ',$v,': ',var_export($$k,true),"\n";
