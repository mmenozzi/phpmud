import { svg, hash, seededRandom } from './svg.js';
import { WIDTH, HEIGHT, ROOM, CENTER, DOORS, SLOTS } from './layout.js';
import { DIRECTIONS, label, opposite } from './directions.js';
import { themeFor } from './themes.js';

const WALK_MS = 700;

const OUTSIDE = {
    north: { x: DOORS.north.x, y: DOORS.north.y - 50 },
    south: { x: DOORS.south.x, y: DOORS.south.y + 50 },
    west: { x: DOORS.west.x - 50, y: DOORS.west.y },
    east: { x: DOORS.east.x + 50, y: DOORS.east.y },
    up: DOORS.up,
    down: DOORS.down,
};

function place(element, { x, y }) {
    element.style.transform = `translate(${x}px, ${y}px)`;
}

function commitStyles(element) {
    element.getBoundingClientRect();
}

function wait(milliseconds) {
    return new Promise((resolve) => setTimeout(resolve, milliseconds));
}

export class Scene {
    #root;
    #room;
    #sprites;
    #tint;
    #exits;
    #onExit;
    #myName = null;
    #me = null;
    #others = new Map();

    constructor(root, { onExit }) {
        this.#root = root;
        this.#onExit = onExit;
        root.setAttribute('viewBox', `0 0 ${WIDTH} ${HEIGHT}`);
        this.#room = svg('g', { class: 'room' });
        this.#sprites = svg('g', { class: 'sprites' });
        this.#tint = svg('rect', { class: 'tint', x: 0, y: 0, width: WIDTH, height: HEIGHT });
        this.#exits = svg('g', { class: 'exits' });
        root.append(this.#room, this.#sprites, this.#tint, this.#exits);
    }

    setMyName(name) {
        this.#myName = name;
    }

    setDayPhase(dayPhase) {
        this.#root.dataset.dayPhase = dayPhase;
    }

    showLocation(location, themeName, enteringFrom = null) {
        const seed = hash(location.id);
        const theme = themeFor(themeName, seed);
        this.#root.dataset.theme = theme.label;
        this.#drawRoom(theme, seededRandom(seed));
        this.#drawExits(location.exits, theme);
        this.#sprites.replaceChildren();
        this.#others.clear();
        for (const { name } of location.characters) {
            this.#others.set(name, this.#addSprite(name, this.#freeSlot(), false));
        }
        const start = enteringFrom === null ? CENTER : OUTSIDE[enteringFrom];
        this.#me = this.#addSprite(this.#myName ?? '', start, true);
        if (enteringFrom !== null) {
            this.#me.classList.add('hidden');
            commitStyles(this.#me);
            this.#me.classList.remove('hidden');
            place(this.#me, CENTER);
        }
    }

    async walkMeTowards(direction) {
        if (this.#me === null) {
            return;
        }
        place(this.#me, OUTSIDE[direction]);
        this.#me.classList.add('hidden');
        await wait(WALK_MS);
    }

    returnMeToCenter() {
        if (this.#me !== null) {
            this.#me.classList.remove('hidden');
            place(this.#me, CENTER);
        }
    }

    arrive(name, via) {
        if (this.#others.has(name)) {
            return;
        }
        const slot = this.#freeSlot();
        const sprite = this.#addSprite(name, via === null ? slot : OUTSIDE[opposite(via)], false);
        sprite.dataset.slot = String(SLOTS.indexOf(slot));
        sprite.classList.add('hidden');
        this.#others.set(name, sprite);
        commitStyles(sprite);
        sprite.classList.remove('hidden');
        place(sprite, slot);
    }

    async leave(name, via) {
        const sprite = this.#others.get(name);
        if (sprite === undefined) {
            return;
        }
        this.#others.delete(name);
        delete sprite.dataset.slot;
        sprite.classList.add('leaving', 'hidden');
        if (via !== null) {
            place(sprite, OUTSIDE[via]);
        }
        await wait(WALK_MS);
        sprite.remove();
    }

    #drawRoom(theme, random) {
        const decorations = svg('g', { class: 'decorations' });
        theme.decorate(decorations, random);
        this.#room.replaceChildren(
            svg('rect', { x: 0, y: 0, width: WIDTH, height: HEIGHT, fill: theme.wall }),
            svg('rect', {
                x: ROOM.left, y: ROOM.top, width: ROOM.right - ROOM.left, height: ROOM.bottom - ROOM.top,
                fill: theme.floor, stroke: '#000', 'stroke-opacity': 0.35, 'stroke-width': 6,
            }),
            decorations,
        );
        this.#root.style.setProperty('--accent', theme.accent);
    }

    #drawExits(exits, theme) {
        this.#exits.replaceChildren();
        for (const direction of DIRECTIONS) {
            const exit = exits[direction];
            if (exit === undefined) {
                continue;
            }
            const group = svg('g', {
                class: `exit exit-${direction}`,
                role: 'button',
                tabindex: 0,
                'data-direction': direction,
                'aria-label': `${label(direction)}: ${exit.name}`,
            }, [this.#doorShape(direction, theme), ...this.#doorLabel(direction, exit.name)]);
            group.addEventListener('click', () => this.#onExit(direction));
            group.addEventListener('keydown', (event) => {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    this.#onExit(direction);
                }
            });
            this.#exits.append(group);
        }
    }

    #doorShape(direction, theme) {
        const { x, y } = DOORS[direction];
        if (direction === 'north' || direction === 'south') {
            return svg('rect', { class: 'door', x: x - 45, y: y - 20, width: 90, height: 40, rx: 6, fill: theme.floor });
        }
        if (direction === 'east' || direction === 'west') {
            return svg('rect', { class: 'door', x: x - 20, y: y - 45, width: 40, height: 90, rx: 6, fill: theme.floor });
        }
        const steps = [0, 1, 2, 3].map((step) => svg('rect', {
            x: x - 30 + (direction === 'up' ? step * 6 : 0),
            y: y - 30 + step * 15,
            width: 60 - step * 6 * (direction === 'up' ? 2 : 0),
            height: 12,
            rx: 2,
            fill: direction === 'up' ? '#a89580' : '#1b1612',
            opacity: direction === 'up' ? 1 - step * 0.15 : 0.5 + step * 0.15,
        }));

        return svg('g', { class: 'door' }, [svg('rect', { x: x - 36, y: y - 36, width: 72, height: 72, rx: 8, fill: '#000', opacity: 0.25 }), ...steps]);
    }

    #doorLabel(direction, name) {
        const { x, y } = DOORS[direction];
        const anchors = {
            north: { x, y: y + 42, anchor: 'middle' },
            south: { x, y: y - 32, anchor: 'middle' },
            west: { x: x + 30, y: y + 64, anchor: 'start' },
            east: { x: x - 30, y: y + 64, anchor: 'end' },
            up: { x, y: y + 54, anchor: 'middle' },
            down: { x, y: y + 54, anchor: 'middle' },
        };
        const position = anchors[direction];
        const text = svg('text', { class: 'exit-label', x: position.x, y: position.y, 'text-anchor': position.anchor });
        text.textContent = `${label(direction)} · ${name}`;

        return [text];
    }

    #addSprite(name, position, isMe) {
        const color = isMe ? '#f2c14e' : `hsl(${hash(name) % 360} 55% 55%)`;
        const nameplate = svg('text', { class: 'nameplate', x: 0, y: 38, 'text-anchor': 'middle' });
        nameplate.textContent = name;
        const sprite = svg('g', { class: isMe ? 'sprite me' : 'sprite', 'data-name': name }, [
            svg('ellipse', { cx: 0, cy: 22, rx: 18, ry: 6, fill: '#000', opacity: 0.35 }),
            svg('path', { d: 'M -14 20 Q -14 -2 0 -2 Q 14 -2 14 20 Z', fill: color, stroke: '#000', 'stroke-opacity': 0.4 }),
            svg('circle', { cx: 0, cy: -12, r: 10, fill: '#f1d3b3', stroke: '#000', 'stroke-opacity': 0.4 }),
            nameplate,
        ]);
        place(sprite, position);
        this.#sprites.append(sprite);
        if (!isMe) {
            const slotIndex = SLOTS.findIndex((slot) => slot === position);
            if (slotIndex >= 0) {
                sprite.dataset.slot = String(slotIndex);
            }
        }

        return sprite;
    }

    #freeSlot() {
        const taken = new Set([...this.#others.values()].map((sprite) => sprite.dataset.slot));
        const index = SLOTS.findIndex((_, i) => !taken.has(String(i)));

        return SLOTS[index === -1 ? this.#others.size % SLOTS.length : index];
    }
}
