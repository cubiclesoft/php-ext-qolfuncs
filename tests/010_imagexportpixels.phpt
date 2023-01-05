--TEST--
imageexportpixels() test
--SKIPIF--
<?php if (!extension_loaded("qolfuncs") || !extension_loaded("gd"))  echo "skip"; ?>
--FILE--
<?php
	// Export the colors/color indexes of a range of pixels as an array.
	// Prototype:  array imageexportpixels(resource im, int x, int y, int width, int height)

	$img = imagecreatetruecolor(128, 128);
	imagealphablending($img, false);
	imagesavealpha($img, true);

	$pixels = array();
	for ($y = 0; $y < 128; $y++)
	{
		$row = array();
		for ($x = 0; $x < 128; $x++)
		{
			$color = rand() & 0x7FFFFFFF;

			imagesetpixel($img, $x, $y, $color);

			$row[] = $color;
		}

		$pixels[] = $row;
	}

	var_dump(serialize(imageexportpixels($img, 0, 0, 128, 128)) === serialize($pixels));

	imagedestroy($img);
?>
--EXPECT--
bool(true)
