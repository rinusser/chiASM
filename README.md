# Synopsis

An extension for PHP adding inline Assembly support.

The sources are hosted on [GitHub](https://github.com/rinusser/chiASM).


# Requirements

* a 64-bit x86 processor (physical or virtual)
* a 64-bit operating system using the System V calling convention, e.g. Linux (tested with Ubuntu 17.04)
* a 64-bit version of PHP 7 (tested with 7.0.18)
* NASM (tested with 2.12.02)
* build tools for PHP


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
your network, crash your entire system and potentially physically damage your CPU.

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

