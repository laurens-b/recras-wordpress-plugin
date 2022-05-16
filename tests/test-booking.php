<?php
namespace Recras;

class OnlineBookingTest extends WordPressUnitTestCase
{
	function testShortcodeWithoutID(): void
	{
	    $content = $this->createPostAndGetContent('[recras-booking]');
        $this->assertNotFalse(strpos($content, '<iframe'), 'Booking without ID should work');
	}

	function testInvalidIDinShortcode(): void
	{
	    $content = $this->createPostAndGetContent('[recras-booking id=foobar]');
        $this->assertEquals('Error: ID is not a number' . "\n", $content, 'Non-numeric ID should fail');
	}

    function testRegularBooking(): void
    {
        $content = $this->createPostAndGetContent('[recras-booking id=3]');
        $this->assertNotFalse(strpos($content, '<iframe'), 'Regular booking should include an iframe');
    }

    function testAutoresize(): void
    {
        $content = $this->createPostAndGetContent('[recras-booking id=3]');
        $this->assertNotFalse(strpos($content, "window.addEventListener('message'"), 'Should include event listener by default');

        $content = $this->createPostAndGetContent('[recras-booking id=3 autoresize=0]');
        $this->assertFalse(strpos($content, "window.addEventListener('message'"), 'Should not include event listener when autoresize is off');
    }

    function testNewMethod(): void
    {
        $content = $this->createPostAndGetContent('[recras-booking id=3 use_new_library=1]');
        $this->assertNotFalse(strpos($content, "document.addEventListener('DOMContentLoaded'"), 'Using new method should include an event listener');
        $this->assertNotFalse(strpos($content, 'new RecrasBooking(bookingOptions);'), 'Using new method should init the form');
    }
}
