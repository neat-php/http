# Changelog
All notable changes to Neat HTTP components will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [0.6.0] - 2019-12-16
### Added
- Neat HTTP client and server suggestion.
- ServerRequest class.
- Generic Message header and withHeader methods using __call method.
- Message contentDisposition() and withContentDisposition() methods.
- ContentDisposition disposition(), filename() and fieldname() methods.
- ContentType type(), charset() and boundary() methods.
- Upload psr() method.
- Url psr() method.

### Changed
- Request is now only a client-side request.

### Deprecated
- Input, Router, Session and Download/Redirect implementations.
- Receiver and Transmitter interfaces.
- Response::send() method.
- ContentDisposition getValue(), getFilename() and getName() methods.
- ContentType getValue(), getCharset() and getBoundary() methods.
- Upload file() and moved() methods.
- Url getUri() method.

### Removed
- Removed strict_types=1 declaration.

### Fixed
- Readme documentation examples.
- Unit test coverage.

## [0.5.1]
### Added
- Message to RouteNotFoundException and MethodNotAllowedException.

## [0.5.0]
### Fixed
- MethodNotAllowedException during Router match when a wildcard route exists.

### Removed
- ReceiverInterface and TransmitterInterface.
- Router controller method.
- Router segment, isRoot, isLiteral, isVariable, isWildcard methods.

## [0.4.2]
### Fixed
- Router path segment normalization.

### Added
- Router any method.

### Deprecated
- Router controller method (will be replaced with proper controller support).

## [0.4.1] - 2019-09-24
### Fixed
- ReceiverInterface and TransmitterInterface break BC.

## [0.4.0] - 2019-09-02
### Changed
- Add RouteNotFound and MethodNotAllowed exceptions.
- Router match now throws RouteNotFound or MethodNotAllowed exceptions instead of StatusException.
- Moved StatusException to exception namespace. 

## [0.3.2] - 2019-09-24
### Fixed
- ReceiverInterface and TransmitterInterface break BC.

## [0.3.1] - 2019-08-20
### Fixed
- Redirect back now uses the Referer header as it's supposed to.

## [0.3.0] - 2019-08-20
### Changed
- Store and fetch only one error per input field.
- Add string type hints to withHeader and withoutHeader method signatures.
- Add type hints to Input class method signatures.
- Refactor internal Message (Request and Response base class) implementation.

### Fixed
- Fixed error in Url->withQueryParameters().
- Fixed broken ResponseFactory reference.

## [0.2.4] - 2019-09-24
### Fixed
- ReceiverInterface and TransmitterInterface break BC.

## [0.2.3] - 2019-08-15
### Changed
- Rename ReceiverInterface to Receiver and TransmitterInterface to Transmitter.

### Deprecated
- The ReceiverInterface and TransmitterInterface interfaces are deprecated as of now.

## [0.2.2] - 2019-08-02
### Added
- Added Url->isSecure(): bool method.

### Fixed
- Upload->move() doesn't throw Runtime exceptions when it succeeds.

## [0.2.1] - 2019-08-01
### Added
- Added doc blocks to the TransmitterInterface.

### Changed
- Router->match() now returns the remaining segments for wildcards.

## [0.2.0] - 2019-07-24
### Added
- Recursive router implementation.
- TransmitterInterface and ReceiverInterface.

### Changed
- Moved to a PSR7 wrapper instead.
