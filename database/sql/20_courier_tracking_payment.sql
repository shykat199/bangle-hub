-- =====================================================================
-- 20 — Courier Tracking & Payment Report এর জন্য প্রয়োজনীয় কলাম
-- =====================================================================
-- courier_sent_at         : কবে কুরিয়ারে পাঠানো হলো / ট্র্যাকিং আইডি পাওয়া গেল
-- courier_payment_status  : pending | received  (কুরিয়ার থেকে টাকা পেয়েছি কিনা)
-- courier_paid_at         : কবে টাকা পেয়েছি
-- courier_paid_amount     : কত টাকা পেয়েছি
-- সব idempotent (MariaDB) — বারবার চালালেও সমস্যা নেই।
-- =====================================================================

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
