<div id="confirm-dialog" class="fixed inset-0 z-50 flex items-center justify-center hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <!-- Background backdrop -->
    <div class="fixed inset-0 bg-slate-900 bg-opacity-75 transition-opacity backdrop-blur-sm"></div>

    <!-- Modal panel -->
    <div class="relative bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-lg w-full m-4">
        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
            <div class="sm:flex sm:items-start">
                <div id="confirm-dialog-icon-container" class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                    <i id="confirm-dialog-icon" class="fas fa-exclamation-triangle text-red-600"></i>
                </div>
                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                    <h3 class="text-lg leading-6 font-bold text-slate-900" id="confirm-dialog-title">
                        Confirmar acci\u00f3n
                    </h3>
                    <div class="mt-2">
                        <p class="text-sm text-slate-500" id="confirm-dialog-message">
                            \u00bfEst\u00e1s seguro de que deseas realizar esta acci\u00f3n?
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <div class="bg-slate-50 px-4 py-3 sm:px-6 flex flex-row-reverse gap-3">
            <button type="button" id="confirm-dialog-btn-confirm" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:w-auto sm:text-sm transition-colors">
                Confirmar
            </button>
            <button type="button" id="confirm-dialog-btn-cancel" class="mt-3 w-full inline-flex justify-center rounded-lg border border-slate-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary sm:mt-0 sm:w-auto sm:text-sm transition-colors">
                Cancelar
            </button>
        </div>
    </div>
</div>

<script>
    window.confirmDialog = function(options) {
        return new Promise((resolve) => {
            const dialog = document.getElementById('confirm-dialog');
            const titleEl = document.getElementById('confirm-dialog-title');
            const messageEl = document.getElementById('confirm-dialog-message');
            const btnConfirm = document.getElementById('confirm-dialog-btn-confirm');
            const btnCancel = document.getElementById('confirm-dialog-btn-cancel');
            const iconContainer = document.getElementById('confirm-dialog-icon-container');
            const iconEl = document.getElementById('confirm-dialog-icon');
            
            // Set options
            titleEl.textContent = options.title || '\u00bfConfirmar?';
            messageEl.textContent = options.message || '\u00bfEst\u00e1 seguro?';
            btnConfirm.textContent = options.confirmText || 'Confirmar';
            btnCancel.textContent = options.cancelText || 'Cancelar';
            
            // Set style based on type (danger, warning, info)
            const type = options.type || 'danger';
            if (type === 'danger') {
                iconContainer.className = 'mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10';
                iconEl.className = 'fas fa-exclamation-triangle text-red-600';
                btnConfirm.className = 'w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:w-auto sm:text-sm transition-colors';
            } else if (type === 'warning') {
                iconContainer.className = 'mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-amber-100 sm:mx-0 sm:h-10 sm:w-10';
                iconEl.className = 'fas fa-exclamation-circle text-amber-600';
                btnConfirm.className = 'w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-amber-600 text-base font-medium text-white hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 sm:w-auto sm:text-sm transition-colors';
            } else if (type === 'info') {
                iconContainer.className = 'mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10';
                iconEl.className = 'fas fa-info-circle text-blue-600';
                btnConfirm.className = 'w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:w-auto sm:text-sm transition-colors';
            }

            // Show dialog
            dialog.classList.remove('hidden');
            
            // Handlers
            const cleanup = () => {
                dialog.classList.add('hidden');
                btnConfirm.removeEventListener('click', onConfirm);
                btnCancel.removeEventListener('click', onCancel);
            };
            
            const onConfirm = () => {
                cleanup();
                resolve(true);
            };
            
            const onCancel = () => {
                cleanup();
                resolve(false);
            };
            
            btnConfirm.addEventListener('click', onConfirm);
            btnCancel.addEventListener('click', onCancel);
        });
    };
</script>
