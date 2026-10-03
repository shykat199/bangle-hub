<?php
namespace App\Utils;

use App\Models\Product;
use App\Models\ProductStock;
use App\Models\Variation;

class Util {

	/**
	 * `products.stock_quantity` is the counter the storefront reads (cart
	 * availability, "X items left", the out-of-stock alert), but until now
	 * only the website checkout touched it — every admin-panel sale, cash
	 * sale, cancel and courier return moved `product_stocks` alone. The two
	 * ledgers drifted apart, so the shop kept selling stock it no longer had
	 * and cancelled orders never gave their units back.
	 *
	 * Every stock movement now goes through here as well, by delta rather
	 * than by recompute — products that keep no `product_stocks` rows at all
	 * would be zeroed out by a recompute.
	 */
	private function adjustProductStockColumn($product_id, $delta): void
	{
		if (empty($product_id) || (int) $delta === 0) return;

		$current = (int) (Product::where('id', $product_id)->value('stock_quantity') ?? 0);

		Product::where('id', $product_id)->update([
			'stock_quantity' => max(0, $current + (int) $delta),
		]);
	}

	/**
	 * স্টক দুই জায়গায় রাখা হয় — `product_stocks` (আসল হিসাব, অর্ডারে কমে/বাড়ে)
	 * আর `variations.stock_quantity` (অ্যাডমিন ফর্মে দেখানো হতো)। আগে শুধু
	 * product_stocks বদলাত, তাই এডিট ফর্মে পুরনো সংখ্যা দেখাত এবং বারবার
	 * হাতে ঠিক করতে হতো। এখন যেকোনো পরিবর্তনে দুটোই এক রাখা হয়।
	 */
	private function syncVariationStock($variation_id, $quantity): void
	{
		if (empty($variation_id)) return;

		Variation::where('id', $variation_id)->update([
			'stock_quantity' => max(0, (int) $quantity),
		]);
	}

	/**
	 * A caller without a variation_id (the landing-page "buy now" endpoint
	 * never asked for one) used to make increase/decreaseProductStock() give
	 * up entirely on product_stocks/variations.stock_quantity — only
	 * products.stock_quantity moved. For a single product there is always
	 * exactly one variation row, so resolve it here instead of skipping the
	 * other two ledgers.
	 */
	private function resolveVariationIdForStock($product_id, $variation_id)
	{
		if (!empty($variation_id)) {
			return $variation_id;
		}

		return Variation::where('product_id', $product_id)->orderBy('id')->value('id');
	}

	public function expenseStatus($expense){

		if ($expense) {
			$due=$expense->amount - $expense->payments()->sum('amount');
			if ($due==0) {
				$status='paid';
			}else if($due ==$expense->amount){
				$status='due';
			}else{
				$status='partial';
			}

			$expense->payment_status=$status;
			$expense->save();
		}
		return true;

	}

	public function purchaseStatus($purchase){

		if ($purchase) {
			$due=$purchase->amount - $purchase->payments()->sum('amount');
			if ($due==0) {
				$status='paid';
			}else if($due ==$purchase->amount){
				$status='due';
			}else{
				$status='partial';
			}

			$purchase->payment_status=$status;
			$purchase->save();
		}
		return true;

	}


	public function increaseProductStock($product_id,$variation_id, $stock){

		$this->adjustProductStockColumn($product_id, +$stock);

		$variation_id = $this->resolveVariationIdForStock($product_id, $variation_id);
		if (empty($variation_id)) return true;

		$item=ProductStock::where(['product_id'=>$product_id,'variation_id'=>$variation_id])->first();

		if ($item) {
			
		}
		else{
			$item=new ProductStock();
			$item->product_id=$product_id;
			$item->variation_id=$variation_id;
			$item->quantity=0;
		}

		$item->quantity+=$stock;
		$item->save();

		$this->syncVariationStock($variation_id, $item->quantity);

		return true;

	}


	public function updateProductStock($product_id, $variation_id, $old_stock, $new_stock){

		$this->adjustProductStockColumn($product_id, $new_stock - $old_stock);

		if (empty($variation_id)) return true;

		$item=ProductStock::where(['product_id'=>$product_id, 'variation_id'=>$variation_id])->first();
		$stock=$new_stock -$old_stock;
		if ($stock !=0) {
			if ($item) {
				
			}else{
				$item=new ProductStock();
				$item->product_id=$product_id;
				$item->variation_id=$variation_id;
				$item->quantity=0;
			}

			$item->quantity +=$stock;
			$item->save();

			$this->syncVariationStock($variation_id, $item->quantity);
		}
		return true;

		

	}


	public function decreaseProductStock($product_id, $variation_id, $stock){

		$this->adjustProductStockColumn($product_id, -$stock);

		$variation_id = $this->resolveVariationIdForStock($product_id, $variation_id);
		if (empty($variation_id)) return true;

		$item=ProductStock::where(['product_id'=>$product_id, 'variation_id'=>$variation_id])->first();

		if($item){
			// Never let stock fall below zero — a negative quantity is corrupt
			// data that breaks reports and reorder logic.
			$item->quantity = max(0, $item->quantity - $stock);
			$item->save();

			$this->syncVariationStock($variation_id, $item->quantity);
		}

		return true;


	}


	public function checkProductStock($product_id, $variation_id){

		$item=ProductStock::where(['product_id'=>$product_id, 'variation_id'=>$variation_id])->first();
		
		return $item?$item->quantity:0;


	}
	
	// Anything stored under public/ is served straight back by the web server,
	// so an upload named .php would execute. Only these extensions are ever
	// written to disk.
	const ALLOWED_UPLOAD_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg', 'ico', 'avif'];

	/**
	 * True when an uploaded file is safe to place in the public folder — the
	 * extension must be a known image type AND the detected MIME type must
	 * agree, so renaming shell.php to shell.jpg does not get through either.
	 */
	public static function isSafeImageUpload($file): bool
	{
		if (!$file || !$file->isValid()) return false;

		$ext = strtolower((string) $file->getClientOriginalExtension());
		if (!in_array($ext, self::ALLOWED_UPLOAD_EXTENSIONS, true)) return false;

		$mime = strtolower((string) $file->getMimeType());

		return str_starts_with($mime, 'image/');
	}

	public static function uploadFile($file, $folder)
	{
	    if(!empty($file && $folder))
        {
            if (!self::isSafeImageUpload($file)) {
                return null;
            }

            $new_name = rand().'.'.strtolower($file->getClientOriginalExtension());
            $file->move(public_path('uploads/'.$folder), $new_name);
            return $new_name;
        }
	}
		
		
	public static function deleteFile($file, $folder)
	{
       if(!empty($file && $folder))
       {
            $path = public_path('uploads/'.$folder.'/').$file;
            if(file_exists($path))
            {
                unlink($path);
            }
       }
        
        return true;
	}
	




}