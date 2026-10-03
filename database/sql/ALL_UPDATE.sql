-- ##################################################################
--  BizCare — একসাথে সব আপডেট (২টা কাজ এক ফাইলে)
--  ------------------------------------------------------------------
--  অংশ ১ : Courier Tracking & Payment রিপোর্টের কলাম যোগ
--  অংশ ২ : product_stocks পরিষ্কার + দুই টেবিলের স্টক এক করা
--
--  ⚠️ চালানোর আগে অবশ্যই DB ব্যাকআপ নিন (phpMyAdmin → Export)।
--  ✅ পুরোটা idempotent — ভুল করে দুইবার চালালেও ক্ষতি নেই।
--  📌 phpMyAdmin → আপনার DB → Import → এই ফাইল → Go
-- ##################################################################


-- ==================================================================
--  অংশ ১ — Courier Tracking & Payment (নতুন রিপোর্টের জন্য)
-- ==================================================================
-- courier_sent_at         : কবে কুরিয়ারে পাঠানো হলো / ট্র্যাকিং আইডি পাওয়া গেল
-- courier_payment_status  : pending | received  (কুরিয়ার থেকে টাকা পেয়েছি কিনা)
-- courier_paid_at         : কবে টাকা পেয়েছি
-- courier_paid_amount     : কত টাকা পেয়েছি

ALTER TABLE `orders`
  ADD COLUMN IF NOT EXISTS `courier_sent_at` DATETIME NULL AFTER `courier_status`,
  ADD COLUMN IF NOT EXISTS `courier_payment_status` VARCHAR(20) NULL DEFAULT 'pending' AFTER `courier_sent_at`,
  ADD COLUMN IF NOT EXISTS `courier_paid_at` DATETIME NULL AFTER `courier_payment_status`,
  ADD COLUMN IF NOT EXISTS `courier_paid_amount` DECIMAL(10,2) NULL AFTER `courier_paid_at`;

-- রিপোর্টের ফিল্টার দ্রুত করতে ইনডেক্স
ALTER TABLE `orders` ADD INDEX IF NOT EXISTS `idx_courier_sent_at` (`courier_sent_at`);
ALTER TABLE `orders` ADD INDEX IF NOT EXISTS `idx_courier_payment_status` (`courier_payment_status`);

-- আগে থেকে যেসব অর্ডার কুরিয়ারে পাঠানো আছে (ট্র্যাকিং আইডি আছে) কিন্তু
-- courier_sent_at ফাঁকা — সেগুলোর জন্য updated_at কে আনুমানিক তারিখ ধরা হলো,
-- যাতে পুরনো ডেটাও রিপোর্টে দেখা যায়।
UPDATE `orders`
   SET `courier_sent_at` = `updated_at`
 WHERE `courier_sent_at` IS NULL
   AND `courier_tracking_id` IS NOT NULL
   AND `courier_tracking_id` <> '';

UPDATE `orders`
   SET `courier_payment_status` = 'pending'
 WHERE `courier_payment_status` IS NULL;


-- ==================================================================
--  অংশ ২ — product_stocks পরিষ্কার ও স্টক এক করা
-- ==================================================================
-- সমস্যা ছিল:
--   ক) orphan row — যেসব stock row-এর variation আর নেই (ভুয়া স্টক দেখাত)
--   খ) duplicate row — একই variation-এর একাধিক row (স্টক দ্বিগুণ দেখাত)
--   গ) variations.stock_quantity আর product_stocks.quantity আলাদা হয়ে যেত
--      (এডিট ফর্মে পুরনো সংখ্যা দেখাত, তাই বারবার হাতে ঠিক করতে হতো)

-- ধাপ ১: orphan row মুছে ফেলা (যার variation নেই)
DELETE ps FROM `product_stocks` ps
LEFT JOIN `variations` v ON v.id = ps.variation_id
WHERE v.id IS NULL;

-- ধাপ ২: variation_id ফাঁকা/শূন্য এমন আবর্জনা row
DELETE FROM `product_stocks` WHERE `variation_id` IS NULL OR `variation_id` = 0;

-- ধাপ ৩: একই variation-এর একাধিক row থাকলে সর্বশেষটা রেখে বাকিগুলো মুছে ফেলা
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


-- ==================================================================
--  ফলাফল যাচাই — তিনটি সংখ্যাই 0 এলে সব ঠিক আছে ✅
-- ==================================================================
SELECT
  (SELECT COUNT(*) FROM product_stocks ps LEFT JOIN variations v ON v.id=ps.variation_id WHERE v.id IS NULL) AS baki_orphan,
  (SELECT COUNT(*) FROM (SELECT variation_id FROM product_stocks GROUP BY variation_id HAVING COUNT(*)>1) x) AS baki_duplicate,
  (SELECT COUNT(*) FROM variations v JOIN product_stocks ps ON ps.variation_id=v.id WHERE IFNULL(v.stock_quantity,-1) <> ps.quantity) AS baki_mismatch,
  (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='orders' AND COLUMN_NAME='courier_sent_at') AS courier_column_ready;
