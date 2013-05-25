$(document).on('click', '.choicefilter li a', function (e) {
    var parentDiv = $(this).parent().parent().parent();
    parentDiv.find('.name_input').val($(this).attr('data-value'));
    parentDiv.find('button').html($(this).html());
    e.preventDefault();
});