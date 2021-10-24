@extends(backpack_view('blank'))
@section('content')

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
                            <label class="strong">Unread PO</label>
                            <h2>11</h2>
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
                            <label class="strong">Total PO</label>
                            <h2>101</h2>
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
                            <h2>91</h2>
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
                            <h2>76</h2>
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
                <h5 class="text-white text-bold mb-0">Quick Shortcuts</h5>
            </div>
            <div class="card-body">
                <h2>Hi, {{Auth::guard('backpack')->user()->username}}</h2>
                Selamat Datang di Vendor Portal PT. Kubota Indonesia
                Perhatian : Data di Website ini terupdate setiap harinya jam 12.00 WIB dan 18.00 WIB.

                
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header" style="background: #48abac;">
                <h5 class="text-white text-bold mb-0">Help</h5>
            </div>
            <div class="card-body">
                <div class="accordion" id="accordionExample">
                    @foreach($general_messages as $key => $gm)
                    <div class="card mb-2">
                        <div class="card-header" id="heading-{{$key}}">
                            <h2 class="mb-0">
                                <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#collapse-{{$key}}" aria-expanded="true" aria-controls="collapse-{{$key}}">
                                {{$gm->title}}
                                </button>
                            </h2>
                        </div>

                        <div id="collapse-{{$key}}" class="collapse" aria-labelledby="headingOne" data-parent="#accordionExample">
                            <div class="card-body">
                            {{$gm->content}}                        
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
                <h5 class="text-white text-bold mb-0">Information</h5>
            </div>
            <div class="card-body">
                <h1>Hi, {{Auth::guard('backpack')->user()->username}}</h1>
                Selamat Datang di Vendor Portal PT. Kubota Indonesia
                Perhatian : Data di Website ini terupdate setiap harinya jam 12.00 WIB dan 18.00 WIB.

                
            </div>
        </div>
    </div>

</div>


@endsection