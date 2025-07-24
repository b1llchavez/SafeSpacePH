
    <?php
    
    


    include("../connection.php");



    if($_POST){

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

            if($result->num_rows==1){
                $id2=$result->fetch_assoc()["cid"];
            }else{
                $id2=$id;
            }
            

            if($id2!=$id){
                $error='1';



                    
            }else{


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

        $error='3';
    }
    

    header("location: settings.php?action=edit&error=".$error."&id=".$id);
    ?>
    
   

</body>
</html>