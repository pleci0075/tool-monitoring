// File: assets/js/script.js (Versi Final, Lengkap, dan Stabil)
$(document).ready(function() {

    // ===================================================
    // BAGIAN 1: INISIALISASI LIBRARY (DataTables & Select2)
    // ===================================================

    // Mengaktifkan DataTables pada semua tabel di portal admin
    $('#historyTable, #toolsTable, #mechanicsTable, #usersTable, #reportsHistoryTable, #detailHistoryTable').DataTable({
        "language": { "url": "https://cdn.datatables.net/plug-ins/2.0.8/i18n/id.json" }
    });

    // Fungsi helper untuk inisialisasi Select2
    const initSelect2 = (selector, placeholderText, parent) => {
        $(selector).select2({
            theme: "bootstrap-5",
            dropdownParent: parent,
            placeholder: placeholderText
        });
    };

    // Menerapkan Select2 saat modal yang sesuai ditampilkan
    $('#pinjamModal').on('shown.bs.modal', function() {
        initSelect2('#mechanics_public', 'Pilih satu atau lebih mekanik', $(this));
        initSelect2('#supervisor_borrow_public', 'Pilih pengawas', $(this));
    });

    $('#kembalikanModal').on('shown.bs.modal', function() {
        initSelect2('#returner_mechanic_id', 'Pilih nama pengembali', $(this));
        initSelect2('#supervisor_return_public', 'Pilih pengawas', $(this));
    });

    $('#reportModal').on('shown.bs.modal', function() {
        initSelect2('#report_tool_id', 'Cari & Pilih Nama Tool', $(this));
        initSelect2('#report_mechanic_id', 'Pilih Nama Anda', $(this));
    });

    // Inisialisasi Select2 untuk modal di halaman Manajemen
    $('#addToolModal, #editToolModal').on('shown.bs.modal', function() {
        initSelect2($(this).find('.select-category'), 'Pilih kategori', $(this));
    });

    // ===================================================
    // LOGIKA UNTUK REAL-TIME UPDATE DASHBOARD ADMIN
    // ===================================================
    if ($('#live-activity-feed').length) {
        
        function updateDashboardData() {
            $.ajax({
                url: 'api/dashboard_data.php',
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                // Update kartu statistik (tetap sama)
                $('#stat-total-tools').text(data.total_tools);
                $('#stat-available-tools').text(data.available_tools);
                $('#stat-borrowed-tools').text(data.borrowed_tools);
                $('#stat-damaged-tools').text(data.damaged_tools);

                // Update feed aktivitas terkini dengan desain yang diperbaiki
                let activityFeed = $('#live-activity-feed');
                activityFeed.empty(); // Kosongkan daftar lama

                if (data.latest_activities && data.latest_activities.length > 0) {
                    data.latest_activities.forEach(function(act) {
                        
                        // PERBAIKAN 1: Menggunakan format tanggal, bukan jam
                        let eventDate = new Date(act.event_date).toLocaleDateString('id-ID', {
                            day: 'numeric', month: 'short', year: 'numeric'
                        });

                        // PERBAIKAN 2: Mengembalikan ke desain 2 baris yang lebih jelas
                        // dan mengatasi 'undefined'
                        let listItem = `
                            <li class="list-group-item px-0">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1">${act.tool_name}</h6>
                                    <small class="text-muted">${eventDate}</small>
                                </div>
                                <p class="mb-1 text-muted small">${act.action} <strong>${act.person}</strong></p>
                            </li>
                        `;
                        activityFeed.append(listItem);
                    });
                } else {
                    activityFeed.append('<li class="list-group-item px-0">Belum ada aktivitas terkini.</li>');
                }
            },
                error: function(xhr, status, error) {
                    console.error("Gagal mengambil data dashboard:", status, error);
                    $('#live-activity-feed').html('<li class="list-group-item text-danger">Gagal memuat data.</li>');
                }
            });
        }

        // Panggil fungsi pertama kali saat halaman dimuat
        updateDashboardData();
        
        // Atur interval untuk memanggil fungsi setiap 15 detik (15000 milidetik)
        setInterval(updateDashboardData, 15000);
    }

    // ===================================================
    // BAGIAN 2: EVENT LISTENER UNTUK SEMUA TOMBOL AKSI
    // ===================================================

    // Tombol "Tambah"
    $('#btn-add-tool').on('click', function() {
        bootstrap.Modal.getOrCreateInstance(document.getElementById('addToolModal')).show();
    });
    $('#btn-add-mechanic').on('click', function() {
        bootstrap.Modal.getOrCreateInstance(document.getElementById('addModal')).show();
    });
    $('#btn-add-user').on('click', function() {
        bootstrap.Modal.getOrCreateInstance(document.getElementById('addUserModal')).show();
    });

    // Tombol "Edit"
    $(document).on('click', '.btn-edit-tool', function() {
        $('#edit_tool_id').val($(this).data('id'));
        $('#edit_tool_name').val($(this).data('name'));
        $('#edit_tool_code').val($(this).data('code'));
        $('#edit_category').val($(this).data('category'));
        bootstrap.Modal.getOrCreateInstance(document.getElementById('editToolModal')).show();
    });

    $(document).on('click', '.btn-edit-mechanic', function() {
        $('#edit_id').val($(this).data('id'));
        $('#edit_nrp').val($(this).data('nrp'));
        $('#edit_name').val($(this).data('name'));
        bootstrap.Modal.getOrCreateInstance(document.getElementById('editModal')).show();
    });

    $(document).on('click', '.btn-edit-user', function() {
        $('#edit_user_id').val($(this).data('id'));
        $('#edit_user_nrp').val($(this).data('nrp'));
        $('#edit_user_name').val($(this).data('name'));
        $('#edit_user_role').val($(this).data('role')).trigger('change');
        bootstrap.Modal.getOrCreateInstance(document.getElementById('editUserModal')).show();
    });

    // Tombol "Review Laporan"
    $(document).on('click', '.btn-review-report', function() {
        $('#modal_report_code').text($(this).data('report-code'));
        $('#modal_tool_code').text($(this).data('tool-code'));
        $('#modal_report_date').text($(this).data('report-date'));
        $('#modal_reporter_name').text($(this).data('reporter-name'));
        $('#modal_description').text($(this).data('description'));
        $('#modal_photo_link').attr('href', $(this).data('photo-path'));
        $('#modal_photo_img').attr('src', $(this).data('photo-path'));
        $('.modal_report_id_input').val($(this).data('report-id'));
        bootstrap.Modal.getOrCreateInstance(document.getElementById('reviewReportModal')).show();
    });

    // Tombol "Pinjam"
    $(document).on('click', '.btn-pinjam', function() {
        $('#modal_tool_id').val($(this).data('bs-tool-id'));
        $('#modal_tool_name').text($(this).data('bs-tool-name'));
        bootstrap.Modal.getOrCreateInstance(document.getElementById('pinjamModal')).show();
    });

    // Tombol "Kembalikan"
    $(document).on('click', '.btn-kembalikan', function() {
        const transactionId = $(this).data('bs-transaction-id');
        const borrowers = $(this).data('borrowers');
        $('#modal_transaction_id').val(transactionId);
        const returnerSelect = $('#returner_mechanic_id');
        returnerSelect.empty().append(new Option('-- Pilih Nama Pengembali --', '', true, true));
        if (borrowers && Array.isArray(borrowers) && borrowers.length > 0) {
            borrowers.forEach(function(borrower) {
                returnerSelect.append(new Option(borrower.name, borrower.id, false, false));
            });
        }
        returnerSelect.trigger('change');
        bootstrap.Modal.getOrCreateInstance(document.getElementById('kembalikanModal')).show();
    });


    // ===================================================
    // BAGIAN 3: FUNGSI-FUNGSI LAINNYA
    // ===================================================

    // Form "Hapus" dengan konfirmasi SweetAlert2
    $(document).on('submit', '.delete-form', function(e) {
        e.preventDefault();
        var form = this;
        Swal.fire({
            title: 'Apakah Anda Yakin?',
            text: "Data yang dihapus tidak dapat dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });

    // Pencarian di Portal Publik
    $('#toolSearchInput').on('keyup', function() {
        let searchTerm = $(this).val().toLowerCase();
        $('.main-tool-table .category-header-row').each(function() {
            let headerRow = $(this);
            let contentRows = headerRow.nextUntil('.category-header-row');
            let hasVisibleContent = false;
            contentRows.each(function() {
                if ($(this).text().toLowerCase().includes(searchTerm)) {
                    $(this).show();
                    hasVisibleContent = true;
                } else {
                    $(this).hide();
                }
            });
            if (hasVisibleContent || searchTerm === "") {
                headerRow.show();
            } else {
                headerRow.hide();
            }
        });
    });

});