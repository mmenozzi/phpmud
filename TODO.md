# TODO

Punti architetturali emersi da una code review (2026-09-09), da affrontare in
sessioni future. Vedi `AGENTS.md` per il contesto architetturale generale.

## Refusi minori

- `UnknowCommand` / `UnknowCommandExecutor` → rinominare in `UnknownCommand` /
  `UnknownCommandExecutor` per coerenza con `UnknownCommandResponse`, già
  scritto correttamente.
- `CharacterRepository::add()` viene usato sia per creare che per aggiornare
  un personaggio esistente (upsert). Valutare di rinominarlo `save()` per
  riflettere meglio il comportamento, riservando `add()` al solo inserimento.
  Attenzione: oggi l'implementazione in-memory di `CharacterRepository::add()`
  fa append (duplica il personaggio se chiamata due volte), mentre quella
  filesystem fa upsert. `MoveCommandExecutor` su repository in-memory
  duplicherebbe il personaggio a ogni mossa: allineare le due semantiche
  (come già fatto per `LocationRepository` in-memory).
