<?php

namespace hhun\Web3Recover;

use kornrunner\Keccak;

class Web3Recover
{
    function __construct()
    {
    }

    /**
     * fromHex
     *
     * @param $hex
     * @param $signed
     * @param string $type
     * @return string
     * @throws \Exception
     */
    public static function fromHex($hex, $signed, string $type = 'EIP20'): string
    {
        $hex = self::hexToBin($hex);
        return self::fromText($hex, $signed, $type);
    }

    /**
     * fromText
     *
     * @param $msg
     * @param $signed
     * @param string $type
     * @return string
     * @throws \Exception
     */
    public static function fromText($msg, $signed, string $type = 'EIP20'): string
    {
        switch (strtoupper($type)) {
            case 'EIP20':
                $personal_prefix_msg = "\x19Ethereum Signed Message:\n" . strlen($msg) . $msg;
                break;
            case 'EIP712':
                $personal_prefix_msg = hex2bin(substr(self::keccak256('string Messageuint32 A number'), 2) . substr(self::keccak256('Hi, Alice!' . pack('N', 1337)), 2));
                break;
            default:
                return '';
        }
        $hex = self::keccak256($personal_prefix_msg);
        return self::EcRecover($hex, $signed);
    }

    /**
     * EcRecover
     *
     * @param $hex
     * @param $signed
     * @return string
     * @throws \Exception
     */
    public static function EcRecover($hex, $signed): string
    {
        $rHex = substr($signed, 2, 64);
        $sHex = substr($signed, 66, 64);
        $vValue = hexdec(substr($signed, 130, 2));
        $messageHex = substr($hex, 2);
        $messageByteArray = unpack('C*', hex2bin($messageHex));
        $messageGmp = gmp_init('0x' . $messageHex);
        $r = $rHex; //hex string without 0x
        $s = $sHex; //hex string without 0x
        $v = $vValue == 0 ? 27 : ($vValue == 1 ? 28 : $vValue); //27 or 28

        //with hex2bin it gives the same byte array as the javascript
        $rByteArray = unpack('C*', self::hexToBin($r));
        $sByteArray = unpack('C*', self::hexToBin($s));
        $rGmp = gmp_init('0x' . $r);
        $sGmp = gmp_init('0x' . $s);

        $recovery = $v - 27;
        if ($recovery !== 0 && $recovery !== 1) {
            throw new \Exception('Invalid signature v value');
        }

        $publicKey = Signature::recoverPublicKey($rGmp, $sGmp, $messageGmp, $recovery);
        $publicKeyString = $publicKey['x'] . $publicKey['y'];

        return '0x' . substr(self::keccak256(self::hexToBin($publicKeyString)), -40);
    }

    /**
     * isHex
     *
     * @param $value
     * @return bool
     */
    public static function isHex($value)
    {
        return (is_string($value) && preg_match('/^(0x)?[a-f0-9]*$/', $value) === 1);
    }

    /**
     * hexToBin
     *
     * @param $value
     * @return string
     */
    public static function hexToBin($value): string
    {
        if (!is_string($value)) {
            throw new \Exception('The value to hexToBin function must be string.');
        }
        $value = self::stripZero($value);
        return pack('H*', $value);
    }

    /**
     * strToHex
     *
     * @param $string
     * @return string
     */
    public static function BinToHex($string): string
    {
        $hex = unpack('H*', $string);
        return '0x' . array_shift($hex);
    }

    /**
     * keccak256
     *
     * @param $str
     * @return string
     * @throws \Exception
     */
    private static function keccak256($str): string
    {
        return '0x' . Keccak::hash($str, 256);
    }

    /**
     * isZeroPrefixed
     *
     * @param $value
     * @return bool
     */
    public static function isZeroPrefixed($value): bool
    {
        if (!is_string($value)) {
            throw new \Exception('The value to isZeroPrefixed function must be string.');
        }
        return (strpos($value, '0x') === 0);
    }

    /**
     * stripZero
     *
     * @param $value
     * @return string
     * @throws \Exception
     */
    public static function stripZero($value): string
    {
        if (self::isZeroPrefixed($value)) {
            $count = 1;
            return str_replace('0x', '', $value, $count);
        }
        return $value;
    }

    /**
     * toChecksumAddress
     *
     * @param $value
     * @return string
     * @throws \Exception
     */
    public static function toChecksumAddress($value): string
    {
        if (!is_string($value)) {
            throw new \Exception('The value to toChecksumAddress function must be string.');
        }
        $value = self::stripZero(strtolower($value));
        $hash = self::stripZero(self::keccak256($value));
        $ret = '0x';

        for ($i = 0; $i < 40; $i++) {
            if (intval($hash[$i], 16) >= 8) {
                $ret .= strtoupper($value[$i]);
            } else {
                $ret .= $value[$i];
            }
        }
        return $ret;
    }

}
