# General

Please bear in mind that this extension for PHP is not intended for serious use: while I'm generally open to ideas for
new features I most likely won't turn this into enterprise-grade software.

If you do find bugs, issues in the documentation, or think of features you consider useful or fun please let me know.


# Submitting GitHub Issues

### Bugs

If you find a bug (that's not caused by invalid Assembly code) please create a ticket with:
* the relevant parts of your Assembly code
* enough context data to reproduce the the issue (e.g. var\_export()'d input/output variables)
* your OS type and version (e.g. Ubuntu 17.10)
* any virtualization you're using (e.g. I'm running this extension in a VirtualBox VM)
* your CPU model info, including any BIOS/UEFI settings that might be relevant (e.g. whether VT-x is enabled)

### New Features

If there's a feature you think might be useful for somebody or fun to implement please create a ticket with:
* a description of the new feature
* what it's for


# Working on Code

### Scope

The upcoming/planned work is managed in the [Issues list](https://github.com/rinusser/chiASM/issues). Each
implemented/fixed GitHub issue corresponds to one commit in `master`, with the issue number (prefixed with `CHI-`) in
the commit message.

### Code Style

There's no automated enforcing of code styling, please just continue the existing style: 2 spaces for indenting, Allman
style brackets.

### Tests

There are .phpt tests to be executed by the PHP build system. Each new feature should include such tests that cover the
main use cases - if there are any interesting fringe cases those should be tested as well.

Bug fixes should include regression tests to confirm that there was a bug, that the change fixed it and that it won't
return in the future.

### Validation

Each commit into the `master` branch includes documentation (if it's a feature or changes the extension's limitations)
and passes all tests.

Test execution depends on whether you're building a static extension:

    $ make test TESTS=ext/chiasm

or a shared extension:

    $ make test

See the installation instructions in README.md for details.

### Documentation

Features and limitations should be documented. The target audience can be considered to know PHP, C and x86 Assembly
already.

### Licensing

Please note that the code is currently licensed under GPLv3 - any contributions are expected to share this license.

I may change the license to a less restrictive open source license at a later date.
