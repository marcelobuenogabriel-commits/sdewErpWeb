$(document).ready(function () {
    setTimeout(function () {
        $('#alertas_custom').remove();
    }, 7000);

    $('.money').maskMoney({
        prefix: 'R$ ',
        allowNegative: false,
        thousands: '.',
        decimal: ',',
        affixesStay: true
    });
});

function validField() {

    let divs = document.getElementsByClassName('error_span');
    let retorno = true;

    document.querySelectorAll('input, textarea, select').forEach(function (key) {
        if (key.type != 'submit' && key.value == '' && key.id != '' && key.hidden != true) {

            if(key.parentElement.parentElement.hidden == false) {
                if (key.parentElement.childElementCount > 2) {
                    $('#' + key.id).css({"border": "2px solid red"});
                    key.parentElement.children.error_span.hidden  = false;

                    retorno = false;
                }
            }
        } else {
            if (key.type != 'submit' && key.value != '' && key.id != '' && key.hidden != true) {

                if (key.parentElement.childElementCount > 2) {
                    $('#' + key.id).css({"border": "1px solid #ced4da"});
                    key.parentElement.children.error_span.hidden  = true;
                }
            }
        }
    });

    return retorno;
}

