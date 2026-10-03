use Illuminate\Support\Facades\Http;
use App\Models\Information;

if (!function_exists('SendSms')) {
    function SendSms($number, $msg)
    {
        $settings = Information::first();

        if ($settings && $settings->sms_api_key && $settings->sms_sender_id) {
            try {
                $response = Http::get("http://bulksmsbd.net/api/smsapi", [
                    'api_key' => $settings->sms_api_key,
                    'type' => 'text',
                    'number' => $number,
                    'senderid' => $settings->sms_sender_id,
                    'message' => $msg,
                ]);

                return true;
            } catch (\Exception $e) {
                \Log::error("SMS Sending Error: " . $e->getMessage());
                return false;
            }
        }
        return false;
    }
}