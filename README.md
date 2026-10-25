# PHPMud

A personal project to learn Domain Driven Design while implementing a MUD (Multi-User Dungeon) game in PHP.

Players connect over telnet or with a graphical browser client, log in with a
character name and password, then explore the world (move, look, see other
characters coming and going, watch the day phases change).

## Requirements

- PHP >= 8.4 with the `sockets` extension
- Composer

If the default `php` on your system is older, prefix every command below with
the PHP 8.4 binary (e.g. Homebrew: `/opt/homebrew/opt/php@8.4/bin/php`).

## Getting started

Install the dependencies:

```
composer install
```

Initialize the world: this seeds the locations from `utils/locations.csv` and
creates the character **John** (password `p4ssw0rd`) in the starting location,
the inn (marked by the `Partenza` column).
It overwrites `/tmp/phpmud.location` and `/tmp/phpmud.character`.

```
php utils/init_world.php
```

If you edited the `Scena` column of `utils/locations.csv`, regenerate the
browser client's scene themes too:

```
php utils/build_scenes.php
```

Start the game server (in background, pid in `/tmp/phpmud.pid`, output in
`/tmp/phpmud.log`) and stop it when done:

```
composer start
composer stop
```

One game hour lasts 60 seconds by default; set `PHPMUD_TICK_INTERVAL` (in
seconds) to change it, e.g. `PHPMUD_TICK_INTERVAL=1 composer start`.

If the server fails to load the world after an update (e.g. a domain class was
renamed), stop it, delete `/tmp/phpmud.location`, `/tmp/phpmud.character` and
`/tmp/phpmud.world_clock`, run `php utils/init_world.php` again and restart.

## Playing

### Telnet

```
telnet 127.0.0.1 10666
```

After the banner, type the character name and password. A name that does not
exist yet creates a new character: confirm the name (`si`/`no`), choose its sex
(`m`/`f`) and a password (at least 4 characters, typed twice). Names are 3 to
15 letters, capitalized automatically and unique regardless of case. New
characters wake up in the inn, where whoever is there sees them appear.

Then play with:

- `look` to describe the current location, its exits and who is here;
- a direction to move: `north`, `east`, `south`, `west`, `up`, `down`, or
  their initials (`n`, `e`, ...), optionally after `move`, `go` or `walk`
  (e.g. `go north`).

### Browser

Open <http://127.0.0.1:10667/> and choose whether to log in with an existing
character or create a new one (name, sex, password typed twice). The
location is drawn as a top-down room: click a door or the stairs (or use the
arrow keys, PageUp/PageDown) to move. The side panel shows the room
description, the exits, who is here and an event log.

The browser client speaks a JSON protocol over the WebSocket at
`ws://127.0.0.1:10667/ws`, which any other client can use as well.

Telnet and browser players share the same world: open two browser tabs with
two characters, plus a telnet session, to see each other move around.

## Architecture

The code follows DDD and is organized **bounded context first, layers
inside**: `src/<Context>/{Domain,Application,Infrastructure}`. Today there is a
single context, `World`.

- **Domain** (`src/World/Domain`): the model, grouped in Modules by concept.
  `Character` and `Location` are aggregates that reference each other only by
  id; `MovementPolicy` is the domain service that moves a character through a
  location's exits; `WorldClock` is the game time and its `DayPhase`s.
  Aggregates record Domain Events (`CharacterMoved`, `DayPhaseStarted`). No
  framework dependencies.
- **Application** (`src/World/Application`): one folder per use case, each
  with a `Command` (input DTO), a `CommandExecutor` (loads the aggregate, does
  the domain work, saves it, dispatches its events) and a `CommandResponse`
  (output DTO of views, never aggregates). A `CommandRouter` maps commands to
  executors; event subscribers decide who perceives a fact and send them a
  notification through the `NotifierInterface` port.
- **Infrastructure** (`src/World/Infrastructure`): the Symfony DI `Kernel`,
  the repositories (filesystem for the running game, in-memory for tests) and
  the game host. Following Ports & Adapters, `Server/` is transport-agnostic
  (connected clients, notifications, the `Ticker` that advances game time),
  while each player interface is a delivery adapter in `Delivery/`: `Telnet`
  (text) and `WebSocket` (JSON protocol, which also serves the browser
  client).

The browser client (`client/`) is a separate application in vanilla JS and
SVG, with no build step: it only depends on the WebSocket JSON protocol, and
keeps its presentation content (scene themes per location) outside the
domain model.

See [AGENTS.md](AGENTS.md) for the full architecture notes.

## Contributing

```
composer test
```

runs code style checks (ECS), static analysis (PHPStan) and the Behat suites.
The `@telnet` and `@websocket` suites run against the real server, which must
be already running with a 1-second tick: `PHPMUD_TICK_INTERVAL=1 composer start`.
