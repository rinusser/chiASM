# Synopsis

An extension for PHP adding inline Assembly support.

The sources are hosted on [GitHub](https://github.com/rinusser/chiASM).


# What the..?

Seriously. This extension for PHP adds an asm() function that will assemble, link and run Assembly code from within a
PHP script.

What for, you ask? Well I'm glad you did! Here's a list of good reasons to use this feature:

* uhh..

(end of list)

Actually I was writing another piece of Assembly code ([chASM](https://github.com/rinusser/chASM), just about as
practical as this) and the idea of adding inline Assembly to PHP came up repeatedly. The thought made me laugh, so
eventually I gave in and just implemented it.

You won't get any performance benefit from small chunks of Assembly code (quite the opposite, actually) because the
overhead is significant: this extension uses just-in-time assembly.

For the record, I haven't seen the words "just-in-time assembly" used anywhere in 20+ years of software development.
If the phrase is up for grabs I'm calling dibs.


# Requirements

* a 64-bit x86 processor (physical or virtual)
* a 64-bit operating system using the System V calling convention, e.g. Linux (tested with Ubuntu 17.04)
* a 64-bit version of PHP 7 (tested with 7.0.18)
* NASM (tested with 2.12.02)
* build tools for PHP


# Installation

### Prerequisites

You'll need to extract the PHP sources somewhere, e.g. /usr/src/php7/. All paths in the installation instructions below
are relative to this sources root.

It's recommended you successfully build the PHP sources before adding this extension. This will make any troubleshooting
faster and easier.

You need to copy this code into a new directory, ext/chiasm/.

### Building

You'll need to tell PHP to look for and include the new extension:

    $ buildconf -force
    $ ./configure --enable-chiasm

If `configure` reports that the `--enable-chiasm` parameter is invalid then you might not have extracted the extension
to ext/chiasm/. Once `configure` works you won't have to run `buildconf` again (unless you e.g. rename the extension).

You can now build PHP with the new module.

    $ make

If you haven't built this PHP version before this might take a few minutes, otherwise it should finish quickly.
Once `make` is done the new binaries are ready.

### Verification

You can run the .phpt tests (`make test TESTS=ext/chiasm`, see "Tests" below) to make sure everything works as expected.
There's a small demonstration script, you can run it with:

    $ sapi/cli/php -f ext/chiasm/demo.php

As of writing this it should output this:

    result should be true: true
    out1 should be 5<<3-6=34: 34
    out2 should be 0.5*0.5=0.25: 0.25
    out3 should be false: false

Check out demo.php for how this is done.


# Usage

### Basics

If the extension is loaded there's a new PHP function `asm`:

    mixed asm(string $code, ?array $inputs=NULL, ?array $outputs=NULL)

`$code` is the Assembly code to execute, in Intel syntax (NASM is used to assemble it).

`$inputs` is an optional array of input variables. They're passed by value into the Assembly code. Note that booleans
and NULLs are encoded in a ZVAL's type field, so they currently can't be used as inputs.

`$outputs` is an optional array of output variables. They're passed by reference into the Assembly code.

Some internal errors will make asm() return a string with the error message.

### Inputs/Outputs

Variables, regardless of whether they're by value or by reference, are passed in these 13 GPRs:

    rax, rbx, rcx, rdx, rbp, r8, r9, r10, r11, r12, r13, r14, r15

These registers are used left to right for input variables (rax is the first input, rbx the second and so on) and right
to left for output variables (r15 being the first output, r14 the second and so on). If more than 13 inputs and outputs
total are passed to the asm() function it will error out.

The other 3 GPRs (rsp, rsi and rdi) have special meanings:

`rsi` points to the return value ZVAL.

`rdi` points to the zend\_execute\_data context.

`rsp` needs to be preseved, previous register values are saved/restored on the stack.

### Volatile Registers

The asm() function will check your Assembly code for all used GPRs. This list of GPRs, in addition to all input and
output GPRs will be pushed to the stack before your code, and popped from the stack after your code.

Currently no other registers are be preserved.

### Stack Alignment

Currently there's no automatic stack alignment. Whether rsp is aligned to e.g. 128 bits (for SSE stack moves) depends
on the number of registers pushed to the stack. The internally generated code preamble is deterministic and the
extension code itself gets invoked with rsp aligned, so once you fix the rsp alignment it will continue to work (with
the exact same PHP binary).

Keep in mind the rsp register modification to align the stack pointer will mark the rsp register as volatile, thus
triggering an additional stack move before your code is called if you hadn't already used rsp before. It's recommended
to include "rsp" in the code first (e.g. in a comment line), then fix the alignment.

### Example

The code:

    <?php
    $code=<<<EOS
      mov qword [rsi+8],3   ;set return type to true
    EOS;
    var_dump(asm($code));

will output:

    bool(true)


# Tests

Contains .phpt tests. Execute them in your PHP sources root by either running the entire test suite (`make test`, this
will take a while), or just running this extension's tests:

    $ make test TESTS=ext/chiasm

All tests should pass.


# Legal

### Disclaimer

Do not use this PHP extension in production or another environment where user input, transmitted files or any other
kind of data not 100% under the developer's control finds its way to the asm() function.

Abusing this extension can crash PHP, your web server, be used as an attack vector in privilege escalations, compromise
your network, crash your entire system and potentially physically damage your hardware.

USE AT YOUR OWN RISK!

### Copyright

Copyright (C) 2017 Richard Nusser

### License

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <http://www.gnu.org/licenses/>.

