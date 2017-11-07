$(() => {
    $("#ports-table").tabulator({
        layout:"fitColumns",
        columns:[
            {title:"ID", field:"id", visible: false},
            {title:"Input Port", field:"input_port", editor: "input", validator: ["unique", "required", "integer", "min:0", "max:65535"]},
            {title:"Ouput Port", field:"output_port", editor: "input", validator: ["unique", "required", "integer", "min:0", "max:65535"]},
            {formatter: "buttonCross", align: "center", cellClick: (e, cell) => {
                $("#ports-table").tabulator("deleteRow", cell.getRow());
            }, width: 30, headerSort: false}
        ],
        ajaxURL: "/rule",
        ajaxResponse: (url, params, response) => {
            return response.data;
        },
        rowDeleted: (row) => {
            alert("deleted row");
        }
    });
});