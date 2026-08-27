<div class="navbar navbar-inverse navbar-fixed-top">
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#myNavbar">
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>                        
            </button>
            <a class="navbar-brand" href="index.php">Lifestyle Store</a>
        </div>
        <div class="collapse navbar-collapse" id="myNavbar">
            <form class="navbar-form navbar-left" action="search.php" method="get" role="search">
                <div class="form-group">
                    <input type="text" name="q" class="form-control" placeholder="Search products"
                           value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                </div>
                <button type="submit" class="btn btn-default">
                    <span class="glyphicon glyphicon-search"></span>
                </button>
            </form>
            <ul class="nav navbar-nav navbar-right">
                <?php
                if (isset($_SESSION['email'])) {
                    ?>
                    <li><a href = "cart.php"><span class = "glyphicon glyphicon-shopping-cart"></span> Cart </a></li>
                    <li><a href = "settings.php"><span class = "glyphicon glyphicon-user"></span> Settings</a></li>
                    <li><a href = "orderhistory.php"><span class = "glyphicon glyphicon-file"></span> Order History</a></li>
                    <li><a href = "logout_script.php"><span class = "glyphicon glyphicon-log-in"></span> Logout</a></li>
                    <?php
                } else {
                    ?>
                    <li><a href="signup.php"><span class="glyphicon glyphicon-user"></span> Sign Up</a></li>
                    <li><a href="login.php"><span class="glyphicon glyphicon-log-in"></span> Login</a></li>
                    <li><a href="aboutus.php"><span class="glyphicon glyphicon-tasks"></span> About us</a></li>
                    <li><a href="contact.php"><span class="glyphicon glyphicon-phone"></span> Contact</a></li>
                        <?php
                    }
                    ?>
            </ul>
        </div>
    </div>
</div>