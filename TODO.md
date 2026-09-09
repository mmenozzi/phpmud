# TODO

Punti architetturali emersi da una code review (2026-09-09), da affrontare in
sessioni future. Vedi `AGENTS.md` per il contesto architetturale generale.

## Rispettare il boundary tra gli aggregate Character e Location

`Character` e `Location` hanno repository separati (sembrano due aggregate
distinti) ma `Character` tiene un riferimento diretto all'oggetto `Location`
invece che al suo id. In DDD un aggregate dovrebbe referenziare un altro
aggregate per identità, non per riferimento diretto all'oggetto. Da valutare:
`Character` tiene un `LocationId`, è l'Application layer (l'Executor) a
caricare la `Location` tramite `LocationRepositoryInterface` quando serve.

## Correggere la gestione errori "fantasma" in `MoveCommandExecutor`

`MoveCommandExecutor::execute()` fa `try { $character->moveTo($direction) }
catch (\Throwable)` per rilevare una mossa fallita, ma `Character::moveTo()`
non lancia mai un'eccezione (c'è un TODO commentato nel metodo stesso: "For
now we simply remain in the current location without feedback"). Il catch
quindi non scatta mai: se il giocatore prova a muoversi in una direzione
senza uscita, l'Executor ritorna comunque `MoveCommandResponse(true, ...)`
invece di `false`, e il messaggio "Non puoi muoverti in quella direzione!"
probabilmente non viene mai mostrato. Da risolvere facendo comunicare
esplicitamente l'esito da `moveTo()` (eccezione di dominio dedicata, es.
`NoNeighborInDirectionException`, oppure un valore di ritorno booleano),
invece di affidarsi a un catch generico su un'eccezione che non arriva mai.
`LookCommandExecutor` ha un problema simile: `Assert::notNull($character->getLocation())`
su una property tipizzata non-nullable, quindi è un assert sempre vero (dead
code) da rimuovere.

## Refusi minori

- `UnknowCommand` / `UnknowCommandExecutor` → rinominare in `UnknownCommand` /
  `UnknownCommandExecutor` per coerenza con `UnknownCommandResponse`, già
  scritto correttamente.
- `CharacterRepository::add()` viene usato sia per creare che per aggiornare
  un personaggio esistente (upsert). Valutare di rinominarlo `save()` per
  riflettere meglio il comportamento, riservando `add()` al solo inserimento.
