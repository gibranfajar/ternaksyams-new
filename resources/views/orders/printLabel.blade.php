@extends('layouts.app')

@section('content')
    <div class="pc-container">
        <div class="pc-content">
            <div class="page-header">
                <h3>Print Label</h3>
            </div>

            <div class="card">
                <div class="card-body">
                    <form id="labelForm" action="{{ route('orders.labelstore') }}" method="POST">
                        @csrf
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th><input type="checkbox" id="checkAll"></th>
                                    <th>Invoice</th>
                                    <th>Customer</th>
                                    <th>Order Number</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($orders as $order)
                                    <tr>
                                        <td>
                                            <input type="checkbox"
                                                name="selected_orders[]"value="{{ $order->shipping->order_number }}">
                                        </td>
                                        <td>{{ $order->invoice }}</td>
                                        <td>{{ $order->shipping->shippingInfo->name }}</td>
                                        <td>
                                            <span class="badge bg-light text-dark border">
                                                {{ $order->shipping->order_number }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-info">{{ ucfirst($order->status) }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-success">Generate Label</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(function() {
            $('#checkAll').on('change', function() {
                $('input[name="selected_orders[]"]').prop('checked', this.checked);
            });

            $('#labelForm').on('submit', function(e) {
                e.preventDefault();

                const form = this;
                const formData = new FormData(form);

                Swal.fire({
                    title: 'Sedang generate label...',
                    text: 'Mohon tunggu sebentar.',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                $.ajax({
                    url: form.action,
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(res) {
                        Swal.close();

                        if (res.success && res.url) {
                            // 🔥 tampilkan alert sukses dulu
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: 'Label berhasil digenerate!',
                                confirmButtonText: 'Lihat Label',
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    // 🟢 baru buka tab saat user klik tombol di SweetAlert
                                    window.open(res.url, '_blank');
                                }
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: res.message || 'Terjadi kesalahan.'
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.close();
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: xhr.responseJSON?.message ||
                                'Gagal menghubungi server.'
                        });
                    }
                });
            });
        });
    </script>
@endpush
