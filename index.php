<!DOCTYPE html>
<html>
<head>
<title>Assignment 7</title>
<meta http-quiv="content-type" content="text/html; charset=UTF-8" />
<style>
    #login td{
        border: 1px solid black;
        vertical-align: top;
    }
    #search td{
        border: 1px solid black;
        border-collapse: collapse;
        width: 300px;
        vertical-align: top;
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

    .top{
      width: 60%;
      margin: 0 auto;
    }
    .innerTop{
      width: 30%;
      margin: 0 auto;
    }

    .button {
      background-color: #4CAF50;
      border: none;
      color: white;
      padding: 15px 32px;
      text-align: center;
      text-decoration: none;
      display: inline-block;
      font-size: 16px;
      cursor: pointer;
        margin: 0 auto;
    }
    .button1 {
        font-size: 12px;
        padding: 10px 20px;
        
    }
</style>
</head>
<body>
<table id="login">
<tr><td style="text-align:center;"><b>Admin Login<b></td></tr>
<tr>
<td>
<label>Username: </label>
<input type="text" ><br>
<label>Password:&nbsp</label>
<input type="text" ><br>
<form action="admin.php">
    <input type="submit" value="Login" class="button button1">
</form>

</td>
</tr>
</table>
<?php
    error_reporting( error_reporting() & ~E_NOTICE );
    
    function test_input($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }
    
    $listItem = array();
    
    include 'db_connection.php';
    
    $conn = mysqli_connect("localhost", "root", "","library") or die("Connect failed: %s\n". $conn -> error);
        
    $QueryResult = mysqli_query($conn, "SELECT * FROM Cars");

    while (($Row = mysqli_fetch_row($QueryResult)) != false){
        array_push($listItem,array("ID"=>$Row[0],"City"=>$Row[1],"Make"=>$Row[2],"Year"=>$Row[3],"Transmission"=>$Row[4], "State"=>$Row[5]));
        }
    
    $makeCount= $transCount= $stateCount= array();
    
    function counting($array,$where=""){
        $GLOBALS['stateCount']= $GLOBALS['transCount']= $GLOBALS['makeCount']= array();
        foreach($array as $car => $attribute){
            $QueryResult = mysqli_query($GLOBALS['conn'], "SELECT Make, Count(*) FROM Cars".$where." GROUP BY Make");
            while (($Row = mysqli_fetch_row($QueryResult)) != false){
                $GLOBALS['makeCount'][$Row[0]] = $Row[1];
                }
            $QueryResult = mysqli_query($GLOBALS['conn'], "SELECT STATE, Count(*) FROM Cars".$where." GROUP BY STATE");
            while (($Row = mysqli_fetch_row($QueryResult)) != false){
                $GLOBALS['stateCount'][$Row[0]] = $Row[1];
                }
            $QueryResult = mysqli_query($GLOBALS['conn'], "SELECT Transmission, Count(*) FROM Cars".$where." GROUP BY Transmission");
            while (($Row = mysqli_fetch_row($QueryResult)) != false){
                $GLOBALS['transCount'][$Row[0]] = $Row[1];
                }
        }
    }
    counting($listItem);

//create variables for form values
    $state= $transmission= $make= array();
        
    if (isset($_POST['state']))
    {
        $state = $_POST['state'];
    }

    if (isset($_POST['make']))
    {
        $make = $_POST['make'];
    }
    
    if (isset($_POST['transmission']))
    {
        $transmission = $_POST['transmission'];
    }

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!empty($_POST["state"])) {
        $state = $_POST["state"];
        }
    if (!empty($_POST["transmission"])) {
        $transmission = $_POST["transmission"];
    }
    if (!empty($_POST["make"])) {
        $make = $_POST["make"];
    }
    
    $query = "SELECT * FROM Cars";
    
    if(isset($_POST['reset'])) {
        $state= $transmission= $make= array();
    }

    if (!empty($state) || !empty($transmission) || !empty($make)){
        $where .= " WHERE";
        
        if(!empty($_POST["state"])){
            $where .= " STATE in (\"".implode("\",\"",$state)."\")";
            if(!empty($_POST["transmission"]) || !empty($_POST["make"])){
                $where .= " AND";
            }
        }
        
        if(!empty($transmission)){
            $where .= " Transmission in (\"".implode("\",\"",$transmission)."\")";
            if( !empty($make)){
                $where .= " AND";
            }
        }
        
        if(!empty($_POST["make"])){
            $where .= " Make in (\"".implode("\",\"",$make)."\")";
        }
    }
    $QueryResult = mysqli_query($conn, $query.$where);
    $listItem = array();
    while (($Row = mysqli_fetch_row($QueryResult)) != false){
        array_push($listItem,array("ID"=>$Row[0],"City"=>$Row[1],"Make"=>$Row[2],"Year"=>$Row[3],"Transmission"=>$Row[4], "State"=>$Row[5]));
        }
    counting($listItem,$where);
}

?>
<h1 style="text-align:center">Thinh's Car Dealership</h1>
<form name="assignment3" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post">
<div class="top">

<table id="search">
    <tr>
    <td>
        <label for="state"><b>State:</b></label><br>
        <?php
            $QueryResult = mysqli_query($conn, "SELECT DISTINCT STATE FROM Cars");
            $stateArray = array();
            while (($Row = mysqli_fetch_row($QueryResult)) != false){
                array_push($stateArray, $Row[0]);
                }
            foreach ($stateArray as $x)
            {
                if (in_array($x,$state)){
                    echo "<input type='checkbox' name='state[]' checked = 'checked' value='".$x."'><label for='state'>".$x."</label> (".$stateCount[$x].")<br>";
                }
                else{
                    echo"<input type='checkbox' name='state[]' value='".$x."'><label for='state'>".$x."</label> (".$stateCount[$x].")<br>";
                }
            }
        ?>
    </td>

    <td>
        <label for="make"><b>Brand:</b></label><br>
        <?php
            $QueryResult = mysqli_query($conn, "SELECT DISTINCT Make FROM Cars");
            $makeArray = array();
            while (($Row = mysqli_fetch_row($QueryResult)) != false){
                array_push($makeArray, $Row[0]);
                }

            foreach($makeArray as $x){
                if (in_array($x,$make)){
                    echo "<input type='checkbox' name='make[]'checked = 'checked' value='".$x."'><label for='make'>".$x."</label> (".$makeCount[$x].")<br>";
                }else{
                    echo "<input type='checkbox' name='make[]' value='".$x."'><label for='make'>".$x."</label> (".$makeCount[$x].")<br>";
                }
            }
        ?>
    </td>

    <td>
        <label for="tranmission"><b>Transmission: </b></label><br>
        <?php
            $QueryResult = mysqli_query($conn, "SELECT DISTINCT Transmission FROM Cars");
            $transArray = array();
            while (($Row = mysqli_fetch_row($QueryResult)) != false){
                array_push($transArray, $Row[0]);
                }
            foreach($transArray as $x){
                if (in_array($x,$transmission)){
                    echo "<input type='checkbox' name='transmission[]'checked = 'checked' value='".$x."'><label for='transmission'>".$x."</label> (".$transCount[$x].")<br>";
                }else{
                    echo "<input type='checkbox' name='transmission[]' value='".$x."'><label for='transmission'>".$x."</label> (".$transCount[$x].")<br>";
                }
            }
        ?>
    </td>
    </tr>
    </table>
    <div class="innerTop">
    <input type="submit" value="Reset" name ="reset" class="button">
    <input type="submit" value="Submit" class="button"><br><br>
    </div>
</div>
</form>

<?php
    echo '<div class="bottom">';
        echo " <table id='results'><caption><h1>Car Available for sale<h1></caption><tr><th>ID</th><th>City</th><th>Make</th><th>Year</th><th>Transmission</th><th>State</th></tr>";
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
    CloseCon($conn);

?>
</div>
</body>
</html>
