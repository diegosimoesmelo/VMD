(() => {
    const contentSelector = "#appointments-page-content";

    function getContentElement() {
        return document.querySelector(contentSelector);
    }

    function headers() {
        return {
            Accept: "application/json",
            "X-Requested-With": "XMLHttpRequest",
        };
    }

    async function parseJson(response) {
        const text = await response.text();

        if (!text) {
            return {};
        }

        try {
            return JSON.parse(text);
        } catch (error) {
            return {};
        }
    }

    function replaceContent(html, url) {
        const content = getContentElement();
        if (!content || typeof html !== "string" || html.trim() === "") {
            return;
        }

        content.innerHTML = html;

        if (typeof url === "string" && url !== "") {
            window.history.replaceState({}, "", url);
        }
    }

    function replaceSlot(form, html) {
        if (typeof html !== "string" || html.trim() === "") {
            return;
        }

        const slotCell = form.closest(".agenda-slot-cell");
        if (!slotCell) {
            return;
        }

        slotCell.innerHTML = html;
    }

    async function requestAgenda(url) {
        const response = await fetch(url, {
            method: "GET",
            headers: headers(),
            credentials: "same-origin",
        });

        const payload = await parseJson(response);

        if (!response.ok) {
            throw new Error(payload.message || "Falha ao carregar a agenda.");
        }

        replaceContent(payload.html, url);
    }

    async function submitSlotForm(form) {
        const response = await fetch(form.action, {
            method: form.method || "POST",
            headers: headers(),
            body: new FormData(form),
            credentials: "same-origin",
        });

        const payload = await parseJson(response);

        if (!response.ok) {
            return;
        }

        replaceSlot(form, payload.slot_html);
    }

    async function submitDeleteForm(form) {
        const response = await fetch(form.action, {
            method: "POST",
            headers: headers(),
            body: new FormData(form),
            credentials: "same-origin",
        });

        const payload = await parseJson(response);

        if (!response.ok) {
            return;
        }

        replaceSlot(form, payload.slot_html);
    }

    document.addEventListener("submit", (event) => {
        const filterForm = event.target.closest("form[data-agenda-filter-form]");
        if (filterForm) {
            event.preventDefault();
            requestAgenda(filterForm.action + "?" + new URLSearchParams(new FormData(filterForm)).toString())
                .catch(() => {});
            return;
        }

        const slotForm = event.target.closest("form.slot-form");
        if (slotForm) {
            event.preventDefault();
            submitSlotForm(slotForm).catch(() => {});
            return;
        }

        const deleteForm = event.target.closest("form.slot-delete-form");
        if (deleteForm) {
            event.preventDefault();
            submitDeleteForm(deleteForm).catch(() => {});
        }
    });

    document.addEventListener("click", (event) => {
        const navLink = event.target.closest("a[data-agenda-nav]");
        if (!navLink) {
            return;
        }

        event.preventDefault();
        requestAgenda(navLink.href).catch(() => {});
    });
})();
