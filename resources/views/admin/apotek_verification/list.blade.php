@extends(backpack_view('blank'))

@php
    $defaultBreadcrumbs = [
        trans('backpack::crud.admin') => url(config('backpack.base.route_prefix'), 'dashboard'),
        $crud->entity_name_plural => url($crud->route),
        trans('backpack::crud.list') => false,
    ];
    
    // if breadcrumbs aren't defined in the CrudController, use the default breadcrumbs
    $breadcrumbs = $breadcrumbs ?? $defaultBreadcrumbs;
@endphp

@section('header')
    <div class="container-fluid">
        <h2>
            <span class="text-capitalize">{!! $crud->getHeading() ?? $crud->entity_name_plural !!}</span>
            {{-- <small id="datatable_info_stack">{!! $crud->getSubheading() ?? '' !!}</small> --}}
        </h2>
    </div>
@endsection

@section('content')
    {{-- Default box --}}
    <div class="row">

        {{-- THE ACTUAL CONTENT --}}
        <div class="{{ $crud->getListContentClass() }}">

            {{-- <div class="row mb-0">
                <div class="col-sm-6">
                    @if ($crud->buttons()->where('stack', 'top')->count() ||
    $crud->exportButtons())
                        <div class="d-print-none {{ $crud->hasAccess('create') ? 'with-border' : '' }}">

                            @include('crud::inc.button_stack', ['stack' => 'top'])

                        </div>
                    @endif
                </div>
                <div class="col-sm-6">
                    <div id="datatable_search_stack" class="mt-sm-0 mt-2 d-print-none"></div>
                </div>
            </div> --}}

            {{-- Backpack List Filters --}}
            @if ($crud->filtersEnabled())
                @include('crud::inc.filters_navbar')
            @endif

            <div class="tab-container mb-2 mt-2">
                <div class="nav-tabs-custom " id="form_tabs">
                    <ul class="nav nav-tabs " role="tablist">
                        <li role="presentation" class="nav-item" id="open">
                            <a href="#tab_open" aria-controls="tab_open" role="tab" tab_name="open" data-toggle="tab"
                                class="nav-link active">Open</a>
                        </li>
                        <li role="presentation" class="nav-item" id="accepted">
                            <a href="#tab_accepted" aria-controls="tab_accepted" role="tab" tab_name="accepted"
                                data-toggle="tab" class="nav-link">Accepted</a>
                        </li>
                        <li role="presentation" class="nav-item" id="rejected">
                            <a href="#tab_rejected" aria-controls="tab_rejected" role="tab" tab_name="rejected"
                                data-toggle="tab" class="nav-link">Rejected</a>
                        </li>
                    </ul>

                    <!-- <div class="tab-content p-0 col-md-12"> -->
                    <div class="tab-content p-0 ">
                        <div role="tabpanel" class="tab-pane active" id="tab_open">
                            <div class="row">
                                <div class="col-md-12 bold-labels" style="width:100%">
                                    <table id="open_apotek_verification_table"
                                        class="bg-white table table-striped table-hover rounded shadow-xs border-xs mt-2"
                                        style="width:100%" cellspacing="0">
                                        <thead>
                                            <tr>
                                                <th>User</th>
                                                <th>Status</th>
                                                <th>Apotek</th>
                                                <th>Requested At</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                    </table>

                                    @if ($crud->buttons()->where('stack', 'bottom')->count())
                                        <div id="bottom_buttons" class="hidden-print">
                                            @include('crud::inc.button_stack', ['stack' => 'bottom'])

                                            <div id="datatable_button_stack" class="float-right text-right hidden-xs"></div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div role="tabpanel" class="tab-pane" id="tab_accepted">
                            <div class="row">
                                <div class="col-md-12 bold-labels" style="width:100%">
                                    <table id="accepted_apotek_verification_table"
                                        class="bg-white table table-striped border-xs mt-2" style="width:100%"
                                        cellspacing="0">
                                        <thead>
                                            <tr>
                                                <th>User</th>
                                                <th>Status</th>
                                                <th>Apotek</th>
                                                <th>Requested At</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                    </table>

                                    @if ($crud->buttons()->where('stack', 'bottom')->count())
                                        <div id="bottom_buttons" class="hidden-print">
                                            @include('crud::inc.button_stack', ['stack' => 'bottom'])

                                            <div id="datatable_button_stack" class="float-right text-right hidden-xs"></div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div role="tabpanel" class="tab-pane" id="tab_rejected">
                            <div class="row">
                                <div class="col-md-12 bold-labels" style="width:100%">
                                    <table id="rejected_apotek_verification_table"
                                        class="bg-white table table-striped border-xs mt-2" style="width:100%"
                                        cellspacing="0">
                                        <thead>
                                            <tr>
                                                <th>User</th>
                                                <th>Status</th>
                                                <th>Apotek</th>
                                                <th>Requested At</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                    </table>

                                    @if ($crud->buttons()->where('stack', 'bottom')->count())
                                        <div id="bottom_buttons" class="hidden-print">
                                            @include('crud::inc.button_stack', ['stack' => 'bottom'])

                                            <div id="datatable_button_stack" class="float-right text-right hidden-xs"></div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if ($crud->buttons()->where('stack', 'bottom')->count())
                <div id="bottom_buttons" class="d-print-none text-center text-sm-left">
                    @include('crud::inc.button_stack', ['stack' => 'bottom'])

                    <div id="datatable_button_stack" class="float-right text-right hidden-xs"></div>
                </div>
            @endif

        </div>
    </div>
@endsection

{{-- Modal Detail --}}
<div class="modal fade" id="detail_modal" tabindex="-1" aria-labelledby="myModalLabel" style="display: none;"
    aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Modal title</h4>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="container">
                    <div class="row">
                        <div class="col-sm">
                            <div class="card">
                                <div class="card-header">
                                    <b>User</b>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-2"><label>Name</label></div>
                                        <div class="col-md-10"><label id="detail_name"></label></div>

                                        <div class="col-md-2"><label>Email</label></div>
                                        <div class="col-md-10"><label id="detail_email"></label></div>

                                        <div class="col-md-2"><label>Phone</label></div>
                                        <div class="col-md-10"><label id="detail_phone"></label></div>

                                        <div class="col-md-2"><label>Gender</label></div>
                                        <div class="col-md-10"><label id="detail_gender"></label></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm">
                            <div class="card">
                                <div class="card-header">
                                    <b>Apotek Info</b>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4"><label>Name</label></div>
                                        <div class="col-md-8"><label id="detail_name_apotek"></label></div>

                                        <div class="col-md-4"><label>Address</label></div>
                                        <div class="col-md-8"><label id="detail_address"></label></div>

                                        <div class="col-md-4"><label>Province</label></div>
                                        <div class="col-md-8"><label id="detail_province"></label></div>

                                        <div class="col-md-4"><label>City</label></div>
                                        <div class="col-md-8"><label id="detail_city"></label></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="container">
                        <div class="row text-center mt-1 mb-1">
                            <div class="col">
                                <div><b>Apotek Image</b></div>
                                <div class="d-flex justify-content-center align-items-center">
                                    <div id="carouselExampleControls" class="carousel slide w-50"
                                        data-ride="carousel">
                                        <div class="carousel-inner image-apotek-carousel">
                                        </div>
                                        <a class="carousel-control-prev" href="#carouselExampleControls"
                                            role="button" data-slide="prev">
                                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                            <span class="sr-only">Previous</span>
                                        </a>
                                        <a class="carousel-control-next" href="#carouselExampleControls"
                                            role="button" data-slide="next">
                                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                            <span class="sr-only">Next</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row text-center mt-5">
                        <div class="col-md-6">
                            <div><b>Photo</b></div>
                            <img id="detail_photo_profile" width="50%" height="auto">
                        </div>
                        <div class="col-md-6">
                            <div><b>KTP</b></div>
                            <img id="detail_ktp" width="50%" height="auto">
                        </div>
                    </div>
                    <div class="row text-center mt-5">
                        <div class="col-md-6">
                            <div><b>NPWP</b></div>
                            <img id="detail_npwp" width="50%" height="auto">
                        </div>
                        <div class="col-md-6">
                            <div><b>Surat Izin Usaha</b></div>
                            <img id="detail_surat_izin_usaha" width="50%" height="auto">
                        </div>
                    </div>
                    <div class="row text-center mt-5">
                        <div class="col">
                            <div><b>Apotek Location</b></div>
                            <iframe id="detail_maps" width="50%" height="500" frameborder="0" scrolling="no"
                                marginheight="0" marginwidth="0">
                            </iframe>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-dismiss="modal">Close</button>
                {{-- <button class="btn btn-primary" type="button">Save changes</button> --}}
            </div>
        </div>
        <!-- /.modal-content-->
    </div>
    <!-- /.modal-dialog-->
</div>

@section('after_styles')
    {{-- DATA TABLES --}}
    <link rel="stylesheet" type="text/css"
        href="{{ asset('packages/datatables.net-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" type="text/css"
        href="{{ asset('packages/datatables.net-fixedheader-bs4/css/fixedHeader.bootstrap4.min.css') }}">
    <link rel="stylesheet" type="text/css"
        href="{{ asset('packages/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css') }}">

    {{-- CRUD LIST CONTENT - crud_list_styles stack --}}
    @stack('crud_list_styles')
@endsection

@section('after_scripts')
    @include('crud::inc.datatables_logic')

    <script src="{{ asset('js/apotek_verification.js') }}"></script>
    <script>
        const url = "{{ backpack_url() }}";

        function detailModal() {
            $('.btn_detail').on('click', function(e) {
                e.stopImmediatePropagation();
                // User 
                $('#detail_name').html($(this).data('name'));
                $('#detail_email').html($(this).data('email'));
                $('#detail_phone').html($(this).data('phone'));
                $('#detail_gender').html($(this).data('gender'));

                // Apotek Info
                $('#detail_name_apotek').html($(this).data('name_apotek'));
                $('#detail_address').html($(this).data('address'));
                $('#detail_province').html($(this).data('province'));
                $('#detail_city').html($(this).data('city'));


                // Images
                // $('#detail_image_apotek').attr('src', $(this).data('image_apotek'));
                let imageApotek = $(this).data('image_apotek').split(',');
                $('#detail_photo_profile').attr('src', $(this).data('photo'));
                $('#detail_ktp').attr('src', $(this).data('ktp'));
                $('#detail_npwp').attr('src', $(this).data('npwp'));
                $('#detail_surat_izin_usaha').attr('src', $(this).data('surat_izin_usaha'));

                $('.image-apotek-carousel').empty();
                for (let i = 0; i < imageApotek.length; i++) {
                    let active = (i == 0) ? "active" : '';
                    let html = `
                            <div class="carousel-item ${active}">
                                <img class="d-block w-100" src="${imageApotek[i]}">
                            </div>
                        `;
                    $('.image-apotek-carousel').append(html);
                    console.log('image', imageApotek[i]);
                }

                // Maps
                let latitude = $(this).data('latitude');
                let longitude = $(this).data('longitude');

                $('#detail_maps').attr('src',
                    `https://www.google.com/maps/embed?pb=!1m14!1m12!1m3!1d1510.345323556068!2d${longitude}!3d${latitude}!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!5e0!3m2!1sen!2sid!4v1671536568574!5m2!1sen!2sid`
                );

                $('#detail_modal').modal('show');
            });
        }

        function acceptEntry() {
            let id = $('.btn_accept').data('id');
            let userId = $('.btn_accept').data('user_id');
            swal({
                title: "Accept apotek Info?",
                text: "Are you sure? This action can't be undone",
                icon: "warning",
                buttons: {
                    cancel: true,
                    confirm: {
                        text: "Accept",
                        className: "btn-success"
                    },
                },
            }).then((value) => {
                if (value) {
                    swal({
                        title: 'Processing...',
                        text: 'Please wait a moment',
                        buttons: false,
                        closeOnEsc: false,
                        closeOnClickOutside: false,
                    });
                    $.ajax({
                        url: url + '/apotek/update-status',
                        type: 'POST',
                        data: {
                            id: id,
                            user_id: userId,
                            status: 'accepted',
                        },
                        success: function(result) {
                            swal({
                                icon: 'success',
                                title: 'Accepted',
                                text: result.message,
                                buttons: false,
                                closeOnEsc: false,
                                closeOnClickOutside: false,
                                timer: 2000,
                            }).then(() => {
                                $("#accepted").find('a').trigger("click");
                            });
                        },
                        error: function(result) {
                            swal({
                                title: "Error",
                                text: result.responseJSON.message,
                                icon: "error",
                                timer: 4000,
                                buttons: false,
                            });
                        }
                    });
                }
            })
        }

        function rejectEntry() {
            let id = $('.btn_reject').data('id');
            let userId = $('.btn_reject').data('user_id');
            swal({
                title: "Reject apotek Info?",
                text: "Are you sure? This action can't be undone",
                icon: "warning",
                buttons: {
                    cancel: true,
                    confirm: {
                        text: "Reject",
                        className: "btn-danger"
                    },
                },
            }).then((value) => {
                if (value) {
                    swal({
                        title: 'Processing...',
                        text: 'Please wait a moment',
                        buttons: false,
                        closeOnEsc: false,
                        closeOnClickOutside: false,
                    });
                    $.ajax({
                        url: url + '/apotek/update-status',
                        type: 'POST',
                        data: {
                            id: id,
                            user_id: userId,
                            status: 'rejected',
                        },
                        success: function(result) {
                            swal({
                                icon: 'success',
                                title: 'Rejected',
                                text: result.message,
                                buttons: false,
                                closeOnEsc: false,
                                closeOnClickOutside: false,
                                timer: 2000,
                            }).then(() => {
                                $("#rejected").find('a').trigger("click");
                            });
                        },
                        error: function(result) {
                            swal({
                                title: "Error",
                                text: result.responseJSON.message,
                                icon: "error",
                                timer: 4000,
                                buttons: false,
                            });
                        }
                    });
                }
            })
        }
    </script>
    {{-- CRUD LIST CONTENT - crud_list_scripts stack --}}
    @stack('crud_list_scripts')
@endsection
