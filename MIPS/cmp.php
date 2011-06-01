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
$opcodes = array(
	'add'	=> '000000',
	'addi'	=> '001000',
	'addiu'	=> '001001',
	'addu'	=> '000000',
	'and'	=> '000000',
	'andi'	=> '001100',
	'beq'	=> '000100',
	'bne'	=> '000101',
	'j'		=> '000010',
	'jal'	=> '000011',
	'jr'	=> '000000',
	'lb'	=> '100000',
	'lw'	=> '100011',
	'or'	=> '000000',
	'ori'	=> '001101',
	'sb'	=> '101000',
	'sll'	=> '000000',
	'slt'	=> '000000',
	'slti'	=> '001010',
	'sltiu'	=> '001011',
	'srl'	=> '000000',
	'sub'	=> '000000',
	'sw'	=> '101011',
	'xor'	=> '000000',
	'xori'	=> '001110',
);
$types = array(
	'add'	=> '12d0f',
	'addi'	=> '1di',
	'addiu'	=> '1di',
	'addu'	=> '12d0f',
	'and'	=> '12d0f',
	'andi'	=> '1di',
	'beq'	=> 'd1i',
	'bne'	=> 'd1i',
	'or'	=> '12d0f',
	'ori'	=> '1di',
	'sll'	=> '01dhf',
	'slt'	=> '12d0f',
	'slti'	=> '1di',
	'sltiu'	=> '1di',
	'srl'	=> '01dhf',
	'sub'	=> '12d0f',
	'xor'	=> '12d0f',
	'xori'	=> '1di',
);
$functcodes = array(
	'add'	=> '100000',
	'addu'	=> '100001',
	'and'	=> '100100',
	'or'	=> '100101',
	'sll'	=> '000000',
	'slt'	=> '101010',
	'srl'	=> '000010',
	'sub'	=> '100010',
	'xor'	=> '100110',
);
$registers = array(
	'zero'	=> 0,
	'at'	=> 1,
	'v0'	=> 2,
	'v1'	=> 3,
	'a0'	=> 4,
	'a1'	=> 5,
	'a2'	=> 6,
	'a3'	=> 7,
	't0'	=> 8,
	't1'	=> 9,
	't2'	=> 10,
	't3'	=> 11,
	't4'	=> 12,
	't5'	=> 13,
	't6'	=> 14,
	't7'	=> 15,
	's0'	=> 16,
	's1'	=> 17,
	's2'	=> 18,
	's3'	=> 19,
	's4'	=> 20,
	's5'	=> 21,
	's6'	=> 22,
	's7'	=> 23,
	't8'	=> 24,
	't9'	=> 25,
	'k0'	=> 26,
	'k1'	=> 27,
	'gp'	=> 28,
	'sp'	=> 29,
	'fp'	=> 30,
	'ra'	=> 31,
);
function get_register($reg)
{
	global $registers;
	if (substr($reg, 0, 1) == '$' || substr($reg, 0, 1) == 'R')
	{
		$reg = substr($reg, 1);
	}
	if (!isset($registers[$reg]) && ($reg < 0 || $reg >= 32))
	{
		return false;
	}
	else if (isset($registers[$reg]))
	{
		$reg = $registers[$reg];
	}
	return sprintf("%05.5s", decbin($reg));
}
$mips = 'lw R1, 0(R0) 
lw R2, 1(R0)
lw R3, 2(R0)
lw R4, 3(R0)        
loop1: slt R5, R3,R1
beq R5, R0, exit 
sub R6, R3,R2
loop2: slt R5, R6, R0
beq R5, R2, exit1
add R7,R4,R6
lw R8, 0(R7)
lw R9, 1(R7)
slt R5, R9,R8
beq R5,R0, exit2
sw R9, 0(R7)
sw R8, 1(R7)
exit2: sub R6,R6,R2
j loop2
exit1: add R3,R3,R2
j loop1
exit: add R0, R0, R0';
if (isset($_REQUEST['mips']))
{
	$mips = $_REQUEST['mips'];
}
//$pattern = '/([\d\w]+:)?[\s]*((j\w*)[\s]*[R\$]?([\d\w]+))|((\w+)[\s]*[R\$]?([\d\w]+),[\s]*(([R\$]?([\d\w]+),[\s]*[R\$]?([\d\w]+))|(([\d]+)\([R\$]?([\d\w]+)\))))/i';
//$pattern = '/([\d\w]+:)?[\s]*((j\w*)[\s]*[R\$]?([\d\w]+))|((lui[\s]*[R\$]?([\d\w]+),[\s]*([0-9]+))|((\w+)[\s]*[R\$]?([\d\w]+),[\s]*(([R\$]?([\d\w]+),[\s]*[R\$]?([\d\w]+))|(([\d]+)\([R\$]?([\d\w]+)\)))))/i';
$pattern = '/([\d\w]+:)?[\s]*((j\w*)[\s]*([\$\d\w]+))|((lui[\s]*([\$\d\w]+),[\s]*([0-9]+))|((\w+)[\s]*([\$\d\w]+),[\s]*((([\$\d\w]+),[\s]*([\$\d\w]+))|(([\d]+)\(([\$\d\w]+)\)))))/i';
$replacement = '$1|$2|$3|$4|$5|$6|$7|$8|$9|$10|$11|$12|$13|$14|$15|$16|$17|$18|$19';

$mips_arr = explode("\n", $mips);
$jumps = array();
$counter = 0;
foreach ($mips_arr as $string)
{
	$replaced = preg_replace($pattern, $replacement, trim($string));
	$replaced_arr = explode('|', $replaced);
	if (strlen($replaced_arr[0]) > 0) // has a jump point
	{
		$jumps[substr($replaced_arr[0], 0, -2)] = $counter;
	}
	$counter += 4;
}
$counter = 0;
echo '<span style="display:none;">';
print_r($jumps);
echo '</span>';
$codes = '';
$total = '';
$total_hex = '';
$counters = '';
$testbench = '';
foreach ($mips_arr as $string)
{
	$code = '';
	$replaced = preg_replace($pattern, $replacement, trim($string));
	$replaced_arr = explode('|', $replaced);
	if (sizeof($replaced_arr) < 17)
	{
		$codes .= 'ERROR: line failure on "' . $string . '"<br />';
		continue;
	}
	if (strlen($replaced_arr[1]) > 0) // J type instruction
	{
		$code = $opcodes[$replaced_arr[2]]; // opcode
		if ($replaced_arr[2] == 'jr')
		{
			$reg = get_register($replaced_arr[3]);
			if ($reg === false)
			{
				$codes .= 'ERROR: register "' . $replaced_arr[3] . '" is invalid on line "' . $string . '"<br />';
				continue;
			}
			$code .= $reg . '000000000000000001000';
		}
		else
		{
			$code .= substr(sprintf("%028.28s", decbin((isset($jumps[$replaced_arr[3]]) ? $jumps[$replaced_arr[3]] : decbin($replaced_arr[3])))), 0, -2);
		}
	}
	else if (strlen($replaced_arr[5]) > 0) // lui instruction
	{
		$code = '00111100000';
		$rd = get_register($replaced_arr[6]);
		if ($rd === false)
		{
			$codes .= 'ERROR: register "' . $replaced_arr[6] . '" is invalid on line "' . $string . '"<br />';
			continue;
		}
		$code .= $rd;
		$code .= sprintf("%016.16s", decbin($replaced_arr[7]));
	}
	else 
	{
		$code = $opcodes[$replaced_arr[9]]; // opcode
		$rd = get_register($replaced_arr[10]);
		if ($rd === false)
		{
			$codes .= 'ERROR: register "' . $replaced_arr[10] . '" is invalid on line "' . $string . '"<br />';
			continue;
		}
		if (strlen($replaced_arr[15]) > 0) // memory loads and stores
		{
			$immediate = sprintf("%016.16s", decbin($replaced_arr[16]));
			$r1 = get_register($replaced_arr[17]);
			if ($r1 === false)
			{
				$codes .= 'ERROR: register "' . $replaced_arr[17] . '" is invalid on line "' . $string . '"<br />';
				continue;
			}
			$code .= $r1 . $rd . $immediate;
		}
		else // 3 register (or 2 register and one immediate) style commands
		{
			$r1 = get_register($replaced_arr[13]);
			$r2 = get_register($replaced_arr[14]);
			$immediate = (isset($jumps[$replaced_arr[14]]) ? substr(sprintf("%018.18s", decbin($jumps[$replaced_arr[14]] - $counter - 4)), 0, -2) : sprintf("%016.16s",  decbin($replaced_arr[14])));
			$h = sprintf("%05.5s", decbin($replaced_arr[14]));
			$builder = str_split($types[$replaced_arr[9]]);
			foreach ($builder as $key)
			{
				switch ($key)
				{
					case '0':
						$code .= '00000';
						break;
					case '1':
						if ($r1 === false)
						{
							$codes .= 'ERROR: register "' . $replaced_arr[13] . '" is invalid on line "' . $string . '"<br />';
							continue;
						}
						$code .= $r1;
						break;
					case '2':
						if ($r2 === false)
						{
							$codes .= 'ERROR: register "' . $replaced_arr[14] . '" is invalid on line "' . $string . '"<br />';
							continue;
						}
						$code .= $r2;
						break;
					case 'd':
						$code .= $rd;
						break;
					case 'f':
						$code .= $functcodes[$replaced_arr[9]];
						break;
					case 'h':
						$code .= $h;
						break;
					case 'i':
						$code .= $immediate;
						break;
					default:
						$codes .= 'ERROR: unknown builder key "' . $key . '" or invalid option for "' . $replaced_arr[9] . '" on line "' . $string . '"<br />';
						continue;
				}
			}
		}
	}
	$codes .= $code . '<br />';
	$total .= $code;
	$total_hex .= dechex(bindec($code)) . '<br />';
	$counters .= sprintf("%04.4s", dechex($counter)) . ': ' . '<br />';
	echo '<span style="display:none;">';
	print_r($replaced_arr);
	print_r($builder);
	echo '</span><br />';
	$testbench .= "     #10\n	reset			= 1'b1;\n    Load 			= 1'b1;\n	InstMemWrite	= 1'b1;\n	InstMemRead		= 1'b0;\n	DataMemWrite	= 1'b1;	\n	DataAddr		= 32'h". dechex($counter/4) .";\n	InstAddr		= 32'h". dechex($counter) .";\n	Data			= 32'h0;\n	Inst			= 32'h". dechex(bindec($code)) ."; // " . $string . "\n\n";
	$counter += 4;
}
//echo $total;
?>
<div style="position:absolute;overflow-y:auto;top:0;bottom:0;left:0;right:0; padding: 1%;font-family:'Courier New', Courier, monospace;">
	<div style="float: left; width: 100%; margin-bottom: 10px; border: none;"><input type="text" style="width: 100%;" value="<? print $total; ?>"></div>
	<div style="text-align:left;float: left; width: auto;padding-right: 5px;">
		<form action="" method="post">
		<textarea style="background-color: #C1C2FF;" name="mips" rows="40" cols="25"><? print $mips; ?></textarea><br />
		<input type="submit" value="Compile MIPS" />
		</form>
	</div>
	<div style="background-color: #C4FFC0;text-align:left;float: left; width: auto;padding: 5px;">
		<? print str_replace("\n", '<br />', $mips); ?>
	</div>
	<div style="background-color: #C4FFC0;;float: left; width: auto;padding: 5px;">
		<? print $counters; ?>
	</div>
	<div style="background-color: #C4FFC0;;float: left; width: auto;padding: 5px; ">
		<? print $codes; ?>
	</div>
	<div style="background-color: #C4FFC0;;float: left; width: auto;padding: 5px; ">
		<? print $total_hex; ?>
	</div>
	<div style="text-align:left;float: right; width: auto;padding-right: 5px;">
		<textarea style="background-color: #C1C2FF;" name="mips" rows="40" cols="35"><? print $testbench; ?></textarea>
	</div>
</div>

</body>
</html>