export class Connection {
    #socket;
    #handlers = new Map();

    constructor(url) {
        this.#socket = new WebSocket(url);
        this.#socket.addEventListener('message', (event) => this.#dispatch(JSON.parse(event.data)));
        this.#socket.addEventListener('open', () => this.#emit('open', {}));
        this.#socket.addEventListener('close', () => this.#emit('close', {}));
    }

    static toSameOrigin(path) {
        const scheme = window.location.protocol === 'https:' ? 'wss' : 'ws';

        return new Connection(`${scheme}://${window.location.host}${path}`);
    }

    on(type, handler) {
        this.#handlers.set(type, [...(this.#handlers.get(type) ?? []), handler]);

        return this;
    }

    send(message) {
        if (this.#socket.readyState === WebSocket.OPEN) {
            this.#socket.send(JSON.stringify(message));
        }
    }

    #dispatch(message) {
        if (!this.#handlers.has(message.type)) {
            console.warn('Unhandled message', message);
        }
        this.#emit(message.type, message);
    }

    #emit(type, message) {
        for (const handler of this.#handlers.get(type) ?? []) {
            handler(message);
        }
    }
}
