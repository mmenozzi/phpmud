const NAMESPACE = 'http://www.w3.org/2000/svg';

export function svg(tag, attributes = {}, children = []) {
    const element = document.createElementNS(NAMESPACE, tag);
    for (const [name, value] of Object.entries(attributes)) {
        element.setAttribute(name, String(value));
    }
    for (const child of children) {
        element.append(child);
    }

    return element;
}

export function hash(text) {
    let value = 0x811c9dc5;
    for (const character of text) {
        value ^= character.codePointAt(0);
        value = Math.imul(value, 0x01000193);
    }

    return value >>> 0;
}

export function seededRandom(seed) {
    let state = seed;

    return () => {
        state = (state + 0x6d2b79f5) | 0;
        let t = Math.imul(state ^ (state >>> 15), 1 | state);
        t = (t + Math.imul(t ^ (t >>> 7), 61 | t)) ^ t;

        return ((t ^ (t >>> 14)) >>> 0) / 4294967296;
    };
}
