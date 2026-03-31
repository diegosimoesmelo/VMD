(() => {
    const DEFAULT_MESSAGE = "Ocorreu um erro interno ao processar sua solicitacao. Tente novamente.";

    function getModalElements() {
        const modal = document.getElementById("global-error-modal");
        const card = document.getElementById("global-error-modal-card");
        const title = document.getElementById("global-error-modal-title");
        const message = document.getElementById("global-error-modal-message");

        if (!modal || !card || !title || !message) {
            return null;
        }

        return { modal, card, title, message };
    }

    function closeModal() {
        const elements = getModalElements();
        if (!elements) {
            return;
        }

        elements.modal.classList.remove("is-open");
        elements.modal.setAttribute("aria-hidden", "true");
    }

    function resolveTitle(type, explicitTitle) {
        if (typeof explicitTitle === "string" && explicitTitle.trim() !== "") {
            return explicitTitle.trim();
        }

        return type === "warning" ? "Aviso" : "Erro no sistema";
    }

    function showModal(message, options = {}) {
        const elements = getModalElements();
        if (!elements) {
            return;
        }

        const type = options.type === "warning" ? "warning" : "error";
        elements.card.setAttribute("data-alert-type", type);
        elements.title.textContent = resolveTitle(type, options.title);
        elements.message.textContent = message || DEFAULT_MESSAGE;
        elements.modal.classList.add("is-open");
        elements.modal.setAttribute("aria-hidden", "false");
    }

    function normalizeMessage(payloadMessage) {
        if (typeof payloadMessage === "string" && payloadMessage.trim() !== "") {
            return payloadMessage.trim();
        }

        return DEFAULT_MESSAGE;
    }

    function shouldHandleStatus(status) {
        return status === 0 || status === 403 || status === 409 || status === 422 || status >= 500;
    }

    function extractJsonPayload(contentType, body) {
        if (typeof body !== "string" || body.trim() === "") {
            return null;
        }

        if (!contentType || contentType.indexOf("application/json") === -1) {
            return null;
        }

        try {
            return JSON.parse(body);
        } catch (error) {
            return null;
        }
    }

    function handleHttpError(status, payload = null) {
        if (!shouldHandleStatus(status)) {
            return;
        }

        const type = payload && payload.type === "warning" ? "warning" : (status === 422 ? "warning" : "error");
        const title = payload && typeof payload.title === "string" ? payload.title : null;
        const message = payload && typeof payload.message === "string"
            ? payload.message
            : DEFAULT_MESSAGE;

        showModal(normalizeMessage(message), { type, title });

        if (payload && typeof payload.redirect_to === "string" && payload.redirect_to.trim() !== "") {
            window.setTimeout(() => {
                window.location.href = payload.redirect_to;
            }, 1200);
        }
    }

    function installModalInteractions() {
        const elements = getModalElements();
        if (!elements) {
            return;
        }

        elements.modal.addEventListener("click", (event) => {
            if (event.target === elements.modal || event.target.hasAttribute("data-alert-modal-close")) {
                closeModal();
            }
        });

        document.addEventListener("keydown", (event) => {
            if (event.key === "Escape" && elements.modal.classList.contains("is-open")) {
                closeModal();
            }
        });
    }

    function installFetchInterceptor() {
        if (typeof window.fetch !== "function") {
            return;
        }

        const originalFetch = window.fetch.bind(window);

        window.fetch = async function fetchWithGlobalErrorHandler(...args) {
            try {
                const response = await originalFetch(...args);

                if (shouldHandleStatus(response.status)) {
                    let payload = null;
                    const contentType = response.headers.get("content-type");

                    try {
                        const clonedResponse = response.clone();
                        payload = extractJsonPayload(contentType, await clonedResponse.text());
                    } catch (error) {
                        payload = null;
                    }

                    handleHttpError(response.status, payload);
                }

                return response;
            } catch (error) {
                showModal(DEFAULT_MESSAGE, { type: "error" });
                throw error;
            }
        };
    }

    function installXmlHttpRequestInterceptor() {
        if (typeof window.XMLHttpRequest !== "function") {
            return;
        }

        const originalOpen = window.XMLHttpRequest.prototype.open;
        const originalSend = window.XMLHttpRequest.prototype.send;

        window.XMLHttpRequest.prototype.open = function patchedOpen(...args) {
            this.__globalErrorHandlerUrl = args[1] || "";
            return originalOpen.apply(this, args);
        };

        window.XMLHttpRequest.prototype.send = function patchedSend(...args) {
            this.addEventListener("load", function onLoad() {
                if (!shouldHandleStatus(this.status)) {
                    return;
                }

                const contentType = this.getResponseHeader("content-type");
                const payload = extractJsonPayload(contentType, this.responseText);
                handleHttpError(this.status, payload);
            });

            this.addEventListener("error", function onError() {
                showModal(DEFAULT_MESSAGE, { type: "error" });
            });

            return originalSend.apply(this, args);
        };
    }

    document.addEventListener("DOMContentLoaded", () => {
        window.showGlobalErrorModal = showModal;
        window.closeGlobalErrorModal = closeModal;

        installModalInteractions();
        installFetchInterceptor();
        installXmlHttpRequestInterceptor();
    });
})();
