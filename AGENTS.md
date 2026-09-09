# AGENTS.md

This file provides guidance to AI Coding Agents like Claude Code (claude.ai/code) when working with code in this repository.

## Project

PHPMud: personal project to learn Domain-Driven Design by implementing a MUD
(Multi-User Dungeon) text game in PHP. Players connect via raw TCP socket,
authenticate with character name/password, then issue text commands (move,
look, ...) to play.

The codebase is mid-refactor toward DDD (see commit "WIP ddd refactor"):
`src/Infrastructure/Server/Client.php` still has an old `handleCommand()`
method (`whoami`/`look`/else) that duplicates logic now properly handled by
`CommandResolver` → `CommandRouter` → `ExecutorInterface` → `CommandResponseHandler`.
Don't copy patterns from that method; follow the Application/Command pattern below.

## Commands

```
composer install       # install deps
composer start         # boot the socket server (bin/phpmud), backgrounded, pid in /tmp/phpmud.pid
composer stop          # kill the backgrounded server
composer test          # runs ecs, phpstan, behat in sequence
composer ecs           # code style check (Symplify ECS, Symfony ruleset) — src/ and tests/
composer phpstan       # static analysis, level max, src/ only
composer behat         # run Behat suites
```

Run a single Behat suite or scenario with tags, e.g.:
```
vendor/bin/behat --tags="@movement&&@domain"
vendor/bin/behat features/movement.feature:20
```

`ecs` can autofix: `vendor/bin/ecs check --fix`.

### Running the socket Behat suites

Suites tagged `@socket` (see below) connect to a **real** socket server at
`127.0.0.1:10666` and read/write the filesystem repositories at
`/tmp/phpmud.location` / `/tmp/phpmud.character` (paths hardcoded in
`src/Infrastructure/Resources/config/services.xml`). The server must
already be running (`composer start`) before these scenarios execute — Behat
does not start it for you. `@domain` suites do not need this; they use the
in-memory repositories and boot a fresh container per scenario.

`utils/init_world.php` seeds `/tmp/phpmud.*` from `utils/locations.csv` for
manual/local play; Behat's `PurgeContext` (`@BeforeScenario`) clears both
repositories before each scenario regardless, and each feature's
`Background:` sets up the fixtures it needs.

## Commit style

Short imperative subject line, capitalized, no trailing period, no body,
no ticket refs, no conventional-commit prefix (`feat:`, `fix:`, ...). E.g.:
`Add look command handling`, `Fix init_world.php script`, `Improve README`.

## Architecture

Three layers, strict dependency direction `Infrastructure → Application → Domain`:

- **`src/Domain`** — entities (`Character`, `Location`), the `Direction` enum,
  and repository *interfaces* only. No framework/infra dependencies, with
  one accepted exception: third-party libraries that only provide plain
  data structures (e.g. `Doctrine\Common\Collections\Collection`,
  `Symfony\Component\Uid\Uuid`) are fine to use directly in entities — they
  carry no framework/infra coupling (no DB, no HTTP, no config wiring), so
  depending on them doesn't compromise the Domain layer's independence.
  `Location` is a graph: `placeBorderingLocation()` links two locations in a
  `Direction` and auto-links the reverse (`direction->opposite()`) if not
  already set. `Character::moveTo()` just follows `getNeighbor()`.
- **`src/Application`** — one subdirectory per feature (`Movement`, `Help`),
  each following the same triad:
  - `Command/*Command.php` implements `CommandInterface` (marker interface,
    plain readonly DTO carrying whatever the executor needs, e.g. a
    `Character` + `Direction`).
  - `Command/*CommandResponse.php` implements `CommandResponseInterface`
    (marker interface, DTO carrying the result).
  - `CommandExecutor/*CommandExecutor.php` implements `ExecutorInterface`
    (`execute(CommandInterface): CommandResponseInterface`), asserts the
    concrete command type with `Webmozart\Assert`, does the domain work,
    persists via a repository if state changed, returns the response.
  - `CommandRouter` maps command class → executor (wired explicitly in
    `services.xml` via `addExecutorForCommand` calls); it has no
    autodiscovery.
  To add a new player command: create the Command/Response/Executor triple,
  register the executor + router mapping in `services.xml`, and teach
  `CommandResolver` (parses raw input into a `CommandInterface`) and
  `CommandResponseHandler` (renders a `CommandResponseInterface` back to the
  socket) about the new types — both are hand-written `instanceof`/if-chains,
  not polymorphic dispatch.
- **`src/Infrastructure`**:
  - `Kernel` boots a Symfony `ContainerBuilder` from XML configs — always
    loads `services.xml`, then optionally `services_{environment}.php`'s
    XML sibling (`services_test.xml` exists, imports Behat's own service
    config and swaps in-memory repositories in).
  - `Server/SocketServer` (built on `amphp/socket`) accepts connections,
    reads newline-delimited commands per client, and dispatches to
    `ClientHandler`.
  - `ClientHandler` owns the auth handshake (name → password →
    `password_verify` against `Character::getPasswordHash()`) state machine
    per `Client`, then for authenticated clients delegates each raw line to
    `CommandResolver → CommandRouter → executor → CommandResponseHandler`.
  - `Repository/Filesystem/*` (single-file serialized storage, used by
    `dev`/socket-test config) and `Repository/InMemory/*` (from
    `webgriffe/in-memory-repository`, used by `test`/domain-test config)
    are the two `*RepositoryInterface` implementations; which one is active
    is purely a matter of which `services*.xml` is loaded.

## Testing

Behat, not PHPUnit, is the test framework (`tests/` has no PHPUnit tests).
Suites are declared in `tests/Behat/Resources/config/suites.yml` (imports
per-suite YAML under `suites/domain/` and `suites/socket/`) and filtered by
tag pairs like `@movement&&@domain` vs `@movement&&@socket` — the same
`features/*.feature` file is exercised by both a fast in-memory "domain"
suite and an end-to-end "socket" suite that goes through the real TCP
server; scenarios are tagged `@domain @socket` when both apply.

Context classes are grouped by role under `tests/Behat/Context/`: `Setup`
(fixture creation, filesystem or in-memory variant per suite), `Domain`
(drives the domain layer directly), `Socket` (drives the game over a raw
`socket_connect`/`socket_write`, see `SocketResponseTrait`), `Transform`
(Behat argument transforms), `Hook` (`PurgeContext`, wipes repos before
each scenario). The custom `SymfonyExtension` (`tests/Behat/Extension/`)
boots a fresh `PHPMud\Infrastructure\Kernel` per scenario and injects its
services into contexts by type.
