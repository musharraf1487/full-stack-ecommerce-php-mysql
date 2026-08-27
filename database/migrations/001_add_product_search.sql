-- ---------------------------------------------------------------
-- Migration 001 — product search support
--
-- The `items` table stored only id, name and price, while category and
-- imagery lived as hard-coded HTML in products.php. Search needs both in
-- the database, so this migration adds them and backfills the existing
-- twelve rows from the values products.php was using.
--
-- Target: MariaDB 10.4 (the `IF NOT EXISTS` clauses are MariaDB syntax and
-- make this file safe to re-run).
-- Apply with:  mysql -u root store < database/migrations/001_add_product_search.sql
-- ---------------------------------------------------------------

ALTER TABLE `items`
  ADD COLUMN IF NOT EXISTS `category` VARCHAR(32)  NOT NULL DEFAULT '' AFTER `name`,
  ADD COLUMN IF NOT EXISTS `image`    VARCHAR(255) NOT NULL DEFAULT '' AFTER `price`;

-- Backfill from the values previously hard-coded in products.php.
UPDATE `items` SET `category` = 'cameras', `image` = 'img/5.jpg'  WHERE `id` = 1;
UPDATE `items` SET `category` = 'cameras', `image` = 'img/2.jpg'  WHERE `id` = 2;
UPDATE `items` SET `category` = 'cameras', `image` = 'img/3.jpg'  WHERE `id` = 3;
UPDATE `items` SET `category` = 'cameras', `image` = 'img/4.jpg'  WHERE `id` = 4;
UPDATE `items` SET `category` = 'watches', `image` = 'img/18.jpg' WHERE `id` = 5;
UPDATE `items` SET `category` = 'watches', `image` = 'img/19.jpg' WHERE `id` = 6;
UPDATE `items` SET `category` = 'watches', `image` = 'img/20.jpg' WHERE `id` = 7;
UPDATE `items` SET `category` = 'watches', `image` = 'img/21.jpg' WHERE `id` = 8;
UPDATE `items` SET `category` = 'shirts',  `image` = 'img/22.jpg' WHERE `id` = 9;
UPDATE `items` SET `category` = 'shirts',  `image` = 'img/23.jpg' WHERE `id` = 10;
UPDATE `items` SET `category` = 'shirts',  `image` = 'img/24.jpg' WHERE `id` = 11;
UPDATE `items` SET `category` = 'shirts',  `image` = 'img/25.jpg' WHERE `id` = 12;

-- Category filtering and price sorting are equality/range predicates, so a
-- B-tree index serves them directly.
--
-- Note on the name search: search.php matches with LIKE '%term%'. A leading
-- wildcard means no B-tree index can be used and MariaDB falls back to a full
-- table scan. That is acceptable for a twelve-row catalogue; at real scale the
-- fix is a FULLTEXT index with MATCH ... AGAINST, e.g.
--     ALTER TABLE `items` ADD FULLTEXT KEY `ft_items_name` (`name`);
CREATE INDEX IF NOT EXISTS `idx_items_category` ON `items` (`category`);
CREATE INDEX IF NOT EXISTS `idx_items_price`    ON `items` (`price`);
