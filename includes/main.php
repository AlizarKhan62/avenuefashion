</head>

<body>

  <header class="page-header">
    <!-- topline -->
    <div class="page-header__topline">
      <div class="container clearfix">

        <div class="currency">
          <a class="currency__change" href="customer/my_account.php?my_orders">
          <?php
          if(!isset($_SESSION['customer_email'])){
          echo "Welcome :Guest"; 
          }
          else
          { 
              echo "Welcome : " . $_SESSION['customer_email'] . "";
            }
?>
          </a>
        </div>

        <div class="basket">
          <a href="cart.php" class="btn btn--basket">
            <i class="icon-basket"></i>
            <?php items(); ?> items
          </a>
        </div>
        
        
        <ul class="login">

<li class="login__item">
<?php
if(!isset($_SESSION['customer_email'])){
  echo '<a href="customer_register.php" class="login__link">Register</a>';
} 
  else
  { 
      echo '<a href="customer/my_account.php?my_orders" class="login__link">My Account</a>';
  }   
?>  
</li>


<li class="login__item">
<?php
if(!isset($_SESSION['customer_email'])){
  echo '<a href="checkout.php" class="login__link">Sign In</a>';
} 
  else
  { 
      echo '<a href="./logout.php" class="login__link">Logout</a>';
  }   
?>  
  
</li>
</ul>
      
      </div>
    </div>
    <!-- bottomline -->
    <div class="page-header__bottomline">
      <div class="container clearfix">

        <div class="logo">
          <a class="logo__link" href="index.php">
            <img class="logo__img" src="images/logo.png" alt="Avenue fashion logotype" width="237" height="19">
          </a>
        </div>

        <!-- Mobile menu toggle button -->
        <button class="mobile-menu-toggle" type="button" onclick="toggleMobileMenu()">
          <span class="sr-only">Toggle navigation</span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
        </button>

        <nav class="main-nav">
          <ul class="categories" id="mobile-menu">`

            <!-- <li class="categories__item">
              <a class="categories__link" href="#">
                Mens
               
              </a>
              </li> -->

            <li class="categories__item">
              <a class="categories__link" href="index.php">
                Home
               
              </a>
            </li>
        
            <li class="categories__item">
              <a class="categories__link" href="shop.php">
                Shop
              </a>
            </li>

            <li class="categories__item">
              <a class="categories__link" href="about.php">
                About US
              </a>
            </li>

            <li class="categories__item">
              <a class="categories__link" href="virtual_tryon.php">
                Virtual Try-On
              </a>
            </li>

          <li class="categories__item">
              <a class="categories__link" href="customer/my_account.php?my_orders">
                My Account
                <!-- <i class="icon-down-open-1"></i> -->
              </a>
              <!-- <div class="dropdown dropdown--lookbook">
                <div class="clearfix">
                  <div class="dropdown__half">
                    <div class="dropdown__heading">Account Settings</div>
                    <ul class="dropdown__items">
                      <li class="dropdown__item">
                        <a href="customer/my_wishlist.php" class="dropdown__link">My Wishlist</a>
                      </li>
                      <li class="dropdown__item">
                        <a href="#" class="dropdown__link">My Orders</a>
                      </li>
                      <li class="dropdown__item">
                        <a href="#" class="dropdown__link">View Shopping Cart</a>
                      </li>
                    </ul>
                  </div>
                  <div class="dropdown__half">
                    <div class="dropdown__heading"></div>
                    <ul class="dropdown__items">
                      <li class="dropdown__item">
                        <a href="#" class="dropdown__link">Edit Your Account</a>
                      </li>
                      <li class="dropdown__item">
                        <a href="#" class="dropdown__link">Change Password</a>
                      </li>
                      <li class="dropdown__item">
                        <a href="#" class="dropdown__link">Delete Account</a>
                      </li>
                    </ul>
                  </div>
                </div>
             

              </div> -->

            </li>

          </ul>
        </nav>
      </div>
    </div>
  </header>