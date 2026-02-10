$(document).ready(function(){
    $('#table_id').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Portuguese-Brasil.json"
        },
        "paging": true,
        "lengthChange": true,
        "searching": true,
        "order": [[1, "desc"]],
        "info": true,
        "autoWidth": false,
        "responsive": true,
    });
});