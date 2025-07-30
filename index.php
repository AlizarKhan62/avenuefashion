<?php

session_start();

include("includes/db.php");
include("includes/header.php");
include("functions/functions.php");
include("includes/main.php");

?>


  <!-- Cover -->
  <main>
    <div class="hero">
      <a href="shop.php" class="btn1">View all products
      </a>
    </div>
    <!-- Main -->
    <div class="wrapper">
            <h1>Featured Collection<h1>
            
      </div>



    <div id="content" class="container"><!-- container Starts -->

    <div class="row"><!-- row Starts -->
      
    <?php

    getPro();

    ?>

    </div><!-- row Ends -->

    </div><!-- container Ends -->
    <!-- FOOTER -->
    <footer class="page-footer">

      <div class="footer-nav">
        <div class="container clearfix">

          <div class="footer-nav__col footer-nav__col--info">
            <div class="footer-nav__heading">Information</div>
            <ul class="footer-nav__list">
              <li class="footer-nav__item">
                <a href="about.php" class="footer-nav__link">The brand</a>
              </li>
              <li class="footer-nav__item">
                <a href="virtual_tryon.php" class="footer-nav__link">Virtual Try-On</a>
              </li>
              <li class="footer-nav__item">
                <a href="contact.php" class="footer-nav__link">Customer service</a>
              </li>
              <li class="footer-nav__item">
                <a href="terms.php" class="footer-nav__link">Privacy &amp; cookies</a>
              </li>
              <!-- <li class="footer-nav__item">
                <a href="#" class="footer-nav__link">Site map</a>
              </li> -->
            </ul>
          </div>

          <div class="footer-nav__col footer-nav__col--whybuy">
            <div class="footer-nav__heading">Useful Links</div>
            <ul class="footer-nav__list">
              <li class="footer-nav__item">
                <a href="terms.php" class="footer-nav__link">Shipping &amp; returns</a>
              </li>
              <li class="footer-nav__item">
                <a href="terms.php" class="footer-nav__link">Secure shipping</a>
              </li>
              <li class="footer-nav__item">
                <a href="shop.php" class="footer-nav__link">All Products</a>
              </li>
              <li class="footer-nav__item">
                <a href="terms.php" class="footer-nav__link">Size Guide</a>
              </li>
              <!-- <li class="footer-nav__item">
                <a href="#" class="footer-nav__link">Award winning</a>
              </li>
              <li class="footer-nav__item">
                <a href="#" class="footer-nav__link">Ethical trading</a>
              </li> -->
            </ul>
          </div>

          <div class="footer-nav__col footer-nav__col--account">
            <div class="footer-nav__heading">Your account</div>
            <ul class="footer-nav__list">
              <!-- <li class="footer-nav__item">
                <a href="#" class="footer-nav__link">Sign in</a>
              </li>
              <li class="footer-nav__item">
                <a href="#" class="footer-nav__link">Register</a>
              </li> -->
              <li class="footer-nav__item">
                <a href="cart.php" class="footer-nav__link">View cart</a>
              </li>
              <li class="footer-nav__item">
                <a href="customer/my_account.php?my_wishlist" class="footer-nav__link">Wishlist</a>
              </li>
              <li class="footer-nav__item">
                <a href="track_order.php" class="footer-nav__link">Track an order</a>
              </li>
              <li class="footer-nav__item">
                <a href="customer/my_account.php?edit_account" class="footer-nav__link">Update information</a>
              </li>
            </ul>
          </div>


          <div class="footer-nav__col footer-nav__col--contacts">
            <div class="footer-nav__heading">Contact details</div>
            <address class="address">
            Head Office: Avenue Fashion.<br>
            180-182 Regent Street, Faisalabad, Pakistan
          </address>
            <div class="phone">
              Telephone:
              <a class="phone__number" href="tel:+92123456789">+92-123-456-789</a>
            </div>
            <div class="email">
              Email:
              <a href="mailto:support@avenuefashion.com" class="email__addr">support@avenuefashion.com</a>
            </div>
            <div class="social-links" style="margin-top: 15px;">
              <a href="#" style="color: #3b5998; margin-right: 10px;"><i class="fa fa-facebook"></i></a>
              <a href="#" style="color: #1da1f2; margin-right: 10px;"><i class="fa fa-twitter"></i></a>
              <a href="#" style="color: #e1306c; margin-right: 10px;"><i class="fa fa-instagram"></i></a>
              <a href="#" style="color: #0077b5;"><i class="fa fa-linkedin"></i></a>
            </div>
          </div>

        </div>
      </div>

      <!-- <div class="banners">
        <div class="container clearfix">

          <div class="banner-award">
            <span>Award winner</span><br> Fashion awards 2016
          </div>

          <div class="banner-social">
            <a href="#" class="banner-social__link">
            <i class="icon-facebook"></i>
          </a>
            <a href="#" class="banner-social__link">
            <i class="icon-twitter"></i>
          </a>
            <a href="#" class="banner-social__link">
            <i class="icon-instagram"></i>
          </a>
            <a href="#" class="banner-social__link">
            <i class="icon-pinterest-circled"></i>
          </a>
          </div>

        </div>
      </div> -->

      <div class="page-footer__subline">
        <div class="container clearfix">

          <div class="copyright">
            &copy; <?php echo date('Y'); ?> Avenue Fashion&trade;
          </div>

          <div class="developer">
            Developed by Alizar Ali
          </div>

          <div class="designby">
            Design by Asghar Abbas
          </div>

        </div>
      </div>
    </footer>
</body>

</html>
