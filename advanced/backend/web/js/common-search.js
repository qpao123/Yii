$('.btn-reset').on('click', function(){
    var form = $(this).closest('form');
    form.find('input[type="text"]').each(function(){
        $(this).val('');
    })
    form.find('select').each(function(){
        $(this).find('option').attr('selected', false);
        $(this).find('option:first-child').attr('selected', true);
    })
})