--TEST--
mat_mult() test
--SKIPIF--
<?php if (!extension_loaded("qolfuncs"))  echo "skip"; ?>
--FILE--
<?php
	// Multiplies the values of two 2D matrices or the values of a 2D matrix or row of the matrix with a scalar value.
	// Prototype:  mat_mult(array $a, array|float|int $b, [int $row = null])

	echo "Matrix multiply (integer):\n";
	$a = array(
		array(1, 2),
		array(3, 4)
	);

	$b = array(
		array(5, 6),
		array(7, 8)
	);

	$c = array(
		array($a[0][0] * $b[0][0] + $a[0][1] * $b[1][0], $a[0][0] * $b[0][1] + $a[0][1] * $b[1][1]),
		array($a[1][0] * $b[0][0] + $a[1][1] * $b[1][0], $a[1][0] * $b[0][1] + $a[1][1] * $b[1][1])
	);

	var_dump(mat_mult($a, $b));
	var_dump($c);
	echo "\n";

	echo "Matrix multiply (double):\n";
	$a = array(
		array(1.1, 2.1),
		array(3.1, 4.1)
	);

	$b = array(
		array(5, 6),
		array(7, 8)
	);

	$c = array(
		array($a[0][0] * $b[0][0] + $a[0][1] * $b[1][0], $a[0][0] * $b[0][1] + $a[0][1] * $b[1][1]),
		array($a[1][0] * $b[0][0] + $a[1][1] * $b[1][0], $a[1][0] * $b[0][1] + $a[1][1] * $b[1][1])
	);

	var_dump(mat_mult($a, $b));
	var_dump($c);
	echo "\n";

	echo "Scalar multiply (integer):\n";
	$a = array(
		array(1, 2),
		array(3, 4)
	);

	$b = 5;

	$c = array(
		array($a[0][0] * $b, $a[0][1] * $b),
		array($a[1][0] * $b, $a[1][1] * $b)
	);

	var_dump(mat_mult($a, $b));
	var_dump($c);

	$c = array(
		array($a[0][0], $a[0][1]),
		array($a[1][0] * $b, $a[1][1] * $b)
	);

	var_dump(mat_mult($a, $b, 1));
	var_dump($c);
	echo "\n";

	echo "Scalar multiply (double):\n";
	$a = array(
		array(1, 2),
		array(3, 4)
	);

	$b = 5.1;

	$c = array(
		array($a[0][0] * $b, $a[0][1] * $b),
		array($a[1][0] * $b, $a[1][1] * $b)
	);

	var_dump(mat_mult($a, $b));
	var_dump($c);

	$c = array(
		array($a[0][0], $a[0][1]),
		array($a[1][0] * $b, $a[1][1] * $b)
	);

	var_dump(mat_mult($a, $b, 1));
	var_dump($c);
	echo "\n";

	// Makes a number of assumptions about the inputs for this function.
	function mat_mult_matrixonly_userland($a, $b)
	{
		$c = array();

		$arows = count($a);
		$acols = count($b);
		$bcols = count($b[0]);

		for ($i = 0; $i < $arows; $i++)
		{
			$tmpa = $a[$i][0];
			$b2 = &$b[0];

			$c[$i] = array();
			$c2 = &$c[$i];

			for ($j = 0; $j < $bcols; $j++)
			{
				$c2[$j] = $tmpa * $b2[$j];
			}

			for ($k = 1; $k < $acols; $k++)
			{
				$tmpa = $a[$i][$k];
				$b2 = &$b[$k];

				for ($j = 0; $j < $bcols; $j++)
				{
					$c2[$j] += $tmpa * $b2[$j];
				}
			}
		}

		return $c;
	}

	echo "NxM multiply (integer):\n";
	$a = array(
		array(1, 2, 3),
		array(4, 5, 6),
		array(7, 8, 9),
		array(10, 11, 12)
	);

	$b = array(
		array(1, 2, 3, 4, 5),
		array(6, 7, 8, 9, 10),
		array(11, 12, 13, 14, 15)
	);

	$c = mat_mult_matrixonly_userland($a, $b);
	$c2 = mat_mult($a, $b);

	var_dump(count($c) === count($c2));
	var_dump(count($c[0]) === count($c2[0]));
	var_dump(serialize($c) === serialize($c2));
	echo "\n";

	echo "NxM multiply (double):\n";
	$a = array(
		array(1.1, 2.1, 3.1),
		array(4.1, 5.1, 6.1),
		array(7.1, 8.1, 9.1),
		array(10.1, 11.1, 12.1)
	);

	$b = array(
		array(1, 2, 3, 4, 5),
		array(6, 7, 8, 9, 10),
		array(11, 12, 13, 14, 15)
	);

	$c = mat_mult_matrixonly_userland($a, $b);
	$c2 = mat_mult($a, $b);

	var_dump(count($c) === count($c2));
	var_dump(count($c[0]) === count($c2[0]));
	var_dump(serialize($c) === serialize($c2));
	echo "\n";
?>
--EXPECT--
Matrix multiply (integer):
array(2) {
  [0]=>
  array(2) {
    [0]=>
    int(19)
    [1]=>
    int(22)
  }
  [1]=>
  array(2) {
    [0]=>
    int(43)
    [1]=>
    int(50)
  }
}
array(2) {
  [0]=>
  array(2) {
    [0]=>
    int(19)
    [1]=>
    int(22)
  }
  [1]=>
  array(2) {
    [0]=>
    int(43)
    [1]=>
    int(50)
  }
}

Matrix multiply (double):
array(2) {
  [0]=>
  array(2) {
    [0]=>
    float(20.2)
    [1]=>
    float(23.4)
  }
  [1]=>
  array(2) {
    [0]=>
    float(44.2)
    [1]=>
    float(51.4)
  }
}
array(2) {
  [0]=>
  array(2) {
    [0]=>
    float(20.2)
    [1]=>
    float(23.4)
  }
  [1]=>
  array(2) {
    [0]=>
    float(44.2)
    [1]=>
    float(51.4)
  }
}

Scalar multiply (integer):
array(2) {
  [0]=>
  array(2) {
    [0]=>
    int(5)
    [1]=>
    int(10)
  }
  [1]=>
  array(2) {
    [0]=>
    int(15)
    [1]=>
    int(20)
  }
}
array(2) {
  [0]=>
  array(2) {
    [0]=>
    int(5)
    [1]=>
    int(10)
  }
  [1]=>
  array(2) {
    [0]=>
    int(15)
    [1]=>
    int(20)
  }
}
array(2) {
  [0]=>
  array(2) {
    [0]=>
    int(1)
    [1]=>
    int(2)
  }
  [1]=>
  array(2) {
    [0]=>
    int(15)
    [1]=>
    int(20)
  }
}
array(2) {
  [0]=>
  array(2) {
    [0]=>
    int(1)
    [1]=>
    int(2)
  }
  [1]=>
  array(2) {
    [0]=>
    int(15)
    [1]=>
    int(20)
  }
}

Scalar multiply (double):
array(2) {
  [0]=>
  array(2) {
    [0]=>
    float(5.1)
    [1]=>
    float(10.2)
  }
  [1]=>
  array(2) {
    [0]=>
    float(15.3)
    [1]=>
    float(20.4)
  }
}
array(2) {
  [0]=>
  array(2) {
    [0]=>
    float(5.1)
    [1]=>
    float(10.2)
  }
  [1]=>
  array(2) {
    [0]=>
    float(15.3)
    [1]=>
    float(20.4)
  }
}
array(2) {
  [0]=>
  array(2) {
    [0]=>
    int(1)
    [1]=>
    int(2)
  }
  [1]=>
  array(2) {
    [0]=>
    float(15.3)
    [1]=>
    float(20.4)
  }
}
array(2) {
  [0]=>
  array(2) {
    [0]=>
    int(1)
    [1]=>
    int(2)
  }
  [1]=>
  array(2) {
    [0]=>
    float(15.3)
    [1]=>
    float(20.4)
  }
}

NxM multiply (integer):
bool(true)
bool(true)
bool(true)

NxM multiply (double):
bool(true)
bool(true)
bool(true)
