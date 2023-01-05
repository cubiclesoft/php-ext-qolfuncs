--TEST--
mat_add() test
--SKIPIF--
<?php if (!extension_loaded("qolfuncs"))  echo "skip"; ?>
--FILE--
<?php
	// Adds the values of two 2D matrices.
	// Prototype:  array mat_add(array $a, array $b)

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

	var_dump(mat_add($a, $b));

	$b = array();

	var_dump(mat_add($a, $b));

	$b = array(
		array(1, 1, 1, 1),
		array(1, 1, 1)
	);

	var_dump(mat_add($a, $b));

	$b = array(
		array(0.1, 0.1, 0.1, 0.1),
		array(0.1, 0.1, 0.1, 0.1),
		array(0.1, 0.1, 0.1, 0.1)
	);

	var_dump(mat_add($a, $b));
	var_dump($a);
?>
--EXPECT--
array(3) {
  [0]=>
  array(4) {
    [0]=>
    int(2)
    [1]=>
    int(4)
    [2]=>
    int(4)
    [3]=>
    int(6)
  }
  [1]=>
  array(4) {
    [0]=>
    int(6)
    [1]=>
    int(8)
    [2]=>
    int(8)
    [3]=>
    int(10)
  }
  [2]=>
  array(4) {
    [0]=>
    int(10)
    [1]=>
    int(12)
    [2]=>
    int(12)
    [3]=>
    int(14)
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
    int(2)
    [1]=>
    int(3)
    [2]=>
    int(4)
    [3]=>
    int(5)
  }
  [1]=>
  array(4) {
    [0]=>
    int(6)
    [1]=>
    int(7)
    [2]=>
    int(8)
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
    float(1.1)
    [1]=>
    float(2.1)
    [2]=>
    float(3.1)
    [3]=>
    float(4.1)
  }
  [1]=>
  array(4) {
    [0]=>
    float(5.1)
    [1]=>
    float(6.1)
    [2]=>
    float(7.1)
    [3]=>
    float(8.1)
  }
  [2]=>
  array(4) {
    [0]=>
    float(9.1)
    [1]=>
    float(10.1)
    [2]=>
    float(11.1)
    [3]=>
    float(12.1)
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
