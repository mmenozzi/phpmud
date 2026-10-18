import { svg } from './svg.js';
import { ROOM, CENTER, randomFreePoint } from './layout.js';

function scatter(group, random, count, margin, draw) {
    for (let i = 0; i < count; i++) {
        group.append(draw(randomFreePoint(random, margin), random));
    }
}

function rock({ x, y }, random, fill) {
    const size = 14 + random() * 22;
    const points = Array.from({ length: 7 }, (_, i) => {
        const angle = (i / 7) * Math.PI * 2;
        const radius = size * (0.7 + random() * 0.4);

        return `${(x + Math.cos(angle) * radius).toFixed(1)},${(y + Math.sin(angle) * radius * 0.75).toFixed(1)}`;
    });

    return svg('polygon', { points: points.join(' '), fill, opacity: 0.9 });
}

function flagstones(group, stroke) {
    for (let x = ROOM.left + 40; x < ROOM.right; x += 40) {
        group.append(svg('line', { x1: x, y1: ROOM.top, x2: x, y2: ROOM.bottom, stroke, 'stroke-width': 1 }));
    }
    for (let y = ROOM.top + 40; y < ROOM.bottom; y += 40) {
        group.append(svg('line', { x1: ROOM.left, y1: y, x2: ROOM.right, y2: y, stroke, 'stroke-width': 1 }));
    }
}

function torch({ x, y }) {
    return svg('g', {}, [
        svg('circle', { cx: x, cy: y, r: 26, fill: '#ffb347', opacity: 0.18 }),
        svg('circle', { cx: x, cy: y, r: 6, fill: '#ffcf70' }),
    ]);
}

function pillar({ x, y }, fill, stroke) {
    return svg('g', {}, [
        svg('ellipse', { cx: x + 4, cy: y + 6, rx: 20, ry: 12, fill: '#000', opacity: 0.25 }),
        svg('circle', { cx: x, cy: y, r: 18, fill, stroke, 'stroke-width': 3 }),
    ]);
}

const WALL_TORCHES = [
    { x: ROOM.left + 120, y: ROOM.top + 8 }, { x: ROOM.right - 120, y: ROOM.top + 8 },
    { x: ROOM.left + 120, y: ROOM.bottom - 8 }, { x: ROOM.right - 120, y: ROOM.bottom - 8 },
];

const CORNERS = [
    { x: ROOM.left + 70, y: ROOM.top + 70 }, { x: ROOM.right - 70, y: ROOM.top + 70 },
    { x: ROOM.left + 70, y: ROOM.bottom - 70 }, { x: ROOM.right - 70, y: ROOM.bottom - 70 },
];

export const THEMES = {
    cave: {
        label: 'Caverna',
        floor: '#4a4036', wall: '#2a231d', accent: '#7a6a58',
        decorate(group, random) {
            scatter(group, random, 70, -60, ({ x, y }) => svg('circle', { cx: x, cy: y, r: 1 + random() * 2, fill: '#6b5d4e' }));
            scatter(group, random, 12, 10, (point) => rock(point, random, '#5e5245'));
        },
    },
    corridor: {
        label: 'Corridoio',
        floor: '#5a5a62', wall: '#2c2c33', accent: '#9a9aa8',
        decorate(group) {
            flagstones(group, '#4a4a52');
            WALL_TORCHES.forEach((point) => group.append(torch(point)));
        },
    },
    hall: {
        label: 'Sala',
        floor: '#6b5a48', wall: '#3a2f25', accent: '#8c2f39',
        decorate(group) {
            flagstones(group, '#5d4e3e');
            group.append(svg('rect', {
                x: CENTER.x - 70, y: ROOM.top + 10, width: 140, height: ROOM.bottom - ROOM.top - 20,
                fill: '#8c2f39', opacity: 0.55, rx: 6,
            }));
            CORNERS.forEach((point) => group.append(pillar(point, '#a89580', '#7a6a58')));
            WALL_TORCHES.forEach((point) => group.append(torch(point)));
        },
    },
    temple: {
        label: 'Tempio',
        floor: '#8a8170', wall: '#4b4538', accent: '#d8c690',
        decorate(group) {
            for (const y of [ROOM.top + 80, ROOM.bottom - 80]) {
                for (const x of [ROOM.left + 70, ROOM.left + 160, ROOM.right - 160, ROOM.right - 70]) {
                    group.append(pillar({ x, y }, '#cfc6b0', '#9a907a'));
                }
            }
            group.append(svg('rect', { x: ROOM.left + 20, y: 250, width: 50, height: 70, fill: '#d8c690', rx: 4 }));
            group.append(svg('circle', { cx: ROOM.left + 45, cy: 285, r: 9, fill: '#ffef9f' }));
        },
    },
    garden: {
        label: 'Giardino',
        floor: '#3f5a33', wall: '#2a3a22', accent: '#e68fac',
        decorate(group, random) {
            scatter(group, random, 50, -40, ({ x, y }) => svg('circle', {
                cx: x, cy: y, r: 3, fill: ['#e68fac', '#f2d16b', '#9fd4ff'][Math.floor(random() * 3)],
            }));
            scatter(group, random, 9, 20, ({ x, y }) => svg('g', {}, [
                svg('circle', { cx: x + 5, cy: y + 8, r: 30, fill: '#000', opacity: 0.2 }),
                svg('circle', { cx: x, cy: y, r: 28, fill: '#2f6b2a' }),
                svg('circle', { cx: x - 8, cy: y - 8, r: 12, fill: '#3f8a37' }),
            ]));
        },
    },
    water: {
        label: 'Acqua',
        floor: '#3d4f57', wall: '#1f2b30', accent: '#6fc3df',
        decorate(group, random) {
            const left = random() < 0.5;
            const cx = left ? ROOM.left + 120 : ROOM.right - 120;
            group.append(svg('ellipse', { cx, cy: ROOM.bottom - 110, rx: 110, ry: 80, fill: '#1f6f8b', opacity: 0.85 }));
            for (let r = 20; r < 80; r += 20) {
                group.append(svg('ellipse', {
                    class: 'ripple', cx, cy: ROOM.bottom - 110, rx: r * 1.3, ry: r, fill: 'none', stroke: '#9fe0f5', opacity: 0.4,
                }));
            }
            scatter(group, random, 6, 0, (point) => rock(point, random, '#4f646d'));
        },
    },
    lava: {
        label: 'Lava',
        floor: '#3a2a26', wall: '#1d1412', accent: '#ff6a2b',
        decorate(group, random) {
            const y = ROOM.top + 60 + random() * 40;
            const d = `M ${ROOM.left} ${y} C 200 ${y + 60}, 260 ${y - 30}, 330 ${y + 10} S 520 ${y - 40}, ${ROOM.right} ${y + 20}`;
            group.append(svg('path', { d, fill: 'none', stroke: '#ff6a2b', 'stroke-width': 26, opacity: 0.35, class: 'glow' }));
            group.append(svg('path', { d, fill: 'none', stroke: '#ffb347', 'stroke-width': 10 }));
            scatter(group, random, 8, 0, (point) => rock(point, random, '#2b1f1c'));
        },
    },
    crypt: {
        label: 'Cripta',
        floor: '#43414a', wall: '#232129', accent: '#b9b4c7',
        decorate(group) {
            flagstones(group, '#39373f');
            for (const x of [ROOM.left + 40, ROOM.right - 100]) {
                for (const y of [ROOM.top + 40, ROOM.bottom - 120]) {
                    group.append(svg('rect', { x, y, width: 60, height: 80, rx: 8, fill: '#6d6878', stroke: '#8e899b', 'stroke-width': 2 }));
                    group.append(svg('line', { x1: x + 30, y1: y + 20, x2: x + 30, y2: y + 60, stroke: '#b9b4c7', 'stroke-width': 3 }));
                    group.append(svg('line', { x1: x + 18, y1: y + 32, x2: x + 42, y2: y + 32, stroke: '#b9b4c7', 'stroke-width': 3 }));
                }
            }
        },
    },
    library: {
        label: 'Biblioteca',
        floor: '#5b4632', wall: '#2e2318', accent: '#c9a96e',
        decorate(group, random) {
            for (const x of [ROOM.left + 10, ROOM.right - 40]) {
                for (const y of [ROOM.top + 20, ROOM.bottom - 150]) {
                    group.append(svg('rect', { x, y, width: 30, height: 130, fill: '#3b2a1a' }));
                    for (let book = y + 6; book < y + 124; book += 8) {
                        group.append(svg('rect', {
                            x: x + 3, y: book, width: 24, height: 6,
                            fill: ['#8c2f39', '#2f5d8c', '#c9a96e', '#3f7a3a'][Math.floor(random() * 4)],
                        }));
                    }
                }
            }
            group.append(svg('rect', { x: ROOM.left + 90, y: ROOM.top + 30, width: 90, height: 50, rx: 4, fill: '#7a5a3a' }));
        },
    },
    ice: {
        label: 'Ghiaccio',
        floor: '#b9d4e0', wall: '#6f8fa0', accent: '#e8f7ff',
        decorate(group, random) {
            scatter(group, random, 10, 10, ({ x, y }) => {
                const height = 30 + random() * 40;

                return svg('polygon', {
                    points: `${x},${y - height} ${x + 12},${y} ${x},${y + 8} ${x - 12},${y}`,
                    fill: '#e8f7ff', stroke: '#9cc7dc', opacity: 0.9,
                });
            });
        },
    },
    tower: {
        label: 'Torre',
        floor: '#5d5866', wall: '#2f2c36', accent: '#c7b8ff',
        decorate(group) {
            group.append(svg('circle', { cx: CENTER.x, cy: CENTER.y, r: 210, fill: 'none', stroke: '#c7b8ff', 'stroke-width': 3, opacity: 0.4 }));
            group.append(svg('circle', { cx: CENTER.x, cy: CENTER.y, r: 190, fill: 'none', stroke: '#c7b8ff', 'stroke-width': 1, opacity: 0.4 }));
            const telescope = CORNERS[2];
            group.append(svg('g', { transform: `rotate(-35 ${telescope.x} ${telescope.y})` }, [
                svg('rect', { x: telescope.x - 30, y: telescope.y - 8, width: 70, height: 16, rx: 6, fill: '#b08d57' }),
                svg('circle', { cx: telescope.x, cy: telescope.y + 14, r: 6, fill: '#3a3540' }),
            ]));
        },
    },
};

const FALLBACK_THEMES = Object.keys(THEMES);

export function themeFor(name, seed) {
    return THEMES[name] ?? THEMES[FALLBACK_THEMES[seed % FALLBACK_THEMES.length]];
}
