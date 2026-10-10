export function initJamCountdown(): void {
    const timer = document.querySelector<HTMLElement>('.js-jam-countdown');
    if (!timer) return;

    const target = Date.parse(timer.dataset.target ?? '');
    const server = Date.parse(timer.dataset.serverNow ?? '');
    if (!Number.isFinite(target) || !Number.isFinite(server)) return;

    // Le serveur fait foi, même si l'horloge du visiteur est décalée.
    const offset = server - Date.now();
    let finished = false;
    let interval: ReturnType<typeof setInterval>;
    const draw = (): void => {
        const secondsLeft = Math.max(0, Math.ceil((target - Date.now() - offset) / 1000));
        const values: Record<string, number> = {
            days: Math.floor(secondsLeft / 86400),
            hours: Math.floor((secondsLeft % 86400) / 3600),
            minutes: Math.floor((secondsLeft % 3600) / 60),
            seconds: secondsLeft % 60,
        };
        for (const [name, value] of Object.entries(values)) {
            const element = timer.querySelector<HTMLElement>(`[data-jam-unit="${name}"]`);
            if (element) element.textContent = String(value).padStart(2, '0');
        }
        if (secondsLeft === 0 && !finished) {
            finished = true;
            clearInterval(interval);
            window.location.reload();
        }
    };
    interval = setInterval(draw, 1000);
    draw();
}
