OpenSimplex PHP
===============

Translation from JAVA to PHP.
Original [JAVA version](https://gist.github.com/KdotJPG/b1270127455a94ac5d19)



Usage
-----

Quick example :

```php
$iSeed = 5462;
$oNoiseMaker = OpenSimplexNoise::createBySeed($iSeed);
/** 
 * The noise value at coordonates [0,0] for seed 5462
 * Always include between [-1.0,1.0] more likely to be between [-0.7,0.7]
 * @var $fNoiseValue float
 */
$fNoiseValue = $oNoiseMaker->->getValue2D( 0, 0 );
```

Typical use :

```php
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

// Add up all the layers
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
```

Result of example.php
---------------------

For seed : 53317, octave : 5 and frequency 0.025

![result](http://jeremyginer.github.io/img/example.s53317.o5.f0025.png)
