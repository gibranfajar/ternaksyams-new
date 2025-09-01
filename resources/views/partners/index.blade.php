@extends('layouts.app')

@section('content')
    <div class="pc-container">
        <div class="pc-content">
            <div class="page-header">
                <div class="page-block">
                    <div class="row align-items-center">
                        <div class="col-md-12">
                            <div class="page-header-title">
                                <h3>Partners</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Tabs -->
            <ul class="nav nav-tabs mb-3" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active" id="reseller-tab" data-bs-toggle="tab" data-bs-target="#reseller"
                        type="button" role="tab">
                        Reseller
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" id="affiliate-tab" data-bs-toggle="tab" data-bs-target="#affiliate"
                        type="button" role="tab">
                        Affiliate
                    </button>
                </li>
            </ul>

            <!-- Main Tab Content -->
            <div class="tab-content">

                {{-- Reseller Tab --}}
                <div class="tab-pane fade show active" id="reseller" role="tabpanel">
                    <div class="tab-content">
                        @foreach ($types as $key => $type)
                            <div class="tab-pane fade {{ $key === 0 ? 'show active' : '' }}"
                                id="reseller-variant-{{ $type['id'] }}" role="tabpanel">
                                <div class="table-responsive">
                                    <table id="resellerTable-{{ $type['id'] }}"
                                        class="table table-bordered table-striped align-middle">
                                        <thead>
                                            <tr>
                                                <th style="width: 5%" class="text-center">No</th>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Whatsapp</th>
                                                <th style="width: 20%" class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($resellers as $reseller)
                                                <tr>
                                                    <td class="text-center">{{ $loop->iteration }}</td>
                                                    <td>{{ $reseller->name }}</td>
                                                    <td>{{ $reseller->email }}</td>
                                                    <td>{{ $reseller->whatsapp }}</td>
                                                    <td class="text-center">
                                                        <div class="btn-group gap-2" role="group">
                                                            <button type="button" class="btn btn-sm btn-info"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#showResellerModal{{ $reseller->id }}">
                                                                <i class="ti ti-eye"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                                @include('partners.showreseller')
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Affiliate Tab --}}
                <div class="tab-pane fade" id="affiliate" role="tabpanel">
                    <div class="table-responsive">
                        <table id="affiliateTable" class="table table-bordered table-striped align-middle">
                            <thead>
                                <tr>
                                    <th style="width: 5%" class="text-center">No</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Whatsapp</th>
                                    <th style="width: 20%" class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($affiliates as $affiliate)
                                    <tr>
                                        <td class="text-center">{{ $loop->iteration }}</td>
                                        <td>{{ $affiliate->name }}</td>
                                        <td>{{ $affiliate->email }}</td>
                                        <td>{{ $affiliate->whatsapp }}</td>
                                        <td class="text-center">
                                            <div class="btn-group gap-2" role="group">
                                                <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal"
                                                    data-bs-target="#showAffiliateModal{{ $affiliate->id }}">
                                                    <i class="ti ti-eye"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @include('partners.showaffiliate')
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            @foreach ($types as $type)
                $('#resellerTable-{{ $type['id'] }}').DataTable();
            @endforeach
            $('#affiliateTable').DataTable();
        });
    </script>
@endpush
