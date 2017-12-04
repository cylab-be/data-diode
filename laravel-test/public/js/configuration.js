$(function() {
    $("#ports-table").tabulator({
        layout:"fitColumns",
        columns:[
            {title:"ID", field:"id", visible: false},
            {title:"Input Port", field:"input_port", editor: "input", validator: ["unique", "required", "integer", "min:1", "max:65535"]},
            {title:"Ouput Port", field:"output_port", editor: "input", validator: ["unique", "required", "integer", "min:1", "max:65535"]},
            {title:"Destination address", field:"destination", editor: "input", validator: ["required", "regex:^(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$"]},
            {formatter: "buttonCross", align: "center", cellClick: function(e, cell){
                swal.resetDefaults();
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
        addRowPos: "bottom",
        cellEdited: editRule
    });
    $("#add-rule").click(function() {
        swal.resetDefaults();
        swal.setDefaults({
            progressSteps: ["1", "2", "3"]
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
            confirmButtonText: "Next",
            showCancelButton: true,
            inputValidator: validatePort
        },
        {
            title: "Destination address",
            input: "text",
            confirmButtonText: "OK",
            showCancelButton: true,
            inputValidator: validateIP
        }
        ]).then(function(result){
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

function validateIP(value){
    return new Promise(function(resolve, reject){
        if(!value){
            reject("Destination address is needed");
        } else {
            if(!value.match("^(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$")){
                reject("This is not a valid IP address");
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
            output_port: values[1],
            destination: values[2]
        },
        error: function() {
            toastr.error("Operation failed !");
        },
        success: function(data) {
            $("#ports-table").tabulator("addRow", {
                id: data.id,
                input_port: values[0],
                output_port: values[1],
                destination: values[2]
                
            });
            toastr.success("Successfully added !");
        }
    });
}

function editRule(cell){
    $.ajax({
        type: "PUT",
        url: "/rule/" + cell.getRow().row.data.id,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: {
            input_port: cell.getRow().row.data.input_port,
            output_port: cell.getRow().row.data.output_port,
            destination: cell.getRow().row.data.destination
        },
        error: function() {
            toastr.error("Operation failed !");
        },
        success: function(){
            toastr.success("Successfully edited !");
        }
    });
}