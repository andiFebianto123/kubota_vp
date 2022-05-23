<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ config('backpack.base.html_direction') }}">
<head>
    @include(backpack_view('inc.head'))
</head>
<body class="app flex-row align-items-center">

  @yield('header')

  <div class="container">
    @yield('content')
  </div>

  <footer class="app-footer sticky-footer">
      @include('backpack::inc.footer')
  </footer>




  @yield('before_scripts')
  @stack('before_scripts')

  @include(backpack_view('inc.scripts'))

  @yield('after_scripts')
  @stack('after_scripts')
  <script>
    if($( window ).height() < 424){
      $('.app-footer').addClass('d-none')
    }
    $(window).on('resize', function(){
        var win = $(this); //this = window
        if (win.height() < 424) {
          $('.app-footer').addClass('d-none')
        }else{
          $('.app-footer').removeClass('d-none')
        }
    });

  </script>

</body>
</html>
