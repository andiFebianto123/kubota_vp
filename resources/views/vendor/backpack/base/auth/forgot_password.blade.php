@extends(backpack_view('layouts.plain'))

@section('content')
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-4">
            <img src="{{asset('img/logo-kubota.png')}}" style="width: 100px;" class="img img-fluid" alt="">
            <div class="card">
                <div class="card-body">
                    <form class="col-md-12 p-t-10" id="form-forgot-password" role="form" method="POST" action="{{route('forgotpassword.sendlink')}}">
                        {!! csrf_field() !!}
                        <div class="form-group">
                            <label class="control-label">Masukkan Email Anda</label>
                            <div>
                                <input type="email" class="form-control rect-validation" name="email">
                            </div>
                        </div>

                        <div class="form-group">
                            <div>
                                <button type="button" id="btn-for-form-forgot-password" onclick="submitAfterValid('form-forgot-password')" class="btn btn-block btn-primary-vp">
                                    Kirim
                                </button>
                                <small>Anda akan menerima email berisi link untuk mengupdate password </small>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @section('after_scripts')
    <script>
        $('input').keypress(function (e) {
            if (e.which == 13) {
                submitAfterValid('form-forgot-password')
                return false; 
            }
        });
    </script>
    @endsection
@endsection


