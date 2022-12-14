<nav class="navbar navbar-expand-lg navbar-filters mb-0 pb-0 pt-0">
      <!-- Brand and toggle get grouped for better mobile display -->
      <a class="nav-item d-none d-lg-block"><span class="la la-filter"></span></a>
      <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#bp-filters-navbar" aria-controls="bp-filters-navbar" aria-expanded="false" aria-label="{{ trans('backpack::crud.toggle_filters') }}">
        <span class="la la-filter"></span> {{ trans('backpack::crud.filters') }}
      </button>

      <!-- Collect the nav links, forms, and other content for toggling -->
      <div class="collapse navbar-collapse" id="bp-filters-navbar">
        <ul class="nav navbar-nav">
          <!-- THE ACTUAL FILTERS -->
          @foreach ($crud->filters() as $filter)
            @if(isset($filter->options['custom_table']) && $filter->options['custom_table'] == true)
              @include($filter->getViewWithNamespace())
            @endif
    			@endforeach
        </ul>
      </div><!-- /.navbar-collapse -->
  </nav>

