<?php

 require_once "db.php";


  $conn = new Database();
  $conn = $conn->getConn();

    $data = getAll($conn);
    
  
  if ($_SERVER["REQUEST_METHOD"] == "POST") {

  
    // var_dump($_FILES);

    try {
    
      if (empty($_FILES)) {
        throw new Exception('Invalid upload');
      }

      switch ($_FILES['file']['error']) {
        case UPLOAD_ERR_OK:
          break;
        case UPLOAD_ERR_NO_FILE:
          throw new Exception('No file uploaded');
          break;
        case UPLOAD_ERR_INI_SIZE:
          throw new Exception('File is too large (from the server settings)');
          break;
        default:
        throw new Exception('An error occurred');
      }

      // Restrict the file size
      if ($_FILES['file']['size'] > 1000000) {

        throw new Exception('File is too large');
      }

      $mime_types = ['image/gif', 'image/png', 'image/jpeg'];

      $finfo = finfo_open(FILEINFO_MIME_TYPE);
      $mime_type = finfo_file($finfo, $_FILES['file']['tmp_name']);

      if ( ! in_array($mime_type, $mime_types)) {

        throw new Exception('Invalid file type');
      }

      // Move the uploaded file
      $pathinfo = pathinfo($_FILES["file"]["name"]);

      $base = $pathinfo['filename'];

      // Replace any characters that aren't letters, numbers, underscores or hyphens with an underscore
      $base = preg_replace('/[^a-zA-Z0-9_-]/', '_', $base);
      
      //restrict the lenght of filename to 200 characters
      $base = mb_substr($base, 0, 200);

      $filename = $base . "." . $pathinfo['extension'];

      $destination = "./uploads/$filename";

      $i = 1;

      while (file_exists($destination)) {

        $filename = $base . "-$i." . $pathinfo['extension'];
        $destination = "./uploads/$filename";

        $i++;
      }

      if (move_uploaded_file($_FILES['file']['tmp_name'], $destination)) {

        setFile($conn, $filename);

        echo "File uploaded successfully.";



      } else {

        throw new Exception('Unable to move uploaded file');
      }
        

    } catch (Exception $e) {
      echo $e->getMessage();
    }

  }

      /**
     * Update the image file property
     *
     * @param object $conn Connection to the database
     * @param string $filename The filename of the image file
     *
     * @return boolean True if it was successful, false otherwise
     */
    function setFile($conn, $filename)
    {
        $sql = "INSERT INTO files
               (file_path) VALUES(:path_file)";

        $stmt = $conn->prepare($sql);

        $stmt->bindValue(':path_file', $filename, PDO::PARAM_STR);

       if(!$stmt->execute()){
          echo $e->getMessage();
       }
    }

    
    /**
     * Get all the file in the database
     *
     * @param object $conn Connection to the database
     * @param integer $id the article ID
     * @param string $columns Optional list of columns for the select, defaults to *
     *
     * @return mixed An object of this class, or null if not found
     */
    function getAll($conn)
    {
        $sql = "SELECT * FROM FILES";

        $stmt = $conn->prepare($sql);
      

        if ($stmt->execute()) {

          return $stmt->fetchAll(PDO::FETCH_ASSOC);

        }
    }

 ?>

  <!DOCTYPE html>
  <html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <title>Uploader_Files_PHP</title>
  </head>
  <body>

  
    <div class="container">
      <div class="row mt-5">
          <div class="col-md-6">
          <h2 >Upload files</h2>

            <form method="post" enctype="multipart/form-data">

              <div class="form-group">
                <label for="file">Image file</label>
                <input type="file" name="file" id="file">
              </div>

              <button class="btn btn-outline-secondary">Send</button>
            </form> 
          </div>
          <div class="col-md-6P">
             <h3>Upload files on display</h3>
             
             <?php foreach ($data as $key => $value) : ?>

             <div class="card m-2" style="width:300px;">
                <img class="img-responsive" src="./uploads/<?= $data[$key]['file_path'] ?>" alt="Card image cap" style="height:250px;" >
                <div class="card-body">
                  <p class="card-text">Lorem ipsum dolor, sit amet consectetur adipisicing elit. Voluptatibus, sint..</p>
                </div>
              </div>
              <?php endforeach ?>
          </div>
      </div>
    </div>

    <script src="script.js"></script>
  </body>
  </html>
