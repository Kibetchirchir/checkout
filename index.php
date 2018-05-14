<?php
/**
 * Created by PhpStorm.
 * User: langa
 * Date: 5/11/2018
 * Time: 11:44 AM
 */
include 'includes/DB.php';
include 'helpers/Response.php';


if(!empty($_POST['function']))
    $function = $_POST['function'];

if(!empty($_GET['function']))
    $function = $_GET['function'];

if(!empty($_POST['function']))
    $function = $_POST['checkout'];

if(!empty($_REQUEST['function']))
    $function = $_REQUEST['function'];


    
function referenceCode(){
    $today = date("d");
    $rand = strtoupper(substr(uniqid(sha1(time())),0,4));

    return $unique = $today . $rand;
}



if($function=='checkout'){
    $amount=$_POST['amount'];
    $amount = number_format($amount, 2);//format amount to 2 decimal places
    $desc = $_POST['description'];
    $type = $_POST['type']; //default value = MERCHANT
    $referenceCode = referenceCode();//unique order id of the transaction, generated by merchant
    $first_name = $_POST['first_name']; //[optional]
    $last_name = $_POST['last_name']; //[optional]
    $email = $_POST['email'];
    $phonenumber =$_POST['phonenumber']; //ONE of email or phonenumber is required



    $sql="INSERT INTO `payments`(`reference_code`, `amount`, `email`, `phone_no`, `firstname`, `lastname`)
                VALUES ('$referenceCode','$amount','$email','$phonenumber','$first_name','$last_name')";


    $result = DB::instance()->executeSQL($sql);

    if($result){

        $response = new Response();
        $response->status = Response::STATUS_SUCCESS;
        $response->message= "success";
        $response->success = true;
        echo json_encode($response);
        exit();
    }else{

        $response = new Response();
        $response->status = Response::STATUS_SUCCESS;
        $response->message= "failed";
        $response->success = false;
        echo json_encode($response);
        exit();
    }


}
if($function=="cash"){
    $ref_id=referenceCode();
    $amount=$_POST['amount'];
    $agent_no=$_POST['agent_no'];
    $referenceCode=$_POST['reference code'];


    $sql="INSERT INTO `cash`(`ref_id`, `amount`, `agent_no`, `reference_code`)
          VALUES ('$ref_id','$amount','$agent_no','$referenceCode')";

    $result = DB::instance()->executeSQL($sql);

    if($result){

        $response = new Response();
        $response->status = Response::STATUS_SUCCESS;
        $response->message= "success";
        $response->success = true;
        echo json_encode($response);
        exit();
    }else{

        $response = new Response();
        $response->status = Response::STATUS_SUCCESS;
        $response->message= "failed";
        $response->success = false;
        echo json_encode($response);
        exit();
    }
}

if ($function =="cheques_slip") {

    //GET IMAGE AND SAVE TO THE FOLDER UPLOADS
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    // Check if image file is a actual image or fake image
    if (isset($_POST["submit"])) {
        $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
        if ($check !== false) {
            echo "File is an image - " . $check["mime"] . ".";
            $uploadOk = 1;
        } else {
            $response = new Response();
            $response->status = Response::STATUS_SUCCESS;
            $response->message = "File is not an image.";
            $response->success = false;
            echo json_encode($response);
            $uploadOk = 0;
        }
    }
    // Check if file already exists
    if (file_exists($target_file)) {
        $response = new Response();
        $response->status = Response::STATUS_SUCCESS;
        $response->message = "Sorry, file already exists.";
        $response->success = false;
        echo json_encode($response);
        $uploadOk = 0;
    }
    // Check file size
    if ($_FILES["fileToUpload"]["size"] > 500000) {
        $response = new Response();
        $response->status = Response::STATUS_SUCCESS;
        $response->message = "Sorry, your file is too large.";
        $response->success = false;
        echo json_encode($response);
        $uploadOk = 0;
    }
    // Allow certain file formats
    if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
        && $imageFileType != "gif") {
        echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
        $uploadOk = 0;
    }
    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        $response = new Response();
        $response->status = Response::STATUS_SUCCESS;
        $response->message = "Sorry, your Image was not uploaded.";
        $response->success = false;
        echo json_encode($response);
        exit();
        // if everything is ok, try to upload file
    } else {
        if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {

                 $ref_id=referenceCode();
                 $image = "https://localhost/checkout/api/uploads/".basename( $_FILES["fileToUpload"]["name"]);
                 $referenceCode=$_POST['reference code'];
                 $status=$_POST['status'];





            $sql="INSERT INTO `bank`(`ID`, `reference_code`, `image`) VALUES ('$ref_id','$referenceCode','$image')";

            $result = DB::instance()->executeSQL($sql);
            if ($result) {
                $response = new Response();
                $response->status = Response::STATUS_SUCCESS;
                $response->message = "image added Successfully";
                $response->success = true;
                echo json_encode($response);
                exit();

            } else {
                $response = new Response();
                $response->status = Response::STATUS_SUCCESS;
                $response->message = "Fail to add image";
                $response->success = false;
                echo json_encode($response);
                exit();
            }

        } else {

            $response = new Response();
            $response->status = Response::STATUS_SUCCESS;
            $response->message = "Sorry, there was an error uploading your file.";
            $response->success = false;
            echo json_encode($response);
            exit();
        }
    }
}
if($function=="bank_status"){



    $referenceCode=$_POST['reference code'];
    $amount=$_POST['amount'];

    $sql="SELECT * FROM `bank` WHERE `reference_code`= '$referenceCode' and `amount`= '$amount'";
     $result = DB::instance()->executeSQL($sql);

     $count=mysqli_num_rows($result);

     if($count==1){

         $sql="UPDATE `bank` SET `status`=1 WHERE `reference_code`='$referenceCode'";
         $result = DB::instance()->executeSQL($sql);

         if ($result) {
             $response = new Response();
             $response->status = Response::STATUS_SUCCESS;
             $response->message = "status updated";
             $response->success = true;
             echo json_encode($response);
             exit();

         }else{
             $response = new Response();
             $response->status = Response::STATUS_SUCCESS;
             $response->message = "Sorry, there was an error updating the status.";
             $response->success = false;
             echo json_encode($response);
             exit();
         }

     }else{
         $response = new Response();
         $response->status = Response::STATUS_SUCCESS;
         $response->message = "Sorry, no details available for the details given.";
         $response->success = false;
         echo json_encode($response);
         exit();
     }

}

if($result=="cash_status"){

    $referenceCode=$_POST['reference code'];
    $amount=$_POST['amount'];
    $agent_no=$_POST['agent_no'];

    $sql="SELECT * FROM `cash` WHERE  `reference_code`= '$referenceCode' and `amount`= '$amount' and `agent_no`='$agent_no'";
    $result = DB::instance()->executeSQL($sql);

    $count= mysqli_num_rows($result);

    if($count==1){

        $sql="UPDATE `cash` SET `status`=1 WHERE `reference_code`='$referenceCode'";

        $result = DB::instance()->executeSQL($sql);

        if ($result) {
            $response = new Response();
            $response->status = Response::STATUS_SUCCESS;
            $response->message = "status updated";
            $response->success = true;
            echo json_encode($response);
            exit();

        }else{
            $response = new Response();
            $response->status = Response::STATUS_SUCCESS;
            $response->message = "Sorry, there was an error updating the status.";
            $response->success = false;
            echo json_encode($response);
            exit();
        }

    }else{
        $response = new Response();
        $response->status = Response::STATUS_SUCCESS;
        $response->message = "Sorry, no details available for the details transaction.";
        $response->success = false;
        echo json_encode($response);
        exit();
    }


}
