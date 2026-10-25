import { Connection } from './connection.js';
import { Scene } from './scene.js';
import { DIRECTIONS, label, opposite, leftMessage, arrivedMessage } from './directions.js';

const DAY_PHASES = {
    dawn: { label: 'Alba', started: 'Il sole sorge a est: un nuovo giorno ha inizio.' },
    noon: { label: 'Mezzogiorno', started: 'Il sole è alto nel cielo: è mezzogiorno.' },
    sunset: { label: 'Tramonto', started: 'Il sole tramonta a ovest, tingendo il cielo di rosso.' },
    night: { label: 'Notte', started: 'Cala la notte.' },
};

const KEYS = {
    ArrowUp: 'north',
    ArrowRight: 'east',
    ArrowDown: 'south',
    ArrowLeft: 'west',
    PageUp: 'up',
    PageDown: 'down',
};

const $ = (id) => document.getElementById(id);

const scenes = await fetch('content/scenes.json')
    .then((response) => (response.ok ? response.json() : {}))
    .catch(() => ({}));

const state = {
    identifiedName: null,
    pendingPassword: null,
    myName: null,
    location: null,
    occupants: [],
    pendingMove: null,
};

const scene = new Scene($('scene'), { onExit: move });
const connection = Connection.toSameOrigin('/ws');

function log(text, kind = 'info') {
    const item = document.createElement('li');
    item.textContent = text;
    item.className = kind;
    $('log').prepend(item);
    while ($('log').children.length > 50) {
        $('log').lastChild.remove();
    }
}

function loginMessage(text) {
    $('login-message').textContent = text;
}

function showLoginForm(message = '') {
    state.identifiedName = null;
    state.pendingPassword = null;
    $('create-form').hidden = true;
    $('login-form').hidden = false;
    loginMessage(message);
    $('login-name').focus();
}

function showCreateForm(name) {
    state.identifiedName = name;
    $('create-name').textContent = name;
    $('create-form').reset();
    $('create-password').value = state.pendingPassword ?? '';
    state.pendingPassword = null;
    $('create-message').textContent = '';
    $('login-form').hidden = true;
    $('create-form').hidden = false;
    $('create-form').querySelector('input[name="sex"]').focus();
}

function enterTheGame() {
    $('login').hidden = true;
    $('game').hidden = false;
}

const CREATION_FAILURES = {
    name_not_allowed: 'Nome non ammesso: solo lettere, da 3 a 15.',
    name_taken: 'Questo nome è già stato preso.',
};

function renderOccupants() {
    $('occupants').replaceChildren(...state.occupants.map((name) => {
        const item = document.createElement('li');
        item.textContent = name;

        return item;
    }));
    if (state.occupants.length === 0) {
        const item = document.createElement('li');
        item.textContent = 'Nessuno';
        item.className = 'empty';
        $('occupants').append(item);
    }
}

function renderPanel(location) {
    $('room-name').textContent = location.name;
    $('room-description').textContent = location.description;
    $('exits').replaceChildren(...DIRECTIONS.filter((direction) => location.exits[direction]).map((direction) => {
        const button = document.createElement('button');
        button.type = 'button';
        button.dataset.direction = direction;
        button.textContent = `${label(direction)}: ${location.exits[direction].name}`;
        button.addEventListener('click', () => move(direction));
        const item = document.createElement('li');
        item.append(button);

        return item;
    }));
    renderOccupants();
}

function move(direction) {
    if (state.pendingMove !== null || state.location?.exits[direction] === undefined) {
        return;
    }
    state.pendingMove = { direction, walking: scene.walkMeTowards(direction) };
    connection.send({ type: 'move', direction });
}

function setDayPhase(dayPhase) {
    scene.setDayPhase(dayPhase);
    $('day-phase').hidden = false;
    $('day-phase').textContent = DAY_PHASES[dayPhase]?.label ?? dayPhase;
}

async function showLocation(location) {
    let enteringFrom = null;
    if (state.pendingMove !== null) {
        await state.pendingMove.walking;
        enteringFrom = opposite(state.pendingMove.direction);
        state.pendingMove = null;
    }
    state.location = location;
    state.occupants = location.characters.map(({ name }) => name);
    renderPanel(location);
    scene.showLocation(location, scenes[location.id]?.theme, enteringFrom);
}

$('login-form').addEventListener('submit', (event) => {
    event.preventDefault();
    const name = $('login-name').value.trim();
    const password = $('login-password').value;
    loginMessage('');
    if (state.identifiedName?.toLowerCase() !== name.toLowerCase()) {
        state.pendingPassword = password;
        connection.send({ type: 'identify', name });

        return;
    }
    connection.send({ type: 'authenticate', password });
});

$('create-form').addEventListener('submit', (event) => {
    event.preventDefault();
    const password = $('create-password').value;
    if (password !== $('create-password-repeat').value) {
        $('create-password-repeat').value = '';
        $('create-message').textContent = 'Le password non coincidono.';

        return;
    }
    $('create-message').textContent = '';
    const sex = new FormData($('create-form')).get('sex');
    connection.send({ type: 'create', name: state.identifiedName, password, sex });
});

$('create-cancel').addEventListener('click', () => showLoginForm());

$('reconnect').addEventListener('click', () => window.location.reload());

document.addEventListener('keydown', (event) => {
    if ($('game').hidden || event.target instanceof HTMLInputElement || !(event.key in KEYS)) {
        return;
    }
    event.preventDefault();
    move(KEYS[event.key]);
});

connection
    .on('identified', ({ name, exists, allowed }) => {
        if (!allowed) {
            showLoginForm('Nome non ammesso: solo lettere, da 3 a 15.');

            return;
        }
        if (!exists) {
            showCreateForm(name);

            return;
        }
        state.identifiedName = name;
        if (state.pendingPassword !== null) {
            connection.send({ type: 'authenticate', password: state.pendingPassword });
            state.pendingPassword = null;
        }
    })
    .on('authentication_failed', () => {
        $('login-password').value = '';
        loginMessage('Password errata.');
    })
    .on('too_many_attempts', () => {
        $('disconnected-message').textContent = 'Troppi tentativi falliti. Addio!';
    })
    .on('authenticated', enterTheGame)
    .on('character_created', enterTheGame)
    .on('creation_failed', ({ reason }) => {
        if (reason === 'password_too_short') {
            $('create-password').value = '';
            $('create-password-repeat').value = '';
            $('create-message').textContent = 'La password è troppo corta.';

            return;
        }
        showLoginForm(CREATION_FAILURES[reason] ?? reason);
    })
    .on('whoami', ({ name }) => {
        state.myName = name;
        scene.setMyName(name);
        $('whoami').textContent = name;
    })
    .on('location', showLocation)
    .on('move_failed', () => {
        state.pendingMove = null;
        scene.returnMeToCenter();
        log('Non puoi muoverti in quella direzione!', 'warning');
    })
    .on('day_phase', ({ day_phase: dayPhase }) => setDayPhase(dayPhase))
    .on('day_phase_started', ({ day_phase: dayPhase }) => {
        setDayPhase(dayPhase);
        log(DAY_PHASES[dayPhase]?.started ?? dayPhase);
    })
    .on('character_arrived', ({ name, via }) => {
        if (!state.occupants.includes(name)) {
            state.occupants.push(name);
        }
        renderOccupants();
        scene.arrive(name, via);
        log(arrivedMessage(name, via));
    })
    .on('character_left', ({ name, via }) => {
        state.occupants = state.occupants.filter((occupant) => occupant !== name);
        renderOccupants();
        scene.leave(name, via);
        log(leftMessage(name, via));
    })
    .on('unknown_command', () => log('Cosa???', 'warning'))
    .on('error', ({ message }) => {
        loginMessage(message);
        log(message, 'warning');
    })
    .on('close', () => {
        $('disconnected').hidden = false;
        $('game').hidden = true;
        $('login').hidden = true;
    });
