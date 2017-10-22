<?php
use Magento\Framework\Component\ComponentRegistrar as R;
R::register(R::MODULE, 'Dfe_Stripe', __DIR__);
// 2017-04-25
// К сожалению, прямолинейным образом вынести этот код
// в повторноиспользуемую функцию df_lib() не получается:
// в эту точку программы мы попадаем раньше инициализации повторноиспользуемой функции.
// @todo Надо подумать, как это сделать...
$base = dirname(__FILE__); /** @var string $base */
if (is_dir($libDir = "{$base}/lib")) { /** @var string $libDir */
	// 2015-02-06
	// array_slice removes «.» and «..».
	// http://php.net/manual/function.scandir.php#107215
	foreach (array_slice(scandir($libDir), 2) as $c) {require_once "{$libDir}/{$c}";}
}