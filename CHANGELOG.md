# Changelog

## master

- BC breaks (follow `UPGRADE-2.0.md` to upgrade):
  - simplify the built-in form by replacing hidden fields with view variables

## 1.4.2 (2017-03-27)

- prevent changes to names and sections of settings via hidden fields in the built-in form
- use namespaced template names (available since Twig 1.10)

## 1.4.1 (2015-12-29)

- added support for PHP 7.0 and HHVM

## 1.4.0 (2015-11-30)

- added support for Symfony 3.*
- dropped support for Symfony 2.1 and 2.2

## 1.3.2 (2015-11-30)

- [#22]+[#23]+[#28]: added conditional code updates to avoid deprecation notices with Symfony 2.8
- simplified controller code by injecting the `Request` into the action

[#22]: https://github.com/craue/CraueConfigBundle/issues/22
[#23]: https://github.com/craue/CraueConfigBundle/issues/23
[#28]: https://github.com/craue/CraueConfigBundle/issues/28

## 1.3.1 (2015-02-27)

- [#17]: added Russian translation
- added conditional code updates to avoid deprecation notices

[#17]: https://github.com/craue/CraueConfigBundle/issues/17

## 1.3.0 (2014-11-04)

- [#16]: added method `getBySection` to get settings with a given section

[#16]: https://github.com/craue/CraueConfigBundle/issues/16

## 1.2.0 (2014-05-22)

- [#10]: added Twig function `craue_setting`
- [#12]: added Dutch translation

[#10]: https://github.com/craue/CraueConfigBundle/issues/10
[#12]: https://github.com/craue/CraueConfigBundle/issues/12

## 1.1.4 (2013-12-08)

- fixed handling flash messages in the base template
- fixed translating setting names in the modification form
- adjusted the Composer requirements to also allow Symfony 2.4 and up (now 2.1 and up)

## 1.1.3 (2013-11-21)

- [#7]: fixed the base template to be compatible with Symfony 2.3 as well (now 2.1 and up)

[#7]: https://github.com/craue/CraueConfigBundle/issues/7

## 1.1.2 (2013-09-25)

- adjusted the Composer requirements to also allow Symfony 2.3

## 1.1.1 (2013-05-02)

- allow a custom route for redirection after handling form submission

## 1.1.0 (2013-02-28)

- adjustments to changes in the Form component for Symfony 2.1.*
- [#2]: added setters to the `Config` class

[#2]: https://github.com/craue/CraueConfigBundle/issues/2

## 1.0.0 (2012-05-26)

- first stable release for Symfony 2.0.*

## 2011-07-07

- initial commit
