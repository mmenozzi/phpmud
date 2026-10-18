export const WIDTH = 800;
export const HEIGHT = 560;
export const ROOM = { left: 60, top: 60, right: 740, bottom: 500 };
export const CENTER = { x: 400, y: 290 };

export const DOORS = {
    north: { x: 400, y: ROOM.top },
    south: { x: 400, y: ROOM.bottom },
    west: { x: ROOM.left, y: 280 },
    east: { x: ROOM.right, y: 280 },
    up: { x: 650, y: 135 },
    down: { x: 150, y: 425 },
};

export const SLOTS = [
    { x: 300, y: 230 }, { x: 500, y: 230 }, { x: 300, y: 350 }, { x: 500, y: 350 },
    { x: 400, y: 185 }, { x: 400, y: 395 }, { x: 230, y: 290 }, { x: 570, y: 290 },
    { x: 220, y: 200 }, { x: 580, y: 200 }, { x: 220, y: 380 }, { x: 580, y: 380 },
];

export function isFreeForDecoration(point, margin = 0) {
    if (Math.hypot(point.x - CENTER.x, point.y - CENTER.y) < 200 + margin) {
        return false;
    }

    return Object.values(DOORS).every((door) => Math.hypot(point.x - door.x, point.y - door.y) > 90 + margin);
}

export function randomFreePoint(random, margin = 0) {
    for (let attempt = 0; attempt < 200; attempt++) {
        const point = {
            x: ROOM.left + 30 + random() * (ROOM.right - ROOM.left - 60),
            y: ROOM.top + 30 + random() * (ROOM.bottom - ROOM.top - 60),
        };
        if (isFreeForDecoration(point, margin)) {
            return point;
        }
    }

    return { x: ROOM.left + 40, y: ROOM.top + 40 };
}
