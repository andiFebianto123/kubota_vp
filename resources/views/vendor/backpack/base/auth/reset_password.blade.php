@extends(backpack_view('layouts.plain'))

@section('content')
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-4">
            <img src="{{asset('img/logo-kubota.png')}}" style="width: 100px;" class="img img-fluid" alt="">
            <div class="card">
                <div class="card-body">
                    <form class="col-md-12 p-t-10" id="form-update-password" role="form" method="POST" action="{{route('forgotpassword.update')}}">
                        {!! csrf_field() !!}
                        <div class="form-group">
                            <label class="control-label">New Password</label>
                            <div class="input-group">
                                <input id="password" type="password" class="form-control rect-validation" name="password">
                                <div class="input-group-append">
                                    <span class="input-group-text show-password" style="cursor: pointer">
                                        <i class="la la-eye" aria-hidden="true"></i>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label">New Password Confirmation</label>
                            <div class="input-group">
                                <input id="password_confirmation" type="password" class="form-control rect-validation" name="password_confirmation">
                                <div class="input-group-append">
                                    <span class="input-group-text show-password" style="cursor: pointer">
                                        <i class="la la-eye" aria-hidden="true"></i>
                                    </span>
                                </div>
                            </div>
                            <input type="hidden" name="token" value="{{request('t')}}">
                        </div>

                        <div class="form-group">
                            <div>
                                <button type="button" id="btn-for-form-update-password" onclick="submitAfterValid('form-update-password')" class="btn btn-block btn-primary-vp">
                                    Update
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
                submitAfterValid('form-update-password')
                return false;    //<---- Add this line
            }
        });
        $("span.show-password").mousedown(function(){
            $(this).parent().prev().attr('type','text');
        }).mouseup(function(){
            $(this).parent().prev().attr('type','password');
        }).mouseout(function(){
            $(this).parent().prev().attr('type','password');
        });

        $("span.show-password").each(function(i, el){
            onLongPress(el, function(){
                $(el).parent().prev().attr('type','text');
            }, function(){
                $(el).parent().prev().attr('type','password');
            })
        });

        function onLongPress(element, callback, cancel) {
            element.addEventListener('touchstart', () => { 
                callback();
            });

            element.addEventListener('touchend', cancel);
            element.addEventListener('touchmove', function(e){
                var selectedElement = document.elementFromPoint(e.touches[0].clientX, e.touches[0].clientY);
                if (!selectedElement.classList.contains('show-password') && !selectedElement.classList.contains('input-group-append') && !selectedElement.classList.contains('la-eye')){
                    cancel();
                }
            });
        }
    </script>
    @endsection
@endsection


