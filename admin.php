<!DOCTYPE html>
<html>
<head>
<title>Assignment 7</title>
    <meta http-quiv="content-type" content="text/html; charset=UTF-8" />
<style>
    table, th, td {
        border: 1px solid black;
        border-collapse: collapse;
    }
    td{
        width: 100px
    }

    * {
      box-sizing: border-box;
    }

    header {
        background-color: #666;
        padding: 30px;
        text-align: center;
        font-size: 35px;
        color: white;
    }

    action {
        font-size: 20px;
        float: left;
        width: 30%;
        height: 350px;
        background: #ccc;
        padding-left: 20px;
    }

    content {
        font-size: 15px;
        float: left;
        padding: 20px;
        width: 70%;
        background-color: #f1f1f1;
        height: 350px;
    }
    .button {
        background-color: #4CAF50; /* Green */
        border: none;
        color: white;
        text-align: center;
        text-decoration: none;
        display: inline-block;
        font-size: 15px;
        padding: 14px 10px;
        width: 120px;
    }
    #results {
        border-collapse: collapse;
    }

    #results td, #results th {
        width: 120px;
        border: 1px solid #ddd;
        padding: 8px;
    }

    #results tr:nth-child(even){background-color: #f2f2f2;}

    #results th {
        padding-top: 12px;
        padding-bottom: 12px;
        text-align: left;
        background-color: #4CAF50;
        color: white;
    }
    .bottom{
      margin: auto;
      width: 60%;
      border: 3px solid #73AD21;
      padding: 10px;
    }
}
</style>

</head>
<body>

<?php
    //trim and make data neat
    function test_input($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    $record = fopen("record.txt","a") or die("File not found");
    $conn = mysqli_connect("localhost", "root", "","library") or die("Connect failed: %s\n". $conn -> error);
    date_default_timezone_set('Australia/Melbourne');
    $date = date('m/d/Y h:i:s a', time());
    
    $state_array = array("NSW" => array("Wollongong","Sydney","Newcastle"),
                   "QLD" => array("Brisbane","Gold Coast"),
                   "WA" => array("Perth","Mandurah"),
                   "VIC" => array("Melbourne","Geelong"),
                   "NT" => array("Darwin","Alice Springs"),
                   "ACT" => array("Canberra"),
                    "TAS" => array("Launceston","Hobart"),
                    "SA" => array("Adelaide"));
    
    $make_array = array("Ford","Suzuki","Honda","Mazda","Toyota","Audi");

    include 'db_connection.php';
    //create variables for form values and error values
    $city= $make= $year= $transmission= $state= $id= $method= $message= "";
    $cityErr= $yearErr= $transErr= $idErr= "";
    

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['Submit']) || isset($_POST["UpdateItem"])) {
        $state = $_POST["state"];
        $make = $_POST["make"];

        //validate city name
        if (empty($_POST["city"])) {
            $cityErr = "City name is required ";
        }  else {
            $city = $_POST["city"];
            $city = test_input($city);
            if (!preg_match("/^[a-zA-Z ]*$/",$city)) {
                $cityErr = "Only letters and white space allowed ";
            }
        }
        //validate year
        if (empty($_POST["year"])) {
            $yearErr = "Make year is required ";
        }
        else {
            $year = $_POST["year"];
            $year = test_input($year);
            if (!preg_match("/^\d+$/",$year)) {
                $yearErr = "Only numbers allowed ";
            }
            elseif ($year>2020 || $year<2000){
                $yearErr = "Year has to be between 2000 - 2020 ";
            }
        }
        
        //validate transmission
        if (empty($_POST["transmission"])) {
            $transErr = "Transmission type is required ";
        } else {
            $transmission = $_POST["transmission"];
        }
        
        //Add item
        if(!empty($city)&&!empty($year)&&!empty($make)&&!empty($transmission)&&empty($cityErr)&&empty($yearErr)&&empty($transErr)&&!isset($_POST["id"])){
            $QueryResult = mysqli_query($conn, "SELECT MAX(ID) FROM Cars");
            $count = mysqli_fetch_row($QueryResult);
            ++$count[0];
            mysqli_query($conn, "INSERT INTO Cars VALUES (".$count[0].",'".$city."','".$make."','".$year."','".$transmission."','".$state."');");
            $message = "New item has been added!";
            fwrite($record,$date.": SUCCEEDED INSERT INTO Cars VALUES (".$count[0].",'".$city."','".$make."','".$year."','".$transmission."','".$state."');\n");
        }
        else{
            if(!isset($_POST['id'])){
                fwrite($record,$date.": FAILED INSERT: ".$cityErr.$yearErr.$transErr."\n");
            }
        }
    }
    
    //reset button
    if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["Reset"])) {
        $city= $make= $year= $transmission= $state= $id= $method= $message= "";
        $cityErr= $yearErr= $transErr= $idErr= "";
    }
    
    //reset button for update/delete form
    if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["ResetUpdate"]) || isset($_POST['DeleteItem'])) {
        $method = "Update/Delete";
        $state= $city= $year= $transmission= $make= $id= "";
        $cityErr= $yearErr= $transErr="";
    }

    //change to update/delete mode
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['UpdateDelete'])){
        $method = $_POST['UpdateDelete'];
    }
    
    //cheking ID and populate data
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['select']) || isset($_POST['DeleteItem']) || isset($_POST['UpdateItem'])){
        $method = "Update/Delete";
        $conn = mysqli_connect("localhost", "root", "","library") or die("Connect failed: %s\n". $conn -> error);
        $QueryResult = mysqli_query($conn, "SELECT MAX(ID) FROM Cars");
        $count = mysqli_fetch_row($QueryResult);
        if (empty($_POST["id"])) {
            $idErr = "ID is required ";
        }
        else {
            $id = $_POST["id"];
            $id = test_input($id);
            if (!preg_match("/^\d+$/",$id)) {
                $idErr = "Only numbers allowed for ID ";
            }
            elseif ($id<0 || $id>$count[0]){
                $idErr = "ID has to be between 0 - ".$count[0];

            }
            else{
                $QueryResult = mysqli_query($conn, "SELECT * FROM Cars WHERE ID =".$id);
                $Result = mysqli_fetch_row($QueryResult);
                $city= $Result[1];
                $make= $Result[2];
                $year= $Result[3];
                $transmission= $Result[4];
                $state= $Result[5];
            }
        }
    }
    
    //Delete item
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['DeleteItem'])){
        $method = "Update/Delete";
        if($idErr==""){
            $QueryResult = mysqli_query($conn, "DELETE FROM Cars WHERE ID =".$_POST["id"].";");
            $record = fopen("record.txt","a") or die("File not found");
            fwrite($record,$date.": SUCCEEDED DELETE FROM Cars WHERE ID =".$_POST["id"].";\n");
            $message = "Item has been deleted!";
        }
        else{
            fwrite($record,$date.": FAILED DELETE: ".$idErr."\n");
        }
    }

    //Update item
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['UpdateItem'])){
        $method = "Update/Delete";
        if(isset($_POST["id"])){
            $id = $_POST["id"];
        }
        if(!empty($city)&&!empty($id)&&!empty($year)&&!empty($make)&&!empty($transmission)&&empty($cityErr)&&empty($yearErr)&&empty($transErr)&&empty($idErr)){
            $QueryResult = mysqli_query($conn, "UPDATE Cars SET City ='".$_POST["city"]."', Make ='".$_POST["make"]."',Year ='".$_POST["year"]."',Transmission='".$_POST["transmission"]."',STATE='".$_POST["state"]."' WHERE ID=".$_POST["id"].";");
            $record = fopen("record.txt","a") or die("File not found");
            fwrite($record,$date.": SUCCEEDED UPDATE Cars SET City ='".$_POST["city"]."', Make ='".$_POST["make"]."',Year ='".$_POST["year"]."',Transmission='".$_POST["transmission"]."',STATE='".$_POST["state"]."' WHERE ID=".$_POST["id"].";\n");
            $message = "Item has been updated!";
        }
        else{
            fwrite($record,$date.": FAILED UPDATE: ".$idErr.$cityErr.$yearErr.$transErr."\n");
        }
    }
?>

<header>ADMIN</header>
<section>
    <form name="assignment3" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post">
    <action>
    
    <h1>Action:</h1>
    <input class="button" type="submit" value="Add" name="Add"><br><br>
    <input class="button" type="submit" value="Update/Delete" name="UpdateDelete"><br><br>
    <a href = "record.txt" download><input type="button" value="Download Record"><br></a>
    </action>
    <content>
    <?php
        if ($method == "Update/Delete"){
            echo '<label>Choose ID: </label>';
            echo '<input type="text" id="id" name="id" value= "'.$id.'">';
            echo '<input type="submit" value="Select" name="select">';
            echo '<span style="color:red">'.$idErr.'</span><br><br>';
        }
    ?>

    <label for="city">City:</label>
    <input type="text" id="city" name="city" value= "<?php echo $city;?>" >
    <span style="color:red"><?php echo $cityErr;?></span>
    <br><br>
    
    <label for="state">Choose State:</label>
    <select id="state" name="state" size="1">
    <?php
        foreach ($state_array as $x => $y)
        {
            if ($x == $state){
                echo "<option selected = 'selected' value=".$x.">".$x."</option>";
            }
            else{
                echo "<option value=".$x.">".$x."</option>";
            }
        }
    ?>
    </select><br><br>

    <label for="make">Choose Brand:</label>
    <select id="make" name="make" size="1">
    <?php
        foreach ($make_array as $x)
        {
            if ($x == $make){
                echo "<option selected = 'selected' value=".$x.">".$x."</option>";
            }
            else{
                echo "<option value=".$x.">".$x."</option>";
            }
        }
    ?>
    </select><br><br>

    <label for="year">Year:</label>
    <input type="text" id="year" name="year" value="<?php echo $year;?>">
    <span style="color:red"><?php echo $yearErr;?></span><br><br>

    <label>Transmission: </label><br>
    <input type="radio" id="automatic" name="transmission" <?php if (isset($transmission) && $transmission=="Automatic") echo "checked";?> value="Automatic">
    <label for="automatic">Automatic</label><br>
    <input type="radio" id="manual" name="transmission" <?php if (isset($transmission) && $transmission=="5-Gear Manual") echo "checked";?> value="5-Gear Manual">
    <label for="manual">5-Gear Manual</label><br>
    <input type="radio" id="manual" name="transmission" <?php if (isset($transmission) && $transmission=="6-Gear Manual") echo "checked";?> value="6-Gear Manual">
    <label for="manual">6-Gear Manual</label><br>
    <span style="color:red"><?php echo $transErr;?></span><br>

    <?php
        if ($method == "Update/Delete"){
            echo '<input type="submit" value="Update" name="UpdateItem">';
            echo '<input type="submit" value="Delete" name="DeleteItem">';
            echo '<input type="submit" value="Reset" name="ResetUpdate"><br><br>';
        }else {
            echo '<input type="submit" value="Reset" name="Reset">';
            echo '<input type="submit" value="Submit" name="Submit"><br><br>';
        }
    ?>
    <span style="color:red"><?php echo $message;?></span><br>

    </form>
    </content>
</section>

<?php
    $listItem = array();

    $QueryResult = mysqli_query($conn, "SELECT * FROM Cars");

    while (($Row = mysqli_fetch_row($QueryResult)) != false){
        array_push($listItem,array("ID"=>$Row[0],"City"=>$Row[1],"Make"=>$Row[2],"Year"=>$Row[3],"Transmission"=>$Row[4], "State"=>$Row[5]));
        }

    echo '<div class="bottom">';
        echo " <table id='results'><caption><h1>Car Database<h1></caption><tr><th>ID</th><th>City</th><th>Make</th><th>Year</th><th>Transmission</th><th>State</th></tr>";
            foreach ($listItem as $car=>$attribute){
                echo "<tr>";
                echo "<td>",$attribute["ID"],"</td>";
                echo "<td>",$attribute["City"],"</td>";
                echo "<td>",$attribute["Make"],"</td>";
                echo "<td>",$attribute["Year"],"</td>";
                echo "<td>",$attribute["Transmission"],"</td>";
                echo "<td>",$attribute["State"],"</td>";
                echo "</tr>";
            }
    echo '</div>';
    fclose($record);
    CloseCon($conn);
?>
</body>
</html>
