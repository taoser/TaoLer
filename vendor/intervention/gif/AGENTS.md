# Intervention Gif Agent Guide

This document provides a guide for software engineering agents working on the Intervention Gif codebase.

## 1. Project Overview

**intervention/gif** -- Native PHP GIF encoder/decoder library. Parses and generates
GIF binary data. Part of the [Intervention Image](https://github.com/Intervention/image) ecosystem.

- **Language:** PHP 8.3+  |  **Namespace:** `Intervention\Gif`

### 1.1 Architecture

The codebase mirrors the GIF89a binary specification. Each GIF format block has three
corresponding classes:

- `src/Blocks/<Name>.php` -- Data model (value object)
- `src/Decoders/<Name>Decoder.php` -- Binary stream reader
- `src/Encoders/<Name>Encoder.php` -- Binary stream writer

Encoder/decoder resolution is convention-based via `CanDecode`/`CanEncode` traits.
All block models extend `AbstractEntity` (which implements `Stringable`).

## 2. Development Environment

The project uses Composer to manage dependencies. These dependencies are installed automatically when using the Docker test runners.

## 3. Build, Lint, and Test Commands

The following commands are used to ensure code quality and correctness.

### 3.1. Testing (PHPUnit)

The project uses PHPUnit for unit and feature testing.

- **Run all tests:**
  ```bash
  docker compose run --rm tests
  ```

- **Run a single test file:**
  To run a specific test file, provide the path to the file.
  ```bash
  docker compose run --rm tests tests/Unit/BuilderTest.php
  ```

- **Run a single test method:**
  Use the `--filter` option to run a specific test method by its name.
  ```bash
  docker compose run --rm tests tests/Unit/BuilderTest.php --filter testMethodName
  ```

- **Check test coverage:**
  ```bash
  docker compose run --rm coverage
  ```

### 3.2. Static Analysis (PHPStan)

PHPStan is used for static analysis to find potential bugs.

- **Run static analysis:**
  ```bash
  docker compose run --rm analysis
  ```

### 3.3. Coding Standards (PHP CodeSniffer)

The project adheres to the PSR-12 coding standard with additional rules. PHP CodeSniffer is used to enforce these standards.

- **Check for coding standard violations:**
  ```bash
  docker compose run --rm standards
  ```

## 4. Code Style and Conventions

Consistency is key. Adhere to the following guidelines when writing code.

### 4.1. Formatting

- **PSR-12:** The primary coding standard is PSR-12.
- **Indentation:** Use 4 spaces for indentation, not tabs.
- **Line Endings:** Use Unix-style line endings (LF).
- **Strict Types:** All PHP files must start with `declare(strict_types=1);`.
- **Class Structure:** Follow the ordering defined in `phpcs.xml`:
    1. `uses`
    2. `enum cases`
    3. `constants`
    4. `static properties`
    5. `properties`
    6. `constructor`
    7. `static constructors`
    8. `methods`
    9. `magic methods`

### 4.2. Naming Conventions

- **Classes:** `PascalCase`.
- **Methods:** `camelCase`.
- **Variables:** `camelCase`.
- **Constants:** `UPPER_CASE` with underscore separators.
- **File Names:** File names must match the class name they contain (e.g., `MyClass.php` for `class MyClass`).

### 4.3. Imports

- **One class per `use` statement:** Do not group multiple classes in a single `use` statement.
- **No leading backslash:** `use` statements must not start with a backslash.
- **Order:** `use` statements should be ordered alphabetically. Unused imports must be removed.

### 4.4. Types and Type Hinting

- **Strict Typing:** All code should be strictly typed.
- **Parameter Types:** All method parameters must have a type hint.
- **Return Types:** All methods must have a return type hint.
- **Property Types:** All class properties must have a type hint.
- **Nullable Types:** Use nullable types (`?TypeName`) when a `null` value is explicitly allowed.

### 4.5. Error Handling

- Exceptions should be used for error handling.
- When catching exceptions, be as specific as possible. Avoid catching generic `\Exception` or `\Throwable`.
- Exception messages should be clear and descriptive.

### 4.6. PHPDoc (DocBlocks)

- PHPDoc blocks are required for all classes, properties, and methods.
- Follow the annotation order defined in `phpcs.xml`.
- Use DocBlocks to provide context and explain complex logic. Do not restate the obvious from the code signature.
