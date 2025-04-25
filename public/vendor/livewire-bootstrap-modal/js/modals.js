import { Modal } from "bootstrap";

const modalsElement = document.getElementById("livewire-bootstrap-modal");
let modalInstance = null;

if (modalsElement) {
    modalInstance = Modal.getOrCreateInstance(modalsElement, {
        backdrop: true,
        keyboard: true,
    });

    modalsElement.addEventListener("hidden.bs.modal", () => {
        Livewire.dispatch("resetModal");
    });

    modalsElement.addEventListener("click", (e) => {
        if (e.target === modalsElement) {
            Livewire.dispatch("hideModal");
        }
    });
}

Livewire.on("showBootstrapModal", ({ autoClose }) => {
    if (modalInstance) {
        modalInstance.show();
        if (autoClose && Number.isInteger(autoClose)) {
            setTimeout(() => {
                Livewire.dispatch("hideModal");
            }, autoClose);
        }
    }
});

Livewire.on("hideBootstrapModal", () => {
    if (modalInstance) {
        modalInstance.hide();
        Livewire.dispatch("resetModal");
    }
});

Livewire.on("show-toastr", (event) => {
    const { type, message } = Array.isArray(event) ? event[0] : event;
    if (typeof toastr !== "undefined") {
        if (type === "success") {
            toastr.success(message);
        } else if (type === "error") {
            toastr.error(message);
        } else if (type === "warning") {
            toastr.warning(message);
        }
    } else {
        console.warn("Toastr is not defined. Please include Toastr library.");
    }
});

Livewire.on("modalError", (error) => {
    console.error("Modal Error:", error);
});
