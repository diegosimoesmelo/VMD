(() => {
    const contentSelector = "#lesson-monitoring-page-content";

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

        const slotCell = form.closest(".lesson-monitoring-slot");
        if (!slotCell) {
            return;
        }

        slotCell.innerHTML = html;
    }

    async function requestGrid(url) {
        const response = await fetch(url, {
            method: "GET",
            headers: headers(),
            credentials: "same-origin",
        });

        const payload = await parseJson(response);

        if (!response.ok) {
            return;
        }

        replaceContent(payload.html, url);
    }

    async function submitMonitoringForm(form) {
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
        const filterForm = event.target.closest("form[data-lesson-monitoring-filter-form]");
        if (filterForm) {
            event.preventDefault();
            requestGrid(filterForm.action + "?" + new URLSearchParams(new FormData(filterForm)).toString())
                .catch(() => {});
            return;
        }

        const monitorForm = event.target.closest("form.lesson-monitoring-form");
        if (monitorForm) {
            event.preventDefault();
            submitMonitoringForm(monitorForm).catch(() => {});
        }
    });

    document.addEventListener("click", (event) => {
        const navLink = event.target.closest("a[data-lesson-monitoring-nav]");
        if (!navLink) {
            return;
        }

        event.preventDefault();
        requestGrid(navLink.href).catch(() => {});
    });
})();
