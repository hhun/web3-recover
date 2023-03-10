# Web3还原已签名数据的签名帐户

**中文** | [English](README.en.md)

## 使用方法

## JavaScript 签名消息
``` JavaScript
var msg = 'login'
var from = web3.eth.accounts[0]
web3.eth.sign(msg, from, function (err, result) {
	if (err) {
		return console.error(err);
	}
	console.log('signed: ' + result);
});
```

## PHP恢复签名地址
``` php
use hhun\Web3Recover\Web3Recover;

$signed = '0x4cde93d4........'; // 签名内容

$msg = 'login';
echo Web3Recover::fromText($msg, $signed);

$hex = '0x6c6f67696e';
echo Web3Recover::fromHex($hex, $signed);
```
