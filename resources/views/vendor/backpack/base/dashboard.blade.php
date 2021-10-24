@extends(backpack_view('blank'))
@section('content')

<div class="card">
    <div class="card-header" style="background: #e91e63;">
       <h5 class="text-white text-bold mb-0">Quick Shortcuts</h5> 
    </div>
    <div class="card-body">
        Selamat Datang di Vendor Portal PT. Kubota Indonesia
        Perhatian : Data di Website ini terupdate setiap harinya jam 12.00 WIB dan 18.00 WIB.

        Hi, {{Auth::guard('backpack')->user()->username}}
    </div>
</div>

@endsection