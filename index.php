<?php
include 'reader.php';

//This code test require to use any php library

//Todo: Connect to DB with mysqli
$GLOBALS['con'] = mysqli_connect("database", "root", "", "test");

// Check connection
if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
    exit();
} else {
    // Create tables if not exist
    if (empty($result)) {
        $query = "CREATE TABLE IF NOT EXISTS tbl_lessons (
                  id int(11) AUTO_INCREMENT,
                  uuid varchar(255) NOT NULL,
                  name varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
                  lesson_day float(11) NOT NULL,
                  required_year int(11) NOT NULL,
                  PRIMARY KEY  (ID)
                  ) CHARACTER SET utf8 COLLATE utf8_unicode_ci;";
        
        $result = mysqli_query($GLOBALS['con'], $query);
        
        $query = "CREATE TABLE IF NOT EXISTS tbl_roles (
                  id int(11) AUTO_INCREMENT,
                  name varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
                  PRIMARY KEY  (ID)
                  ) CHARACTER SET utf8 COLLATE utf8_unicode_ci;";
        
        $result = mysqli_query($GLOBALS['con'], $query);
        
        $query = "CREATE TABLE IF NOT EXISTS roles_has_lessons (
                  id int(11) AUTO_INCREMENT,
                  lesson_id int(11) NOT NULL,
                  role_id int(11) NOT NULL,
                  PRIMARY KEY  (ID)
                  );";
        
        $result = mysqli_query($GLOBALS['con'], $query);
    }
}

/*Todo: Read Excel with any php library
  -> Read all courses/ roles in the excel
  -> In the excel (B5), there is a "X", that's mean need to Link up the courses and roles
*/
if (count($_FILES) > 0) {
    $uploadDir = './upload';
    $tmp_name  = $_FILES['file']["tmp_name"];
    $name      = basename($_FILES['file']["name"]);
    $result    = move_uploaded_file($tmp_name, "$uploadDir/$name");
    
    readAndStoreExcel($uploadDir . '/' . $name);
}

// Read excel
//Todo: Save to Database
function readAndStoreExcel ($filename)
{
    if ($xlsx = SimpleXLSX::parse($filename)) {
        // The first 4 row insert to db is exist
        $tmp = [];
        foreach ($xlsx->rows() as $row => $elt) {
            if ($row < 4) {
                foreach ($elt as $index => $item) {
                    if ($item !== "") {
                        $tmp[$index][] = $item;
                    }
                }
            }
        }
        
        foreach ($tmp as $row) {
            $query = "SELECT uuid from tbl_lessons where uuid = '$row[0]'";
            
            $result = mysqli_query($GLOBALS['con'], $query);
            
            if ($result->num_rows === 0) {
                $query = "INSERT INTO tbl_lessons (uuid, name, lesson_day, required_year) VALUES ('$row[0]', '$row[1]', $row[2], $row[3]);";
                
                mysqli_query($GLOBALS['con'], $query);
            }
        }
        
        $uuids = $xlsx->rows()[0];
        foreach ($xlsx->rows() as $row => $elt) {
            if ($row > 3) {
                $role_id = null;
                foreach ($elt as $index => $item) {
                    if ($index === 0) {
                        $query = "SELECT name from tbl_roles where name = '$item'";
                        
                        $result = mysqli_query($GLOBALS['con'], $query);
                        
                        if ($result->num_rows === 0) {
                            $query = "INSERT INTO tbl_roles (name) VALUES ('$item');";
                            
                            mysqli_query($GLOBALS['con'], $query);
                        }
                        
                        $query = "SELECT id from tbl_roles where name = '$item'";
                        
                        $result = mysqli_query($GLOBALS['con'], $query);
                        
                        $result = mysqli_fetch_assoc($result);
                        
                        $role_id = $result['id'];
                    } else {
                        if (strtolower($item) === 'x') {
                            $name = $uuids[$index];
                            
                            $query = "SELECT id from tbl_lessons where uuid = '$name'";
                            
                            $result = mysqli_query($GLOBALS['con'], $query);
                            
                            if ($result->num_rows > 0) {
                                $result = mysqli_fetch_assoc($result);
                                
                                $lesson_id = $result['id'];
                                
                                $query = "SELECT id from roles_has_lessons where lesson_id = $lesson_id and role_id = $role_id";
                                
                                $result = mysqli_query($GLOBALS['con'], $query);
                                
                                if ($result->num_rows === 0) {
                                    $query = "INSERT INTO roles_has_lessons (lesson_id, role_id) VALUES ($lesson_id, $role_id);";
                                    
                                    mysqli_query($GLOBALS['con'], $query);
                                }
                            }
                        }
                    }
                }
            }
        }
    } else {
        echo SimpleXLSX::parseError();
    }
}

//Todo: Retrieve Back the data from and show in the table
function getReport ()
{
    $result = [];
    
    $query = "SELECT * from tbl_lessons";
    
    $tmp = mysqli_query($GLOBALS['con'], $query);
    
    $tmp = mysqli_fetch_all($tmp);
    
    $result['lessons'] = $tmp;
    
    $query = "SELECT * from tbl_roles";
    
    $tmp = mysqli_query($GLOBALS['con'], $query);
    
    $tmp = mysqli_fetch_all($tmp);
    
    $result['roles'] = $tmp;
    
    $query = "SELECT * from roles_has_lessons";
    
    $tmp = mysqli_query($GLOBALS['con'], $query);
    
    $tmp = mysqli_fetch_all($tmp);
    
    $result['rolesHasLessons'] = $tmp;
    
    return $result;
}

function test ()
{
    echo '123';
}

$result = getReport();
?>

<html>
<head>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css"
	      integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
	<script src="https://code.jquery.com/jquery-3.5.1.min.js"
	        integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"
	        integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q"
	        crossorigin="anonymous"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"
	        integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl"
	        crossorigin="anonymous"></script>
</head>
<body>
<div class="container">
	<form method="POST" enctype="multipart/form-data">
		<div class="form-group">
			<label for="file">Upload Excel</label>
			<input type="file" class="form-control-file" name="file" id="file">
		</div>
		<button type="submit" class="btn btn-primary">Upload</button>
	</form>
	<div class="mt-5">
      <?php if (count($result['lessons']) > 0 && count($result['roles']) > 0) { ?>
				<table class="table">
					<thead>
					<tr>
						<th></th>
              <?php foreach ($result['lessons'] as $index => $item) { ?>
								<th><?php echo $item[1] ?></th>
              <?php } ?>
					</tr>
					<tr>
						<th></th>
              <?php foreach ($result['lessons'] as $index => $item) { ?>
								<th><?php echo $item[2] ?></th>
              <?php } ?>
					</tr>
					</thead>
					<tbody>
					<tr>
						<td>Duration (Days)</td>
              <?php foreach ($result['lessons'] as $index => $item) { ?>
								<td><?php echo $item[3] ?></td>
              <?php } ?>
					</tr>
					<tr>
						<td>Validity (Year)</td>
              <?php foreach ($result['lessons'] as $index => $item) { ?>
								<td><?php echo $item[4] ?></td>
              <?php } ?>
					</tr>
          <?php foreach ($result['roles'] as $index => $role) { ?>
						<tr>
							<td><?php echo $role[1] ?></td>
                <?php foreach ($result['lessons'] as $index => $item) { ?>
									<td data-role-id="<?php echo $role[0] ?>" data-lesson-id="<?php echo $item[0] ?>"
									    class="cursor updateStatus">
                      <?php
                      foreach ($result['rolesHasLessons'] as $relation) {
                          if ($relation[1] === $item[0] && $relation[2] === $role[0]) {
                              echo 'X';
                          }
                      }
                      ?>
									</td>
                <?php } ?>
						</tr>
          <?php } ?>
					</tbody>
				</table>
      <?php } ?>
	</div>
</div>

<style>
	.cursor {
		cursor: pointer;
	}
</style>

<script>
	$(document).ready(function () {
		$('.updateStatus').click(function () {
			$.ajax({
				url: '/ajax.php',
				data: {
					role_id: $(this).data('role-id'),
					lesson_id: $(this).data('lesson-id')
				},
				dataType: 'json',
				type: "POST",
				success: function (data, status, xhr) {
					window.location.reload();
				}
			})
		})
	});
</script>
</body>
</html>
