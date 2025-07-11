
    <?php
    
    

    //import database
    include("../connection.php");



    if($_POST){
        //print_r($_POST);
        $result= $database->query("select * from webuser");
        $name=$_POST['name'];
        $lawyerbarid=$_POST['lawyerbarid'];
        $oldemail=$_POST["oldemail"];
        $address=$_POST['address'];
        $email=$_POST['email'];
        $tele=$_POST['Tele'];
        $password=$_POST['password'];
        $cpassword=$_POST['cpassword'];
        $id=$_POST['id00'];
        
        if ($password==$cpassword){
            $error='3';
            $aab="select client.cid from client inner join webuser on client.cemail=webuser.email where webuser.email='$email';";
            $result= $database->query($aab);
            //$resultqq= $database->query("select * from lawyer where lawyerid='$id';");
            if($result->num_rows==1){
                $id2=$result->fetch_assoc()["cid"];
            }else{
                $id2=$id;
            }
            

            if($id2!=$id){
                $error='1';
                //$resultqq1= $database->query("select * from lawyer where lawyeremail='$email';");
                //$did= $resultqq1->fetch_assoc()["lawyerid"];
                //if($resultqq1->num_rows==1){
                    
            }else{

                //$sql1="insert into lawyer(lawyeremail,lawyername,docpassword,lawyerbarid,lawyertel,specialties) values('$email','$name','$password','$lawyerbarid','$tele',$spec);";
                $sql1="update client set cemail='$email',cname='$name',cpassword='$password',lawyerbarid='$lawyerbarid',ptel='$tele',paddress='$address' where cid=$id ;";
                $database->query($sql1);
                echo $sql1;
                $sql1="update webuser set email='$email' where email='$oldemail' ;";
                $database->query($sql1);
                echo $sql1;
                
                $error= '4';
                
            }
            
        }else{
            $error='2';
        }
    
    
        
        
    }else{
        //header('location: signup.php');
        $error='3';
    }
    

    header("location: settings.php?action=edit&error=".$error."&id=".$id);
    ?>
    
   

</body>
</html>