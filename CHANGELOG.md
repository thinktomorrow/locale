# Changelog

## Unreleased
- A minimum of php8.2 is now required.
- `thinktomorrow/url` package is used for Root and Url logic. 
- The config file is now located at `config/locale` instead of `config/thinktomorrow/locale`.
- Default locale segment is no longer restricted to a slash '/'. The slash used to be required as default, but now if omitted, the last entry of each scope is considered the default locale instead.

### 1.3.2
- added `Locale::setAvailables()` to define available locales from a source other than config.
