-- =====================================================================
-- 21 — product_stocks পরিষ্কার ও দুই টেবিলের স্টক এক করা
-- =====================================================================
-- সমস্যা ছিল:
--   ক) orphan row — যেসব stock row-এর variation আর নেই (ভুয়া স্টক দেখাত)
--   খ) duplicate row — একই variation-এর একাধিক row (স্টক দ্বিগুণ দেখাত)
--   গ) variations.stock_quantity আর product_stocks.quantity আলাদা হয়ে যেত
--      (এডিট ফর্মে পুরনো সংখ্যা দেখাত, তাই বারবার হাতে ঠিক করতে হতো)
--
-- ⚠️ চালানোর আগে DB ব্যাকআপ নিন। একবারই চালালেই হবে।
-- =====================================================================

-- ধাপ ১: orphan row মুছে ফেলা (যার variation নেই)
DELETE ps FROM `product_stocks` ps
LEFT JOIN `variations` v ON v.id = ps.variation_id
WHERE v.id IS NULL;

-- ধাপ ২: variation_id ফাঁকা/শূন্য এমন আবর্জনা row
DELETE FROM `product_stocks` WHERE `variation_id` IS NULL OR `variation_id` = 0;

-- ধাপ ৩: একই variation-এর একাধিক row থাকলে সবচেয়ে বড় id-রটা (সর্বশেষ) রেখে বাকিগুলো মুছে ফেলা
DELETE ps FROM `product_stocks` ps
JOIN (
    SELECT variation_id, MAX(id) AS keep_id
    FROM `product_stocks`
    GROUP BY variation_id
    HAVING COUNT(*) > 1
) dup ON dup.variation_id = ps.variation_id AND ps.id <> dup.keep_id;

-- ধাপ ৪: stock row-এর product_id ভুল থাকলে variation দেখে ঠিক করা
UPDATE `product_stocks` ps
JOIN `variations` v ON v.id = ps.variation_id
SET ps.product_id = v.product_id
WHERE ps.product_id <> v.product_id;

-- ধাপ ৫: যেসব variation-এর কোনো stock row নেই, সেগুলোর জন্য row তৈরি
INSERT INTO `product_stocks` (`product_id`, `variation_id`, `quantity`, `created_at`, `updated_at`)
SELECT v.product_id, v.id, IFNULL(v.stock_quantity, 0), NOW(), NOW()
FROM `variations` v
LEFT JOIN `product_stocks` ps ON ps.variation_id = v.id
WHERE ps.id IS NULL;

-- ধাপ ৬: variations.stock_quantity = product_stocks.quantity (product_stocks-ই আসল)
UPDATE `variations` v
JOIN `product_stocks` ps ON ps.variation_id = v.id
SET v.stock_quantity = ps.quantity
WHERE IFNULL(v.stock_quantity, -1) <> ps.quantity;

-- ধাপ ৭: single প্রোডাক্টের products.stock_quantity-ও মিলিয়ে দেওয়া
UPDATE `products` p
JOIN (
    SELECT v.product_id, SUM(ps.quantity) AS total
    FROM `variations` v JOIN `product_stocks` ps ON ps.variation_id = v.id
    GROUP BY v.product_id
) s ON s.product_id = p.id
SET p.stock_quantity = s.total
WHERE p.type = 'single';

-- ফলাফল যাচাই
SELECT
  (SELECT COUNT(*) FROM product_stocks ps LEFT JOIN variations v ON v.id=ps.variation_id WHERE v.id IS NULL) AS baki_orphan,
  (SELECT COUNT(*) FROM (SELECT variation_id FROM product_stocks GROUP BY variation_id HAVING COUNT(*)>1) x) AS baki_duplicate,
  (SELECT COUNT(*) FROM variations v JOIN product_stocks ps ON ps.variation_id=v.id WHERE IFNULL(v.stock_quantity,-1) <> ps.quantity) AS baki_mismatch;
