@extends('layouts.datatables')

@section('content1')
    <div class="container mt-3 mb-3">
        <div class="card text-white bg-primary">
        <div class="card-header font-weight-bold">Nojs User
            <a href="{{ route('nojs.create') }}" class="btn btn-success modal-show float-right" title="Create User"><i class="fa fa-plus"></i> Create</a>
        </div>

            <div class="card-body bg-light text-dark">
            <table class="table table-striped table-bordered dt-responsive nowrap" style="width:100%" id="tableusers" url="{{route('nojs.table')}}">
                <thead>
                    <tr>
                    <th scope="col">Nojs</th>
                    <th scope="col">site</th>
                    <th scope="col">LC</th>
                    <th scope="col">Mitra</th>
                    <th scope="col">Action</th>
                    </tr>
                </thead>
                <tbody>

                </tbody>
                </table>
            </div>
        </div>
    </div>

@push('scripts1')
    <script src="{{asset('js/nojs/nojs.js')}}"></script>
    <script src="{{asset('js/nojs/datatables.js')}}"></script>
    <script src="{{ asset('vendor/sweetalert2/sweetalert2.all.min.js') }}"></script>
@endpush

@include('nojs._modal')
@endsection
