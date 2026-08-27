<?php
require("includes/common.php");

/**
 * Escapes the LIKE wildcards % and _ so a search for "50%" is treated as
 * literal text rather than as a pattern. The backslash must be doubled first,
 * otherwise the escapes added below would themselves be re-escaped.
 */
function like_escape($value) {
    return str_replace(array('\\', '%', '_'), array('\\\\', '\\%', '\\_'), $value);
}

// ---------------------------------------------------------------
// Read and normalise the filters coming in on the query string.
// ---------------------------------------------------------------
$q        = isset($_GET['q'])        ? trim($_GET['q'])        : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : '';
$sort     = isset($_GET['sort'])     ? trim($_GET['sort'])     : 'name_asc';

$minPrice = (isset($_GET['min_price']) && $_GET['min_price'] !== '') ? (int) $_GET['min_price'] : null;
$maxPrice = (isset($_GET['max_price']) && $_GET['max_price'] !== '') ? (int) $_GET['max_price'] : null;

// A user-supplied ORDER BY cannot be bound as a parameter, so it is resolved
// through a whitelist instead of being interpolated into the query.
$sortColumns = array(
    'name_asc'   => '`name` ASC',
    'price_asc'  => '`price` ASC',
    'price_desc' => '`price` DESC',
);
if (!isset($sortColumns[$sort])) {
    $sort = 'name_asc';
}
$orderBy = $sortColumns[$sort];

$categories = array('cameras', 'watches', 'shirts');
if ($category !== '' && !in_array($category, $categories, true)) {
    $category = '';
}

// Swap a reversed price range rather than returning nothing at all.
if ($minPrice !== null && $maxPrice !== null && $minPrice > $maxPrice) {
    $swap     = $minPrice;
    $minPrice = $maxPrice;
    $maxPrice = $swap;
}

// ---------------------------------------------------------------
// Build the query. Every value is bound, never concatenated.
// ---------------------------------------------------------------
$where  = array();
$params = array();
$types  = '';

if ($q !== '') {
    $where[]  = "`name` LIKE ?";
    $params[] = '%' . like_escape($q) . '%';
    $types   .= 's';
}
if ($category !== '') {
    $where[]  = "`category` = ?";
    $params[] = $category;
    $types   .= 's';
}
if ($minPrice !== null) {
    $where[]  = "`price` >= ?";
    $params[] = $minPrice;
    $types   .= 'i';
}
if ($maxPrice !== null) {
    $where[]  = "`price` <= ?";
    $params[] = $maxPrice;
    $types   .= 'i';
}

$sql = "SELECT `id`, `name`, `category`, `price`, `image` FROM `items`";
if (count($where) > 0) {
    $sql .= " WHERE " . implode(' AND ', $where);
}
$sql .= " ORDER BY " . $orderBy;

$stmt = mysqli_prepare($con, $sql) or die(mysqli_error($con));
if ($types !== '') {
    // The spread unpacks the collected values as individual bind arguments.
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$items = array();
while ($row = mysqli_fetch_assoc($result)) {
    $items[] = $row;
}
mysqli_stmt_close($stmt);

$hasFilters = ($q !== '' || $category !== '' || $minPrice !== null || $maxPrice !== null);
$safeQuery  = htmlspecialchars($q, ENT_QUOTES, 'UTF-8');
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Search | Life Style Store</title>
        <link href="css/bootstrap.css" rel="stylesheet">
        <link href="css/style.css" rel="stylesheet">
        <script src="js/jquery.js"></script>
        <script src="js/bootstrap.min.js"></script>
    </head>

    <body>
        <?php
        include 'includes/header.php';
        include 'includes/check-if-added.php';
        include 'includes/product-card.php';
        ?>
        <div class="container" id="content">
            <div class="home-spacer">
                <h2>Search products</h2>

                <form action="search.php" method="get" class="well">
                    <div class="row">
                        <div class="col-md-4">
                            <label for="q">Product name</label>
                            <input type="text" class="form-control" id="q" name="q"
                                   value="<?php echo $safeQuery; ?>" placeholder="e.g. DSLR, Titan, shirt">
                        </div>
                        <div class="col-md-2">
                            <label for="category">Category</label>
                            <select class="form-control" id="category" name="category">
                                <option value="">All</option>
                                <?php foreach ($categories as $option) { ?>
                                    <option value="<?php echo $option; ?>"
                                        <?php echo ($category === $option) ? 'selected' : ''; ?>>
                                        <?php echo ucfirst($option); ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="min_price">Min price</label>
                            <input type="number" min="0" class="form-control" id="min_price" name="min_price"
                                   value="<?php echo ($minPrice === null) ? '' : (int) $minPrice; ?>">
                        </div>
                        <div class="col-md-2">
                            <label for="max_price">Max price</label>
                            <input type="number" min="0" class="form-control" id="max_price" name="max_price"
                                   value="<?php echo ($maxPrice === null) ? '' : (int) $maxPrice; ?>">
                        </div>
                        <div class="col-md-2">
                            <label for="sort">Sort by</label>
                            <select class="form-control" id="sort" name="sort">
                                <option value="name_asc"   <?php echo ($sort === 'name_asc')   ? 'selected' : ''; ?>>Name (A–Z)</option>
                                <option value="price_asc"  <?php echo ($sort === 'price_asc')  ? 'selected' : ''; ?>>Price (low to high)</option>
                                <option value="price_desc" <?php echo ($sort === 'price_desc') ? 'selected' : ''; ?>>Price (high to low)</option>
                            </select>
                        </div>
                    </div>
                    <div class="row" style="margin-top: 15px;">
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-primary">
                                <span class="glyphicon glyphicon-search"></span> Search
                            </button>
                            <?php if ($hasFilters) { ?>
                                <a href="search.php" class="btn btn-default">Clear filters</a>
                            <?php } ?>
                        </div>
                    </div>
                </form>

                <p class="text-muted">
                    <?php
                    $count = count($items);
                    if ($q !== '') {
                        echo $count . ' ' . ($count === 1 ? 'result' : 'results') . ' for "' . $safeQuery . '"';
                    } else {
                        echo 'Showing ' . $count . ' ' . ($count === 1 ? 'product' : 'products');
                    }
                    ?>
                </p>
            </div>
            <hr>

            <?php if (count($items) === 0) { ?>
                <div class="text-center home-spacer">
                    <h4>No products matched your search.</h4>
                    <p class="text-muted">Try a shorter word, widen the price range, or browse all categories.</p>
                    <p>
                        <a href="search.php" class="btn btn-default">Clear filters</a>
                        <a href="products.php" class="btn btn-primary">Browse all products</a>
                    </p>
                </div>
            <?php } else { ?>
                <div class="row text-center">
                    <?php foreach ($items as $item) { render_product_card($item); } ?>
                </div>
            <?php } ?>
        </div>
        <?php include 'includes/footer.php'; ?>
    </body>
</html>
