if(isset($_GET['order'])){
    if(isset($_POST)){
        $id = $_GET['order']; // id = folder
        $uid = $_GET['uid']; // user id ;
        $folderPath = $_SERVER['DOCUMENT_ROOT'] . '/uploads/' . $id . '/';
        $uploads_dir = '/uploads/' . $id . '/';
        // check if folder exist
        if (!is_dir($folderPath)) {
            mkdir($folderPath, 0777, true);
        }
            foreach ($_FILES["files"]["error"] as $key => $error) {
                if ($error == UPLOAD_ERR_OK) {
                    $nextNameIndex = 1;
                    $tmp_name = $_FILES["files"]["tmp_name"][$key];
                    $name = str_replace(" ", "_", basename($_FILES["files"]["name"][$key]));
                    if(file_exists($folderPath.$name)){
                        // file exist
                       # $nameExplode = explode(".", $name);
                       #bugFix: name contains dots
                        $fileExtension = ".".pathinfo($name, PATHINFO_EXTENSION); // get file extension with the dot -> .pdf
                        $fileName = trim($name, $fileExtension); // get only the file name without the dot and the extension
                        $indexName = 0;
                        while(file_exists($folderPath.$name)){
                            $indexName++;
                           # $name = $nameExplode[0]."(".$indexName.").".$nameExplode[1];
                             $name = $fileName."(".$indexName.").".$fileExtension;
                        }
                    }
                    // success upload
                    move_uploaded_file($tmp_name, "$folderPath$name");
                    // status log
                    $time = time();
                    $reason = "Прикачи нов файл ($name)";
                    $db->query("INSERT INTO `status_log` 
                        (parent_id, uid, time_start, status, reason) 
                        values 
                        ('$id', '$uid', '$time', '11', '$reason')");

                }
            }
        // return total files in folder
        echo (is_dir($folderPath)) ? count(glob($folderPath . '*')) : 0;
        }
     exit();
    }

/// show files in dir
if (isset($_GET['showfiles'])) {
    $id = $_GET['showfiles'];
    #$folderPath = dirname(__FILE__, 2) . '/uploads/' . $id . '/';
    $folderPath = $_SERVER['DOCUMENT_ROOT'] . '/uploads/' . $id . '/';
    if (!is_dir($folderPath)) {
        $return = '{"data": [ ]}';
    } else {
        function getFileType(string $url): string
        {
            $filename = explode('.', $url);
            $extension = end($filename);
            return match ($extension) {
                'pdf' => $extension,
                'docx', 'doc' => 'word',
                'xls', 'xlsx' => 'excel',
                default => 'alt',
            };
        }
        $data = '{"data": [';
        // dir exist
        #$orderFiles = glob($folderPath . '*');
        $orderFiles = array_diff(scandir($folderPath), array('..', '.'));
        $orderFiles_count = sizeof($orderFiles);
        foreach ($orderFiles as $file) {
            $explode = explode(".", $file);
            $fileExt = $explode[1];
            $fileName = $explode[0];
            $filePath = '/uploads/' . $id . '/';
            $fileSize = round((filesize($folderPath . $file)) / 1024, 1);
            $data .= '{"fileName":"<a href=\'/dl.php?id='.$id.'&f='.$file.'\'><i class=\'far fa-file-'.getFileType($file).' fa-2x\'></i> ' . $file . '</a>",'
                . '"delButton":"<span onclick=\'deleteFile(`'.$id.'`, `'.$file.'`)\' class=\'btn btn-danger\'><i class=\'far fa-trash-alt\'></i></span>",'
                . '"fileSize":"' . $fileSize . ' KB"}, ';
        }
        $return = rtrim(stripslashes($data), ', ') . ' ]}';
    }
    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/json; charset=utf-8');
    echo $return;
    exit();

}
// delete file
if (isset($_GET['removeFile'])) {
    $fileName = $_GET['fileName'];
    $id = $_GET['removeFile'];
    $uid = $_GET['uid'];
    ///
    if (is_dir($folderPath = $_SERVER['DOCUMENT_ROOT'] . '/uploads/' . $id . '/')) {
        echo "yes, dir";
        if (file_exists($folderPath = $_SERVER['DOCUMENT_ROOT'] . '/uploads/' . $id . '/' . $fileName)) {
            echo "yes, exist file, can remove";
            if (unlink($folderPath = $_SERVER['DOCUMENT_ROOT'] . '/uploads/' . $id . '/' . $fileName)) {
                echo "removed file";
                /// status log write , user removed file with filename
                $time = time();
                $reason = "Премахна файл ($fileName)";
                $db->query("INSERT INTO `status_log` 
                        (parent_id, uid, time_start, status, reason) 
                        values 
                        ('$id', '$uid', '$time', '12', '$reason')");
            } else {
                echo "error removing file";
            }
        } else {
            echo "no file, // $fileName";
        }
    }
    exit();
}
