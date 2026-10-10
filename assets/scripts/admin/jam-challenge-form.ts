export function initJamChallengeForm(): void {
    const root = document.querySelector<HTMLElement>('.js-challenge-form');
    if (!root) return;

    root.querySelectorAll<HTMLElement>('.js-challenge-collection').forEach(collection => {
        const items = collection.querySelector<HTMLElement>('.js-challenge-items');
        const add = collection.querySelector<HTMLButtonElement>('.js-challenge-add');
        const maximum = Number(collection.dataset.max ?? '12');
        if (!items || !add) return;

        const refresh = (): void => {
            add.disabled = items.querySelectorAll('.js-challenge-row').length >= maximum;
        };
        add.addEventListener('click', (): void => {
            const prototype = collection.dataset.prototype;
            if (!prototype || add.disabled) return;
            const index = Number(collection.dataset.index ?? '0');
            const row = document.createElement('div');
            row.className = `js-challenge-row ${maximum === 3 ? 'admin-challenge-color' : 'admin-challenge-platform'}`;
            const fields = document.createElement('div');
            fields.className = maximum === 3 ? '' : 'admin-form-row';
            fields.innerHTML = prototype.replace(/__name__/g, String(index));
            row.append(fields);
            const remove = document.createElement('button');
            remove.type = 'button';
            remove.className = 'admin-button admin-button--secondary js-challenge-remove';
            remove.textContent = 'Retirer';
            row.append(remove);
            items.append(row);
            collection.dataset.index = String(index + 1);
            refresh();
        });
        items.addEventListener('click', (event: MouseEvent) => {
            const target = event.target;
            if (!(target instanceof Element)) return;
            const button = target.closest('.js-challenge-remove');
            if (!button) return;
            if (maximum === 3 && items.querySelectorAll('.js-challenge-row').length <= 1) return;
            button.closest('.js-challenge-row')?.remove();
            refresh();
        });
        refresh();
    });
}
