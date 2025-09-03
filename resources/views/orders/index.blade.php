@extends('layouts.app')

@section('content')
    <div class="pc-container">
        <div class="pc-content">
            <!-- [ breadcrumb ] start -->
            <div class="page-header">
                <div class="page-block">
                    <div class="row align-items-center">
                        <div class="col-md-12">
                            <div class="page-header-title">
                                <h3>Orders</h3>
                            </div>
                            <div class="float-end">
                                <a href="{{ route('orders.pickup') }}" type="button" class="btn btn-primary btn-sm me-1">
                                    <i class="bi bi-truck"></i>
                                    Requst Pickup
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- [ breadcrumb ] end -->

            <!-- [ Main Content ] start -->
            <div class="row">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-body">
                            <table id="myTable" class="table table-bordered table-striped align-middle"
                                style="font-size: 14px">
                                <thead>
                                    <tr>
                                        <th style="width: 5%" class="text-center">No</th>
                                        <th class="text-center">Invoice</th>
                                        <th class="text-center">Name</th>
                                        <th class="text-center">Status Order</th>
                                        <th class="text-center">Status Payment</th>
                                        <th class="text-center">Order Date</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($orders as $item)
                                        <tr>
                                            <td class="text-center">{{ $loop->iteration }}</td>
                                            <td>{{ $item->invoice }}</td>
                                            <td>{{ $item->shipping->shippingInfo->name }}</td>
                                            <td class="text-center">
                                                <span
                                                    class="badge {{ $item->status == 'pending' ? 'bg-warning' : 'bg-success' }}">
                                                    {{ ucfirst($item->status) }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <span
                                                    class="badge 
                                                        @if ($item->payment->status == 'pending') bg-warning 
                                                        @elseif($item->payment->status == 'settlement') bg-success 
                                                        @elseif($item->payment->status == 'failed') bg-danger 
                                                        @else bg-secondary @endif">
                                                    {{ ucfirst($item->payment->status) }}
                                                </span>
                                            </td>

                                            <td class="text-center">
                                                {{ $item->created_at->format('d M Y | H:i') }}
                                            </td>
                                            <td class="d-flex gap-2 align-items-center justify-content-center">
                                                <button type="button" class="btn btn-info btn-sm me-1"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#showItemsModal{{ $item->id }}">
                                                    <i class="ti ti-eye"></i>
                                                </button>
                                                @include('orders.modalitems')
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <!-- [ Main Content ] end -->
        </div>
    </div>
@endsection
