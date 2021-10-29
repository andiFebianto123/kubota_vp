@extends(backpack_view('layouts.plain'))

@section('content')
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-4">
            <img src="{{asset('img/logo-kubota.png')}}" style="width: 100px;" class="img img-fluid" alt="">
            <div class="card">
                <div class="card-body">
                    <form id="form-login" class="col-md-12 p-t-10"  method="post" action="{{ route('rectmedia.auth.authenticate') }}">
                        {!! csrf_field() !!}

                        <div class="form-group">
                            <label class="control-label">Username</label>

                            <div>
                                <input type="text" class="form-control rect-validation" name="username" id="username">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label" for="password">Password</label>

                            <div>
                                <input type="password" class="form-control rect-validation" name="password" id="password">
                            </div>
                        </div>

                        <div class="form-group">
                            <div>
                                <button type="button" id="btn-for-form-login" onclick="submitAfterValid('form-login')" class="btn btn-block btn-primary-vp">
                                    Login
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
                submitAfterValid('form-login')
                return false;    //<---- Add this line
            }
        });
    </script>
    @endsection
@endsection
