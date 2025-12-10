<script src="{{ asset('assets/js/plugins/popper.min.js') }}"></script>
<script src="{{ asset('assets/js/plugins/simplebar.min.js') }}"></script>
<script src="{{ asset('assets/js/plugins/bootstrap.min.js') }}"></script>
<script src="{{ asset('assets/js/fonts/custom-font.js') }}"></script>
<script src="{{ asset('assets/js/pcoded.js') }}"></script>
<script src="{{ asset('assets/js/plugins/feather.min.js') }}"></script>

<script>
    // datatables
    $(document).ready(function() {
        $('#myTable').DataTable();
    });

    $(document).ready(function() {
        $('#usersTable').DataTable({
            paging: false, // matikan paging
            searching: true, // search tetap aktif
            info: false, // hilangkan "showing X of Y"
            scrollY: "300px", // tinggi konten scroll
            scrollCollapse: true // biar scroll rapih
        });
    });

    layout_change('light');
    change_box_container('false');
    layout_rtl_change('false');
    preset_change("preset-1");
    font_change("Public-Sans");

    // preview image
    $(document).ready(function() {
        $("#giziInput").on("change", function() {
            const file = this.files[0];
            if (file) {
                let reader = new FileReader();
                reader.onload = function(e) {
                    $("#previewImg").attr("src", e.target.result).show();
                };
                reader.readAsDataURL(file);
            }
        });
    });

    $(document).ready(function() {
        $(document).on('change', '#thumbnail', function() {
            // coba cari modal terdekat
            let modal = $(this).closest('.modal');

            let preview;
            if (modal.length) {
                // kalau input ada di dalam modal
                preview = modal.find('#thumbnailPreview');
            } else {
                // fallback: cari global (untuk halaman create)
                preview = $('#thumbnailPreview');
            }

            preview.empty();

            let file = this.files[0];
            if (file) {
                let reader = new FileReader();
                reader.onload = function(e) {
                    let img = $('<img>', {
                        src: e.target.result,
                        class: 'img-thumbnail mt-2',
                        css: {
                            maxWidth: '200px'
                        }
                    });
                    preview.append(img);
                };
                reader.readAsDataURL(file);
            }
        });
    });



    // SweetAlert handle
    @if (session('success'))
        document.addEventListener("DOMContentLoaded", function() {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: @json(session('success')),
                showConfirmButton: false,
                timer: 1000,
                timerProgressBar: true
            });
        });
    @endif


    @if (session('error'))
        document.addEventListener("DOMContentLoaded", function() {
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: @json(session('error')),
            });
        });
    @endif

    // error handling request
    @if ($errors->any())
        document.addEventListener("DOMContentLoaded", function() {
            let errorMessages = `{!! implode('\n', $errors->all()) !!}`;
            Swal.fire({
                icon: 'error',
                title: 'Validasi Gagal',
                text: errorMessages,
            });
        });
    @endif

    // popup handle delete confirmation
    $(document).ready(function() {
        $('.btn-delete').on('click', function(e) {
            e.preventDefault();

            const form = $(this).closest('form');

            Swal.fire({
                title: 'Yakin ingin menghapus data ini?',
                text: "Data yang sudah dihapus tidak dapat dikembalikan!",
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
    });
</script>
