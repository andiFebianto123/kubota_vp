@extends(backpack_view('layouts.plain'))

@section('content')
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-4">
            <img src="{{asset('img/logo-kubota.png')}}" style="width: 100px;" class="img img-fluid" alt="">
            <div class="card">
                <div class="card-body">
                    <form class="col-md-12 p-t-10" id="form-two-factor" role="form" method="POST" action="{{route('twofactor.update')}}">
                        {!! csrf_field() !!}
                        <div class="form-group">
                            <label class="control-label">Masukkan OTP yang telah Anda terima</label>
                            <div>
                                <input type="text" class="form-control rect-validation" name="two_factor_code">
                            </div>
                        </div>

                        <div class="form-group">
                            <div>
                                <button type="button" id="btn-for-form-two-factor" onclick="submitAfterValid('form-two-factor')" class="btn btn-block btn-primary-vp">
                                    Next
                                </button>
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
                submitAfterValid('form-two-factor')
                return false; 
            }
        });
    </script>
    @endsection
@endsection


