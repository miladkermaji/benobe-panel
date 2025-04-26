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



Livewire.on("modalError", (error) => {
    console.error("Modal Error:", error);
});
