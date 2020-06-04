
@extends('layouts.datatables')

@push('styles1')
    <link href="{{ asset('css/nojs/bootstrap-select.min.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/datetime/css/jquery.datetimepicker.min.css') }}" rel="stylesheet">
    <script src="{{ asset('js/nojs/bootstrap-select.min.js') }}" defer></script>
@endpush

@section('content1')
    <div class="container mt-3 mb-3">
        <div class="card text-white bg-primary">
        <div class="card-header font-weight-bold">
        </div>
            <div class="card-body bg-light text-dark">
                <div class="row justify-content-center">
                    <div class="input-group-prepend col-md-5 col-sm-6 mb-3">
                        <button type="button" id="toggleStart" class="input-group-text"><i class="fa fa-calendar"></i></button>
                        <input type="text" id="start" class="form-control start " placeholder="START" value="">
                    </div>

                    <div class="input-group-prepend col-md-5 col-sm-6 mb-3">
                        <button type="button" id="toggleEnd" class="input-group-text"><i class="fa fa-calendar"></i></button>
                        <input type="text" id="end" class="form-control end start" placeholder="END" value="">
                    </div>
                </div>

                <div class="row justify-content-center">
                    <div class="form-group col-md-5">
                        <div class="row">
                            <div class="col">
                                <select  data-live-search="true" class="form-control selectpicker-data-point h-50 d-inline-block" data-size="5" data-style="btn-success" title="Data Point"></select>
                            </div>
                            <div class="col">
                                <select  data-live-search="true" class="form-control selectpicker-param h-50 d-inline-block" data-size="5" data-style="btn-primary" title="Param"></select>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-5 ">
                        <div class="row">
                            <div class="col">
                                <select  data-live-search="true" class="form-control selectpicker-project h-50 d-inline-block" data-size="5" data-style="btn-warning" title="Project"></select>
                            </div>
                            <div class="col">
                                <button type="button" class="btn btn-primary btn-lg" id="btnstart">Start</button>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="container mt-3" id="datatable">

                </div>

                </div>

            </div>
        </div>
    </div>
@endsection

@push('scripts1')
    <script type="module" src="{{ asset('js/other/other.js') }}" defer></script>
    <script src="{{ asset('vendor/datetime/js/moment.min.js') }}" defer></script>
    <script src="{{ asset('vendor/datetime/js/jquery.datetimepicker.full.js') }}" defer></script>
    <script src="{{ asset('vendor/sweetalert2/sweetalert2.all.min.js') }}" defer></script>
@endpush

