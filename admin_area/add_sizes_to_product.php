<?php
// Database structure fix for sizes table
if(!isset($_SESSION['admin_email'])){
    echo "<script>window.open('login.php','_self')</script>";
    exit;
}

// Fix database structure
$fix_database = false;
if(isset($_GET['fix_db']) && $_GET['fix_db'] == '1') {
    $fix_database = true;
}

if($fix_database) {
    echo "<div class='alert alert-info'>Fixing database structure...</div>";
    
    // Check and fix sizes table structure
    $check_sizes_table = "SHOW TABLES LIKE 'sizes'";
    $sizes_exists = mysqli_query($con, $check_sizes_table);
    
    if(mysqli_num_rows($sizes_exists) == 0) {
        // Create sizes table
        $create_sizes = "CREATE TABLE `sizes` (
          `size_id` int(10) NOT NULL AUTO_INCREMENT,
          `size_name` varchar(50) NOT NULL,
          `size_type` enum('clothing','shoes_men','shoes_women','shoes_kids','custom') DEFAULT 'clothing',
          `size_order` int(3) DEFAULT 1,
          `size_desc` text,
          `size_status` enum('active','inactive') DEFAULT 'active',
          `created_date` timestamp DEFAULT CURRENT_TIMESTAMP,
          `updated_date` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          PRIMARY KEY (`size_id`),
          KEY `size_type` (`size_type`),
          KEY `size_order` (`size_order`),
          UNIQUE KEY `size_type_name` (`size_type`, `size_name`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        
        if(mysqli_query($con, $create_sizes)) {
            echo "<div class='alert alert-success'>Sizes table created successfully</div>";
        }
    } else {
        // Check and add missing columns
        $columns_to_add = array(
            'size_status' => "ALTER TABLE sizes ADD COLUMN size_status enum('active','inactive') DEFAULT 'active'",
            'created_date' => "ALTER TABLE sizes ADD COLUMN created_date timestamp DEFAULT CURRENT_TIMESTAMP",
            'updated_date' => "ALTER TABLE sizes ADD COLUMN updated_date timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP"
        );
        
        foreach($columns_to_add as $column => $query) {
            $check_column = "SHOW COLUMNS FROM sizes LIKE '$column'";
            $column_exists = mysqli_query($con, $check_column);
            
            if(mysqli_num_rows($column_exists) == 0) {
                if(mysqli_query($con, $query)) {
                    echo "<div class='alert alert-success'>Added column: $column</div>";
                } else {
                    echo "<div class='alert alert-danger'>Error adding column $column: " . mysqli_error($con) . "</div>";
                }
            }
        }
    }
    
    // Insert default sizes if none exist
    $count_sizes = "SELECT COUNT(*) as count FROM sizes";
    $run_count = mysqli_query($con, $count_sizes);
    $count_result = mysqli_fetch_array($run_count);
    
    if($count_result['count'] == 0) {
        $default_sizes = array(
            array('XS', 'clothing', 1, 'Extra Small'),
            array('S', 'clothing', 2, 'Small'),
            array('M', 'clothing', 3, 'Medium'),
            array('L', 'clothing', 4, 'Large'),
            array('XL', 'clothing', 5, 'Extra Large'),
            array('XXL', 'clothing', 6, 'Double Extra Large')
        );
        
        foreach($default_sizes as $size) {
            $insert_default = "INSERT INTO sizes (size_name, size_type, size_order, size_desc) VALUES ('{$size[0]}', '{$size[1]}', {$size[2]}, '{$size[3]}')";
            mysqli_query($con, $insert_default);
        }
        echo "<div class='alert alert-success'>Default sizes added</div>";
    }
    
    echo "<div class='alert alert-info'>Database structure fix completed. <a href='index.php?view_sizes'>View Sizes</a></div>";
}

?>

<div class="row"><!-- row Begin -->
    
    <div class="col-lg-12"><!-- col-lg-12 Begin -->
        
        <ol class="breadcrumb"><!-- breadcrumb Begin -->
            
            <li class="active"><!-- active Begin -->
                
                <i class="fa fa-dashboard"></i> Dashboard / Insert Sizes
                
            </li><!-- active Finish -->
            
        </ol><!-- breadcrumb Finish -->
        
    </div><!-- col-lg-12 Finish -->
    
</div><!-- row Finish -->


<div class="row"><!-- row Begin -->
    
    <div class="col-lg-12"><!-- col-lg-12 Begin -->
        
        <div class="panel panel-default"><!-- panel panel-default Begin -->
            
           <div class="panel-heading"><!-- panel-heading Begin -->
               
               <h3 class="panel-title"><!-- panel-title Begin -->
                   
                       <i class="fa fa-money fa-fw"></i> Insert Sizes
                   
               </h3><!-- panel-title Finish -->
               
           </div> <!-- panel-heading Finish -->
           
           <div class="panel-body"><!-- panel-body Begin -->
               
               <form method="post" class="form-horizontal" enctype="multipart/form-data"><!-- form-horizontal Begin -->
                   
                       <div class="form-group"><!-- form-group Begin -->
                           
                          <label class="col-md-3 control-label"> Size Name: </label> 
                          
                          <div class="col-md-6"><!-- col-md-6 Begin -->
                              
                              <input name="size_name" type="text" class="form-control" required>
                              
                          </div><!-- col-md-6 Finish -->
                           
                       </div><!-- form-group Finish -->

                       <div class="form-group"><!-- form-group Begin -->
                           
                          <label class="col-md-3 control-label"> Size Type: </label> 
                          
                          <div class="col-md-6"><!-- col-md-6 Begin -->
                              
                              <select name="size_type" class="form-control" required>
                                  <option value="clothing">Clothing</option>
                                  <option value="shoes_men">Shoes (Men)</option>
                                  <option value="shoes_women">Shoes (Women)</option>
                                  <option value="shoes_kids">Shoes (Kids)</option>
                                  <option value="custom">Custom</option>
                              </select>
                              
                          </div><!-- col-md-6 Finish -->
                           
                       </div><!-- form-group Finish -->

                       <div class="form-group"><!-- form-group Begin -->
                           
                          <label class="col-md-3 control-label"> Size Order: </label> 
                          
                          <div class="col-md-6"><!-- col-md-6 Begin -->
                              
                              <input name="size_order" type="number" class="form-control" required>
                              
                          </div><!-- col-md-6 Finish -->
                           
                       </div><!-- form-group Finish -->
                       
                       <div class="form-group"><!-- form-group Begin -->
                           
                          <label class="col-md-3 control-label"> Size Description: </label> 
                          
                          <div class="col-md-6"><!-- col-md-6 Begin -->
                              
                              <textarea name="size_desc" cols="19" rows="6" class="form-control"></textarea>
                              
                          </div><!-- col-md-6 Finish -->
                           
                       </div><!-- form-group Finish -->
                       
                       <div class="form-group"><!-- form-group Begin -->
                           
                          <label class="col-md-3 control-label"></label> 
                          
                          <div class="col-md-6"><!-- col-md-6 Begin -->
                              
                              <input name="submit" value="Insert Size" type="submit" class="btn btn-primary form-control">
                              
                          </div><!-- col-md-6 Finish -->
                           
                       </div><!-- form-group Finish -->
                   
               </form><!-- form-horizontal Finish -->
               
           </div><!-- panel-body Finish -->
            
        </div><!-- canel panel-default Finish -->
        
    </div><!-- col-lg-12 Finish -->
    
</div><!-- row Finish -->

<?php 

if(isset($_POST['submit'])){
    
    $size_name = $_POST['size_name'];
    $size_type = $_POST['size_type'];
    $size_order = $_POST['size_order'];
    $size_desc = $_POST['size_desc'];
    
    $insert_size = "insert into sizes (size_name,size_type,size_order,size_desc) values ('$size_name','$size_type','$size_order','$size_desc')";
    
    $run_size = mysqli_query($con,$insert_size);
    
    if($run_size){
        
        echo "<script>alert('New Size Has Been Inserted')</script>";
        
        echo "<script>window.open('index.php?view_sizes','_self')</script>";
        
    }
    
}

?>