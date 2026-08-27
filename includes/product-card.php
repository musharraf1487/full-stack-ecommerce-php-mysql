<?php

/**
 * Renders one product thumbnail, matching the card markup used on products.php.
 *
 * Expects an associative row from `items` with id, name, price and image.
 * Relies on check_if_added_to_cart() from includes/check-if-added.php, so
 * include that first when the visitor is logged in.
 */
function render_product_card($item) {
    $id    = (int) $item['id'];
    $name  = htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8');
    $image = htmlspecialchars($item['image'], ENT_QUOTES, 'UTF-8');
    $price = number_format((float) $item['price'], 2);
    ?>
    <div class="col-md-3 col-sm-6 home-feature">
        <div class="thumbnail">
            <img src="<?php echo $image; ?>" alt="<?php echo $name; ?>">
            <div class="caption">
                <h3><?php echo $name; ?></h3>
                <p>Price: Rs. <?php echo $price; ?></p>
                <?php if (!isset($_SESSION['email'])) { ?>
                    <p><a href="login.php" role="button" class="btn btn-primary btn-block">Buy Now</a></p>
                <?php } elseif (check_if_added_to_cart($id)) { ?>
                    <a href="#" class="btn btn-block btn-success" disabled>Added to cart</a>
                <?php } else { ?>
                    <a href="cart-add.php?id=<?php echo $id; ?>" class="btn btn-block btn-primary">Add to cart</a>
                <?php } ?>
            </div>
        </div>
    </div>
    <?php
}
