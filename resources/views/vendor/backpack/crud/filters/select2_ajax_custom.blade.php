{{-- Select2 Ajax Backpack CRUD filter --}}

@php
    $filter->options['quiet_time'] = $filter->options['quiet_time'] ?? $filter->options['delay'] ?? 500;
@endphp

<li filter-name="{{ $filter->name }}"
    filter-type="{{ $filter->type }}"
    filter-key="custom_more_table"
	class="nav-item dropdown {{ Request::get($filter->name)?'active':'' }}">
    <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">{{ $filter->label }} <span class="caret"></span></a>
    <div class="dropdown-menu p-0 ajax-select">
	    <div class="form-group mb-0">
            <select
                id="filter_custom_more_table"
                name="filter_{{ $filter->name }}"
                class="form-control input-sm select2"
                placeholder="{{ $filter->placeholder }}"
                data-filter-key="{{ $filter->key }}"
                data-filter-type="select2_ajax"
                data-filter-name="{{ $filter->name }}"
                data-select-key="{{ $filter->options['select_key'] ?? 'id' }}"
                data-select-attribute="{{ $filter->options['select_attribute'] ?? 'name' }}"
                data-language="{{ str_replace('_', '-', app()->getLocale()) }}"
                filter-minimum-input-length="{{ $filter->options['minimum_input_length'] ?? 2 }}"
                filter-method="{{ $filter->options['method'] ?? 'GET' }}"
                filter-quiet-time="{{ $filter->options['quiet_time'] }}"
            >
				@if (Request::get($filter->name))
					<option value="{{ Request::get($filter->name) }}" selected="selected"> {{ Request::get($filter->name.'_text') ?? 'Previous selection' }} </option>
				@endif
			</select>
	    </div>
    </div>
  </li>

@push('crud_list_scripts')
  <script>
    // SCRIPT untuk custom filter table
        jQuery(document).ready(function($) {
            // trigger select2 for each untriggered select2 box
            //TODO: Is it really necessary to foreach an ID when it must be UNIQUE ?
            $('#filter_custom_more_table').each(function () {

            	// if the filter has already been initialised, do nothing
            	if ($(this).attr('data-initialised')) {
            		return;
            	} else {
	            	$(this).attr('data-initialised', 'true');
            	}

            	var filterName = $(this).attr('data-filter-name');
                var filterKey = $(this).attr('data-filter-key');
                var selectAttribute = $(this).attr('data-select-attribute');
                var selectKey = $(this).attr('data-select-key');

            	$(this).select2({
				    theme: "bootstrap",
				    minimumInputLength: $(this).attr('filter-minimum-input-length'),
	            	allowClear: true,
	        	    placeholder: $(this).attr('placeholder'),
					closeOnSelect: false,
					dropdownParent: $(this).parent('.form-group'),
				    // tags: [],
				    ajax: {
				        url: '{{ $filter->values }}',
				        dataType: 'json',
				        type: $(this).attr('filter-method'),
				        delay: $(this).attr('filter-quiet-time'),

				        processResults: function (data) {
                            //it's a paginated result
                            if(Array.isArray(data.data)) {
                                if(data.data.length > 0) {
                               return {
                                    results: $.map(data.data, function (item) {
                                     return {
                                        text: item[selectAttribute],
                                        id: item[selectKey]
                                    }
                                })
                                };
                                }
                            }else{
                                //it's non-paginated result
                                return {
                                    results: $.map(data, function (item, i) {
                                        return {
                                            text: item,
                                            id: i
                                        }
                                    })
                                };
                            }
				        }
				    }
				}).on('change', function (evt) {
					var val = $(this).val();
					var val_text = $(this).select2('data')[0]?$(this).select2('data')[0].text:null;
					var parameter = filterName;

			    	// behaviour for ajax table
					var ajax_table = $('#crudTable2').DataTable();
					var current_url = ajax_table.ajax.url();
					var new_url = addOrUpdateUriParameter(current_url, parameter, val);
					new_url = addOrUpdateUriParameter(new_url, parameter + '_text', val_text);
					new_url = normalizeAmpersand(new_url.toString());

					// replace the datatables ajax url with new_url and reload it
					ajax_table.ajax.url(new_url).load();

					// add filter to URL
					crud.updateUrl(new_url);

					// mark this filter as active in the navbar-filters
					if (URI(new_url).hasQuery(filterName, true)) {
						$('li[filter-key=custom_more_table]').addClass('active');
					}
					else
					{
						$("li[filter-key=custom_more_table]").removeClass("active");
						$("li[filter-key=custom_more_table]").find('.dropdown-menu').removeClass("show");
					}
				});

				// when the dropdown is opened, autofocus on the select2
				$('li[filter-key=custom_more_table]').on('shown.bs.dropdown', function () {
					$('#filter_custom_more_table').select2('open');
				});

				// clear filter event (used here and by the Remove all filters button)
				$('li[filter-key=custom_more_table]').on('filter:clear', function(e) {
					$('li[filter-key=custom_more_table]').removeClass('active');
	                $('#filter_custom_more_table').val(null).trigger('change');
				});
            });
        });
    </script>
@endpush

