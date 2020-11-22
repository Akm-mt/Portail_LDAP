/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './styles/manage-users-page.scss';

// Need jQuery? Install it with "yarn add jquery", then uncomment to import it.
// import $ from 'jquery';
//$(document).ready(function (e) {

$(document).ready(function () {

    var DT1 = initTable();

    $(".selectall-checkbox").on("click", function (e) {
        if ($(this).is(":checked")) {
            DT1.rows().select();
        } else {
            DT1.rows().deselect();
        }
    });

    $('#user-table tbody').on('click', '.btn-edit', function () {
        var data = DT1.row($(this).parents('tr')).data();
        alert(data[0] + "'s salary is: " + data[6]);
    });

    $('#user-table tbody').on('click', '.btn-del', function () {
        var globalSelected= [];
        var data = DT1.row($(this).parents('tr')).data();
        delete data[0];
        globalSelected.push(Object.assign({},transfomDataWithHeader(data)));
        deleteUsers(Object.assign({},globalSelected), 1);

    });

});
function addUser() {

    var url = new URL(window.location.href);
    url.hash = "";
    url.search = "";

    $.confirm({
        icon: 'fa fa-warning',
        title: 'Confirmation de suppression',
        content: '' +
        '<form action="" class="formName">' +
        '<div class="form-group">' +
        '<label>Enter something here</label>' +
        '<input type="text" placeholder="Your name" class="name form-control" required />' +
        '</div>' +
        '</form>',
        animation: 'opacity',
        theme: 'dark',
        buttons: {
            cancel: function () {
                return;
            },
            Ajouter: {
                btnClass: 'btn-info',
                action:function () {
                    $.alert('Your name is ' + name);
                    // $.ajax({
                    //     url: url,
                    //     type: 'DELETE',
                    //     contentType: "application/json",
                    //     dataType: "json",
                    //     data: JSON.stringify(arrayUsers)
                    // }).always(function (data_request) {
                    //     toastr.success(data_request['message']);
                    //     reloadTable();
                    // });
                },
            }
        }
    });    
}

function deleteUsers(arrayUsers, count) {

    var url = new URL(window.location.href);
    url.hash = "";
    url.search = "";

    $.confirm({
        icon: 'fa fa-warning',
        title: 'Confirmation de suppression',
        content: 'Voulez vous supprimmer les élements selectionner ('+ count +') ? ',
        animation: 'opacity',
        theme: 'dark',
        buttons: {
            cancel: function () {
                return;
            },
            confirm: {
                btnClass: 'btn-danger',
                action:function () {
                    $.ajax({
                        url: url,
                        type: 'DELETE',
                        contentType: "application/json",
                        dataType: "json",
                        data: JSON.stringify(arrayUsers)
                    }).always(function (data_request) {
                        toastr.success(data_request['message']);
                        reloadTable();
                    });
                },
            }
        }
    });    
}



function reloadTable(){
    var url = new URL(window.location.href);
    url.hash = "";
    url.search = "";

    $.ajax({
        url: url,
        type: 'GET',
    }).always(function (data_request) {
        $('#global-content').empty();
        $('#global-content').append(data_request);
        $('#user-table').dataTable().fnDestroy();
        initTable();
    });
    
    return ;
}

function initTable(){

    var DT1 = $('#user-table').DataTable({
        "scrollY": "70vh",
        "scrollCollapse": true,
        columnDefs: [{
            orderable: false,
            className: 'select-checkbox',
            targets: 0
        }, {
            orderable: false,
            targets: -1,
            data: null,
            defaultContent: '<button type="button" class="btn btn-warning btn-edit p-1 mx-1"><i class="fas fa-user-edit"></i></button><button type="button" class="btn btn-danger btn-del px-2 py-1"><i class="fas fa-trash"></i></button>'
        }
        ],
        dom: 'Bfrtip',
        buttons: [
            {
                text: 'Add',
                className: 'btn-info',
                action: function () {
                    toastr.success('Ajouter un utilisateur !');
                    addUser();
                }
            },
            {
                text: 'Delete',
                className: 'btn-danger',
                action: function () {
                    var dataRows = getDataRowSelected(DT1);
                    var count = $.map(dataRows, function(value, index){
                        return [value];
                    });
                    if (count.length > 0){
                        deleteUsers(dataRows, count.length);
                    }else{
                        toastr.error('Vous devez selectionner au moins un élement !');
                    }
                }
            }
        ],
        'select': {
            style: 'multi',
            selector: 'td:first-child',
        },
        'order': [[1, 'asc']],
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.21/i18n/French.json"
        },
    });
    return DT1;
}

function getDataRowSelected(table){

    var globalSelected = [];
    
    table.rows({ selected: true }).every(function (rowIdx, tableLoop, rowLoop) {
        var dataRow = {};
        var data = $.map(this.data(), function (value, index) {
            return [value];
        });
        data.shift();

        var dataRow = transfomDataWithHeader(data);

        globalSelected.push(Object.assign({}, dataRow));
    });
    
    return Object.assign({},globalSelected);
}

function transfomDataWithHeader(objectdata){
    var columnNames = [];
    var dataRow = [];
    var data = $.map(objectdata, function(value, index){
        return [value];
    });
    $("#user-table thead tr th").each(function () {
        columnNames.push($(this).text()); //This executes once per column showing your column names!
    });
    columnNames.shift();  // Removes the first element from an array and returns only that element.
    columnNames.pop();
    
    for (var i = 0, len = columnNames.length; i < len; i++) {
        if(/<\/?[a-z][\s\S]*>/i.test(data[i])){
            dataRow[columnNames[i]] = transformHtmlToStr(data[i]);
        }else{
            dataRow[columnNames[i]] = data[i];
        }
    }

    return dataRow ;
}

function transformHtmlToStr(data){
    var str1 = "" ;
    $.each($.parseHTML(data), function(index,value) {
        if ( /\S/.test(value.textContent) == true ){
            str1 += value.textContent +';'
        } 
    });
    return str1.slice(0, -1);
}