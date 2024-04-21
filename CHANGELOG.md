# Changelog

## Unreleased
- A minimum of php8.2 is now required.
- config file is now located at `config/locale` instead of `config/thinktomorrow/locale`.
- Allow to omit the slash `/` segment. This was required as default, but now if omitted, the last entry is considered the default locale instead.

// TODO: use URL logic of our separate package.

### 1.3.2
- added `Locale::setAvailables()` to define available locales from a source other than config.
