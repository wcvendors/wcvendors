(function ($) {
    var debouncedInit = debounce(initSelect2, 100);

    $( 'document' ).ready( function () {
        initSelect2();
    });
    $('button.editinline').on('click', debouncedInit);
    $('#doaction').on('click', function () {
        if ($('#bulk-action-selector-top').val() === 'edit')
            debouncedInit();
    });
    $('#doaction2').on('click', function () {
        if ($('#bulk-action-selector-bottom').val() === 'edit')
            debouncedInit();
    });

    function initSelect2() {
        var $selectBox = $('.wcv-vendor-select:visible');

        $selectBox.select2({
            minimumInputLength: wcv_vendor_select.minimum_input_length,
            ajax: {
                url: ajaxurl,
                type: 'POST',
                dataType: "json",
                data: function (params) {
                    return {
                        action: 'wcv_search_vendors',
                        term: params.term
                    }
                }
            }
        });

        $('#bulk-edit .cancel').on('click', function () {
            $selectBox.select2('destroy');
        });
    }

    function debounce(func, wait, immediate) {
        var timeout;
        return function () {
            var context = this, args = arguments;
            var later = function () {
                timeout = null;
                if (!immediate) func.apply(context, args);
            };
            var callNow = immediate && !timeout;
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
            if (callNow) func.apply(context, args);
        };
    }
})(jQuery);