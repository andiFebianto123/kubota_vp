@extends(backpack_view('blank'))

@section('after_styles')
    <style media="screen">
        .backpack-profile-form .required::after {
            content: ' *';
            color: red;
        }
    </style>
@endsection

@php
  $breadcrumbs = [
      trans('backpack::crud.admin') => url(config('backpack.base.route_prefix'), 'dashboard'),
      trans('backpack::base.my_account') => false,
  ];
@endphp

@section('header')
    <section class="content-header">
        <div class="container-fluid mb-3">
            <h1>{{ trans('backpack::base.my_account') }}</h1>
        </div>
    </section>
@endsection

@section('content')
    <div class="row">

        @if (session('success'))
        <div class="col-lg-8">
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        </div>
        @endif

        @if ($errors->count())
        <div class="col-lg-8">
            <div class="alert alert-danger">
                <ul class="mb-1">
                    @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
        @endif

        {{-- UPDATE INFO FORM --}}
        <div class="col-lg-8">
            <form class="form" action="{{ route('rectmedia.account.info.update') }}" method="post">

                {!! csrf_field() !!}

                <div class="card padding-10">

                    <div class="card-header">
                        {{ trans('backpack::base.update_account_info') }}
                    </div>

                    <div class="card-body backpack-profile-form bold-labels">
                        <div class="row">
                            <div class="col-md-6 form-group">
                                @php
                                    $label = trans('backpack::base.name');
                                    $field = 'name';
                                @endphp
                                <label class="required">{{ $label }}</label>
                                <input required class="form-control" type="text" name="{{ $field }}" value="{{ old($field) ? old($field) : $user->$field }}">
                            </div>

                            <div class="col-md-6 form-group">
                                @php
                                    $label = config('backpack.base.authentication_column_name');
                                    $field = backpack_authentication_column();
                                @endphp
                                <label>{{ $label }}</label>
                                <input required class="form-control" type="{{ backpack_authentication_column()=='email'?'email':'text' }}" name="{{ $field }}" value="{{ old($field) ? old($field) : $user->$field }}" disabled>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-success"><i class="la la-save"></i> {{ trans('backpack::base.save') }}</button>
                        <a href="{{ backpack_url() }}" class="btn">{{ trans('backpack::base.cancel') }}</a>
                    </div>
                </div>

            </form>
        </div>
        
        {{-- CHANGE PASSWORD FORM --}}
        <div class="col-lg-8">
            <form class="form" action="{{ route('rectmedia.update.password') }}" method="post">

                {!! csrf_field() !!}

                <div class="card padding-10">

                    <div class="card-header">
                        {{ trans('backpack::base.change_password') }}
                        @if($user->last_update_password == null)
                            <label style="color: red;">
                                Anda harus melakukan update password anda terlebih dahulu, untuk dapat menggunakan aplikasi ini!
                            </label>
                        @endif
                    </div>

                    <div class="card-body backpack-profile-form bold-labels">
                        <div class="row">
                            <div class="col-md-4 form-group">
                                @php
                                    $label = trans('backpack::base.old_password');
                                    $field = 'old_password';
                                @endphp
                                <label class="required">{{ $label }}</label>
                                <div class="input-group">
                                <input autocomplete="new-password" required class="form-control" type="password" name="{{ $field }}" id="{{ $field }}" value="{{ old($field) ? old($field) : '' }}">
                                <div class="input-group-append">
                                    <span class="input-group-text show-password" style="cursor: pointer">
                                        <i class="la la-eye" aria-hidden="true"></i>
                                    </span>
                                </div>
                                </div>
                            </div>

                            <div class="col-md-4 form-group">
                                @php
                                    $label = trans('backpack::base.new_password');
                                    $field = 'new_password';
                                @endphp
                                <label class="required">{{ $label }}</label>
                                <div class="input-group">
                                <input autocomplete="new-password" required class="form-control" type="password" name="{{ $field }}"  value="{{ old($field) ? old($field) : '' }}" id="{{ $field }}">
                                <div class="input-group-append">
                                    <span class="input-group-text show-password" style="cursor: pointer">
                                        <i class="la la-eye" aria-hidden="true"></i>
                                    </span>
                                </div>
                                </div>
                            </div>

                            <div class="col-md-4 form-group">
                                @php
                                    $label = trans('backpack::base.confirm_password');
                                    $field = 'confirm_password';
                                @endphp
                                <label class="required">{{ $label }}</label>
                                <div class="input-group">
                                <input autocomplete="new-password" required class="form-control" type="password" name="{{ $field }}" id="{{ $field }}" value="{{ old($field) ? old($field) : '' }}">
                                <div class="input-group-append">
                                    <span class="input-group-text show-password" style="cursor: pointer">
                                        <i class="la la-eye" aria-hidden="true"></i>
                                    </span>
                                </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                            <button type="submit" class="btn btn-success"><i class="la la-save"></i> {{ trans('backpack::base.change_password') }}</button>
                            <a href="{{ backpack_url() }}" class="btn">{{ trans('backpack::base.cancel') }}</a>
                    </div>

                </div>

            </form>
        </div>

    </div>
@endsection

@push('after_scripts')
    <script>
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
@endpush
