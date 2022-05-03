<?php
namespace Recras;

class AvailabilityTest extends WordPressUnitTestCase
{
	function testShortcodeWithoutID(): void
	{
        $content = $this->createPostAndGetContent('[recras-availability]');
        $this->assertEquals('Error: no ID set' . "\n", $content, 'Not setting ID should fail');
	}

	function testInvalidIDinShortcode(): void
	{
        $content = $this->createPostAndGetContent('[recras-availability id=foobar]');
        $this->assertEquals('Error: ID is not a number' . "\n", $content, 'Non-numeric ID should fail');
	}

    function testValidShortcode(): void
    {
        $content = $this->createPostAndGetContent('[recras-availability id=7]');
        $this->assertNotFalse(strpos($content, '<iframe'), 'Availability should include an iframe');
    }
}
