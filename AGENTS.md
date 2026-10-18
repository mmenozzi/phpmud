# AGENTS.md

This file provides guidance to AI Coding Agents like Claude Code (claude.ai/code) when working with code in this repository.

## Project

PHPMud: personal project to learn Domain-Driven Design by implementing a MUD
(Multi-User Dungeon) text game in PHP. Players connect via raw TCP socket,
authenticate with character name/password, then issue text commands (move,
look, ...) to play.

The codebase is mid-refactor toward DDD (see commit "WIP ddd refactor").
Follow the Application/Command pattern below for any new
player command.

## DDD as a learning goal

The owner uses this project to learn DDD hands-on, so:

- Always follow DDD principles when designing or changing code (bounded
  contexts, ubiquitous language, aggregates and their boundaries, entities
  vs value objects, domain services, repositories, Modules, layering).
- In every plan and every answer that proposes or makes a design choice,
  explain how the chosen architecture adheres to DDD: name the principle
  each choice serves and why. When a choice deliberately deviates from
  "textbook" DDD (pragmatism, project size), say so and explain the
  trade-off.

## Commands

Requires PHP >= 8.4 (see `composer.json`). If the system default `php`
resolves to an older version, invoke the 8.4 binary explicitly (e.g.
Homebrew: `/opt/homebrew/opt/php@8.4/bin/php`) in front of every command
below and `composer` itself — otherwise Composer's platform check fails.

```
composer install       # install deps
composer start         # boot the game server (bin/phpmud: telnet on 10666, WebSocket on 10667/ws, browser client on http://127.0.0.1:10667/), backgrounded, pid in /tmp/phpmud.pid, output in /tmp/phpmud.log
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

### Running the telnet and WebSocket Behat suites

Suites tagged `@telnet` / `@websocket` (see below) connect to the **real**
game server at `127.0.0.1:10666` (telnet) / `ws://127.0.0.1:10667/ws`
(WebSocket) and read/write the filesystem repositories at
`/tmp/phpmud.location` / `/tmp/phpmud.character` / `/tmp/phpmud.world_clock`
(paths hardcoded in `src/World/Infrastructure/Resources/config/services.xml`). The
server must already be running before these scenarios execute — Behat does
not start it for you — and with a 1-second tick, otherwise the `@time`
scenarios time out waiting for a day phase broadcast:
`PHPMUD_TICK_INTERVAL=1 composer start`. `@domain` suites do not need this; they use the
in-memory repositories and boot a fresh container per scenario.

The filesystem repositories store PHP-serialized objects, so after renaming
or moving a domain class, or changing the type of a persisted property
(e.g. an id becoming a value object), the existing `/tmp/phpmud.*` files
no longer unserialize correctly (e.g. `__PHP_Incomplete_Class`; even
`PurgeContext` fails): `composer stop`,
delete `/tmp/phpmud.location`, `/tmp/phpmud.character` and
`/tmp/phpmud.world_clock`, then start the server again.

`utils/init_world.php` seeds `/tmp/phpmud.*` from `utils/locations.csv` for
manual/local play; Behat's `PurgeContext` (`@BeforeScenario`) clears the
repositories (and resets the world clock to hour 0) before each scenario
regardless, and each feature's
`Background:` sets up the fixtures it needs.
The seeded `LocationId`s are stable: `utils/location_ids.php` derives
each one as a UUID v5 of the CSV `ID` (fixed namespace), passed to
`Location`'s optional `$id` (`LocationId::fromString()`), so content keyed
by `LocationId` (the browser client's scenes) survives a re-seed. Without
an id `Location` still generates one (`LocationId::generate()`, as in
Behat fixtures).
`locations.csv` is the authored source of both the world and its
presentation content: the `Scena` column (a scene theme: `cave`, `hall`,
`garden`, ...) is not loaded by `init_world.php` into the model;
`php utils/build_scenes.php` turns it into the client's content model
`client/content/scenes.json` (`{locationId: {theme}}`, committed;
re-run it after editing the CSV).

## Commit style

Short imperative subject line, capitalized, no trailing period, no body,
no ticket refs, no conventional-commit prefix (`feat:`, `fix:`, ...). E.g.:
`Add look command handling`, `Fix init_world.php script`, `Improve README`.

Always make atomic commits: one logical change per commit. When a task
touches unrelated concerns (e.g. a translation and a logging fix), split
them into separate commits, each with its own subject line.

## Architecture

### Bounded contexts

Code is organized **bounded context first, layers inside**:
`src/<Context>/{Domain,Application,Infrastructure}` (namespace
`PHPMud\<Context>\...`). The bounded context is the outermost unit because
it is the boundary of a model and its ubiquitous language; layers and
Modules are internal structure of one context. Never split a context
across top-level layer folders (`src/Domain/<Context>`, ...).

Today there is a single context, `World` (`src/World`): the game world
model shared by every feature (characters, locations, movement, time).
The feature folders in `World/Application` (`Access`, `Movement`, ...)
are Modules of this one context, not bounded contexts.

Pragmatic deviation: `Kernel`, `Server/*`, `Delivery/*` and the shared
`CommandInterface`/`CommandResponseInterface`/`ExecutorInterface`/
`CommandRouter`/`DomainEventDispatcher`/`DomainEventSubscriberInterface`/
`NotificationInterface`/`NotifierInterface` (and `Domain/DomainEvent`,
`Domain/RecordsDomainEvents`) are generic host/delivery plumbing, not part of the World
model. They live in `World` only because there is one context; when a
second context appears, move them to a context-independent level (e.g.
`src/Shared` or `src/Server`) instead of duplicating them per context.

Expected evolution, an `Access` (identity) context: today `Character`
holds the password and `World/Application/Access` handles login, so
authentication and gameplay share one model. That is fine while one
login = one character. Split it into `src/Access/{Domain,Application,Infrastructure}`
when the identity model starts to diverge from the game one, e.g.
accounts owning several characters, roles/permissions, bans, password
reset. Then `Access` owns an `Account` aggregate (credentials, status),
`World`'s `Character` keeps only gameplay state and references the
account **by id** (cross-context references follow the same rule as
cross-aggregate ones), and the adapters' login flows (e.g. `TelnetClientHandler`) talk to
`Access` instead of `World`. Do not create the context before that
pressure exists: an empty context with no distinct language is
speculative design, not DDD.

### Layers

Three layers, strict dependency direction `Infrastructure → Application → Domain`:

- **`src/World/Domain`** — organized in DDD *Modules* by domain concept, not by
  building-block type (no `Entity/`, `Repository/`, `ValueObject/`
  folders): each aggregate lives together with its repository interface,
  value objects and enums, so the aggregate boundary is visible in the
  tree.
  - `Character/` — `Character` aggregate, `CharacterId`,
    `CharacterRepositoryInterface` (`get(CharacterId)`,
    `findByLocation(LocationId)`, ...), `CharacterMoved` event.
  - `Location/` — `Location` aggregate, `LocationId`, `Direction`,
    `LocationRepositoryInterface`.
  - `Movement/` — `MovementPolicy` domain service (spans `Character` and
    `Location`, so it belongs to neither) and `NoExitInDirectionException`.
  - `Time/` — `WorldClock` aggregate, `DayPhase`,
    `WorldClockRepositoryInterface`, `DayPhaseStarted` event.
  Pragmatic exception to "no pattern folders": the `DomainEvent` marker
  interface and the `RecordsDomainEvents` trait sit at the `Domain/` root,
  since they are cross-cutting building blocks, not a domain concept.
  New domain code goes in the Module of the concept it belongs to; add a
  new Module for a new concept rather than a folder per pattern.
  This holds inside a Module too: no `Character/Entity/Character`, keep
  `Character/Character`. Namespaces are part of the ubiquitous language
  (pattern words like "Entity" are not domain words), the pattern is
  already clear from the class name (`*RepositoryInterface`, `*Id`,
  `*Exception`), and entity vs value object is a modelling decision that
  may change without forcing a namespace move. When a Module grows too
  big, split it by sub-concept (e.g. `Character/Inventory/`), never by
  pattern.
  No framework/infra dependencies, with
  one accepted exception: third-party libraries that only provide plain
  data structures (e.g. `Doctrine\Common\Collections\Collection`,
  `Symfony\Component\Uid\Uuid`) are fine to use directly in entities — they
  carry no framework/infra coupling (no DB, no HTTP, no config wiring), so
  depending on them doesn't compromise the Domain layer's independence.
  `Character` and `Location` are separate aggregates; aggregates reference
  other aggregates **only by id**, never by object: `Character` holds a `LocationId`,
  and `Location` holds its exits as `Direction → LocationId`.
  `placeBorderingLocation()` links two locations in a `Direction` and
  auto-links the reverse (`direction->opposite()`) if not already set; it
  mutates both locations, so callers must save both.
  Movement rules on the world topology live in `MovementPolicy::move()`
  (throws `NoExitInDirectionException` when there is no exit, otherwise
  calls `Character::relocateTo()`); `relocateTo()` is where invariants on
  the character's own state belong, and is reusable for non-walking moves
  (teleport, recall, ...); it records `CharacterMoved(characterId, from,
  to, ?via)` (`via` is the walking `Direction`, null for non-walking moves).
  `WorldClock` is a singleton aggregate holding the game hour (0..23);
  `tick()` advances it by one hour and records `DayPhaseStarted` when a
  `DayPhase` starts at the new hour (`DayPhase::startingHour()`);
  `dayPhase()` is the phase in force at the current hour (`DayPhase::at()`,
  night wraps past midnight), so "what time of day is it" is answered by
  the model, never derived by an adapter or client.
  Domain Events are facts of the ubiquitous language recorded by the
  aggregate that produces them (`use RecordsDomainEvents`, `recordThat()`),
  carry only ids and value objects, and live in their aggregate's Module.
- **`src/World/Application`** — one subdirectory per feature Module (`Access`, `Character`,
  `Movement`, `Help`, `Time`), and inside it one subdirectory per use case
  (e.g. `Movement/Move/`, `Movement/Look/`, `Access/AuthenticateCharacter/`)
  holding that use case's triad side by side — grouped by use case, not by
  pattern (no `Command/` / `CommandExecutor/` folders), so the files that
  change together live together. Helpers shared by several use cases of a
  feature sit at the feature root (e.g. `Movement/LocationViewFactory`).
  - `<UseCase>Command` implements `CommandInterface` (marker interface,
    plain readonly DTO carrying ids and values, never aggregates, e.g. a
    `CharacterId` + `Direction`).
  - `<UseCase>CommandResponse` implements `CommandResponseInterface`
    (marker interface, DTO carrying the result as views of primitives /
    value objects, never aggregates, e.g. `LocationView`, a character
    name), so any adapter can serialize it without leaking aggregate
    internals.
  - `<UseCase>CommandExecutor` implements `ExecutorInterface`
    (`execute(CommandInterface): CommandResponseInterface`), asserts the
    concrete command type with `Webmozart\Assert`, loads the aggregate by
    id, does the domain work, persists via a repository if state changed,
    returns the response. When the aggregate records events, the order is
    always: mutate → `$events = $aggregate->releaseEvents()` → `save()` →
    `DomainEventDispatcher::dispatch(...$events)` — releasing before
    `save()` keeps events out of the serialized filesystem storage, and
    dispatching after it lets subscribers query the persisted state.
  - `DomainEventDispatcher` maps event class → subscribers (wired
    explicitly in `services.xml` via `addSubscriber` calls, synchronous,
    no autodiscovery). Subscribers implement
    `DomainEventSubscriberInterface` and live in a use case folder named
    after the event they react to (e.g. `Movement/CharacterMoved/`,
    `Time/DayPhaseStarted/`), next to the `*Notification` DTOs they emit.
    They decide **who** perceives a fact (e.g. characters in the same
    location, found with `findByLocation`) and **what** they are told, and
    hand it to `NotifierInterface` (`notify(CharacterId, ...)`,
    `notifyEveryone(...)`), an Application output port implemented in
    Infrastructure.
  - `CommandRouter` maps command class → executor (wired explicitly in
    `services.xml` via `addExecutorForCommand` calls); it has no
    autodiscovery.
  - `Movement/LocationViewFactory` builds a `LocationView` (id, name,
    description, neighbour locations `{id, name}` keyed by `Direction`
    value, and the other characters `{name}` in the location, found with
    `findByLocation` and excluding the viewer `CharacterId`) for responses
    that need to render exit names, since `Location` only knows exit ids.
    Who is in a location is a fact of the model (`Character` holds its
    `LocationId`), so it includes characters whose player is offline:
    "online" is Infrastructure state (`ClientRegistry`) and stays out of
    the view until a presence model is needed. The ids let clients attach presentation data
    (e.g. a graphical scene) to a location without relying on its name.
  - `Access/AuthenticateCharacter` owns the password attempts rule:
    the adapter passes the failed attempts so far (per-connection session
    state) and the response says whether they are exhausted
    (`MAX_FAILED_ATTEMPTS = 3`); the adapter then closes the connection.
    It is an access/security rule, so no adapter may re-implement it.
  To add a new player command: create the Command/Response/Executor triple
  in a new use case folder,
  register the executor + router mapping in `services.xml`, and teach
  every delivery adapter about the new types: telnet
  (`Delivery/Telnet/CommandResolver` parses raw input into a
  `CommandInterface`, `Delivery/Telnet/CommandResponseRenderer` renders a
  `CommandResponseInterface` as text) and WebSocket
  (`Delivery/WebSocket/MessageParser`, `MessageSerializer`, plus the
  protocol table below) — all hand-written `instanceof`/if-chains, not
  polymorphic dispatch. A new notification likewise needs a branch in
  each adapter (`renderNotification()` / `serializeNotification()`).
  Presentation data per location already lives outside the model: the
  browser client's scene themes are a content model keyed by `LocationId`
  (`client/content/scenes.json`, generated from `utils/locations.csv`),
  never fields of `Location` nor of the protocol.
  Pragmatic shortcut: `Location`'s name and description have no
  behavior (only exits do); they are authored content, like future
  scene art, and stay in the aggregate only while every adapter needs
  them as-is. Move them, with presentation assets, to a content model or
  read model keyed by `LocationId` once content starts depending on the
  medium or language (graphical scenes, ASCII maps, translations).
- **`src/World/Infrastructure`**:
  - `Kernel` boots a Symfony `ContainerBuilder` from XML configs — always
    loads `services.xml`, then optionally `services_{environment}.php`'s
    XML sibling (`services_test.xml` exists, imports Behat's own service
    config and swaps in-memory repositories in).
  - `Server/` is the transport-agnostic game host (Ports & Adapters: each
    player interface is a driving adapter under `Delivery/<Adapter>`, and
    `Server/` never knows the wire format):
    - `Client` is the port every adapter implements: `getCharacterId()`,
      `isAuthenticated()`, `send(CommandResponseInterface)` and
      `notify(NotificationInterface)` (the adapter renders in its own
      format; both throw `ClientDisconnectedException` when the connection
      is gone).
    - `ClientRegistry` tracks connected `Client`s of every adapter
      (`getAuthenticatedClient(CharacterId)`).
    - `ClientNotifier` implements the Application `NotifierInterface`:
      delivers a notification to the recipient's client if connected
      (silently dropped otherwise) or to every authenticated client, and
      unregisters clients that turn out to be disconnected.
    - `Ticker` is the game time: every `phpmud.tick_interval` seconds
      (60 = one game hour, overridable with the `PHPMUD_TICK_INTERVAL` env
      var read by `bin/phpmud`) it executes `TickCommand`; day phase
      messages reach clients via `DayPhaseStarted` → `NotifyDayPhaseStarted`
      → `ClientNotifier`. Notifications are the only messages the server
      sends without a client command: the telnet Behat trait routes these
      blocks (`DayPhaseMessage`, `MovementMessage`) to a separate
      `broadcasts` queue so they never interleave with command responses;
      any new unsolicited message must be recognized by
      `TelnetClientTrait::isBroadcast()` and
      `WebSocketClientTrait::isBroadcast()` the same way.
    - `Listener` (`start()`, non-blocking) is implemented by each
      adapter's server. `GameServer` (started by `bin/phpmud`) starts the
      `Ticker`, then every listener wired in `services.xml`, then runs the
      event loop.
    A new player interface goes in a new `Delivery/<Adapter>` folder with
    its own `Client` implementation, `Listener`, input parsing and
    rendering, and is added to `GameServer`'s listeners; it is a delivery
    adapter, not a new bounded context.
  - `Delivery/Telnet/` is the text MUD adapter:
    - `TelnetServer` (built on `amphp/socket`) accepts connections, wraps
      each in a `TelnetClient`, registers it in `ClientRegistry`, reads
      newline-delimited commands and dispatches them to
      `TelnetClientHandler`.
    - `CommandResolver` parses a raw line into a `CommandInterface` for
      the authenticated `CharacterId`; `CommandResponseRenderer` turns a
      `CommandResponseInterface` (`render()`) or a `NotificationInterface`
      (`renderNotification()`, via `DayPhaseMessage` / `MovementMessage`)
      into a text block (pure, no socket). `TelnetClient::send()` /
      `notify()` render and write it.
    - `TelnetClientHandler` owns the per-connection login state machine
      (banner from `Delivery/Telnet/Resources/banner.txt` → name →
      password; the login state, and then the authenticated
      `CharacterId`, lives in `TelnetClient`, since prompt
      login is telnet-specific), but the decisions live in
      `Application/Access`: `IdentifyCharacterCommand` (does the name
      exist? if not, re-prompt name) and `AuthenticateCharacterCommand`
      (`Character::isPasswordValid()`; on failure re-prompt password, or
      close the socket when the response says attempts are exhausted). Login
      messages are written by `TelnetClientHandler` itself, not
      `CommandResponseRenderer`. For authenticated clients it delegates
      each raw line to
      `CommandResolver → CommandRouter → executor → TelnetClient::send()`.
      Right after login both adapters send, in order, `WhoAmICommand`,
      `LookCommand` and `Time/CurrentDayPhase` (`CurrentDayPhaseCommand`),
      so a client knows the day phase before the next `DayPhaseStarted`;
      telnet renders it with `DayPhaseMessage::current()`, a text distinct
      from the `of()` broadcasts.
    - While the password is typed the server sends telnet `IAC WILL ECHO`
      (`Delivery/Telnet/Telnet`) so the client hides input, and
      `IAC WONT ECHO` once login succeeds or the socket is closed;
      `TelnetServer` strips incoming telnet negotiation and trailing `\r`
      from each line, and the telnet Behat trait strips it from responses
      (tracking the echo state).
    - Every message block ends with a blank line: telnet Behat contexts
      split responses on it, so `banner.txt` must not contain empty lines.
  - `Delivery/WebSocket/` is the JSON adapter (base for a browser
    client), on `amphp/websocket-server` at `ws://127.0.0.1:10667/ws`:
    - `WebSocketServer` (`Listener`) exposes an `amphp/http-server` with an
      `amphp/http-server-router` `Router`: `GET /ws` is the `Websocket`
      handler, every other path falls back to a `DocumentRoot`
      (`amphp/http-server-static-content`) serving the browser client in
      `client/` (path in the `phpmud.client_dir` parameter, set by
      `Kernel`), with `setExpiresPeriod(0)` so browsers revalidate the
      unversioned files (ETag) instead of caching them for a week.
      Serving the client's files is plumbing of this adapter;
      the protocol does not depend on it.
      The `Websocket` handler uses `AllowOriginAcceptor` with the origins
      of the served client (`services.xml`), so other web pages cannot open a
      WebSocket with the player's browser (cross-site WebSocket
      hijacking); handshakes without an allowed `Origin` header get 403,
      so `WebSocketClientTrait` sends one (add the real host's origin
      before serving the client from it).
    - `WebSocketClientHandler` registers a `WebSocketClient` (`Client`,
      holding the per-connection login state) in `ClientRegistry`, decodes
      each text message and routes it: before login `identify` /
      `authenticate` with the same Application commands as telnet, after
      login `MessageParser → CommandRouter → executor →
      WebSocketClient::send()`. `MessageSerializer` turns responses and
      notifications into JSON.
    - The JSON protocol is a Published Language: a public, versioned
      contract in the ubiquitous language (enum values of `Direction` and
      `DayPhase`), never the aggregates' internal shape. Every message is
      an object with a `type`. v1:
      - client → server: `identify {name}`, `authenticate {password}`,
        `look`, `move {direction}`;
      - responses: `identified {name, exists}`, `authenticated`,
        `authentication_failed`, `too_many_attempts` (then the connection
        closes), `whoami {name}`, `day_phase {day_phase}` (sent after
        login, after `whoami` and `location`), `location {id, name, description,
        exits: {direction: {id, name}}, characters: [{name}]}`, `move_failed {direction}`,
        `unknown_command` (also for game commands before login),
        `error {message}` (invalid JSON or missing fields);
      - events (unsolicited): `day_phase_started {day_phase}`,
        `character_left {name, via}`, `character_arrived {name, via}`
        (`via` is the walking direction or null; the client derives
        where someone came from as `via`'s opposite).
  - `Repository/Filesystem/*` (single-file serialized storage, used by
    `dev`/end-to-end test config) and `Repository/InMemory/*` (from
    `webgriffe/in-memory-repository`, used by `test`/domain-test config)
    are the two `*RepositoryInterface` implementations; which one is active
    is purely a matter of which `services*.xml` is loaded. The filesystem
    repositories re-read their file on every call (no in-memory cache), so
    the long-running server sees fixtures written by other processes
    (Behat); the flip side is that every loaded object is a fresh copy, so
    a mutated entity must be re-`save()`d to persist.

## Testing

Behat, not PHPUnit, is the test framework (`tests/` has no PHPUnit tests).
Suites are declared in `tests/Behat/Resources/config/suites.yml` (imports
per-suite YAML under `suites/domain/`, `suites/telnet/` and
`suites/websocket/`) and filtered by tag pairs like `@movement&&@domain`
vs `@movement&&@telnet` vs `@movement&&@websocket` — the same
`features/*.feature` file is exercised by a fast in-memory "domain" suite
and by end-to-end "telnet" and "websocket" suites that go through the
real server; scenarios are tagged `@domain @telnet @websocket` when all
apply (only the banner scenario is telnet-only). Three green suites on
the same features are the proof that the model is one and the delivery
is interchangeable, so new scenarios should cover every adapter.

Context classes are grouped by role under `tests/Behat/Context/`: `Setup`
(fixture creation, filesystem or in-memory variant per suite), `Domain`
(drives the executors directly, wired inline in
`Resources/config/context/domain.xml` on the in-memory repositories with
their own `DomainEventDispatcher` and a `Service/RecordingNotifier` that
contexts assert notifications on), `Telnet` (drives the game over a raw
`socket_connect`/`socket_write`, see `TelnetClientTrait`; it supports
several clients per scenario, keyed by name: `me` by default, other
characters created with `there is the character "X" in the location "Y"`
and logged in with `"X" is connected to the game`), `WebSocket` (same
steps over `amphp/websocket-client`, see `WebSocketClientTrait`, also
multi-client; step semantics follow the domain contexts, asserting on
JSON messages), `Transform`
(Behat argument transforms), `Hook` (`PurgeContext`, wipes repos before
each scenario). The custom `SymfonyExtension` (`tests/Behat/Extension/`)
boots a fresh `PHPMud\World\Infrastructure\Kernel` per scenario and injects its
services into contexts by type.
