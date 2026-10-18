export const DIRECTIONS = ['north', 'east', 'south', 'west', 'up', 'down'];

const OPPOSITES = {
    north: 'south',
    east: 'west',
    south: 'north',
    west: 'east',
    up: 'down',
    down: 'up',
};

const LABELS = {
    north: 'Nord',
    east: 'Est',
    south: 'Sud',
    west: 'Ovest',
    up: 'Su',
    down: 'Giù',
};

export function opposite(direction) {
    return OPPOSITES[direction];
}

export function label(direction) {
    return LABELS[direction];
}

export function leftMessage(name, via) {
    switch (via) {
        case null:
            return `${name} se ne va.`;
        case 'up':
            return `${name} va verso l'alto.`;
        case 'down':
            return `${name} va verso il basso.`;
        default:
            return `${name} va verso ${label(via).toLowerCase()}.`;
    }
}

export function arrivedMessage(name, via) {
    switch (via === null ? null : opposite(via)) {
        case null:
            return `${name} arriva.`;
        case 'up':
            return `${name} arriva dall'alto.`;
        case 'down':
            return `${name} arriva dal basso.`;
        default:
            return `${name} arriva da ${label(opposite(via)).toLowerCase()}.`;
    }
}
