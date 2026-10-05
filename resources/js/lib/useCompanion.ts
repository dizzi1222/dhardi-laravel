import { useCallback, useRef, useState } from 'react';
import type { AssistantAnswer } from '../types';

export interface ChatTurn {
    role: 'user' | 'assistant';
    text: string;
}

export interface AnswerMeta {
    provider: string;
    model: string;
    latencyMs: number;
    cached: boolean;
    costUsd: number;
}

export type CompanionState = 'idle' | 'streaming';

export interface CompanionApi {
    turns: ChatTurn[];
    meta: AnswerMeta | null;
    state: CompanionState;
    error: string | null;
    send: (question: string) => Promise<void>;
    reset: () => void;
}

interface FrameHandlers {
    onDelta: (chunk: string) => void;
    onDone: (answer: AssistantAnswer) => void;
    onError: (message: string, rule: string | null) => void;
}

/**
 * Parses one server-sent frame and dispatches it.
 *
 * Pure and exported so the protocol handling can be tested without a socket.
 * Unparseable frames are ignored rather than thrown: a malformed frame from a
 * network hiccup should not take down a conversation that is otherwise fine.
 */
export function applyFrame(frame: string, handlers: FrameHandlers): void {
    let event = 'message';
    const dataLines: string[] = [];

    for (const line of frame.split('\n')) {
        if (line.startsWith('event:')) {
            event = line.slice(6).trim();
        } else if (line.startsWith('data:')) {
            dataLines.push(line.slice(5).trim());
        }
    }

    if (dataLines.length === 0) {
        return;
    }

    let payload: Record<string, unknown>;

    try {
        payload = JSON.parse(dataLines.join('\n')) as Record<string, unknown>;
    } catch {
        return;
    }

    if (event === 'delta') {
        if (typeof payload.text === 'string' && payload.text !== '') {
            handlers.onDelta(payload.text);
        }

        return;
    }

    if (event === 'done') {
        handlers.onDone(payload as unknown as AssistantAnswer);

        return;
    }

    if (event === 'error') {
        handlers.onError(
            typeof payload.message === 'string' ? payload.message : 'unknown error',
            typeof payload.rule === 'string' ? payload.rule : null,
        );
    }
}

/**
 * Drives the assistant over server-sent events.
 *
 * `EventSource` is not usable here: it only issues GET requests, and the
 * question belongs in a request body. So the stream is read off `fetch` and the
 * frames are parsed by {@link applyFrame}.
 *
 * The in-flight request is aborted when a new question starts and on unmount.
 * A stream that keeps writing into an unmounted component is the usual way to
 * leak a connection and produce a warning on every navigation.
 */
export function useCompanion(): CompanionApi {
    const [turns, setTurns] = useState<ChatTurn[]>([]);
    const [meta, setMeta] = useState<AnswerMeta | null>(null);
    const [state, setState] = useState<CompanionState>('idle');
    const [error, setError] = useState<string | null>(null);

    const conversationId = useRef<string | null>(null);
    const abort = useRef<AbortController | null>(null);

    const reset = useCallback((): void => {
        abort.current?.abort();
        abort.current = null;
        conversationId.current = null;
        setTurns([]);
        setMeta(null);
        setError(null);
        setState('idle');
    }, []);

    const send = useCallback(async (question: string): Promise<void> => {
        const trimmed = question.trim();

        if (trimmed === '') {
            return;
        }

        abort.current?.abort();

        const controller = new AbortController();
        abort.current = controller;

        setTurns((previous) => [
            ...previous,
            { role: 'user', text: trimmed },
            { role: 'assistant', text: '' },
        ]);
        setMeta(null);
        setError(null);
        setState('streaming');

        const appendToAssistant = (chunk: string): void => {
            setTurns((previous) => {
                const next = [...previous];

                for (let index = next.length - 1; index >= 0; index -= 1) {
                    const turn = next[index];

                    if (turn?.role === 'assistant') {
                        next[index] = { ...turn, text: turn.text + chunk };

                        return next;
                    }
                }

                return next;
            });
        };

        try {
            const response = await fetch('/api/assistant/stream', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'text/event-stream',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({
                    question: trimmed,
                    conversation_id: conversationId.current,
                }),
                signal: controller.signal,
            });

            if (!response.ok || response.body === null) {
                throw new Error(`HTTP ${response.status}`);
            }

            const reader = response.body.getReader();
            const decoder = new TextDecoder();

            let buffer = '';

            for (;;) {
                const { done, value } = await reader.read();

                if (done) {
                    break;
                }

                buffer += decoder.decode(value, { stream: true });

                // Frames are separated by a blank line. A partial frame stays in
                // the buffer until the rest of it arrives.
                let boundary = buffer.indexOf('\n\n');

                while (boundary !== -1) {
                    const frame = buffer.slice(0, boundary);
                    buffer = buffer.slice(boundary + 2);
                    boundary = buffer.indexOf('\n\n');

                    applyFrame(frame, {
                        onDelta: appendToAssistant,
                        onDone: (answer) => {
                            conversationId.current = answer.conversation_id;

                            setMeta({
                                provider: answer.provider,
                                model: answer.model,
                                latencyMs: answer.latency_ms,
                                cached: answer.cached,
                                costUsd: answer.cost_usd,
                            });
                        },
                        onError: (message) => setError(message),
                    });
                }
            }
        } catch (caught) {
            if (!controller.signal.aborted) {
                setError(caught instanceof Error ? caught.message : String(caught));
            }
        } finally {
            if (abort.current === controller) {
                abort.current = null;
                setState('idle');
            }
        }
    }, []);

    return { turns, meta, state, error, send, reset };
}