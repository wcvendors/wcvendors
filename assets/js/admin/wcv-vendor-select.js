(function ($) {
    $(document).ready(function() {
        var $selectBox = $('.wcv-vendor-select');

        if($selectBox.find('option').length < 100 ) {
            return $selectBox.select2();
        }

        $selectBox.select2({
            minimumInputLength: 4,
            ajax: {
                url: ajaxurl,
                type: 'POST',
                dataType : "json",
                data: function(params) {
                    return {
                        action: 'wcv_search_vendors',
                        term: params.term
                    }
                }
            }
        });
    });
})(jQuery);