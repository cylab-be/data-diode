$(function() {
    $("#ports-table").tabulator({
        layout:"fitColumns",
        columns:[
            {title:"ID", field:"id", visible: false},
            {title:"Input Port", field:"input_port", editor: "input", validator: ["unique", "required", "integer", "min:1", "max:65535"]},
            {title:"Ouput Port", field:"output_port", editor: "input", validator: ["unique", "required", "integer", "min:1", "max:65535"]},
            {formatter: "buttonCross", align: "center", cellClick: function(e, cell){
                swal({
                    title: "Warning",
                    type: "warning",
                    html: "Are you sure you want to delete this rule ?",
                    showCloseButton: true,
                    showCancelButton: true,
                    focusCancel: true,
                    confirmButtonText: "Confirm",
                    cancelButtonText: "Cancel"
                }).then(function(){
                    deleteRow(cell.getRow());
                });
            }, width: 30, headerSort: false}
        ],
        ajaxURL: "/rule",
        ajaxResponse: function(url, params, response){
            return response.data;
        },
        addRowPos: "bottom"
    });
    $("#add-rule").click(function() {
        swal.setDefaults({
            progressSteps: ["1", "2"]
        });
        swal.queue([
        {
            title: "Input port",
            input: "number",
            confirmButtonText: "Next",
            showCancelButton: true,
            inputValidator: validatePort
        },
        {
            title: "Output port",
            input: "number",
            confirmButtonText: "OK",
            showCancelButton: true,
            inputValidator: validatePort
        }
        ]).then(function(result){
            swal.resetDefaults();
            addRule(result);
        });
    });
});

function deleteRow(row){
    $.ajax({
        type: "DELETE",
        url: "/rule/" + row.row.data.id,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        error: function() {
            toastr.error("Operation failed !");
        },
        success: function() {
            $("#ports-table").tabulator("deleteRow", row);
            toastr.success("Successfully deleted !");
        }
    });
}

function validatePort(value){
    return new Promise(function(resolve, reject){
        if(!value){
            reject("Port number is needed");
        } else {
            var intValue = parseInt(value);
            if(intValue < 1 || intValue > 65535){
                reject("Port number should be between 1 and 65535");
            } else {
                resolve();
            }
        }
    });
}

function addRule(values){
    $.ajax({
        type: "POST",
        url: "/rule",
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: {
            input_port: values[0],
            output_port: values[1]
        },
        error: function() {
            toastr.error("Operation failed !");
        },
        success: function(data) {
            $("#ports-table").tabulator("addRow", {
                id: data.id,
                input_port: values[0],
                output_port: values[1]
                
            });
            toastr.success("Successfully added !");
        }
    });
}