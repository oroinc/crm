(function ($) {
    $(function () {
        var countrySelect = $('select.country-select');
        var regionSelect = $('select.region-select');

        countrySelect.change(function () {
            var url = $(this).data('url') || false;
            var countryId = $(this).val();

            regionSelect.find('option[value!=""]').remove();
            if (url !== false) {
                url = url.replace('country_id', countryId);
                $.getJSON(url, {}, function (response) {
                    if (response.length > 0) {
                        $.each(response, function (i, region) {
                            regionSelect.append($('<option>', {value: i}).text(region.name));
                        })
                    }
                });
            }
        });
    }); // $.ready
})(jQuery);
