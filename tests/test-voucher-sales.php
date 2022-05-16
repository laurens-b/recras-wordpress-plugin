<?php
namespace Recras;

class VoucherSalesTest extends WordPressUnitTestCase
{
	function testShortcodeWithoutID(): void
	{
	    $content = $this->createPostAndGetContent('[recras-vouchers]');
        $this->assertNotFalse(strpos($content, "document.addEventListener('DOMContentLoaded'"), 'Should include an event listener');
        $this->assertNotFalse(strpos($content, 'RecrasVoucher(voucherOptions'), 'Should init the form');
	}

	function testInvalidIDinShortcode(): void
	{
	    $content = $this->createPostAndGetContent('[recras-vouchers id=foobar]');
        $this->assertEquals('Error: ID is not a number' . "\n", $content, 'Non-numeric ID should fail');
	}
}
