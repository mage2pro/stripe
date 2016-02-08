<?php
use Magento\Framework\Component\ComponentRegistrar;
$name = implode('_', array_map(function($part) {
	return implode(array_map('ucfirst', explode('-', $part)));
}, array_slice(explode(DIRECTORY_SEPARATOR, __DIR__), -2, 2)));
ComponentRegistrar::register(ComponentRegistrar::MODULE, $name, __DIR__);