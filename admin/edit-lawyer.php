
    <?php
    
    


    include("../connection.php");



    if($_POST){

        $result= $database->query("select * from webuser");
        $name=$_POST['name'];
        $lawyerbarid=$_POST['lawyerbarid'];
        $oldemail=$_POST["oldemail"];
        $spec=$_POST['spec'];
        $email=$_POST['email'];
        $tele=$_POST['Tele'];
        $password=$_POST['password'];
        $cpassword=$_POST['cpassword'];
        $id=$_POST['id00']; 
        
        if ($password==$cpassword){
            $error='3';
            $result= $database->query("select lawyer.lawyerid from lawyer inner join webuser on lawyer.lawyeremail=webuser.email where webuser.email='$email';");

            if($result->num_rows==1){
                $id2=$result->fetch_assoc()["lawyerid"];
            }else{
                $id2=$id;
            }
            
            echo $id2."jdfjdfdh";
            if($id2!=$id){
                $error='1';



                    
            }else{


                $sql1="update lawyer set lawyeremail='$email',lawyername='$name',lawyerpassword='$password',lawyerbarid='$lawyerbarid',lawyertel='$tele',specialties=$spec where lawyerid=$id ;";
                $database->query($sql1);
                
                $sql1="update webuser set email='$email' where email='$oldemail' ;";
                $database->query($sql1);


                $error= '4';
                
            }
            
        }else{
            $error='2';
        }
    
    
        
        
    }else{

        $error='3';
    }

    header("location: lawyers.php?action=edit&error=".$error."&id=".$id);
    ?>
    
   

</body>
</html>