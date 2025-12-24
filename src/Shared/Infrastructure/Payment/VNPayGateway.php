<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Payment;

use Shared\Infrastructure\Config\AppConfig;

/**
 * VNPay Payment Gateway Implementation
 */
class VNPayGateway implements PaymentGateway
{
    private string $tmnCode;
    private string $secretKey;
    private string $url;
    private string $returnUrl;

    public function __construct()
    {
        $config = AppConfig::getInstance();
        $this->tmnCode = $_ENV['VNPAY_TMN_CODE'] ?? '';
        $this->secretKey = $_ENV['VNPAY_SECRET_KEY'] ?? '';
        $this->url = $_ENV['VNPAY_URL'] ?? 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html';
        $this->returnUrl = $_ENV['VNPAY_RETURN_URL'] ?? '';
    }

    public function createPaymentUrl(array $orderData): string
    {
        $vnp_TxnRef = $orderData['order_number'];
        $vnp_OrderInfo = 'Thanh toan don hang ' . $vnp_TxnRef;
        $vnp_OrderType = 'other';
        $vnp_Amount = $orderData['total'] * 100; // Convert to cents
        $vnp_Locale = 'vn';
        $vnp_BankCode = $orderData['bank_code'] ?? '';
        $vnp_IpAddr = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

        $inputData = [
            'vnp_Version' => '2.1.0',
            'vnp_TmnCode' => $this->tmnCode,
            'vnp_Amount' => $vnp_Amount,
            'vnp_Command' => 'pay',
            'vnp_CreateDate' => date('YmdHis'),
            'vnp_CurrCode' => 'VND',
            'vnp_IpAddr' => $vnp_IpAddr,
            'vnp_Locale' => $vnp_Locale,
            'vnp_OrderInfo' => $vnp_OrderInfo,
            'vnp_OrderType' => $vnp_OrderType,
            'vnp_ReturnUrl' => $this->returnUrl,
            'vnp_TxnRef' => $vnp_TxnRef,
        ];

        if (!empty($vnp_BankCode)) {
            $inputData['vnp_BankCode'] = $vnp_BankCode;
        }

        ksort($inputData);
        $query = '';
        $i = 0;
        $hashdata = '';

        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashdata .= '&' . urlencode($key) . '=' . urlencode($value);
            } else {
                $hashdata .= urlencode($key) . '=' . urlencode($value);
                $i = 1;
            }
            $query .= urlencode($key) . '=' . urlencode($value) . '&';
        }

        $vnp_Url = $this->url . '?' . $query;
        $vnpSecureHash = hash_hmac('sha512', $hashdata, $this->secretKey);
        $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;

        return $vnp_Url;
    }

    public function verifyCallback(array $callbackData): bool
    {
        $vnp_SecureHash = $callbackData['vnp_SecureHash'] ?? '';
        unset($callbackData['vnp_SecureHash']);

        ksort($callbackData);
        $i = 0;
        $hashdata = '';

        foreach ($callbackData as $key => $value) {
            if ($i == 1) {
                $hashdata .= '&' . urlencode($key) . '=' . urlencode($value);
            } else {
                $hashdata .= urlencode($key) . '=' . urlencode($value);
                $i = 1;
            }
        }

        $secureHash = hash_hmac('sha512', $hashdata, $this->secretKey);
        return $secureHash === $vnp_SecureHash;
    }

    public function getTransactionId(array $callbackData): ?string
    {
        return $callbackData['vnp_TransactionNo'] ?? null;
    }

    public function getPaymentStatus(array $callbackData): string
    {
        $responseCode = $callbackData['vnp_ResponseCode'] ?? '';
        
        if ($responseCode === '00') {
            return 'paid';
        }

        return 'failed';
    }
}

