<!DOCTYPE html>
<html>
    <head>
        <title>Cinema database</title>
        <link rel="stylesheet" type="text/css" href="cinema.css">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=League+Gothic&display=swap" rel="stylesheet">
        <script type="text/javascript"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    </head>
    <body>
        <img src="assets/masks.png"> <!-- Source: https://www.flaticon.com/free-icon/comedy-and-tragedy_57517 -->
        <h1>CINEMA &nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp DATABASE</h1>
        <div id="block">
            <!-- Form to take value from input box and process request based on selected button -->
            <form method="post" action="#" onSubmit="return Validate(this)"> 
                <select name="queries", id="queries">
                    <option value="1">Add Movie</option>
                    <option value="2">Add Actor</option>
                    <option value="3">Delete Movie</option>
                    <option value="4">Delete Actor</option>
                    <option value="5">Search Movie</option>
                    <option value="6">Search Actor</option>
                </select>
                <input type="text" id="value" name="value" placeholder="ENTER VALUE" autocomplete="off">
                <input type="submit" value="Execute">
            </form>
        </div>
        <!-- JS validation -->
        <script>
            function Validate(form){
                var query = document.getElementById("queries").value;
                
                /* empty input */
                var valid = true;
                for(var i = 0; i < form.length; i++){
                    if(form.elements[i].value.trim() == ""){
                        valid = false;
                    }
                }
                if(!valid){
                    alert("Error: Cannot enter an empty input");             
                    return false;
                }
                else{
                    /* valid movie */
                    if(query == "1"){
                        var inp = $('#value').val().split(", ").length;
                        if(inp != 5){
                            alert("Error: Must include: title, year, actor, genre, price");             
                            return false;
                        }
                        else{
                            return true;
                        }
                    }

                    /* valid actor */
                    if(query == 2){
                        var regex = /^[a-zA-Z]+$/;
                        if($('#value').val().match(regex)){
                            return true;
                        }
                        else{
                            alert("Error: Must include only letters");             
                            return false;
                        }
                    }
                }
            }
        </script>
    </body>
</html>

<?php 

# passed from ajax values
$query = $_POST['queries'];
$value = $_POST['value'];

# sql setup
$dbHost = "mysql.cs.nott.ac.uk";
$dbName = "psysr3_comp10040";
$dbUser = "psysr3_comp10040";
$dbPass = "test123";

# connection validation
$conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
if($conn->connect_errno){
   die("Failed to connect to database");
}

# function based on condition
if($query == "1"){
   AddMovie($value, $conn);
}
elseif($query == "2"){
   AddActor($value, $conn);
}
elseif($query == '3'){
   DeleteMovie($value, $conn);
}
elseif($query == '4'){
   DeleteActor($value, $conn);
}
elseif($query == "5"){
   SearchMovie($value, $conn);
}
elseif($query == '6'){
   SearchActor($value, $conn);
}


# ADD MOVIE -> Add movie to movie
function AddMovie($value, $conn){
    # error -> empty input
    if($value == null){
        echo "<p id='er_msg'>Enter a value</p>";
        exit();
    }

   # remove white space
   $value = trim($value, " ");
   
   $values = explode(', ', $value);
   
   # error -> invalid inputs
   if(count($values) != 5){
      echo "<p id='er_msg'>Must include: title, year, actor, genre, price</p>";
      exit();
   }

   # convert values to int
   $price = floatval($values[4]);
   $year = intval($values[1]);

   # error -> invalid price
   if($price < 0 || $price > 30){
      echo "<p id='er_msg'>Must include an appropriate price</p>";
      exit();
   }
   # error -> invalid year
   else if($year < 1888){
      echo "<p id='er_msg'>Must include an appropriate year</p>";
      exit();
   }

   # get actor id
   $sql = "SELECT actorID FROM Actor WHERE actorName = '$values[2]'";
   $stmt = $conn->prepare($sql);
   $stmt->execute();
   $stmt->bind_result($actorID);
   $stmt->fetch();
   $stmt->close();

   # error -> actor doesn't exist
   if($actorID == null){
      echo "<p id='er_msg'>Actor does not exist in the database</p>";
      exit();
   }

   $sql = "INSERT INTO Movie(actorID, actorName, moviePrice, movieTitle, movieYear, movieGenre) VALUES ($actorID, '$values[2]', $price, '$values[0]', $year, '$values[3]')";
   $stmt = $conn->prepare($sql);
   $stmt->execute();
   echo "<p id='msg'>Query successful!</p>";
   $stmt->close();
}


# ADD ACTOR -> Add actor/actress to actor
function AddActor($value, $conn){
   $Regex = "/^[a-zA-Z'-]+$/";

   # error -> invalid input with numbers
    if(!preg_match($Regex, $value) || strlen($value) > 60){
      echo "<p id='er_msg'>Name must be less than 60 characters and consist of only letters</p>";
      exit();
   }

   # remove white space
   $value = trim($value, " ");

   $sql = "INSERT INTO Actor(actorName) VALUES ('$value')";
   $stmt = $conn->prepare($sql);
   $stmt->execute();
   echo "<p id='msg'>Query successful!</p>";
   $stmt->close();
}

# DELETE MOVIE -> Delete movie from movie
function DeleteMovie($value, $conn){
   # remove white space
   $value = trim($value, " ");

   $sql = "DELETE FROM Movie WHERE movieTitle = '$value'";
   $stmt = $conn->prepare($sql);
   $stmt->execute();
   echo "<p id='msg'>Query successful!</p>";
   $stmt->close();
}  

# DELETE ACTOR -> Delete actor/actress from movie
function DeleteActor($value, $conn){
   # remove white space
   $value = trim($value, " ");

   $sql = "SELECT movieTitle FROM Movie WHERE actorName = '$value'";
   $stmt = $conn->prepare($sql);
   $stmt->execute();
   $stmt->bind_result($title);
   $stmt->fetch();

   # error -> actor assigned to movie
   if($title != null){
      echo "<p id='er_msg'>Cannot remove actor as they exist within a movie</p>";
      exit();
   }
   $stmt->close();

   $sql = "DELETE FROM Actor WHERE actorName = '$value'";
   $stmt = $conn->prepare($sql);
   $stmt->execute();
   echo "<p id='msg'>Query successful!</p>";
   $stmt->close();
}  


# SEARCH MOVIE -> Find specific movie
function SearchMovie($value, $conn){
   # remove white space
   $value = trim($value, " ");

   $sql = "SELECT movieTitle, movieYear, actorName, movieGenre, moviePrice FROM Movie WHERE movieTitle = '$value'";
   $stmt = $conn->prepare($sql);
   $stmt->execute();
   $stmt->bind_result($title, $year, $actor, $genre, $price);
   $stmt->fetch();

   # error -> no movie found
   if($title == null){
      echo "<p id='er_msg'>Could not find movie</p>";
      exit();
   }
   else{
      echo "<table>
      <tr> 
         <th>Title</th> 
         <th>Year</th> 
         <th>Featured actor</th> 
         <th>Genre</th> 
         <th>Price</th> 
      </tr>
      <tr> 
         <td>$title</td> 
         <td>$year</td> 
         <td>$actor</td> 
         <td>$genre</td> 
         <td>$price</td> 
      </tr>
      </table>";
   }
   $stmt->close();
}

# SEARCH ACTOR -> Find specific actor/actress
function SearchActor($value, $conn){
   # remove white space
   $value = trim($value, " ");

   $sql = "SELECT actorName FROM Actor WHERE actorName = '$value'";
   $stmt = $conn->prepare($sql);
   $stmt->execute();
   $stmt->bind_result($actor);
   $stmt->fetch();

   # error -> no actor found
   if($actor == null){
      echo "<p id='er_msg'>Could not find actor/actress</p>";
      exit();
   }
   else{
      echo "<table>
      <tr> 
         <th>Actor</th> 
      </tr>
      <tr> 
         <td>$actor</td> 
      </tr>
      </table>";
   }
   $stmt->close();

}

?>





