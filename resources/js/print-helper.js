window.printReport = function(containerId) {
    const source = document.getElementById(containerId);
    const buffer = document.getElementById('global-print-buffer');
    if (!source || !buffer) {
        console.warn('Print Error: Source or Buffer not found', { containerId });
        return;
    }
    buffer.innerHTML = source.innerHTML;
    setTimeout(() => {
        window.print();
        buffer.innerHTML = '';
    }, 100);
};