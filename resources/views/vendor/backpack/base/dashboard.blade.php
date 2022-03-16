@extends(backpack_view('blank'))
@section('content')
<div class="row">
    <div class="col">
        @if($user_check_password_range->selisih_pertahun >= 335 && $user_check_password_range->selisih_pertahun < 365)
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <strong>Hallo, {{Auth::guard('backpack')->user()->username}}</strong>. Untuk alasan keamanan data, anda diharapkan untuk mengubah password 1 tahun sekali.
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif
        @if($user_check_password_range->selisih_pertahun >= 365)
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Hallo, {{Auth::guard('backpack')->user()->username}}</strong>. Untuk alasan keamanan data, disarankan untuk mengubah password anda sekarang.
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif
    </div>
</div>
<div class="row mt-2">
    <div class="col-lg-3 col-md-6 col-sm-6">
        <div class="card card-stats" style="background:#f06060; color:#ffffff;">
            <div class="card-body">
                <div class="row">
                    <div class="col-5 col-md-4">
                        <div class="icon-big text-center icon-warning">
                            <i class="la la-book"></i>
                        </div>
                    </div>
                    <div class="col-7 col-md-8">
                        <div class="numbers">
                        <label class="strong">Total PO</label>
                            <h2>{{$count['po_all']}}</h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-sm-6">
        <div class="card card-stats" style="background:#477197; color:#ffffff;">
            <div class="card-body">
                <div class="row">
                    <div class="col-5 col-md-4">
                        <div class="icon-big text-center icon-warning">
                            <i class="la la-newspaper"></i>
                        </div>
                    </div>
                    <div class="col-7 col-md-8">
                        <div class="numbers">
                            <label class="strong">Unread PO Line</label>
                            <h2>{{$count['po_line_unread']}}</h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-sm-6">
        <div class="card card-stats" style="background:#41a1b1; color:#ffffff;">
            <div class="card-body">
                <div class="row">
                    <div class="col-5 col-md-4">
                        <div class="icon-big text-center icon-warning">
                            <i class="la la-file"></i>
                        </div>
                    </div>
                    <div class="col-7 col-md-8">
                        <div class="numbers">
                            <label class="strong">Delivery Sheet</label>
                            <h2>{{$count['delivery']}}</h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-sm-6">
        <div class="card card-stats" style="background:#837070; color:#ffffff;">
            <div class="card-body">
                <div class="row">
                    <div class="col-5 col-md-4">
                        <div class="icon-big text-center icon-warning">
                            <i class="la la-flag"></i>
                        </div>
                    </div>
                    <div class="col-7 col-md-8">
                        <div class="numbers">
                            <label class="strong">Delivery Status</label>
                            <h2>{{$count['delivery_status']}}</h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header" style="background: #48abac;">
                <h5 class="text-white text-bold mb-0"><i class="la la-folder"></i> Quick Shortcuts</h5>
            </div>
            <div class="card-body">
                <h2>Hi, {{Auth::guard('backpack')->user()->username}}</h2>
                Selamat Datang di Vendor Portal PT. Kubota Indonesia
                <br>Perhatian : Data di Website ini terupdate setiap harinya jam 12.00 WIB dan 18.00 WIB.
                @if(Auth::guard('backpack')->user()->vendor_id)
                <hr>
                <b>Kode Vendor : {{Auth::guard('backpack')->user()->vendor->vend_num}} </b> 
                <br>{{Auth::guard('backpack')->user()->vendor->vend_name}}
                <br>{{Auth::guard('backpack')->user()->vendor->vend_addr}}
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header" style="background: #48abac;">
                <h5 class="text-white text-bold mb-0"><i class="la la-question-circle"></i> Help</h5>
            </div>
            <div class="card-body">
                <div class="accordion" id="accordionExample">
                    @foreach($generalMessage['help'] as $key => $gm)
                    <div class="card mb-2">
                        <div class="card-header" id="heading-{{$key}}">
                            <h2 class="mb-0">
                                <button class="btn btn-link btn-block text-left" style="font-weight: bold;" type="button" data-toggle="collapse" data-target="#collapse-{{$key}}" aria-expanded="true" aria-controls="collapse-{{$key}}">
                                    <i class="la la-angle-down"></i>
                                    {{$gm->title}} 
                                </button>
                            </h2>
                        </div>

                        <div id="collapse-{{$key}}" class="collapse" aria-labelledby="headingOne" data-parent="#accordionExample">
                            <div class="card-body help-section">
                            {!! $gm->content !!}                        
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header" style="background: #48abac;">
                <h5 class="text-white text-bold mb-0"> <i class="la la-info-circle"></i> Information</h5>
            </div>
            <div class="card-body">
                @foreach($generalMessage['info'] as $key => $gm)
                <div class="information-section mb-2">
                    <h6>{{$gm->title}}</h6>
                    {!! $gm->content !!}
                </div>
                @endforeach
                
            </div>
        </div>
    </div>

</div>


@endsection