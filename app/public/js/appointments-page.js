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

    function showMessage(message, type = "success") {
        const content = getContentElement();
        if (!content || typeof message !== "string" || message.trim() === "") {
            return;
        }

        const notice = document.createElement("p");
        notice.className = `notice ${type === "error" ? "notice-error" : "notice-success"}`;
        notice.textContent = message;
        content.prepend(notice);

        window.setTimeout(() => notice.remove(), 4000);
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
            showMessage(payload.message || "Nao foi possivel salvar este horario.", "error");
            return;
        }

        replaceSlot(form, payload.slot_html);
        showMessage(payload.message || "Agenda atualizada com sucesso.");
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
            showMessage(payload.message || "Nao foi possivel liberar este horario.", "error");
            return;
        }

        replaceSlot(form, payload.slot_html);
        showMessage(payload.message || "Agendamento cancelado com sucesso.");
    }

    function requestFilterForm(form) {
        requestAgenda(form.action + "?" + new URLSearchParams(new FormData(form)).toString())
            .catch((error) => showMessage(error.message, "error"));
    }

    document.addEventListener("submit", (event) => {
        const filterForm = event.target.closest("form[data-agenda-filter-form]");
        if (filterForm) {
            event.preventDefault();
            requestFilterForm(filterForm);
            return;
        }

        const slotForm = event.target.closest("form.slot-form");
        if (slotForm) {
            event.preventDefault();
            submitSlotForm(slotForm).catch((error) => showMessage(error.message, "error"));
            return;
        }

        const deleteForm = event.target.closest("form.slot-delete-form");
        if (deleteForm) {
            event.preventDefault();
            submitDeleteForm(deleteForm).catch((error) => showMessage(error.message, "error"));
        }
    });

    document.addEventListener("click", (event) => {
        const navLink = event.target.closest("a[data-agenda-nav]");
        if (!navLink) {
            return;
        }

        event.preventDefault();
        requestAgenda(navLink.href).catch((error) => showMessage(error.message, "error"));
    });

    document.addEventListener("change", (event) => {
        const modeInput = event.target.closest('input[name="schedule_mode"]');
        if (!modeInput) {
            return;
        }

        const filterForm = modeInput.closest("form[data-agenda-filter-form]");
        if (filterForm) {
            requestFilterForm(filterForm);
        }
    });
})();
