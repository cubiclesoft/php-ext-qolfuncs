--TEST--
imageimportpixels() test
--SKIPIF--
<?php if (!extension_loaded("qolfuncs") || !extension_loaded("gd"))  echo "skip"; ?>
--FILE--
<?php
	// Sets pixels to the specified colors in the 2D array.
	// Prototype:  bool imageimportpixels(resource im, int x, int y, array colors)

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

			$row[] = $color;
		}

		$pixels[] = $row;
	}

	var_dump(imageimportpixels($img, 0, 0, $pixels));
	var_dump(serialize(imageexportpixels($img, 0, 0, 128, 128)) === serialize($pixels));

	imagedestroy($img);
?>
--EXPECT--
bool(true)
bool(true)
