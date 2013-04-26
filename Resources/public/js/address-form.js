(function ($) {
    $(function () {
        var countrySelect = $('select.country-select');
        var regionSelect = $('select.region-select');

        countrySelect.change(function () {
            var url = $(this).data('url') || false;
            var countryId = $(this).val();

            if (url !== false) {
                $.getJSON(url, {countryId: countryId}, function (response) {
                    if (response.content !== false) {
                        regionSelect.replaceWith(response.content);
                    } else {
                        regionSelect.find('option[value!=""]').remove();
                    }
                });
            }
        });
    }); // $.ready
})(jQuery);

