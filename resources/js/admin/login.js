import $ from 'jquery';

$('.toggle-password').on('click', function(e){
    e.preventDefault();
    $(this).toggleClass('fa-eye fa-eye-slash');
    $(this).parent().find('.password').attr('type', function(index, attr) {
        return attr === 'password' ? 'text' : 'password';
    });
});

$('.toggle-confirm-password').on('click', function(e){
    e.preventDefault();
    $(this).toggleClass('fa-eye fa-eye-slash');
    $(this).parent().find('.confirm-password').attr('type', function(index, attr) {
        return attr === 'password' ? 'text' : 'password';
    });
});