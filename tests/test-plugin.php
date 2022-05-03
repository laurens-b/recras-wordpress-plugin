<?php
namespace Recras;

class PluginTest extends WordPressUnitTestCase
{
    function testTooLongSubdomain(): void
    {
        $plugin = new Settings();
        $result = $plugin->sanitizeSubdomain('ThisSubdomainIsLongerThanAllowedButDoesNotContainAnyInvalidCharacters');
        $this->assertFalse($result, 'Too long subdomain should be invalid');
    }

    function testInvalidSubdomain(): void
    {
        $plugin = new Settings();
        $result = $plugin->sanitizeSubdomain('foo@bar');
        $this->assertFalse($result, 'Subdomain with invalid characters should be invalid');
    }

    function testValidSubdomain(): void
    {
        $plugin = new Settings();
        $result = $plugin->sanitizeSubdomain('demo');
        $this->assertEquals('demo', $result, 'Valid subdomain should be valid');
    }
}
