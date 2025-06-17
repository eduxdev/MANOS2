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
            <div class="mt-6 flex justify-end space-x-3">
                <button type="button" 
                        id="<?php echo $id; ?>-cancel"
                        class="hidden inline-flex justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-gray-500 focus-visible:ring-offset-2"
                        onclick="closeMessageModal('<?php echo $id; ?>', false)">
                    Cancelar
                </button>
                <button type="button" 
                        class="inline-flex justify-center rounded-md border border-transparent bg-purple-600 px-4 py-2 text-sm font-medium text-white hover:bg-purple-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-purple-500 focus-visible:ring-offset-2"
                        onclick="closeMessageModal('<?php echo $id; ?>', true)">
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
        function showMessageModal(modalId, title, message, onConfirm = null, showCancel = true) {
            const modal = document.getElementById(modalId);
            const titleEl = document.getElementById(modalId + '-title');
            const contentEl = document.getElementById(modalId + '-content');
            const cancelBtn = document.getElementById(modalId + '-cancel');
            
            titleEl.textContent = title;
            contentEl.textContent = message;
            
            // Mostrar u ocultar el botón de cancelar según el parámetro
            cancelBtn.style.display = showCancel ? 'inline-flex' : 'none';
            
            modal.classList.add('show');

            if (onConfirm) {
                modal.dataset.onConfirm = typeof onConfirm === 'function' ? 'function' : onConfirm;
                modal._onConfirm = onConfirm;
            } else {
                delete modal.dataset.onConfirm;
                delete modal._onConfirm;
            }
        }

        function closeMessageModal(modalId, shouldExecuteCallback = false) {
            const modal = document.getElementById(modalId);
            modal.classList.remove('show');
            
            if (shouldExecuteCallback && modal.dataset.onConfirm) {
                if (modal._onConfirm && typeof modal._onConfirm === 'function') {
                    modal._onConfirm();
                } else if (modal.dataset.onConfirm !== 'function') {
                    const onConfirm = new Function(modal.dataset.onConfirm);
                    onConfirm();
                }
            }
            
            delete modal.dataset.onConfirm;
            delete modal._onConfirm;
        }
    </script>
    <?php
}
?>