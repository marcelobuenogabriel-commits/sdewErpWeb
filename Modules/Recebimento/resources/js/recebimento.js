function getChaveNotaFiscal() {
    let numChv;

    numChv = document.getElementById('txtChaveNF').value.replace(/\s/g, '');

    if (numChv) {
        $.ajax({
            url: "{{ route('recebimento.store') }}",
            type: 'POST',
            data: {
                '_token': '{{csrf_token()}}',
                'numChv': numChv
            },
            dataType: 'json',
            async: true,
            success: function (point) {
                if (point.code == 500) {
                    mdtoast(
                        point.msg, {
                            type: 'warning',
                            duration: 3000,
                            position: 'top left'
                        });
                    document.getElementById('txtChaveNF').value = '';

                    setTimeout(() => window.location.reload(), 3000);
                } else if (point.code == 202) {
                    mdtoast(
                        point.msg, {
                            type: 'alert',
                            duration: 3000,
                            position: 'top left'
                        });
                    document.getElementById('txtChaveNF').value = '';

                    setTimeout(() => window.location.reload(), 3000);
                } else {
                    mdtoast(
                        point.msg, {
                            type: 'success',
                            duration: 3000,
                            position: 'top left'
                        });

                    document.getElementById('txtChaveNF').value = '';

                    setTimeout(() => window.location.reload(), 3000);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                var error = jqXHR.responseText;
                var content = error.content;
                console.log(content.message);
            }
        });
    }
}

function imprimeOc(numOcp, chvNfc) {
    $.ajax({
        url: "{{ route('print_tag') }}",
        type: 'POST',
        data: {
            '_token': '{{csrf_token()}}',
            'numOcp': numOcp,
            'chvNfc': chvNfc
        },
        dataType: 'json',
        async: true,
        success: function (point) {
            if (point.code == 200) {
                mdtoast(
                    point.msg, {
                        type: 'success',
                        duration: 5000
                    });
            } else {
                mdtoast(
                    point.msg, {
                        type: 'error',
                        duration: 5000
                    });
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            var error = jqXHR.responseText;
            var content = error.content;
            console.log(error);
        }
    });
}

function fecharNF(chvNfc) {
    $.ajax({
        url: "{{ route('close_nfc') }}",
        type: 'POST',
        data: {
            '_token': '{{csrf_token()}}',
            'chvNfc': chvNfc
        },
        dataType: 'json',
        async: true,
        success: function (point) {
            if (point.code == 200) {
                mdtoast(
                    point.msg, {
                        type: 'success',
                        duration: 5000
                    });
                window.location.reload();
            } else {
                mdtoast(
                    point.msg, {
                        type: 'error',
                        duration: 5000
                    });
                window.location.reload();
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            var error = jqXHR.responseText;
            var content = error.content;
            console.log(error);
        }
    });
}