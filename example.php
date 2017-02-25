<?php
require 'OpenSimplexNoise.php';

$iWidth = 128;
$iHeight = 128;

$iSeed = 53317;
$iOctaveQ = 5;
$fFirtFreq = 0.025;

//_____________________________________________________________________________
// Generate noise (using multiples layers of noises)
// see www.redblobgames.com/maps/terrain-from-noise/ for more details

$aNoise = [];
$aOctave = [];
$aFreq = [];

// Generate noises with their octaves and frequency
$fFreq = $fFirtFreq;
for ($i = 0; $i < $iOctaveQ; $i++) {
	$aNoise[] = OpenSimplexNoise::createBySeed($iSeed+$i);
	$aOctave[] = 1/($i+1);
	$aFreq[] = $fFreq;
	
	$fFreq *= 2; 
}

// Add all the layers
$aNoiseValue = [];
for ($x = 0; $x < $iWidth; $x++) 
for ($y = 0; $y < $iHeight; $y++) {
	$aNoiseValue[$x.':'.$y] = 0;
	
	$fIntensity = 1.0;
	$fOctaveSum = 0;
	foreach( $aNoise as $i => $oNoise ) {
		$aNoiseValue[$x.':'.$y] += $oNoise->getValue2D(
			$x*$aFreq[$i],
			$y*$aFreq[$i]
		)*$aOctave[$i];
		
		$fOctaveSum += $aOctave[$i];
	}
	
	$aNoiseValue[$x.':'.$y] /= $fOctaveSum; 
}

$fNoiseValueMin = min($aNoiseValue);
$fNoiseValueMax = max($aNoiseValue);

//_____________________________________________________________________________
// Some color tools

function hue2rgb($p, $q, $t) {
	if ($t < 0) $t += 1;
	if ($t > 1) $t -= 1;
	if ($t < 1/6) return $p + ($q - $p) * 6 * $t;
	if ($t < 1/2) return $q;
	if ($t < 2/3) return $p + ($q - $p) * (2/3 - $t) * 6;
	return $p;
}

/**
 * Converts an HSL color value to RGB. Conversion formula
 * adapted from http://en.wikipedia.org/wiki/HSL_color_space.
 * Assumes h, s, and l are contained in the set [0, 1] and
 * returns r, g, and b in the set [0, 255].
 *
 * @param   Number  h       The hue
 * @param   Number  s       The saturation
 * @param   Number  l       The lightness
 * @return  Array           The RGB representation
 */
function hslToRgb($h, $s, $l) {
	$r; $g; $b;

	// achromatic
	if ($s == 0) 
		return [$l, $l, $l]; 
	
	$q = $l < 0.5 ? $l * (1 + $s) : $l + $s - $l * $s;
	$p = 2 * $l - $q;

	$r = hue2rgb($p, $q, $h + 1/3);
	$g = hue2rgb($p, $q, $h);
	$b = hue2rgb($p, $q, $h - 1/3);
	
	return [ $r * 255, $g * 255, $b * 255 ];
}

function _getColor( $gd, &$aColor, $r, $g, $b ) {
	$sKey = $r.':'.$g.':'.$b;
	if( !isset( $aColor[ $sKey ] ) )
		$aColor[ $sKey ] = imagecolorallocate($gd, $r, $g, $b);

	return $aColor[ $sKey ];
}

//_____________________________________________________________________________
// Generate image

$gd = imagecreatetruecolor($iWidth, $iHeight);

$aColor = [];

for ($x = 0; $x < $iWidth; $x++) 
for ($y = 0; $y < $iHeight; $y++) {
	
	$f = $aNoiseValue[$x.':'.$y];
	
	// convert [ min, max ] to [ 0.0, 1.0 ]
	$f = ($f-$fNoiseValueMin)/($fNoiseValueMax-$fNoiseValueMin);
	
	$aRGB = hslToRgb( $f, 0.5, 0.5 );
	
	$iColorId = _getColor($gd, $aColor, $aRGB[0], $aRGB[1], $aRGB[2] );
	
	imagesetpixel($gd, $x, $y, $iColorId );
}

//_____________________________________________________________________________
// Render

header('Content-Type: image/png');
imagepng($gd);
