# Changelog

## [Unreleased]
### Fixed
- Blog post status was not toggling
- Code cleanup

## [4.0.0]
### Notes
- I promise this will be the last major version upgrade for a while (I hope).  This was necessary in order to separate out the heavier dappurwares into their own repository so that they could be developed further.  The dapurwares that were moved into their own repositories are:
	- Deployment
	- Email
	- Oauth2
	- Video

## [3.0.0]
### Notes
I am bumping the version number on on Dappurware for this release to v3.0.0.  This is so that the framework and dappurware will share the same major version number.  Breaking changes will only be introduced in major version updates along with the framework.

### Added
- Deployment: Database connection validation while checking requirements.

### Changed
- Deployment: Using `GIT_SSH_COMMAND` now for identity file access and removed

### Removed
- Deployment: Check and update settings functions as they were not used.
- Deployment: Install deploy key in favor of direct identity file access using `GIT_SSH_COMMAND`

## [1.1.2] - 2018-07-30
### Fixed
- Made menu validation recursive to fix permissions issue

## [1.1.1] - 2018-07-29
### Added
- Menus to `TwigExtensions`

## [1.1.0] - 2018-07-19
### Added
- Moved `TwigExtensions` src from Framework
- Added Gravatar Twig extension

## [1.0.6] - 2018-07-17
### Fixed
- Issue with email recipients it was an ID

## [1.0.5] - 2018-07-12
### Fixed
- Issue with publish date not saving correctly in the blog.

## [1.0.4] - 2018-05-30
### Fixed
- Issue with duplicate emails being sent if user was already registered.

## [1.0.3] - 2018-04-02
### Changed
- Fixed reference to themes folder in Settings.php

## [1.0.2] - 2018-04-02
### Changed
- Fixed reference to project settings file in Settings.php

## [1.0.1] - 2018-04-02
### Changed
- Fixed package name in composer.json.

## [1.0.0] - 2018-04-02
### Added
- Separated Dappurware from the framework.


[Unreleased]: https://github.com/dappur/dappurware/compare/v4.0.0...HEAD
[4.0.0]: https://github.com/dappur/dappurware/compare/v3.0.0...v4.0.0
[3.0.0]: https://github.com/dappur/dappurware/compare/v1.1.2...v3.0.0
[1.1.2]: https://github.com/dappur/dappurware/compare/v1.1.1...v1.1.2
[1.1.1]: https://github.com/dappur/dappurware/compare/v1.1.0...v1.1.1
[1.1.0]: https://github.com/dappur/dappurware/compare/v1.0.6...v1.1.0
[1.0.6]: https://github.com/dappur/dappurware/compare/v1.0.5...v1.0.6
[1.0.5]: https://github.com/dappur/dappurware/compare/v1.0.4...v1.0.5
[1.0.4]: https://github.com/dappur/dappurware/compare/v1.0.3...v1.0.4
[1.0.3]: https://github.com/dappur/dappurware/compare/v1.0.2...v1.0.3
[1.0.2]: https://github.com/dappur/dappurware/compare/v1.0.1...v1.0.2
[1.0.1]: https://github.com/dappur/dappurware/compare/v1.0.0...v1.0.1
[1.0.0]: https://github.com/dappur/dappurware/releases/tag/v1.0.0