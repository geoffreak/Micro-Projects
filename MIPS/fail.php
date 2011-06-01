<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>MIPS Compiler</title>
<style type="text/css">
body, html {
	margin: 0;
	padding: 0;
	overflow: hidden;
}
</style>
</head>

<body>
<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

/*for ($k =1; $k<32;$k++)
{
	echo 'bit_ALU bit'.$k.'(operation,Ainvert,Binvert,A['.$k.'],B['.$k.'],Cout['.$k.'],Less,ALUOut['.$k.'],Cout['.($k+1).']);<br />';
}
/*/
//$mips =  '00000000011000010010100000101010000100001010000000000000010100000000000001100010001100000010001000000000110000000010100000101010000100001010001000000000000010010000000010000110001110000010000010001100111010000000000000000000100011001110100100000000000000010000000100101000001010000010101000010000101000000000000000000010101011001110100100000000000000001010110011101000000000000000000100000000110000100011000000100010000010000000000000000000000001110000000001000011000110000010000000001000000000000000000000000100';
//$mips = '10101101000100000000000000000010';
$mips = '0000000001100001001010000010101000111100000010000000000000101101000100001010000000000001000000000000000001100010001100000010001000000000110000000010100000101010000100001010001000000000000010010000000010000110001110000010000010001100111010000000000000000000100011001110100100000000000000010000000100101000001010000010101000010000101000000000000000000010101011001110100100000000000000001010110011101000000000000000000100000000110000100011000000100010000010000000000000000000110000000000000001000011000110000010000000000000100000000000000000001000';
$mips_arr = str_split($mips, 32);
$opcodes = array(
	'000000'	=> '',
	'000010'	=> 'j',
	'001111'	=> 'lui',
	'000100'	=> 'beq',
	'001000'	=> 'sw',
	'100011'	=> 'lw',
	'101011'	=> 'sw',
);
$functcodes = array(
	'100000'	=> 'add',
	'100010'	=> 'sub',
	'100100'	=> 'and',
	'100101'	=> 'or',
	'101010'	=> 'slt',
	'001000'	=> 'jr',
);
$counters = '';
$codes = '';
$counter = 0;
foreach ($mips_arr as $mip)
{
	$output = '';
	$opcode = $opcodes[substr($mip, 0, 6)];
	if ($opcode == '')
	{
		$functcode = substr($mip, -6, 6);
		$rs = substr($mip, 6, 5);
		$rt = substr($mip, 11, 5);
		$rd = substr($mip, 16, 5);
		$shamt = substr($mip, 21, 5);
		if (substr($functcodes[$functcode], 0, 1) == 'j')
		{
			$jump = substr($mip, 6);
			$output .= $functcodes[$functcode] . ' R' . bindec($rs);
		}
		else
		{
			$output .= $functcodes[$functcode] . ' R' . bindec($rd) . ', R' . bindec($rs) . ', R' . bindec($rt);
		}
	}
	else if (substr($opcode, 0, 1) == 'j')
	{
		$jump = substr($mip, 6, 26);
		$output .= $opcode . ' ' . bindec($jump);
	}
	else
	{
		$rs = substr($mip, 6, 5);
		$rt = substr($mip, 11, 5);
		$immediate = substr($mip, 16, 16);
		if ($opcode == 'lw' || $opcode == 'sw')
		{
			$output .= $opcode . ' R' . bindec($rt) . ', ' . bindec($immediate) . '(R' . bindec($rs) . ')';
		}
		else
		{
			$output .= $opcode . ' R' . bindec($rs) . ', R' . bindec($rt) . ', ' . bindec($immediate);
		}
	}
	$codes .= $output .'<br />';
	$counters .= sprintf("%04.4s", $counter) . ':<br />';
	$counter += 32;
}
?>
<div style="position:absolute;overflow-y:auto;top:0;bottom:0;left:0;right:0; padding: 1%;font-family:'Courier New', Courier, monospace;">
	<div style="background-color: #C4FFC0;;float: left; width: auto;padding: 5px;">
		<? print $counters; ?>
	</div>
	<div style="background-color: #C4FFC0;;float: left; width: 308px;padding: 5px; ">
		<? print $codes; ?>
	</div>
</div>

</body>
</html>