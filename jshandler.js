   
function uploadFiles() {
        var form_data = new FormData();
        {* Read all inputs with class "custom-file-input" *}
        var inputsUpload = document.getElementsByClassName("custom-file-input");
        for (var i =0; i < inputsUpload.length; i++){
            if(inputsUpload[i].files.length > 0){
                for (var index = 0; index < inputsUpload[i].files.length; index++) {
                    form_data.append("files[]", inputsUpload[i].files[index]);
                }
            }
        }
                 $.ajax({
            url: "ajax/testFileUpload.php?order={$parentData.id}&uid={$userid}",
            cache: false,
            dataType: 'text',
            contentType: false,
            processData: false,
            data: form_data,
            type: 'post',
            success: function(response){
                console.log(response);
                if(response>0) {
                    $("#filescount").text(parseInt(response));
                    filestable.ajax.reload(null, false);

                }
            }
        });
    }
