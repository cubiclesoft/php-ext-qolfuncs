--TEST--
mat_sub() test
--SKIPIF--
<?php if (!extension_loaded("qolfuncs"))  echo "skip"; ?>
--FILE--
<?php
	// Subtracts the values of two 2D matrices.
	// Prototype:  array mat_sub(array $a, array $b)

	$a = array(
		array(1, 2, 3, 4),
		array(5, 6, 7, 8),
		array(9, 10, 11, 12)
	);

	$b = array(
		array(1, 2, 1, 2),
		array(1, 2, 1, 2),
		array(1, 2, 1, 2),
	);

	var_dump(mat_sub($a, $b));

	$b = array();

	var_dump(mat_sub($a, $b));

	$b = array(
		array(1, 1, 1, 1),
		array(1, 1, 1)
	);

	var_dump(mat_sub($a, $b));

	$b = array(
		array(0.1, 0.1, 0.1, 0.1),
		array(0.1, 0.1, 0.1, 0.1),
		array(0.1, 0.1, 0.1, 0.1)
	);

	var_dump(mat_sub($a, $b));
	var_dump($a);
?>
--EXPECT--
array(3) {
  [0]=>
  array(4) {
    [0]=>
    int(0)
    [1]=>
    int(0)
    [2]=>
    int(2)
    [3]=>
    int(2)
  }
  [1]=>
  array(4) {
    [0]=>
    int(4)
    [1]=>
    int(4)
    [2]=>
    int(6)
    [3]=>
    int(6)
  }
  [2]=>
  array(4) {
    [0]=>
    int(8)
    [1]=>
    int(8)
    [2]=>
    int(10)
    [3]=>
    int(10)
  }
}
array(3) {
  [0]=>
  array(4) {
    [0]=>
    int(1)
    [1]=>
    int(2)
    [2]=>
    int(3)
    [3]=>
    int(4)
  }
  [1]=>
  array(4) {
    [0]=>
    int(5)
    [1]=>
    int(6)
    [2]=>
    int(7)
    [3]=>
    int(8)
  }
  [2]=>
  array(4) {
    [0]=>
    int(9)
    [1]=>
    int(10)
    [2]=>
    int(11)
    [3]=>
    int(12)
  }
}
array(3) {
  [0]=>
  array(4) {
    [0]=>
    int(0)
    [1]=>
    int(1)
    [2]=>
    int(2)
    [3]=>
    int(3)
  }
  [1]=>
  array(4) {
    [0]=>
    int(4)
    [1]=>
    int(5)
    [2]=>
    int(6)
    [3]=>
    int(8)
  }
  [2]=>
  array(4) {
    [0]=>
    int(9)
    [1]=>
    int(10)
    [2]=>
    int(11)
    [3]=>
    int(12)
  }
}
array(3) {
  [0]=>
  array(4) {
    [0]=>
    float(0.9)
    [1]=>
    float(1.9)
    [2]=>
    float(2.9)
    [3]=>
    float(3.9)
  }
  [1]=>
  array(4) {
    [0]=>
    float(4.9)
    [1]=>
    float(5.9)
    [2]=>
    float(6.9)
    [3]=>
    float(7.9)
  }
  [2]=>
  array(4) {
    [0]=>
    float(8.9)
    [1]=>
    float(9.9)
    [2]=>
    float(10.9)
    [3]=>
    float(11.9)
  }
}
array(3) {
  [0]=>
  array(4) {
    [0]=>
    int(1)
    [1]=>
    int(2)
    [2]=>
    int(3)
    [3]=>
    int(4)
  }
  [1]=>
  array(4) {
    [0]=>
    int(5)
    [1]=>
    int(6)
    [2]=>
    int(7)
    [3]=>
    int(8)
  }
  [2]=>
  array(4) {
    [0]=>
    int(9)
    [1]=>
    int(10)
    [2]=>
    int(11)
    [3]=>
    int(12)
  }
}
