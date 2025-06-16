<?php
function showModal($id = 'message-modal') {
    ?>
    <div id="<?php echo $id; ?>" class="modal">
        <div class="bg-white rounded-xl shadow-2xl p-8 max-w-md w-full mx-4 transform transition-all">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-medium text-gray-900" id="<?php echo $id; ?>-title"></h3>
                <button type="button" class="text-gray-400 hover:text-gray-500" onclick="closeMessageModal('<?php echo $id; ?>')">
                    <span class="sr-only">Cerrar</span>
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="mt-2">
                <p class="text-sm text-gray-500" id="<?php echo $id; ?>-content"></p>
            </div>
            <div class="mt-6 flex justify-end">
                <button type="button" 
                        class="inline-flex justify-center rounded-md border border-transparent bg-purple-600 px-4 py-2 text-sm font-medium text-white hover:bg-purple-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-purple-500 focus-visible:ring-offset-2"
                        onclick="closeMessageModal('<?php echo $id; ?>')">
                    Aceptar
                </button>
            </div>
        </div>
    </div>

    <style>
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            opacity: 0;
            transition: opacity 0.3s ease-in-out;
        }

        .modal.show {
            display: flex;
            opacity: 1;
        }

        .modal > div {
            transform: translateY(20px);
            transition: transform 0.3s ease-in-out;
        }

        .modal.show > div {
            transform: translateY(0);
        }
    </style>

    <script>
        function showMessageModal(modalId, title, message, onClose = null) {
            const modal = document.getElementById(modalId);
            const titleEl = document.getElementById(modalId + '-title');
            const contentEl = document.getElementById(modalId + '-content');
            
            titleEl.textContent = title;
            contentEl.textContent = message;
            modal.classList.add('show');

            if (onClose) {
                modal.dataset.onClose = onClose;
            }
        }

        function closeMessageModal(modalId) {
            const modal = document.getElementById(modalId);
            modal.classList.remove('show');
            
            if (modal.dataset.onClose) {
                const onClose = new Function(modal.dataset.onClose);
                onClose();
                modal.dataset.onClose = '';
            }
        }
    </script>
    <?php
}
?>