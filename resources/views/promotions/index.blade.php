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
                                <h3>Promotions</h3>
                            </div>
                            <div class="float-end">
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                    data-bs-target="#addPromotionModal">
                                    Add Promotion
                                </button>
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
                            <table id="myTable" class="table table-bordered table-striped align-middle">
                                <thead>
                                    <tr>
                                        <th style="width: 5%" class="text-center">No</th>
                                        <th>Thumbnail</th>
                                        <th>Name</th>
                                        <th>Start Date</th>
                                        <th>End Date</th>
                                        <th>Status</th>
                                        <th style="width: 10%" class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($promotions as $item)
                                        <tr>
                                            <td class="text-center">{{ $loop->iteration }}</td>
                                            <td>
                                                <img src="{{ asset('storage/' . $item->thumbnail) }}" alt="thumbnail"
                                                    width="60">
                                            </td>
                                            <td>{{ $item->name }}</td>
                                            <td>{{ Carbon\Carbon::parse($item->start_date)->format('d M Y') }}</td>
                                            <td>{{ Carbon\Carbon::parse($item->end_date)->format('d M Y') }}</td>
                                            <td>
                                                <span
                                                    class="badge {{ $item->status === 'active' ? 'bg-success' : 'bg-secondary' }}">
                                                    {{ ucfirst($item->status) }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-warning btn-sm me-1"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editPromotionModal{{ $item->id }}">
                                                    <i class="ti ti-edit"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <!-- Edit Promotion Modal -->
                                        @include('promotions.modaledit')
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Promotion Modal -->
    @include('promotions.modalcreate')
@endsection
