<?php

namespace App\Functions;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Exception;

class EncryptionFunction
{
    private static $cipher = 'AES-256-CTR'; // Strong cipher
    private static $ivLength = 16; // IV length for AES-256
    private static $hashAlgo = 'sha256'; // Hash algorithm for HMAC
    private static $key = 'YourSuperSecretKey123'; // Secret key

    public static function encode(string $data): string
    {
        // Generate a secure random IV
        $iv = random_bytes(self::$ivLength);

        // Derive encryption key (to avoid reusing the same raw key)
        $encryptionKey = hash_hkdf(self::$hashAlgo, self::$key);

        // Encrypt the data
        $encryptedData = openssl_encrypt(
            $data,
            self::$cipher,
            $encryptionKey,
            OPENSSL_RAW_DATA,
            $iv
        );

        // Concatenate IV and encrypted data
        $payload = $iv . $encryptedData;

        // Generate a truncated HMAC for integrity (16 bytes)
        $hmac = substr(hash_hmac(self::$hashAlgo, $payload, $encryptionKey, true), 0, 16);

        // Combine payload and HMAC, then encode in Base62
        return self::toBase62($hmac . $payload);
    }

    public static function decode(string $encoded)
    {
        try {
            // Decode from Base62
            $decoded = self::fromBase62($encoded);

            // Extract truncated HMAC and payload
            $hmac = substr($decoded, 0, 16); // HMAC is now 16 bytes
            $payload = substr($decoded, 16);

            // Derive encryption key
            $encryptionKey = hash_hkdf(self::$hashAlgo, self::$key);

            // Verify HMAC (truncated comparison)
            $calculatedHmac = substr(hash_hmac(self::$hashAlgo, $payload, $encryptionKey, true), 0, 16);
            if (!hash_equals($hmac, $calculatedHmac)) {
                throw new Exception("Invalid HMAC. Data may have been tampered with. [ENFN-1001]");
            }

            // Extract IV and encrypted data
            $iv = substr($payload, 0, self::$ivLength);
            $encryptedData = substr($payload, self::$ivLength);

            // Decrypt the data
            return openssl_decrypt(
                $encryptedData,
                self::$cipher,
                $encryptionKey,
                OPENSSL_RAW_DATA,
                $iv
            );
        } catch (Exception $e) {
            Log::error('Error occurred : [ENFN-1002]', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return false;
        }
    }

    private static function toBase62(string $binary): string
    {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
        $base = strlen($characters);
        $decimal = gmp_import($binary);
        $output = '';

        while ($decimal > 0) {
            $remainder = gmp_mod($decimal, $base);
            $output = $characters[gmp_intval($remainder)] . $output;
            $decimal = gmp_div($decimal, $base);
        }

        return $output;
    }

    private static function fromBase62(string $base62): string
    {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
        $base = strlen($characters);
        $decimal = gmp_init(0);

        for ($i = 0; $i < strlen($base62); $i++) {
            $decimal = gmp_add(
                gmp_mul($decimal, $base),
                strpos($characters, $base62[$i])
            );
        }

        return gmp_export($decimal);
    }

    /**
     * Encode the ID securely using Laravel's encryption.
     */
    public static function encodeId($id)
    {
        if (empty($id) || !ctype_digit((string)$id)) {
            Log::error('Error occurred : [ENFN-1003]', [
                'message' => 'The input not number [' . $id . ']',
                'file' => 'app\Libraries\EncryptionFunction.php',
                'line' => '168',
            ]);
            return false;
        }

        return EncryptionFunction::encode($id);
    }

    /**
     * Decode the ID securely using Laravel's decryption.
     */
    public static function decodeId($encodedId)
    {
        $decodedId = EncryptionFunction::decode($encodedId);
        if (empty($decodedId) || !ctype_digit((string)$decodedId)) {
            Log::error('Error occurred : [ENFN-1003]', [
                'message' => 'The input not number [' . $decodedId . ']',
                'file' => 'app\Libraries\EncryptionFunction.php',
                'line' => '186',
            ]);
            return false;
        }
        return $decodedId;
    }
}
