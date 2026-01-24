{{-- resources/views/components/global-print-setup.blade.php --}}
<div id="global-print-buffer" class="print-only-container"></div>

<script>
    (function() {
        window.printReport = function(containerId) {
            const source = document.getElementById(containerId);
            const buffer = document.getElementById('global-print-buffer');
            
            if (!source || !buffer) {
                console.error('Print Error: Source or Buffer not found', { containerId });
                return;
            }

            // Очищаем и клонируем содержимое
            buffer.innerHTML = '';
            const clone = source.cloneNode(true);
            buffer.appendChild(clone);

            // Даем время на рендер стилей перед печатью
            setTimeout(() => {
                window.print();
                buffer.innerHTML = '';
            }, 300);
        };
    })();
</script>

<style>
    @media screen {
        #global-print-buffer { display: none; }
    }

    @media print {
        /* Скрываем всё остальное */
        body > *:not(#global-print-buffer) {
            display: none !important;
        }

        /* Настраиваем чистый лист для буфера */
        #global-print-buffer {
            display: block !important;
            position: relative !important;
            width: 100% !important;
            height: auto !important;
            overflow: visible !important;
            visibility: visible !important;
            background: white !important;
        }

        #global-print-buffer * {
            visibility: visible !important;
        }
    }
</style>