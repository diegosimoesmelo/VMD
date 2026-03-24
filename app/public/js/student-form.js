(() => {
    function onlyDigits(value) {
        return (value || "").replace(/\D/g, "");
    }

    function maskCpf(value) {
        const digits = onlyDigits(value).slice(0, 11);
        return digits
            .replace(/^(\d{3})(\d)/, "$1.$2")
            .replace(/^(\d{3})\.(\d{3})(\d)/, "$1.$2.$3")
            .replace(/\.(\d{3})(\d)/, ".$1-$2");
    }

    function maskCep(value) {
        const digits = onlyDigits(value).slice(0, 8);
        return digits.replace(/^(\d{5})(\d)/, "$1-$2");
    }

    function maskPhone(value) {
        const digits = onlyDigits(value).slice(0, 11);
        if (digits.length <= 10) {
            return digits
                .replace(/^(\d{2})(\d)/, "($1) $2")
                .replace(/(\d{4})(\d)/, "$1-$2");
        }

        return digits
            .replace(/^(\d{2})(\d)/, "($1) $2")
            .replace(/(\d{5})(\d)/, "$1-$2");
    }

    function normalizeCurrency(value) {
        const normalized = String(value || "").replace(",", ".");
        const number = Number.parseFloat(normalized);
        if (Number.isNaN(number)) {
            return "";
        }

        return number.toFixed(2);
    }

    function setupMasks() {
        const cpfInput = document.getElementById("cpf");
        const cepInput = document.getElementById("cep");
        const phoneInput = document.getElementById("telefone");
        const workPhoneInput = document.getElementById("telefone_profissional");
        const valueInput = document.getElementById("valor_pago");

        if (cpfInput) {
            cpfInput.addEventListener("input", (event) => {
                event.target.value = maskCpf(event.target.value);
            });
        }

        if (cepInput) {
            cepInput.addEventListener("input", (event) => {
                event.target.value = maskCep(event.target.value);
            });
        }

        [phoneInput, workPhoneInput].forEach((input) => {
            if (!input) return;
            input.addEventListener("input", (event) => {
                event.target.value = maskPhone(event.target.value);
            });
        });

        if (valueInput) {
            valueInput.addEventListener("blur", (event) => {
                event.target.value = normalizeCurrency(event.target.value);
            });
        }
    }

    function setStep(step) {
        const step1 = document.getElementById("step1");
        const step2 = document.getElementById("step2");
        const badge1 = document.getElementById("stepBadge1");
        const badge2 = document.getElementById("stepBadge2");

        if (!step1 || !step2 || !badge1 || !badge2) {
            return;
        }

        const firstStepActive = step === 1;
        step1.classList.toggle("active", firstStepActive);
        step2.classList.toggle("active", !firstStepActive);
        badge1.classList.toggle("active", firstStepActive);
        badge2.classList.toggle("active", !firstStepActive);
        window.scrollTo({ top: 0, behavior: "smooth" });
    }

    window.goToStep2 = function goToStep2() {
        const step1 = document.getElementById("step1");
        if (!step1) {
            return;
        }

        const fields = step1.querySelectorAll("input, select, textarea");
        for (const field of fields) {
            if (typeof field.reportValidity === "function" && !field.reportValidity()) {
                return;
            }
        }

        setStep(2);
    };

    window.goToStep1 = function goToStep1() {
        setStep(1);
    };

    document.addEventListener("DOMContentLoaded", () => {
        setupMasks();
    });
})();
