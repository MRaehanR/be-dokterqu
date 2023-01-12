$(function() {
    dataTable('open');
    dataTable('accepted');
    dataTable('rejected');

    $('#open').click(function () { 
        $('#open_apotek_verification_table').DataTable().destroy()
        dataTable('open')
    });

    $('#accepted').click(function () { 
        $('#accepted_apotek_verification_table').DataTable().destroy()
        dataTable('accepted')
    });

    $('#rejected').click(function () { 
        $('#rejected_apotek_verification_table').DataTable().destroy()
        dataTable('rejected')
    });

    window.addEventListener('resize', function () {
        $($.fn.dataTable.tables(true)).DataTable()
           .columns.adjust();
    });


    function dataTable(status){
        $(`#${status}_apotek_verification_table`).DataTable({
            processing: true,
            fixedHeader: true,
            ajax: {
                url: `${url}/apotek/get-all-apotek?status=${status}`,
                dataSrc: 'data',
                type: 'GET',
            },
            scrollX: true,
            lengthMenu: [10, 30, 50, 100],
            responsive: true,
            autoWidth: false,
            dom:
            "<'row hidden'<'col-sm-6 hidden-xs'i><'col-sm-6 hidden-print'f>>" +
            "<'row'<'col-sm-12'tr>>" +
            "<'row mt-2 '<'col-sm-6 col-md-4'l><'col-sm-2 col-md-4 text-center'B><'col-sm-6 col-md-4 hidden-print'p>>",
            paging: true,
            columnDefs: [
                { width: "15%", targets: 0 },//user
                { width: "5%", targets: 1 },//status
                { width: "15%", targets: 2 },//apotek
                { width: "10%", targets: 3 },//requestAt
                { width: "15%", targets: 4 },//actions
            ],
            columns: [
                {data: 'user' , render : function ( data, type, row, meta ) {
                    let html = ' [#'+row.user_id+'] ' + data.name;
    
                    return html;
                }},
                {data: 'status', name: 'Status'},
                {data: 'name', name: 'Apotek'},
                {data: 'created_at', render: function(data){
                    date = new Date(data).toISOString().
                        replace(/T/, ' ').
                        replace(/\..+/, '')   
                    return date;
                }},

                {data: 'id', render: function(data, type, row, meta) {
                    let html = '';

                    html += '<button class="btn_detail btn btn-inline-block btn-secondary mr-1" type="button" data-toggle="modal" onclick="detailModal()" key="'+data+'" data-id="'+data+'" data-name="'+row.user.name+'" data-email="'+row.user.email+'" data-phone="'+row.user.phone+'" data-photo="'+row.user.photo+'" data-name_apotek="'+row.name+'" data-address="'+row.address+'" data-latitude="'+row.latitude+'" data-longitude="'+row.longitude+'" data-ktp="'+row.ktp+'" data-npwp="'+row.npwp+'" data-surat_izin_usaha="'+row.surat_izin_usaha+'" data-image_apotek="'+row.image+'" data-gender="'+row.user.gender+'">Detail</button>'
                    
                    if(row.status == 'open'){
                        html += '<button class="btn_accept btn btn-inline-block btn-success mr-1" type="button" data-id="'+data+'" data-user_id="'+row.user_id+'" onclick="acceptEntry()">Accept</button>'
                        html += '<button class="btn_reject btn btn-inline-block btn-danger mr-1" type="button" data-id="'+data+'" data-user_id="'+row.user_id+'" onclick="rejectEntry()">Reject</button>'
                    }

                    return html;
                }}
            ],
            rowCallback: function (row, data, index) {
                if (data.status == "open") {
                    $("td:eq(1)", row).addClass('text-center text-uppercase bg-default')
                } else if (data.status == "accepted") {
                    $("td:eq(1)", row).addClass('text-center text-uppercase bg-success')
                } if (data.status == "rejected") {
                    $("td:eq(1)", row).addClass('text-center text-uppercase bg-danger')
                }
            },
        }); 
    }
});