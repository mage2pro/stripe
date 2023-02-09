<?php
use Magento\Framework\Component\ComponentRegistrar as R;
R::register(R::MODULE, 'Dfe_Stripe', __DIR__);
# 2017-04-25, 2017-12-13
# Unfortunately, I have not found a way to make this code reusable among my modules.
# I tried to move this code to a `/lib` function like df_lib(), but it raises a «chicken and egg» problem,
# because Magento runs the `registration.php` scripts before any `/lib` functions are initalized,
# whereas the `/lib` functions are initalized from the `registration.php` scripts.
$base = dirname(__FILE__); /** @var string $base */
if (is_dir($libDir = "{$base}/lib")) { /** @var string $libDir */
	# 2015-02-06
	# array_slice removes «.» and «..».
	# https://php.net/manual/function.scandir.php#107215
	foreach (array_slice(scandir($libDir), 2) as $c) {require_once "{$libDir}/{$c}";}
}