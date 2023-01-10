$(function() {
    dataTable('open');
    dataTable('accepted');
    dataTable('rejected');

    $('#open').click(function () { 
        $('#open_doctor_verification_table').DataTable().destroy()
        dataTable('open')
    });

    $('#accepted').click(function () { 
        $('#accepted_doctor_verification_table').DataTable().destroy()
        dataTable('accepted')
    });

    $('#rejected').click(function () { 
        $('#rejected_doctor_verification_table').DataTable().destroy()
        dataTable('rejected')
    });

    window.addEventListener('resize', function () {
        $($.fn.dataTable.tables(true)).DataTable()
           .columns.adjust();
    });


    function dataTable(status){
        $(`#${status}_doctor_verification_table`).DataTable({
            processing: true,
            fixedHeader: true,
            ajax: {
                url: `${url}/doctor/get-all-doctors?status=${status}`,
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
                { width: "10%", targets: 1 },//status
                { width: "10%", targets: 2 },//experience
                { width: "15%", targets: 3 },//datetime
                { width: "15%", targets: 4 },//actions
            ],
            columns: [
                {data: 'user' , render : function ( data, type, row, meta ) {
                    let html = ' [#'+row.user_id+'] ' + data.name;
    
                    return html;
                }},
                {data: 'status', name: 'Status'},
                {data: 'experience', render: function(data){
                    let html = data + ' Tahun';
    
                    return html;
                }},
                {data: 'created_at', render: function(data){
                    date = new Date(data).toISOString().
                        replace(/T/, ' ').
                        replace(/\..+/, '')   
                    return date;
                }},

                {data: 'id', render: function(data, type, row, meta) {
                    let html = '';

                    html += '<button class="btn_detail btn btn-inline-block btn-secondary mr-1" type="button" data-toggle="modal" onclick="detailModal()" data-id="'+data+'" data-name="'+row.user.name+'" data-email="'+row.user.email+'" data-phone="'+row.user.phone+'" data-photo="'+row.user.photo+'" data-alumnus="'+row.alumnus+", "+row.alumnus_tahun+'" data-experience="'+row.experience+" Tahun"+'" data-tempat_praktik="'+row.tempat_praktik+'" data-type_doctor="'+row.doctor_type.name+'" data-cv="'+row.cv+'" data-str="'+row.str+'" data-ktp="'+row.ktp+'" data-photo="'+row.user.photo+'" data-gender="'+row.user.gender+'">Detail</button>'
                    
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