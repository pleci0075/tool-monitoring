// File: assets/js/script.js (Versi Final)
$(document).ready(function() {

    // Inisialisasi DataTables pada tabel riwayat
    $('#historyTable').DataTable({
        "language": {
            "url": "https://cdn.datatables.net/plug-ins/2.0.8/i18n/id.json" // Terjemahan ke Bahasa Indonesia
        }
    });

    // --- LOGIKA MODAL PEMINJAMAN ---
    $('#mechanics').select2({
        theme: "bootstrap-5",
        dropdownParent: $('#pinjamModal'),
        placeholder: "Ketik untuk mencari nama mekanik..."
    });

    $('#supervisor_borrow').select2({
        theme: "bootstrap-5",
        dropdownParent: $('#pinjamModal'),
        placeholder: "Pilih Pengawas"
    });

    $('.btn-pinjam').on('click', function() {
        const toolId = $(this).data('bs-tool-id');
        const toolName = $(this).data('bs-tool-name');
        $('#modal_tool_id').val(toolId);
        $('#modal_tool_name').text(toolName);
    });


    // --- LOGIKA MODAL PENGEMBALIAN ---
    $('#supervisor_return').select2({
        theme: "bootstrap-5",
        dropdownParent: $('#kembalikanModal'),
        placeholder: "Pilih Pengawas"
    });

    $('.btn-kembalikan').on('click', function() {
        const transactionId = $(this).data('bs-transaction-id');
        $('#modal_transaction_id').val(transactionId);
    });

});